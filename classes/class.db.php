<?php

class database
{
    private $mysql;                 // Mysqli object
    private $last_query;            // Last query ran
    private $mc;                    // Memcache object
    private $mc_ttl_default = 0;    // Default time to live
    public $mc_ttl = 0;             // Time to live for cached queries
    public $mc_force = false;       // Force a recache of next query

    function __construct( &$memcache, $host, $database_name, $username, $password )
    {
        if( is_object($memcache) )
        {
            $this->mc = $memcache;

            $this->mysql = new mysqli($host, $username, $password, $database_name);

            if ( mysqli_connect_errno() ) 
            {
                echo "Cannot connect to database. Please try again later.";
                exit();
            }
        }
        else
        {
            echo "Warning: Memcache not an object.";
        }
    }

    function __destruct()
    {
        $this->mysql->close();
    }
    
    // Variable paramter list: 1st = query, additional = value replacements
    public function query()
    {
        $new_results = array();

        if( func_num_args() > 0 )
        {
            $query = func_get_arg(0);

            if( func_num_args() > 1 )
            {
                $sani_data = $this->sanitize(array_slice(func_get_args(), 1));
                $query = vsprintf($query, $sani_data);
            }
            else 
            {
                $query = $this->sanitize($query);
            }
            
            $this->last_query = $query;
            
            // Retrieve cache if ttl is > 0
            if( is_object($this->mc) && !empty($this->mc_ttl) )
                $cache_data = $this->mc->get(md5($query));
            
            if( empty($cache_data) || $this->mc_force )
            {
                $this->mc_force = false; // Reset forced recache
                
                $results = $this->mysql->query($query);
                
                if( $results !== true )
                {
                    if( $results != false )
                    {                  
                        $i = 0;

                        while( $row = $results->fetch_assoc() ) 
                        {
                            $new_results[$i] = $row;
                            $i++;
                        }

                        // Set cache if ttl > 0
                        if( is_object($this->mc) && !empty($this->mc_ttl) )
                            $this->mc->set(md5($query), $new_results, 0, $this->mc_ttl);
                        
                        // Reset ttl back to default
                        $this->mc_ttl = $this->mc_ttl_default;
                        
                        return $new_results;
                    }
                    else
                    {
                        return false;
                    }
                }
                else 
                {
                    return false;
                }
            }
            else
            {
                return $cache_data;
            }
        }
    }

    public function sanitize( $data )
    {
        if( is_string($data) )
        {
            return $data; // If there's no parameters we SHOULD be able to trust the sql
        }
        elseif( is_array($data) )
        {
            foreach( $data as &$val )
            {
                $val = $this->mysql->real_escape_string($val);
            }

            return $data;
        }
        else 
        {
            return false;
        }
    }

    public function get_affected_rows()
    {
        return $this->mysql->affected_rows;
    }

    public function get_last_error()
    {
        return $this->mysql->error;
    }

    public function get_last_id()
    {
        return $this->mysql->insert_id;
    }
    
    public function get_last_query()
    {
        return $this->last_query;
    }
}

?>