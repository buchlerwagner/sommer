<?php
class groupFieldset extends formContainer {
    const Type = 'fieldset';

    private $toolsHtml = false;

    public function getType():string{
        return $this::Type;
    }

    public function addTools($label, $class, $icon = false, $action = false){
        if($icon){
            $icon = '<i class="' . $icon . ' mr-2"></i>';
        }else{
            $icon = '';
        }

        $this->toolsHtml = '<a href="' . ($action ?: 'javascript:;') . '" class="' . $class . '">' . $icon . '{{ _("' . $label . '") }}</a>';
        return $this;
    }

    public function openTag():string {
        $html = '<fieldset id="' . $this->getId() . '"' . $this->buildClass('form-fieldset') . $this->buildAttributes() . '>';
        if(!Empty($this->label)){
            $html .= '<legend>{{ _("' . $this->getLabel() . '") }}';

            if($this->toolsHtml){
                $html .= '<div class="formbuilder-group-tools float-right">' . $this->toolsHtml . '</div>';
            }

            $html .= '</legend>';
        }
        return $html;
    }

    public function closeTag():string {
        return '</fieldset>';
    }
}