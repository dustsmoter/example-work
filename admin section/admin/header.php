<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Soda Pagoda Admin</title>

<script src="/js/jquery.js"></script>
<script src="/js/jcrop.js"></script>
<link rel="stylesheet" href="/css/jcrop.css" type="text/css" />
<link rel="stylesheet" href="/css/admin.css" type="text/css" />

<script>
$(document).ready(function() {
    
    $('.expand').click(function(){
        $(this).hide();
        $('.expand-content').fadeIn();
    });
});

</script>

</head>

<body>
    
<div id="page-container">
    <div id="header">
        <a href="/admin/"><img src="/img/admin-header.png" /></a>
        <? if( empty($_menu_hide) ): ?>
            <ul id="menu">
                <li><a <? if($page == "home") echo "class='selected'"; ?> href="index.php">Home</a></li>
                <li><a <? if($page == "locations") echo "class='selected'"; ?> href="locations.php">Locations</a></li>
                <li><a <? if($page == "sodas") echo "class='selected'"; ?> href="sodas.php">Sodas</a></li>
                
            </ul>
        <? endif; ?>
    </div>
	<div id="content">