<?php
class inputFile extends formElement {
    use elementPlaceholder;

    const Type = 'file';

    public function init() {
        $this->addClass('file');
        $this->addCss('fileinput', 'bootstrap-fileinput/css/fileinput.min.css');
        $this->addJs('fileinput', 'bootstrap-fileinput/js/fileinput.min.js');
        $this->addJs('fileinput-theme', 'bootstrap-fileinput/themes/fas/theme.min.js');
    }

    public function getType():string{
        return $this::Type;
    }

    public function setMultiple(){
        $this->addAttribute('multiple', 'multiple');
        return $this;
    }
}