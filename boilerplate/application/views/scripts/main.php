<link rel="stylesheet" type="text/css" href="css/reset.css" />
<link rel="stylesheet" type="text/css" href="css/main.css" />
<link rel="stylesheet" type="text/css" href="css/lightbox.css" media="screen" />

<? if (!empty($styles)): ?>
    <? foreach ($styles as $style): ?>
        <?=$this->load->view('styles/' . $style);?>
    <? endforeach; ?>
<? endif; ?>
