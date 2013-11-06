<?php

// API Response Constants
define("APPROVED", "1");
define("DECLINED", "2");
define("ERROR", "3");

class billing
{
	// API Credentials
	var $username = "user";
	var $password = "pass";
	var $api_url = "https://secure.merchantonegateway.com/api/transact.php";
	var $report_url = "https://secure.merchantonegateway.com/api/query.php";
	var $response_text;
	var $curl_error;
	
	var $errors = array();
	var $data = array();
	var $db;
	var $merchant_responses = array();
	
	function billing( &$db )
	{
		if( is_object($db) )
		{
			$this->db = $db;
		}
	}
	
	function retrieve_reports( $date_from, $date_end)
	{
		$post_data = array(
								"username" 			=> $this->username,
								"password" 			=> $this->password,
								"start_date" 		=> $date_from,
								"end_date" 			=> $date_end,
								//"condition" 		=> "complete", // Don't know if I need this?
						  );
		//echo "From: $date_from | End: $date_end\n\n";die();
		$response = $this->curl($post_data, $this->report_url, false);
												  
		return !empty($response) ? $response : ERROR;
	}

	function load_invoice( $id, $crypt )
	{
		if( !empty($id) && is_numeric($id) && !empty($crypt) )
		{
			$sql = "SELECT * FROM invoice WHERE id = $id AND crypt = '$crypt'";
			$results = $this->db->query($sql);
			$results = $results->fetch_assoc();
			
			if( is_array($results) && count($results) > 0 )
			{
				$this->data = $results;
				
				return true;
			}
		}
		
		return false;
	}
	
	function load_trans_data( $date_from, $date_end, $invoice_id = 0 )
	{
		if( !empty($date_from) && !empty($date_end) )
		{
			if( !empty($invoice_id) )
			{
				$where_sql = "AND invoice_id = $invoice_id";
			}
			
			$sql = "SELECT * FROM trans WHERE trans_date BETWEEN '$date_from' AND '$date_end' $where_sql";
			$results = $this->db->query($sql);
			$results = $results->fetch_assoc();
			
			if( is_array($results) && count($results) > 0 )
			{
				return $results;
			}
		}
		
		return false;
	}
	
	function delete_invoice( $id )
	{
		if( !empty($id) && is_numeric($id) )
		{
			$sql = "DELETE FROM invoice WHERE id = $id";
			
			if( $results = $this->db->query($sql) )
			{
				return true;
			}
		}

		return false;
	}
	
	function load_all_invoices()
	{
		$sql = "SELECT * FROM invoice ORDER BY date_created DESC";
		$results = $this->db->query($sql);
		
		if( !empty($results) )
		{
			$this->data['pending_payment_count'] = 0;
			while( $res = $results->fetch_assoc() )
			{
				$return_results[] = $res;
				
				if( $res['payment'] == "unpaid" )
					$this->data['pending_payment_count']++;
			}
			
			return $return_results;
		}
		
		return false;
	}
	
	function pay_invoice ( $data )
	{
		if( is_array($data) && count($data) > 0 && is_array($this->data) && count($this->data) > 0 )
		{
			$post_data = array(
									"type" 			=> "sale",
									"username" 		=> $this->username,
									"password" 		=> $this->password,
									"ccnumber" 		=> $data['cc'],
									"ccexp"			=> $data['expire_month'] . $data['expire_year'],
									"amount" 		=> $this->data['first_amount'],
									"cvv" 			=> $data['cvv'],
									"ipaddress"		=> $this->get_ip(),
									"firstname"		=> $data['first_name_card'],
									"lastname"		=> $data['last_name_card'],
									"address1" 		=> $data['address'],
									"address2" 		=> $data['address2'],
									"city" 			=> $data['city'],
									"state" 		=> $data['state'],
									"zip" 			=> $data['zip'],
									"country" 		=> "US",
									"phone" 		=> $data['contacts_phone'],
									"email" 		=> $data['email'],
									"orderid" 		=> $this->data['id'], // This is the invoice id to link customer to transactions
							  );

			$response = $this->curl($post_data, $this->api_url);
			$this->response_text = $response['responsetext'];
													  
			$this->insert_transaction($this->data['id'], $this->data['first_amount'], date("Y-m-d H:i:s"), "sale", $response['response'], $response, $response['transactionid']);										  
													  
			return !empty($response['response']) ? $response['response'] : ERROR;
		}
		
		return false;
	}
	
