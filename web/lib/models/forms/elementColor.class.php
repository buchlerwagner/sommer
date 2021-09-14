<?php
trait elementColor {
    private $color = '';

    public function setColor(enumColors $color){
        $this->color = $color;
        return $this;
    }

    public function getColor():string{
        return $this->color;
    }
}