<?php

class setting
{
    var $db;
    var $user;
    
    function __construct( &$db, &$user )
    {
        if( !empty($db) && !empty($user) )
        {
            $this->db = $db;
            $this->user = $user;
        }
    }
    
    function get_settings( $setting_name = null )
    {
        if( !empty($this->user->user_data['id']) )
        {
            $user_id = $this->user->user_data['id'];
            
            $name_sql = "";
            if( !empty($setting_name) )
            {
                $name_sql = "AND sn.name = '$setting_name'";
            }
            
            $sql = "SELECT sn.name, s.val ".
                   "FROM setting s ".
                   "JOIN setting_name sn ON sn.id = s.setting_id ".
                   "WHERE user_id = $user_id $name_sql ";
            
            $this->db->mc_force = true;
            $results = $this->db->query($sql);
            
            if( !empty($results) )
            {
                return $results;
            }
        }
        
        return false;
    }
    
    // Static functions
    function get_setting_names(&$db)
    {
        $sql = "SELECT name FROM setting_name";
        $db->mc_ttl = 86000;
        $results = $db->query($sql);
        
        return !empty($results) ? $results : false;
    }
}

?>
