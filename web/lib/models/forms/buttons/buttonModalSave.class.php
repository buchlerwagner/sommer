<?php
class buttonModalSave extends buttonModal {
    const Template = 'button';

    public function init(){
        $this->addClass('btn-modal-submit');
        $this->addClass('float-right');
        $this->addClass('btn-progress');
        $this->setName('save');
        $this->setType(enumButtonTypes::Button());

        return $this;
    }

    public function __construct($id = 'btn-save', $caption = 'BTN_SAVE', $class = 'btn btn-primary'){
        parent::__construct($id, $caption, $class);
        $this->init();
    }
}