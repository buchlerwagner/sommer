<?php
class groupRow extends formContainer {
    const Type = 'row';

    public function getType():string{
        return $this::Type;
    }

    public function openTag():string {
        return '<div id="' . $this->getId() . '"' . $this->buildClass('form-row') . $this->buildAttributes() . '>';
    }

    public function closeTag():string {
        return '</div>';
    }
}