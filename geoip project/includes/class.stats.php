<?php

class stats
{
	var $db;
	var $server;
	var $domain_id;
	var $region_id;
	var $page;
	
	// Stats Data
	var $data;
	var $total_visits;
	var $total_impressions;

	function stats( &$db, $server, $domain_id = null, $region_id = 0, $page = '' )
	{
		if( !empty($db) && is_object($db) )
		{
			$this->db = $db;
		}
 
		if( !empty($server) && is_array($server) )
		{
			$this->server = $server;
		}
		
		if( !empty($domain_id) )
		{
			$this->domain_id = $domain_id;
		}
		
		if( !empty($page) )
		{
			$this->page = $page;
		}
		
		if( !empty($region_id) && is_numeric($region_id) )
		{
			$this->region_id = region_id;
		}
	}
	
	function get_track_data( $type = '', $domain_id = null, $region_id = 0 )
	{
		$track_type = empty($type) ? 'all' : $type;
	
		if( empty($domain_id) )
		{
			return !empty($this->data[$track_type]) ? $this->data[$track_type] : 0;
		}
		elseif( empty($region_id) )
		{
			return !empty($this->data[$domain_id][$track_type]) ? $this->data[$domain_id][$track_type] : 0;
		}
		else
		{
			return !empty($this->data[$domain_id][$region_id][$track_type]) ? $this->data[$domain_id][$region_id][$track_type] : 0;
		}
	}
	
	function load_track_data( $date_from = '', $date_end = '' )
	{	
		$where_clause = array();
		
		// Limit to Domain domain_id
		if( !empty($this->domain_id) )
		{
			$where_clause[] = "domain_id = '{$this->domain_id}'";
		}
		
		if( !empty($date_from) && !empty($date_end) )
		{
			$where_clause[] = "date_hit BETWEEN '$date_from' AND '$date_end'";
		}
		
		// Build WHERE clause
		if( !empty($where_clause) )
		{
			$where_sql = "WHERE " . implode(" AND ", $where_clause);
		}

		$sql = "SELECT domain_id, region_id, type, COUNT(*) AS count ".
			   "FROM stat ".
			   "$where_sql ".
			   "GROUP BY domain_id, region_id, type";
			   
		$results = $this->db->query($sql);
		
		if( !empty($results) )
		{
			// Reset stats data
			$this->data = array();
			$this->total_visits = 0;
			$this->total_impressions = 0;
		
			while( $row = $results->fetch_assoc() )
			{
				$this->data['all'] += $row['count'];
				$this->data[$row['type']] += $row['count'];
				$this->data[$row['domain_id']]['all'] += $row['count'];
				$this->data[$row['domain_id']][$row['type']] += $row['count'];
				$this->data[$row['domain_id']][$row['region_id']]['all'] += $row['count'];
				$this->data[$row['domain_id']][$row['region_id']][$row['type']] += $row['count'];
			}
			
			return true;
		}
		
		return false;
	}
	
	function track( $region_id = 0, $page = "" )
	{
		global $debug;
		
		// Unfortunately, we don't know the region_id right until we track
		if( !empty($region_id) && is_numeric($region_id) )
		{
			$this->region_id = $region_id;
		}
		
		// Same with Page
		if( !empty($page) && is_string($page) )
		{
			$this->page = $page;
		}
		
		// Find a visit that has happend within the last 24 hours
		$sql = "SELECT COUNT(*) AS count FROM stat ".
			   "WHERE ip = INET_ATON('{$this->get_ip()}') ".
			   "AND type = 'visit' ".
			   "AND domain_id = '{$this->domain_id}' ".
			   "AND region_id = {$this->region_id} ".
			   "AND date_hit > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
		
        $this->db->mc_force = true;
		$results = $this->db->query($sql);
		
		if( !empty($results) )
		{			
			// If we found a row then a visit has already been recorded	
			if( !empty($results) && is_array($results) && $results[0]['count'] > 0 )
			{
				$type = "impression";
			}
			else
			{
				$type = "visit";
			}
			
			// Insert track
			$sql = "INSERT INTO stat ".
				   "VALUES(null, '{$type}', INET_ATON('{$this->get_ip()}'), '{$this->domain_id}', {$this->region_id}, '{$this->get_referrer()}', ".
				   "'{$this->page}', NOW())";
				
			$this->db->query($sql);
			
			return true;
		}
		
		return false;
	}
	
	function get_ip()
	{
		if( !empty($this->server['HTTP_CLIENT_IP']) )  //check ip from share internet
		{
			$ip = $this->server['HTTP_CLIENT_IP'];
		}
		elseif( !empty($this->server['HTTP_X_FORWARDED_FOR']) )    //to check ip is pass from proxy
		{
			$ip = $this->server['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			$ip = $this->server['REMOTE_ADDR'];
		}
		
		return $ip;
	}
	
	function get_referrer()
	{
		// Get the server HTTP Referer
		$referral = $this->server['HTTP_REFERER'];
		
		// All to lowercase
		$referral = strtolower($referral);
		
		return $referral;
	}
}

?>