	function setup_recurring ( $data )
	{
		if( is_array($data) && count($data) > 0 && is_array($this->data) && count($this->data) > 0 )
		{
			$post_data = array(
									"type" 			=> "add_recurring",
									"username" 		=> $this->username,
									"password" 		=> $this->password,
									"ccnumber" 		=> $data['cc'],
									"ccexp"			=> $data['expire_month'] . $data['expire_year'],
									"amount" 		=> $this->data['lock_amount'],
									"start_date"	=> date('Ymd', strtotime (date("Y-m-d") ." + 30 days")),
									"cvv" 			=> $data['cvv'],
									"product_sku_1" => $this->data['sku_plan'],
									"ipaddress"		=> $this->get_ip(),
									"firstname"		=> $data['first_name_card'],
									"lastname"		=> $data['last_name_card'],
									"address1" 		=> $data['address'],
									"address2" 		=> $data['address2'],
									"city" 			=> $data['city'],
									"state" 		=> $data['state'],
									"zip" 			=> $data['zip'],
									"country" 		=> "US",
									"phone" 		=> $data['contacts_phone'],
									"email" 		=> $data['email'],
									"orderid" 		=> $this->data['id'], // This is the invoice id to link customer to transactions
							  );
							  
			$response = $this->curl($post_data, $this->api_url);
			$this->response_text = $response['responsetext'];
			
			$this->insert_transaction($this->data['id'], "0.00", date("Y-m-d H:i:s"), "setup_sku", $response['response'], $response, $response['transactionid']);
			
			return !empty($response['response']) ? $response['response'] : ERROR;
		}
		
		return false;
	}
	
	function update_paid_status($status)
	{
		if( !empty($status) && !empty($this->data) && in_array($status, array("paid", "unpaid")) )
		{
			$sql = "UPDATE invoice SET payment = '$status', date_paid = NOW() WHERE id = {$this->data['id']}";
			$this->db->query($sql);
		}
	}
	
	function insert_transaction( $invoice_id, $amount, $trans_date, $trans_type, $trans_result, $merchant_info, $processor_id = '', $ip = '0.0.0.0' )
	{
		// Make sure we've had previous transactions in this object
		if( !empty($invoice_id) && !empty($amount) && !empty($trans_type) && !empty($trans_result) && !empty($processor_id) && !empty($merchant_info) )
		{
			$serialized_info = serialize($merchant_info);
			
			if( empty($ip) )
			{
				$ip = $this->get_ip();
			}
			
			$sql = "INSERT INTO transactions ".
				   "VALUES (null, $invoice_id, '$amount', '$trans_type', '$trans_result', '$trans_date', '$serialized_info', '$processor_id', INET_ATON('$ip'))";
			
			$this->db->query($sql);
			
			if( $this->db->insert_id )
			{
				return $this->db->insert_id;
			}
		}
		
		return false;
	}
	
	function get_ip()
	{
		if( !empty($_SERVER['HTTP_CLIENT_IP']))  //check ip from share internet
	    {
	      $ip=$_SERVER['HTTP_CLIENT_IP'];
	    }
	    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
	    {
	      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	    }
	    else
	    {
	      $ip=$_SERVER['REMOTE_ADDR'];
	    }
	    
	    return !empty($ip) ? $ip : '0.0.0.0';
	}
	
