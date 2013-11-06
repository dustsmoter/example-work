<?php

class Pages extends CI_Controller {

    function Pages() {
        parent::__construct();
    }

    function index() {
        $data['content'] = "main";
        $this->load->view('layout/layout', $data);
    }

}

?>