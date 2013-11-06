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
    
    function insert( $post_data )
    {
        $fields = array("account_name" => "varchar", "domain" => "url");
        $required = array("account_name", "domain");
        $this->errors = req_fields($post_data, $fields, $required);
 
        if( !empty($post_data) && !$this->errors )
        {
            extract($post_data);
            
            // Clean up domain
            $domain = remove_http($domain);
            
            $sql = "INSERT INTO domain (account_name, domain, status, date_created)".
                   "VALUES('%s', '%s', 'enabled', NOW())";
            
            $this->db->query($sql, $account_name, $domain);
            
            if( $this->db->get_affected_rows() )
            {
                return $this->db->get_last_id();
            }
            else
            {
                $this->errors[] = "Query failed.";
            }
        }
        
        return false;
    }
    
    function edit( $post_data )
    {
        $fields = array("account_name" => "varchar", "domain" => "url");
        $required = array("account_name", "domain");
        $this->errors = req_fields($post_data, $fields, $required);
 
        if( !empty($post_data) && !$this->errors )
        {
            extract($post_data);
            
            // Clean up domain
            $domain = remove_http($domain);
            
            $sql = "UPDATE domain SET account_name = '%s', domain = '%s' WHERE id = %d";
            
            $this->db->query($sql, $account_name, $domain, $id);
            
            return true;
        }
        
        return false;
    }
    
    function delete( $domain_id )
    {
        if( !empty($domain_id) )
        {
            $sql = "DELETE FROM domain WHERE id = $domain_id";
            $this->db->query($sql);
            return true;
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
    
    function push( $domain_id )
    {
        global $config;
        
        if( $domain = $this->load($domain_id) )
        {
            $tpl_path = $config['shared_path'] . "/templates/";
            $new_path = "/home/{$domain['account_name']}/public_html/";
            
            // Create all necessary directories
            foreach( $this->directories as $dir )
            {
                if( !file_exists($new_path . $dir) || !is_dir($new_path . $dir) )
                {
                    if( !mkdir($new_path . $dir) )
                    {
                        return false;
                    }
                }
            }
            
            // Run through each template
            foreach( $this->templates as $template )
            {
                // Copy template file to new directory
                if( copy($tpl_path . $template, $new_path . $template) )
                {
                    // Open the newly copied template file
                    if( $file = get_file($new_path . $template) )
                    {                    
                        foreach( $this->replacements as $replacement )
                        {
                            // Janky way to replace tags. Needs better searching
                            $index = strtolower(trim($replacement,"[]"));
                            $file = str_replace($replacement, $domain[$index], $file);
                        }
                        
                        // Write file with replacements
                        put_file($new_path . $template, $file);
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
            
            // Write a backup copy
            mkdir("/home/backup/" . $domain['account_name']);
            shell_exec("cp -r $new_path /home/backup/{$domain['account_name']}");
            shell_exec("chown -R {$domain['account_name']}:nobody /home/{$domain['account_name']}");
      
            return true;
        }
        
        return false;
    }
}

?>