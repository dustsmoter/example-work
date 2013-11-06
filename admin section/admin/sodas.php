<?php

require_once("../common.inc");
require_once("../classes/class.image.php");
require_once("../classes/class.soda.php");
require_once("../classes/SimpleImage.php");
$page = "sodas";
$title = "Sodas";
$msg = array();

$si = new SimpleImage();
$img = new image($db, $si);

// Verify admin is logged in
if( !$user->logged_in() )
{
    header("Location: login.php");
    exit();
}

// Saving a soda
if( !empty($_POST['submit']) )
{
    $soda = new soda($db);
    
    if( $new_id = $soda->save($_POST) )
    {
        $msg[] = "<div class='success'>soda successfully saved.</div>";
        
        $_POST['id'] = $new_id; // Save new id so it can update if they change info
        
        // Check for image upload
        if( !empty($_FILES['picture']) && $_FILES['picture']['error'] == 0 )
        {
            if( $img->upload($_FILES['picture'], "soda", $new_id) )
            {
                // Redirect to image cropping
                header("Location: crop.php?c=soda&id={$new_id}");
            }
            else
            {
                $msg[] = "<div class='error'>Image Failed to Upload</div>";
            }
        }
    }
    else
    {
        $msg[] = "There was an error trying to save soda.";
    }
}
elseif( !empty($_POST['submit_edits']) && !empty($_POST['sodas']) ) // Saving edits
{
    $soda = new soda($db);
    
    foreach( $_POST['sodas'] AS $id => $soda_data )
    {
        if( !empty($soda_data['delete']) && $soda_data['delete'] == 'on' )
        {
            $soda->delete($id);
        }
        else
        {
            $soda->save($soda_data);
        }
    }
}

// Load all sodas
$sodas = soda::load_all($db);

?>

<? include("header.php") ?>

<? if( !empty($msg) ): ?>
    <? foreach($msg as $m ): ?>
        <p><?=$m?></p>
    <? endforeach; ?>
<? endif; ?>
    
<h2>Add New soda</h2>
<form method="post" enctype="multipart/form-data">
    <table border="1">
        <tr>
            <th>Manufacturer</th>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Vote</th>
            <th>Status</th>
            <th>Image</th>
        </tr>

        <tr>
            <td><input name="manufacturer" type="text" value="<?=$_POST['manufacturer']?>"></td>
            <td><input name="name" type="text" value="<?=$_POST['name']?>"></td>
            <td><textarea name="description"><?=$_POST['description']?></textarea></td>
            <td><input size="4" name="price" type="text" value="<?=!empty($_POST['price']) ? $_POST['price'] : "2.00"?>"></td>
            <td><input size="4" name="vote" type="text" value="<?=$_POST['vote']?>"></td>
            <td>
                <select name="status">
                    <option value="enabled" <? if($_POST['status'] == 'enabled') echo 'selected';?>>enabled</option>
                    <option value="disabled" <? if($_POST['status'] == 'disabled') echo 'selected';?>>disabled</option>
                </select>
            </td>
            <td><input name="picture" type="file" value=""></td>
        </tr>
        <tr>
            <td colspan="7" align="center"><input name="submit" value="Add soda" type="submit"></td>
        </tr>
    </table>
</form>
    
<br />

<? if( !empty($sodas) ): ?>
    <form method="post">
        <h2>Sodas</h2>
        <table border="1">
            <tr>
                <th>Manufacturer</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Vote</th>
                <th>Status</th>
                <th>Edit Image</th>
                <th>Delete</th>
            </tr>
            <? foreach($sodas as $soda): extract($soda); ?>
                <tr>
                    <td><input style="width: 150px;" name="sodas[<?=$id?>][manufacturer]" type="text" value="<?=$manufacturer?>"></td>
                    <td>
                        <input name="sodas[<?=$id?>][id]" type="hidden" value="<?=$id?>"> 
                        <input style="width: 100px;" name="sodas[<?=$id?>][name]" type="text" value="<?=$name?>">
                    </td>
                    <td><textarea name="sodas[<?=$id?>][description]"><?=$description?></textarea></td>
                    <td><input style="width: 50px;" name="sodas[<?=$id?>][price]" type="text" value="<?=$price?>"></td>
                    <td><input style="width: 50px;" name="sodas[<?=$id?>][vote]" type="text" value="<?=$vote?>"></td>
                    <td>
                        <select name="sodas[<?=$id?>][status]">
                            <option value="disabled" <? if($status == 'disabled') echo 'selected';?>>disabled</option>
                            <option value="enabled" <? if($status == 'enabled') echo 'selected';?>>enabled</option>
                        </select>
                    </td>
                    <td>
                        <a class="button" href="crop.php?c=soda&id=<?=$id?>"><? if($has_pic) echo "<img width='23' height='45' src='/view.php?c=soda&id={$id}&thumb=1' />"; else echo "[Add Image]"; ?></a>
                    </td>
                    <td align="center"><input name="sodas[<?=$id?>][delete]" type="checkbox"></td>
                </tr>
            <? endforeach; ?>

            <tr>
                <td colspan="8" align="center"><input name="submit_edits" type="submit" value="Update All"></td>
            </tr>
        </table>
    </form>
<? endif; ?>
    
<? include("footer.php") ?>