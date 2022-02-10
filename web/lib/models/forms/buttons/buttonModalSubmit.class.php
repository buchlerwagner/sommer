<?php
class buttonModalSubmit extends buttonModal {
    const Template = 'button';

    public function init(){
        $this->addClass('btn-modal-submit');
        //$this->addClass('float-left');
        $this->setType(enumButtonTypes::Button());

        return $this;
    }

    public function __construct($id, $caption, $class = 'btn btn-primary'){
        parent::__construct($id, $caption, $class);
        $this->init();
        $this->setName($this->id);
    }
}