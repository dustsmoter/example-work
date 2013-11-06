<?php

class user
{
    var $db;
    var $user_data = array();
    
    function user( &$db, $user_data = null )
    {
        if( is_object($db) )
        {
            $this->db = $db;
        }
		
		if( !empty($user_data) )
		{
			$this->user_data = $user_data;
		}
    }
    
	function logged_in()
	{
		return !empty($this->user_data) ? true : false;
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
			$sql = "UPDATE user SET perms = $perms WHERE id = $id";
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
            	$sql = "SELECT * FROM user WHERE username = '%s' AND password = '%s' AND status = 'enabled'";
                $results = $this->db->query($sql, $data['username'], $data['password']);
		
				if( !empty($results) && $results[0]['status'] == 'enabled' )
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
    	if( !empty($data['username']) && !empty($data['password']) && !empty($data['email']) )
    	{            
    		$sql = "INSERT INTO user VALUES (null, '%s', '%s', '%s', NOW(), 'enabled')";
    		$this->db->query($sql, $data['username'], $data['password'], $data['email']);
    	
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
}

?>