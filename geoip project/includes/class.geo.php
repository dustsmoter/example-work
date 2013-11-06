<?php

class geo
{
    var $db;
    var $server;
    
    var $user_ip;
    var $user_loc;
    var $region_data = array();
    
    var $bound_box = 1; // How far to look from user coordinates (in degrees and roughly 50 miles)
    
    public function __construct( &$db, $server ) 
    {
        if( !empty($db) && !empty($server) )
        {
            $this->db = $db;
            $this->server = $server;
            $this->user_ip = $this->get_user_ip();
            $this->user_loc = $this->get_user_location();
        }
    }
    
    function get_user_location()
    {
        $sql = "SELECT LATITUDE, LONGITUDE, ZIPCODE, REGION FROM ip_region WHERE INET_ATON('%1\$s') <= IP_TO AND INET_ATON('%1\$s') >= IP_FROM LIMIT 1";
        $this->db->mc_ttl = 86400;
        $results = $this->db->query($sql, $this->user_ip);
        
        if( !empty($results) ) 
        {
            return $results[0];
        }
        
        return false;
    }
    
    function get_sub_region( $subdomain )
    {
        $sql = "SELECT * FROM region WHERE subdomain = '$subdomain'";
        $this->db->mc_ttl = 86400;
        $results = $this->db->query($sql);
        
        if( !empty($results) )
        {
            $this->region_data = $results[0];
            
            // Save region lock
            $this->save_region_lock($results[0]['id']);
        }
        else // Didn't work, just get their nearest region
        {
             $this->get_nearest_region();
            // Here we might pass a value to get_nearest_region ignoring save_region_lock. Depends on how we want that behaviour
        }
    }
    
    function get_nearest_region()
    {
        global $config;
        
        if( !$results = $this->get_region_lock() )
        {
            $sql = "SELECT r.*, ".
                   "(((acos(sin(({$this->user_loc['LATITUDE']} * pi()/180)) * sin((LATITUDE*pi()/180))+cos(({$this->user_loc['LATITUDE']} * pi()/180)) * cos((LATITUDE*pi()/180)) * cos((({$this->user_loc['LONGITUDE']} - LONGITUDE)*pi()/180))))*180/pi())*60*1.1515) as distance ".
                   "FROM ip_region ir ".
                   "JOIN region r ON r.domain_id = {$config['id']} AND r.state = '{$this->user_loc['REGION']}' AND r.city = ir.CITY ".
                   "WHERE ir.REGION = '{$this->user_loc['REGION']}' ".
                   "AND ir.LATITUDE BETWEEN ({$this->user_loc['LATITUDE']} - {$this->bound_box}) AND ({$this->user_loc['LATITUDE']} + {$this->bound_box}) ".
                   "AND ir.LONGITUDE BETWEEN ({$this->user_loc['LONGITUDE']} - {$this->bound_box}) AND ({$this->user_loc['LONGITUDE']} + {$this->bound_box}) ".
                   "GROUP BY ir.CITY ".
                   //"HAVING distance <= 100 ".
                   "ORDER BY distance ASC ".
                   "LIMIT 1";
            
            $this->db->mc_ttl = 86400;
            $results = $this->db->query($sql);
            
            // Save their region
            if( !empty($results) )
                $this->save_region_lock($results[0]['id']);
        }
        
        if( !empty($results) ) 
        {
            $this->region_data = $results[0];
            return true;
        }
        
        return false;
    }
    
    function save_region_lock( $region_id )
    {
        global $config;
        
        $sql = "INSERT INTO ip_region_lock (ip, domain_id, region_id) ".
               "VALUES (" . ip2long($this->user_ip) . ", {$config['id']}, $region_id) ".
               "ON DUPLICATE KEY UPDATE ip = VALUES(ip), domain_id = VALUES(domain_id), region_id = VALUES(region_id)";

        $this->db->query($sql);
        
        return true;
    }
    
    function get_region_lock()
    {
        global $config;
        
        $sql = "SELECT r.* ".
               "FROM ip_region_lock irl ".
               "JOIN region r ON r.id = irl.region_id ".
               "WHERE irl.ip = " . ip2long($this->user_ip) . " AND irl.domain_id = {$config['id']}";
               
        $this->db->mc_force = true;
        $results = $this->db->query($sql);
        
        if( !empty($results) )
        {
            return $results;
        }
        
        return false;
    }
    
    function get_user_ip()
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
}

?>
