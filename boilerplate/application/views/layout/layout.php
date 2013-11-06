<!DOCTYPE HTML>
<html>
<head>
    
<? $this->load->view('layout/meta'); ?>

<title><? if (!empty($title)): echo $title; else: ?>Site Title<? endif; ?></title>

<link rel="icon" href="favicon.ico" type="image/x-icon"> 
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />

<?=$this->load->view('styles/main.php');?>
<?=$this->load->view('scripts/main.php');?>

</head>

<body>
    
<div id="wrapper">
    <div id="page-container">
        <div id="header">
            <? $this->load->view('layout/menu'); ?>
        </div>
        
        <div id="content">
            <? $this->load->view($content); ?>
        </div>
    </div>
    
    <div id="footer">
        <div>This is the footer</div>
    </div>
</div>
    
</body>
</html>