	function curl( $data, $url, $parse_response = true )
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
 
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_POST, 1);
		
		
		if (!$data) 
		{
			return ERROR;
		}
		
		if( $parse_response )
		{
			$data = curl_exec($ch);
			
			$data = explode("&",$data);
			for( $i=0; $i<count($data); $i++ )
			{
				$rdata = explode("=",$data[$i]);
				$responses[$rdata[0]] = $rdata[1];
			}
		
			curl_close($ch);
			unset($ch);
		
			$this->merchant_responses[] = $responses;
			
			return $responses;
		}
		else
		{
			$data = (string)curl_exec($ch);
			
			curl_close($ch);
			unset($ch);
			
			return $data;
		}
	}
	
	// Save POST invoice data to DB
	function save_invoice( $data, &$user = null )
	{
		if( is_array($data) && count($data) > 0 )
		{
			$this->data = $data;
		
			// Fix domain
			$this->data['redirect_domain'] = str_replace("http://", "", trim($this->data['redirect_domain'], "/"));
		
			$fields = array(
								"region" 			=> "numeric",
								"domain" 			=> "url",
								"redirect_domain" 	=> "url",
								"duration" 			=> "numeric",
								"lock_amount" 		=> "currency",
								"first_amount" 		=> "currency",
								"business_name" 	=> "varchar",
								"first_name" 		=> "varchar",
								"last_name" 		=> "varchar",
								"address" 			=> "varchar",
								"address2" 			=> "varchar",
								"city" 				=> "varchar",
								"state" 			=> "state",
								"zip" 				=> "numeric",
								"business_phone" 	=> "phone",
								"contacts_phone" 	=> "phone",
								"fax" 				=> "phone",
								"email" 			=> "email",
								"sku_plan"			=> "numeric",
						   );
						   
		   $required = array("region", "domain", "contacts_phone", "first_name", "last_name", "redirect_domain", "lock_amount", "first_amount", "email", "sku_plan");
						   
		   // Verify all fields are present
		   foreach( $fields as $field => $type )
		   {
		   		$flag_error = false;
		   		
		   		$this->data[$field] = addslashes(strip_tags(trim($this->data[$field])));
		   		
		   		if( !empty($this->data[$field]) )
		   		{
		   			switch( $type )
		   			{
		   				case 'numeric':
		   					if( !is_numeric($this->data[$field]) )
		   						$flag_error = true;
	   					break;
	   					
		   				case 'url':
		   				case 'currency':
	   						if( !strstr($this->data[$field], ".") )
	   							$flag_error = true;
	   					break;
	   					
		   				case 'varchar':
		   					
	   					break;
	   					
		   				case 'state':
		   					if( strlen($this->data[$field]) != 2 )
		   						$flag_error = true;
	   					break;
	   					
		   				case 'phone':
		   					if( strlen($this->data[$field]) < 7 )
		   						$flag_error = true;
	   					break;
	   					
		   				case 'email':
		   					if( !strstr($this->data[$field], "@") || !strstr($this->data[$field], ".") )
		   						$flag_error = true;
	   					break;
		   			}
		   			
		   			if( $flag_error )
		   			{
		   				$this->errors[] = "Field $field is not formatted correctly";
		   			}
		   		}
		   		else 
		   		{
		   			// Make sure it's in the required array before setting error
		   			if( in_array($field, $required) )
		   			{
		   				$this->errors[] = "Missing $field field";   			
		   			}
		   		}
		   }
		   
		   // If there were no errors then save to database
		   if( empty($this->errors) )
		   {
		   		extract($this->data, EXTR_OVERWRITE);
		   		
		   		$this->data['crypt'] = md5("$email|$region|$domain");
		   		
		   		// If trans id exists than this will probably be an update
		   		$id = !empty($this->data['id']) ? $this->data['id'] : "null";
				
				// When we re-save the invoice on the invoice page, we don't want to update the date_created to NOW()
		   		$date_created = !empty($this->data['date_created']) ? "'{$this->data['date_created']}'" : "NOW()";
				
				// If we sent the user object, then use the admin_user id to track sales data as well as the date
				if( !empty($sales_id) ) // If it's already saved in the database, don't overwrite
				{
					$admin_id = $sales_id;
					$admin_date = "'$sales_date'";
				}
				elseif( !is_null($user) )
				{
					$admin_id = $user->user_data['id'];
					$admin_date = "NOW()";
				}
				else
				{
					$admin_id = 0;
					$admin_date = "'0000-00-00 00:00:00'";
				}
				
				// payment default is unpaid
				if( empty($payment) )
				{
					$payment = "unpaid";
				}
		   		
		   		$sql = "INSERT INTO invoice VALUES (".
   					   "$id, $date_created, $region, '$duration', '$domain', '$redirect_domain', '$lock_amount', '$first_amount', '$business_name', '$first_name', '$last_name', '$address', '$address2', ".
   					   "'$city', '$state', '$zip', '$business_phone', '$contacts_phone', '$fax', '$email', $sku_plan, '{$this->data['crypt']}', '$payment', '0000-00-00 00:00:00', $admin_id, $admin_date) ";
   					   
   				// ON DUPE KEY SQL
   				$sql .= "ON DUPLICATE KEY UPDATE date_created = VALUES(date_created), region = VALUES(region), duration = VALUES(duration), domain = VALUES(domain), redirect_domain = VALUES(redirect_domain), ".
   						"lock_amount = VALUES(lock_amount), first_amount = VALUES(first_amount), business_name = VALUES(business_name), first_name = VALUES(first_name), last_name = VALUES(last_name), ".
   						"address = VALUES(address), address2 = VALUES(address2), city = VALUES(city), state = VALUES(state), zip = VALUES(zip), business_phone = VALUES(business_phone), ".
   						"contacts_phone = VALUES(contacts_phone), fax = VALUES(fax), email = VALUES(email), sku_plan = VALUES(sku_plan), crypt = VALUES(crypt), payment = VALUES(payment), sales_id = VALUES(sales_id), sales_date = VALUES(sales_date)";

				if( $this->db->query($sql) )
				{
					// Only if id was not pre-existing do we save the new id
					if( $id == "null" )
					{
						$this->data['id'] = $this->db->insert_id;
					}
					
		   			return true;
				}
				else 
				{
					$this->errors[] = "Error trying to write invoice to database.";
					echo "<span style='color: red;'>" . $this->db->error . "</span><br>";
					echo "$sql<hr>";
				}
		   }
		}
		
		return false;
	}
	
	function domain_display( $domain, $dot_com = ".com" )
	{
		$domains = array(
							"test1.com" => "test1$dot_com",
							"test2.com" => "test2$dot_com",
						);

		return !empty($domains[$domain]) ? $domains[$domain] : $domain;
	}
	
	function domain_terms( $domain )
	{
		$domains = array(
							"test1.com" => array("type" => "Type1"),
							"test2.com" => array("type" => "Type2"),
						);

		return !empty($domains[$domain]) ? $domains[$domain] : $domain;
	}
	
	function send_thank_you()
    {
    	if( !empty($this->data) && is_array($this->data) )
    	{
    		extract($this->data, EXTR_OVERWRITE);
    		
    		$headers = "From: Billing@Company.com\r\n";
			$headers .= "Reply-To: Billing@Company.com\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			
			$to = "Billing@Company.com";
			$content = "<html><head></head><body>";

			$content .= "
							Thank You $first_name $last_name! Your Order Has Been Received & Accepted!
							<br><br>
							You are now the exclusive licensee for the $region_full region of {$this->domain_display($domain)}.
							<br><br>
							You will see a charge from \"Company.com\" on your credit card statement, in the amount of \$$first_amount.
							<br><br>
							The next billing date will be 30 days from today, in the amount of \$$lock_amount. 
							<br><br>
							This is a month to month agreement. If you wish to cancel your license with {$this->domain_display($domain)}, please contact us 7 days prior to your next billing date.
							<br><br>
							Please allow up to 24 hours for your website to appear. Some orders placed on Friday nights & weekends, may take a bit longer to process.
							<br><br>
							If you have any questions, please contact us anytime.
							<br><br>
							Sincerely,
							<br><br>
							Company
							info@Company.com<br>
							<a href=\"http://www.Company.com\">http://www.Company.com</a><br>
					   	";
			  
			$content .= "</body></html>";
    		
			if( mail($this->data['email'], "Re: {$this->domain_display($domain)} - Please Read", $content, $headers) )
			{
    			return true;
			}
    	}
    	
    	return false;
    }
	
    function send_purhcase_details($post_data)
    {
    	if( !empty($this->data) && is_array($this->data) && !empty($_POST) && is_array($_POST) )
    	{
    		extract($this->data, EXTR_OVERWRITE);
    		
    		$headers = "From: Billing@Company.com\r\n";
			$headers .= "Reply-To: Billing@Company.com\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			
			$to = "Billing@Company.com";
			$content = "<html><head></head><body>";

			$content .= "
							<h3>Company Purchase Details:</h3><br>
							<b>Name:</b> {$post_data['first_name_card']} {$post_data['last_name_card']}<br>
							<b>Region:</b> $region_full<br>
							<b>Domain:</b> $domain<br>
							<b>Website:</b> $redirect_domain<br>
							<b>First Month Amount:</b> \$$first_amount<br>
							<b>Lock Amount:</b> \$$lock_amount<br>
							<b>Duration:</b> $duration Months<br>
							<b>Business Name:</b> $business_name<br>
							<b>Address:</b> $address<br>
							<b>Address 2:</b> $address2<br>
							<b>City:</b> $city<br>
							<b>State:</b> $state<br>
							<b>Zip:</b> $zip<br>
							<b>Email:</b> $email<br>
							<b>Phone:</b> $contacts_phone<br>
							<b>SKU Plan #:</b> $sku_plan<br>
					   	";
			  
			$content .= "</body></html>";
    		
			if( mail("billing@Company.com", "Purchase", $content, $headers) )
			{
    			return true;
			}
    	}
    	
    	return false;
    }
    
	function send_invoice_details()
    {
    	if( !empty($this->data) && is_array($this->data) && !empty($this->data['id']) && !empty($this->data['crypt']) )
    	{
    		extract($this->data, EXTR_OVERWRITE);
    		
    		$headers = "From: Billing@Company.com\r\n";
			$headers .= "Reply-To: Billing@Company.com\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			
			$to = "Billing@Company.com";
			$content = "<html><head></head><body>".
					   "Hi $first_name $last_name,<br><br>".
					   "Please click the link below to complete the set up of your new campaign with {$this->domain_display($domain)}<br><br>".
					   "<a href='https://www.Company.com/billing/invoice.php?id={$this->data['id']}&c={$this->data['crypt']}&url=$domain'>Complete Invoice</a><br><br>".
					   "We truly look forward to working with you.<br><br>Sincerely,<br><br>".
					   "
					   		Company LLC
							info@Company.com<br>
							<a href=\"http://www.Company.com\">http://www.Company.com</a><br>
					   ".
					   "</body></html>";
    		
			mail($this->data['email'], "Re: $region_name {$this->domain_display($domain,'')}", $content, $headers);
			mail("billing@Company.com", "Re: $region_name {$this->domain_display($domain,'')}", $content, $headers);
    		
    		return true;
    	}
    	
    	return false;
    }
}

?>