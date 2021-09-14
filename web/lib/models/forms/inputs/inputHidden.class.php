<?php
class inputHidden extends formElement {
    const Type = 'hidden';

    public function __construct($id, $name = false, $default = null, $dbField = false) {
        $this->isContainer = false;
        if(!$dbField){
            $this->notDBField();
        }
        $this->setId($id);
        $this->setName(($name ?: $id));

        $this->default = $default;

        $this->init();
    }

    protected function init() {
    }

    public function getType():string {
        return $this::Type;
    }
}