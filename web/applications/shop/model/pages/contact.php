<?php
/**
 * @var $this router
 * @var $content content
 */

$this->loadForm('contact');
$sections = $this->lib->getWidgetContents('contact');
if($sections){
    foreach($sections AS $content){
        $this->data['content'] = $content;
        break;
    }
}

$this->view->includeValidationJS();
$this->data['isSuccess'] = (isset($_REQUEST['success']));
