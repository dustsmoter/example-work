<script src="js/jquery.js" type="text/javascript"></script>
<script src="js/lightbox.js" type="text/javascript" ></script>

<? if (!empty($scripts)): ?>
    <? foreach ($scripts as $script): ?>
        <?=$this->load->view('scripts/' . $script);?>
    <? endforeach; ?>
<? endif; ?>