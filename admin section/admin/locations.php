<?php

require_once("../common.inc");
require_once("../classes/class.image.php");
require_once("../classes/class.machine.php");
require_once("../classes/SimpleImage.php");
$page = "locations";
$msg = array();

$si = new SimpleImage();
$img = new image($db, $si);

// Verify admin is logged in
if( !$user->logged_in() )
{
	header("Location: login.php");
	exit();
}

// Saving a machine
if( !empty($_POST['submit']) )
{
    $machine = new machine($db);
    
    if( $new_id = $machine->save($_POST) )
    {
        $msg[] = "<div class='success'>Machine successfully saved.</div>";
        
        $_POST['id'] = $new_id; // Save new id so it can update if they change info
        
        // Check for image upload
        if( !empty($_FILES['picture']) && $_FILES['picture']['error'] == 0 )
        {
            if( $img->upload($_FILES['picture'], "machine", $new_id) )
            {
                // Redirect to image cropping
                header("Location: crop.php?c=machine&id={$new_id}");
            }
            else
            {
                $msg[] = "<div class='error'>Image Failed to Upload</div>";
            }
        }
    }
    else
    {
        $msg[] = "There was an error trying to save machine.";
    }
}
elseif( !empty($_POST['submit_edits']) && !empty($_POST['machines']) ) // Saving edits
{
    $machine = new machine($db);
    
    foreach( $_POST['machines'] AS $id => $machine_data )
    {
        if( !empty($machine_data['delete']) && $machine_data['delete'] == 'on' )
        {
            $machine->delete($id);
        }
        else
        {
            $machine->save($machine_data);
        }
    }
}

// Load all machines
$machines = machine::load_all($db);

?>

<? include("header.php") ?>

<? if( !empty($msg) ): ?>
    <? foreach($msg as $m ): ?>
        <p><?=$m?></p>
    <? endforeach; ?>
<? endif; ?>
    
<h2>Add New Machine <a href="#" class="expand">[+]</a></h2>
<form method="post" enctype="multipart/form-data" class="expand-content" style="display: none;">
    <table border="1">
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>URL</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>Status</th>
            <th>Image</th>
        </tr>

        <tr>
            <td><input name="name" type="text" value="<?=$_POST['name']?>"></td>
            <td><textarea name="description"><?=$_POST['description']?></textarea></td>
            <td><input name="url" type="text" value="<?=$_POST['url']?>"></td>
            <td><input size="5" name="lat" type="text" value="<?=$_POST['lat']?>"></td>
            <td><input size="5" name="lon" type="text" value="<?=$_POST['lon']?>"></td>
            <td>
                <select name="status">
                    <option value="enabled" <? if($_POST['status'] == 'enabled') echo 'selected';?>>enabled</option>
                    <option value="disabled" <? if($_POST['status'] == 'disabled') echo 'selected';?>>disabled</option>
                </select>
            </td>
            <td><input name="picture" type="file" value=""></td>
        </tr>
        <tr>
            <td colspan="7" align="center"><input name="submit" value="Add Machine" type="submit"></td>
        </tr>
    </table>
</form>
    
<br />

<? if( !empty($machines) ): ?>
    <form method="post">
        <h2>Machines</h2>
        <table border="1">
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>URL</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Status</th>
                <th>Edit Image</th>
                <th>Delete</th>
            </tr>
            <? foreach($machines as $machine): extract($machine); ?>
                <tr>
                    <td>
                        <input name="machines[<?=$id?>][id]" type="hidden" value="<?=$id?>"> 
                        <input name="machines[<?=$id?>][name]" type="text" value="<?=$name?>">
                    </td>
                    <td><textarea name="machines[<?=$id?>][description]"><?=$description?></textarea></td>
                    <td><input name="machines[<?=$id?>][url]" type="text" value="<?=$url?>"></td>
                    <td><input size="5" name="machines[<?=$id?>][lat]" type="text" value="<?=$lat?>"></td>
                    <td><input size="5" name="machines[<?=$id?>][lon]" type="text" value="<?=$lon?>"></td>
                     <td>
                        <select name="machines[<?=$id?>][status]">
                            <option value="disabled" <? if($status == 'disabled') echo 'selected';?>>disabled</option>
                            <option value="enabled" <? if($status == 'enabled') echo 'selected';?>>enabled</option>
                        </select>
                    </td>
                    <td>
                        <a class="button" href="crop.php?c=machine&id=<?=$id?>"><? if($has_pic) echo "<img src='/view.php?c=machine&id={$id}&thumb=1' />"; else echo "[Add Image]"; ?></a>
                    </td>
                    <td align="center"><input name="machines[<?=$id?>][delete]" type="checkbox"></td>
                </tr>
            <? endforeach; ?>

            <tr>
                <td colspan="8" align="center"><input name="submit_edits" type="submit" value="Update All"></td>
            </tr>
        </table>
    </form>
<? endif; ?>
    
<? include("footer.php") ?>