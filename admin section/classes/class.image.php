<?php

class image
{
    var $db;
    var $si; // SimpleImage object for manipulating sizes
    var $maxsize = 10485760; // 10mb
    var $maxwidth = 1000; // Largest width we want to show on crop screen
    
    // Scale width to these
    var $scale_to = array(
                            "machine" => array("ratio" => 0.666, "normal" => 300, "thumb" => 90),
                            "soda" => array("ratio" => 0.5, "normal" => 300, "thumb" => 90),
                         );
    var $last_upload_validate = false;
    
    function __construct( &$db, &$si = null )
    {
        if(is_object($db) )
        {
            $this->db = $db;
        }
        
        if(is_object($si) )
        {
            $this->si = $si;
        }
    }
    
    function crop( $post, $category, $id )
    {
        if( !empty($post) && !empty($category) && !empty($id) )
        {
            $data = $this->load($category,$id);
            $this->si->image = imagecreatefromstring($data['image_data']);
            $this->si->coord($post['x'], $post['y'], $post['w'], $post['h']);
            $this->si->resizeToWidth($this->scale_to[$category]['normal']);
            $file['tmp_name'] = tempnam("/tmp", "img");
            $file['size'] = "1"; // just fake it
            imagejpeg($this->si->image, $file['tmp_name']);
            
            if( $this->upload($file, $category, $id) )
            {
                return true;
            }
        }
        
        return false;
    }
    
    function validate( $category, $width, $height )
    {
        if( !empty($category) && !empty($width) && !empty($height) )
        {
            if( $width == $this->scale_to[$category]['normal'] )
                    return true;
            /*if( $width == $this->scale_to[$category]['normal'] && $height == round($this->scale_to[$category]['normal'] / $this->scale_to[$category]['ratio']) )
            {
                return true;
            }*/
        }
        
        return false;
    }
    
    function upload( $file, $category, $id = null )
    {
        if( !empty($category) ) 
        {
            // check the file is less than the maximum file size
            if($file['size'] <= $this->maxsize)
            {   
                // Resize to an appropriate width
                $size = getimagesize($file['tmp_name']);
                
                $this->si->load($file['tmp_name']);
                if( $size[0] > $this->maxwidth )
                {
                    $this->si->resizeToWidth($this->maxwidth);
                    $this->si->save($file['tmp_name']);
                }
                
                $img_data = file_get_contents($file['tmp_name']);
                $size = getimagesize($file['tmp_name']);

                // Validate image is correct size
                if( $this->validate($category, $size[0], $size[1]) )
                {
                    $this->last_upload_validate = true;
                    $status = "enabled";
                }
                else
                {
                    $this->last_upload_validate = false;
                    $status = "disabled";
                }
                
                // Create thumb
                $this->si->resizeToWidth($this->scale_to[$category]['thumb']);
                $this->si->save($file['tmp_name']);
                $img_thumb_data = file_get_contents($file['tmp_name']);
                
                $sql = "INSERT INTO image (id, category, type, img, img_thumb, size, status)".
                       "VALUES(%d, '%s', '%s', '%s', '%s', '%s', '%s')".
                       "ON DUPLICATE KEY UPDATE category = VALUES(category), type = VALUES(type), ".
                       "img = VALUES(img), img_thumb = VALUES(img_thumb), size = VALUES(size), status = VALUES(status)";
                
                $this->db->query($sql, $id, $category, $size['mime'], $img_data, $img_thumb_data, $size[3], $status);
                echo $this->db->get_last_error();
                return true;
            }
        }
        
        return false;
    }
    
    function load( $category, $id, $thumb = false )
    {
        if( !empty($category) && !empty($id) )
        {
            $type = "img";
            if( $thumb )
            {
                $type = "img_thumb";
            }
            
            $sql = "SELECT $type AS image_data, size, type FROM image WHERE category = '%s' AND id = %d";
            $results = $this->db->query($sql, $category, $id);
            
            if( !empty($results) && is_array($results) )
            {
                return $results[0];
            }
        }
        
        return false;
    }
    
    function convert_size( $size )
    { 
        $dimensions = str_replace("=\"", ":", $size['3']); 
        $dimensions = str_replace("\"", "px;", $dimensions); 
        return $dimensions; 
    }
}

?>