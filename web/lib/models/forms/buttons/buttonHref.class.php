<?php
class buttonHref extends formButton {
    const Template = 'href';
    protected $target = false;

    public function __construct($id, $caption = '', $class = 'btn btn-primary'){
        $this->id = $id;
        $this->setName($this->getId());
        $this->caption = $caption;
        $this->class[] = $class;
    }

    public function getTemplate() {
        return $this::Template;
    }

    public function setTarget($target):formButton {
        $this->target = $target;
        return $this;
    }

    public function getTarget() {
        return $this->target;
    }

    public function init() {
        return $this;
    }
}