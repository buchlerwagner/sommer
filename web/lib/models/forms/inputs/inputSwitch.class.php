<?php
class inputSwitch extends inputCheckbox {
    const Type = 'checkbox';

    public function init(){
        parent::init();
        if(!$this->getColor()){
            $this->setColor(enumColors::Primary());
        }
    }

    public function getType():string {
        return $this::Type;
    }

    public function getTemplate() {
        return 'switch';
    }
}