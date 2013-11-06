<?php

class database
{
	private $mysql;
	protected $last_query;
	private $return_keys = true;
	
	function __construct( $host, $database_name, $username, $password )
	{
		$this->mysql = new mysqli( $host, $username, $password, $database_name );
		
		if ( mysqli_connect_errno() ) 
		{
    		printf( "Connect failed: %s\n", mysqli_connect_error() );
    		exit();
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
			
			$results = $this->mysql->query( $query );
			$this->last_query = $query;
			
			if( $results !== true ) // It's an insert or update?
			{
				if( $results != false )
				{
					if( $this->return_keys )
					{
						$i = 0;
				
						while( $row = $results->fetch_assoc() ) 
						{
							$new_results[$i] = $row;
							$i++;
						}
					}
					else
					{
						$new_results = $results->fetch_assoc();
					}
					
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
	}
	
	public function sanitize( $data )
	{
		if( is_string($data) )
		{
			return $this->mysql->real_escape_string($data);
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
	
	public function get_last_query()
	{
		return $this->last_query;
	}
}

?>