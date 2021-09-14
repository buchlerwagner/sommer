<?php
class buttonDropdown extends buttonHref {
    const Template = 'dropdown';

    private $options = [];

    public function __construct($caption, $class = 'btn btn-primary'){
        $this->caption = $caption;
        $this->class[] = $class;
    }

    public function getTemplate() {
        return $this::Template;
    }

    /**
     * @todo implement buttonOption class
     * @param ...$options
     * @return formButton
     */
    public function setOptions(...$options):formButton{
        foreach($options AS $option) {
            $this->options[] = $option;
        }
        return $this;
    }

    public function getOptions(){
        return $this->options;
    }
}