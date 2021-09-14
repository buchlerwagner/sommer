<?php
class inputCheckbox extends formElement {
    use elementColor, elementChangeState;

    const Type = 'checkbox';
    private $valueOn = 1;
    private $valueOff = 0;

    protected function init() {
        $this->setConstraints('ui-enabled', 'false');
    }

    public function getType():string {
        return $this::Type;
    }

    public function setStateValues($on = 1, $off = 0){
        $this->valueOn = $on;
        $this->valueOff = $off;
        return $this;
    }

    public function getValueOn(){
        return $this->valueOn;
    }

    public function getValueOff(){
        return $this->valueOff;
    }
}