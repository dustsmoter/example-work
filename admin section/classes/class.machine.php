<?php

class machine
{
	protected $db;
	protected $data;
    
	function __construct( &$db )
	{
            if( is_object($db) )
            {
                    $this->db = $db;
            }
	}
    
    function load( $machine_id )
    {
        if( !empty($machine_id) && is_numeric($machine_id) )
        {
            $sql = "SELECT m.*, NOT ISNULL(i.id) AS has_pic ".
                   "FROM machine m ".
                   "LEFT JOIN image i ON i.category = '%s' AND i.id = m.id AND i.status = '%s' ".
                   "WHERE m.id = $machine_id";
            $results = $this->db->query($sql, 'machine', 'enabled');
            
            if( !empty($results) )
            {
                return $results[0];
            }
        }
        
        return false;
    }
    
    function save( $post_data )
    {
        if( !empty($post_data) )
        {
            extract($post_data);
            
            $sql = "INSERT INTO machine (id, name, description, url, lat, lon, status) ".
                   "VALUES (%d, '%s', '%s', '%s', %f, %f, '%s') ".
                   "ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), url = VALUES(url), lat = VALUES(lat), lon = VALUES(lon), status = VALUES(status)";
            
            $this->db->query($sql, $id, $name, $description, $url, $lat, $lon, $status);
  
            $new_id = !empty($post_data['id']) ? $post_data['id'] : $this->db->get_insert_id();
            
            if( !empty($new_id) )
            {
                return $new_id;
            }
        }
        
        return false;
    }
    
    function delete( $machine_id )
    {
        if( !empty($machine_id) && is_numeric($machine_id) )
        {
            $sql = "DELETE FROM machine WHERE id = $machine_id";
            $this->db->query($sql);
            
            if( $this->db->get_affected_rows() )
            {
                // Delete image (if any)
                $sql = "DELETE FROM image WHERE category = '%s' AND id = $machine_id";
                $this->db->query($sql, 'machine');
                
                return true;
            }
        }
        
        return false;
    }
    
    function get_data()
    {
        if( !empty($this->data) )
        {
            return $this->data;
        }
        
        return false;
    }
    
    // Static methods
    function load_all( &$db, $all = true )
    { 
        $all_sql = $all ? "1 = 1" : "m.status = 'enabled'";
        
        $sql = "SELECT m.*, NOT ISNULL(i.id) AS has_pic FROM machine m LEFT JOIN image i ON i.category = '%s' AND i.id = m.id AND i.status = '%s' WHERE $all_sql";
        $results = $db->query($sql, 'machine', 'enabled');

        if( !empty($results) )
        {
            return $results;
        }

        return false;
    }
}

?>