<?php

class domain
{
    var $db;
    
    var $errors = array();
    
    // Names of templates to push
    var $templates = array(
                                ".htaccess",
                                "config.php",
                                "index.php",
                                "views/index.tpl.php",
                                "views/sitemap.tpl.php",
                          );
    
    // Directories that need to be created
    var $directories = array(
                                "views",
                            );
    
    // Tags to replace
    var $replacements = array(
                                "[ACCOUNT_NAME]",
                                "[DOMAIN]",
                                "[ID]",
                             );
    
    function domain( &$db )
    {
        if( is_object($db) )
        {
            $this->db = $db;
        }
    }
    
    function get_errors()
    {
        $errors = $this->errors;
        $this->errors = array();
        
        return !empty($errors) ? $errors : false;
    }
    
    function save( $post_data )
    {
        $fields = array("account_name" => "varchar", "domain" => "url");
        $required = array("account_name", "domain");
        $this->errors = req_fields($post_data, $fields, $required);
 
        if( !empty($post_data) && !$this->errors )
        {
            extract($post_data);
            
            // Clean up domain
            $domain = remove_http($domain);
            
            $sql = "INSERT INTO domain (account_name, domain, status)".
                   "VALUES('%s', '%s', 'enabled')";
            
            $this->db->query($sql, $account_name, $domain);
            
            if( $this->db->get_affected_rows() )
            {
                return $this->db->get_last_id();
            }
        }
        
        return false;
    }
    
    function load( $domain_id = null )
    {
        $this->db->mc_force = true;
        
        if( !empty($domain_id) )
        {
            $sql = "SELECT * FROM domain WHERE id = %d";
            $results = $this->db->query($sql, $domain_id);
            return $results[0];
        }
        else
        {
            $sql = "SELECT * FROM domain";
            $results = $this->db->query($sql);
            return $results;
        }
        
        return false;
    }
    
    
}

?>