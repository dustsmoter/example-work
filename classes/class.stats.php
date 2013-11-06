<?php

class stats
{
	var $db;
	var $server;
	var $url;
	var $region;
	var $page;
	
	// Stats Data
	var $data;
	var $total_visits;
	var $total_impressions;

	function stats( &$db, $server, $url = '', $region = 0, $page = '' )
	{
		if( !empty($db) && is_object($db) )
		{
			$this->db = $db;
		}
		
		if( !empty($server) && is_array($server) )
		{
			$this->server = $server;
		}
		
		if( !empty($url) )
		{
			$this->url = $url;
		}
		
		if( !empty($page) )
		{
			$this->page = $page;
		}
		
		if( !empty($region) && is_numeric($region) )
		{
			$this->region = region;
		}
	}
	
	function get_track_data( $type = '', $url = '', $region = 0 )
	{
		$track_type = empty($type) ? 'all' : $type;
	
		if( empty($url) )
		{
			return !empty($this->data[$track_type]) ? $this->data[$track_type] : 0;
		}
		elseif( empty($region) )
		{
			return !empty($this->data[$url][$track_type]) ? $this->data[$url][$track_type] : 0;
		}
		else
		{
			return !empty($this->data[$url][$region][$track_type]) ? $this->data[$url][$region][$track_type] : 0;
		}
	}
	
	function load_track_data( $date_from = '', $date_end = '' )
	{	
		$where_clause = array();
		
		// Limit to URL
		if( !empty($this->url) )
		{
			$where_clause[] = "url = '{$this->url}'";
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

		$sql = "SELECT url, region, type, COUNT(*) AS count ".
			   "FROM statistics ".
			   "$where_sql ".
			   "GROUP BY url, region, type";
			   
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
				$this->data[$row['url']]['all'] += $row['count'];
				$this->data[$row['url']][$row['type']] += $row['count'];
				$this->data[$row['url']][$row['region']]['all'] += $row['count'];
				$this->data[$row['url']][$row['region']][$row['type']] += $row['count'];
			}
			
			return true;
		}
		
		return false;
	}
	
	function track( $region = 0, $page = "" )
	{
		global $debug;
		
		// Unfortunately, we don't know the region right until we track
		if( !empty($region) && is_numeric($region) )
		{
			$this->region = $region;
		}
		
		// Same with Page
		if( !empty($page) && is_string($page) )
		{
			$this->page = $page;
		}
		
		// Find a visit that has happend within the last 24 hours
		$sql = "SELECT COUNT(*) AS count FROM statistics ".
			   "WHERE ip = INET_ATON('{$this->get_ip()}') ".
			   "AND type = 'visit' ".
			   "AND url = '{$this->url}' ".
			   "AND region = {$this->region} ".
			   "AND date_hit > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
		
		$results = $this->db->query($sql);
		
		if( is_object($results) )
		{
			$results = $results->fetch_assoc();
			
			// If we found a row then a visit has already been recorded	
			if( !empty($results) && is_array($results) && $results['count'] > 0 )
			{
				$type = "impression";
			}
			else
			{
				$type = "visit";
			}
			
			// Insert track
			$sql = "INSERT INTO statistics ".
				   "VALUES(null, '{$type}', INET_ATON('{$this->get_ip()}'), '{$this->url}', {$this->region}, '{$this->get_referrer}', ".
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
		
		// Only get the referral website
		$referral = explode ("/", $referral);
		$referral = $referral[2];
		
		return $referral;
	}
}

?>