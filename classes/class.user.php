<?php

class user
{
    var $db;
    var $user_data = array();
	var $perms;
    
    function user( &$db )
    {
        if( is_object($db) )
        {
            $this->db = $db;
			
			$this->init_perms();
        }
    }
    
	function init_perms()
	{
		// HACK to store which sections of site are available. (permissions)
		$this->perms["MENU_EMAILS"] 			= 1;
		$this->perms["MENU_NEW_ACCT"] 			= 1 << 1;
		$this->perms["MENU_CREATE_INVOICE"] 	= 1 << 2;
		$this->perms["MENU_CFG_REGIONS"] 		= 1 << 3;
		$this->perms["MENU_DATABASE"] 			= 1 << 4;
		$this->perms["MENU_SD_CONTENT"] 		= 1 << 5;
		$this->perms["MENU_ADD_LICENSEES"] 		= 1 << 6;
		$this->perms["MENU_VIEW_LICENSEES"] 	= 1 << 7;
		$this->perms["MENU_ADD_CONTENT"] 		= 1 << 8;
		$this->perms["MENU_VIEW_CONTENT"] 		= 1 << 9;
		$this->perms["MENU_ADD_ARTICLES"] 		= 1 << 10;
		$this->perms["MENU_VIEW_ARTICLES"] 		= 1 << 11;
		$this->perms["MENU_ADD_DOMAIN_NAME"] 	= 1 << 12;
		$this->perms["MENU_VIEW_DOMAIN_NAMES"] 	= 1 << 13;
	}
	
	function save_perms( $id, $perms )
	{
		if( !empty($id) && is_numeric($perms) )
		{
			$sql = "UPDATE admin_users SET perms = $perms WHERE id = $id";
			$this->db->query($sql);
			
			return true;
		}
		
		return false;
	}
	
	function check_perm( $perm )
	{
		return ($this->user_data['perms'] & $this->perms[$perm]) ? true : false;
	}
	
    function login( $data )
    {
        // Verify the required login data is supplied
        if( !empty($data['username']) && !empty($data['password']) )
        {
            if( is_string($data['username']) && is_string($data['password']) )
            {
            	$sql = "SELECT * FROM user WHERE username = '%s' AND password = '%s' AND status = 1";
                $results = $this->db->query($sql, $data['username'], $data['password']);
		
				if( !empty($results) )
				{
					$this->user_data = $results[0];
					return true;
				}
            }
        }
        
        return false;
    }
    
    function create_user( $data )
    {
    	if( !empty($data['x_response_code']) && !empty($data['x_email']) && !empty($data['x_first_name']) && !empty($data['x_last_name']) )
    	{
    		$username = trim(strtolower(substr($data['x_first_name'], 0, 1) . substr($data['x_last_name'], 0, 10)));
    		
    		do 
    		{
    			$append = rand(1000, 9999);
    			
    			$sql = "SELECT * FROM user WHERE username = '%s'";
    			$this->db->query($sql, ($username . $append));
    			
    		} while( $this->db->get_affected_rows() ); // Check for db error? The infinite loop?
    		
    		$data['username'] = $username . $append;
    		$data['password'] = $this->generate_password(rand(5, 8));
            
    		$sql = "INSERT INTO user VALUES (null, '%s', '%s', '%s', NOW(), 1)";
    		$this->db->query($sql, $data['username'], $data['password'], $data['x_email']);
    	
    		if( $this->db->get_affected_rows() )
    		{
    			if( $this->login($data) )
    			{
    				return true;
    			}
    		}
    	}
    	
    	return false;
    }
    
    function send_login_details()
    {
    	if( !empty($this->user_data) && is_array($this->user_data) )
    	{
    		$headers = "From: test.com\r\n";
			$headers .= "Reply-To: test.com\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			
			$to = "info@test.com";
			$content = "<html><head></head><body><b>Thank you for signing up with test.com!</b><br><br>We have successfully billed your credit card $499.<br><br><b>Your Login Details:</b><br><b>Username:</b> {$this->user_data['username']}<br><b>Password:</b> {$this->user_data['password']}<br><br>Please login by visiting test.com<br><br><i>If you experience any problems please email info@test.com.</i></body></html>";
    		
			mail($this->user_data['email'], "Test Membership", $content, $headers);
    		
    		return true;
    	}
    	
    	return false;
    }
    
    function generate_password( $length )
    { 
    	$password = "";
    	
	    srand((double)microtime()*1000000); 
	     
	    $vowels = array("a", "e", "i", "o", "u"); 
	    $cons = array("b", "c", "d", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "u", "v", "w", "tr", 
	    "cr", "br", "fr", "th", "dr", "ch", "ph", "wr", "st", "sp", "sw", "pr", "sl", "cl"); 
	     
	    $num_vowels = count($vowels); 
	    $num_cons = count($cons); 
	     
	    for($i = 0; $i < $length; $i++){ 
	        $password .= $cons[rand(0, $num_cons - 1)] . $vowels[rand(0, $num_vowels - 1)]; 
	    } 
	    
	    $substr = substr($password, 0, $length);
	    
	    return $substr . (string)rand(100,999); 
	}  
}

?>