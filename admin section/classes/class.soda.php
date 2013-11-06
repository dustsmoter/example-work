<?php

class soda
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
    
    function load( $soda_id )
    {
        if( !empty($soda_id) && is_numeric($soda_id) )
        {
            $sql = "SELECT s.*, NOT ISNULL(i.id) AS has_pic ".
                   "FROM soda s ".
                   "LEFT JOIN image i ON i.category = '%s' AND i.id = s.id AND i.status = '%s' ".
                   "WHERE s.id = $soda_id";
            $results = $this->db->query($sql, 'soda', 'enabled');
            
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
            
            $sql = "INSERT INTO soda (id, manufacturer, name, description, price, vote, status) ".
                   "VALUES (%d, '%s', '%s', '%s', '%s', %d, '%s') ".
                   "ON DUPLICATE KEY UPDATE manufacturer = VALUES(manufacturer), name = VALUES(name), description = VALUES(description), price = VALUES(price), vote = VALUES(vote), status = VALUES(status)";
            
            $this->db->query($sql, $id, $manufacturer, $name, $description, $price, $vote, $status);
            echo $this->db->get_last_error();
            $new_id = !empty($post_data['id']) ? $post_data['id'] : $this->db->get_insert_id();
            
            if( !empty($new_id) )
            {
                return $new_id;
            }
        }
        
        return false;
    }
    
    function delete( $soda_id )
    {
        if( !empty($soda_id) && is_numeric($soda_id) )
        {
            $sql = "DELETE FROM soda WHERE id = $soda_id";
            $this->db->query($sql);
            
            if( $this->db->get_affected_rows() )
            {
                // Delete image (if any)
                $sql = "DELETE FROM image WHERE category = '%s' AND id = $soda_id";
                $this->db->query($sql, 'soda');
                
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
        $all_sql = $all ? "1 = 1" : "s.status = 'enabled'";
        
        $sql = "SELECT s.*, NOT ISNULL(i.id) AS has_pic FROM soda s LEFT JOIN image i ON i.category = '%s' AND i.id = s.id AND i.status = '%s' WHERE $all_sql";
        $results = $db->query($sql, 'soda', 'enabled');

        if( !empty($results) )
        {
            return $results;
        }

        return false;
    }
}

?>