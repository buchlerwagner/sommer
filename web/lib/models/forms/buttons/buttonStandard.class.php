<?php
class buttonStandard extends formButton {
    const Template = 'button';
    protected $value = 1;

    public function __construct($id, $caption = '', $class = 'btn btn-light'){
        $this->setType(enumButtonTypes::Button());

        $this->setId($id);
        $this->setName($id);
        $this->caption = $caption;
        $this->class[] = $class;
    }

    public function getTemplate() {
        return $this::Template;
    }

    public function init() {
        return $this;
    }
}