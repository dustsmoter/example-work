<?php

require_once("../common.inc");
require_once("../classes/class.image.php");
require_once("../classes/SimpleImage.php");

// Verify admin is logged in
if( !$user->logged_in() )
{
	header("Location: login.php");
	exit();
}
$msg = array();


$si = new SimpleImage();
$img = new image($db, $si);

if( !empty($_GET['c']) && in_array($_GET['c'], array("machine", "soda", "user")) )
{
    $id = !empty($_GET['id']) ? $_GET['id'] : null;
    $category = $_GET['c'];
}
else
{
    die("Invalid category or ID.");
}

// Check for image upload
if( !empty($_FILES['picture']) && $_FILES['picture']['error'] == 0 )
{
    if( $img->upload($_FILES['picture'], $category, $id) )
    {
        $msg[] = "<div class='success'>Image Uploaded</div>";
        
        // Check size for warning
        if( !$img->last_upload_validate )
        {
            $msg[] = "<div class='warning'>Image disabled. Please crop to enable.</div>";
        }
    }
    else
    {
        $msg[] = "<div class='error'>Image Failed to Upload</div>";
    }
}

// Cropping upload & thumbnail
if( !empty($_POST['crop']) )
{
    if( $img->crop($_POST, $category, $id) )
    {
        $msg[] = "<div class='success'>Image Cropped</div>";
         
        // Check size for warning
        if( !$img->last_upload_validate )
        {
            $msg[] = "<div class='warning'>Image disabled. Please crop to enable.</div>";
        }
        else
        {
            // On success redirect
            switch( $category )
            {
                case 'machine':
                    header("Location: locations.php");
                break;
                
                case 'soda':
                    header("Location: sodas.php");
                break;
            }
        }
    }
    else
    {
        $msg[] = "<div class='error'>Image Failed to Crop</div>";
    }
}

// Load a picture if one exists
$img_data = $img->load($category, $id);

?>

<? include("header.php") ?>

<? if( !empty($msg) ): ?>
    <? foreach($msg as $m ): ?>
        <p><?=$m?></p>
    <? endforeach; ?>
<? endif; ?>
    

<script>
    
$(document).ready(function(){
    $('#target').Jcrop({
        aspectRatio: <?=$img->scale_to[$category]['ratio']?>,
        //minSize: [200,300],
        onSelect: updateCoords,
        setSelect:   [ 0, 0, $(this).width, $(this).height ]
    });  
    
    function updateCoords(c)
    {
        $('#x').val(c.x);
        $('#y').val(c.y);
        $('#w').val(c.w);
        $('#h').val(c.h);
    };

    function checkCoords()
    {
        if (parseInt($('#w').val())>0) return true;
        alert('Please select a crop region then press submit.');
        return false;
    };

});

</script>

<form method="post" enctype="multipart/form-data">
    <table border="1">
        <tr>
            <th>New Image</th>
            <th>Upload</th>
        </tr>
        <tr>
            <td><input type="file" name="picture" /></td>
            <td><input type="submit" name="upload" value="Upload New Image (Will Override Current)" /></td>
        </tr>
    </table>
</form>

<br /><br />

<? if( !empty($img_data) ): ?>
<div class="center"><img id="target" size="<?=$img_data['size']?>" src="/view.php?c=<?=$category?>&id=<?=$id?>" /></div>
<br />
<form method="post" onsubmit="return checkCoords();">
	<input type="hidden" id="x" name="x" value="0" />
	<input type="hidden" id="y" name="y" value="0" />
	<input type="hidden" id="w" name="w" value="200" />
	<input type="hidden" id="h" name="h" value="300" />
	<input type="submit" name="crop" value="Save Cropped Image" />
</form>
<? endif; ?>

    
<? include("footer.php") ?>