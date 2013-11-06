<?php

class mail
{	
	function mail()
	{
	}
	
	function send_mail( $to, $from, $subject, $content )
	{
		if( !empty($to) && !empty($subject) && !empty($content) )
		{
			$headers = "From: $from\r\n";
			$headers .= "Reply-To: $from\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			
			$content = "<html><head></head><body>" . $content . "</body></html>";
    		
			if( mail($to, $subject, $content, $headers) )
			{
    			return true;
			}
		}
		
		return false;
	}
}

?>