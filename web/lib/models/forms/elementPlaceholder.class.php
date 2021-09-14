<?php
trait elementPlaceholder {
    private $placeholder = '';

    public function setPlaceholder($placeholder){
        $this->placeholder = $placeholder;
        return $this;
    }

    public function getPlaceholder():string{
        return $this->placeholder;
    }
}