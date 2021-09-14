<?php
/**
 * @var $this router
 */

$this->loadForm('myProfile');
$this->loadForm('changePassword');

$this->data['tab'] = $_REQUEST['tab'];

if($this->data['forms']['myProfile']->errors) {
    $this->data['tab'] = 'profile';
}elseif($this->data['forms']['changePassword']->errors){
    $this->data['tab'] = 'security';
}

$this->view->addCss('fileinput.min.css', 'fileinput');
$this->view->addJs('fileinput/fileinput.min.js', 'fileinput');
$this->view->addJs('fileinput/fileinput_locale_' . $this->language . '.js', 'fileinput-locale');
