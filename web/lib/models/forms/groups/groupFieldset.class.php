<?php
class groupFieldset extends formContainer {
    const Type = 'fieldset';

    public function getType():string{
        return $this::Type;
    }

    public function openTag():string {
        $html = '<fieldset id="' . $this->getId() . '"' . $this->buildClass('form-fieldset') . $this->buildAttributes() . '>';
        if(!Empty($this->label)){
            $html .= '<legend>{{ _("' . $this->getLabel() . '") }}</legend>';
        }
        return $html;
    }

    public function closeTag():string {
        return '</fieldset>';
    }
}