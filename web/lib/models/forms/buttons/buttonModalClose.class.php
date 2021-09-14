<?php
class buttonModalClose extends buttonModal {
    const Template = 'href';

    public function init(){
        $this->addData('dismiss', 'modal');
        $this->setUrl('javascript:;');
        return $this;
    }

    public function __construct($id = 'btn-close', $caption = 'BTN_CLOSE', $class = 'btn btn-light ml-2 float-right'){
        parent::__construct($id, $caption, $class);
        $this->init();
    }
}