<?php
class inputButton extends formElement {
    const Type = 'form_button';
    protected $value = 1;
    protected $type = 'button';

    public function __construct($id, $caption = '', $value = 1, $class = 'btn btn-primary'){
        $this->setId($id);
        $this->setName($id);
        $this->label = $caption;
        $this->value = $value;
        $this->class[] = $class;
    }

    protected function init() {
        $this->notDBField();
    }

    public function getType(): string {
        return $this::Type;
    }

    public function setButtonType($type) {
        $this->type = $type;
        return $this;
    }

    public function getButtonType() {
        return $this->type;
    }

    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    public function getValue() {
        return $this->value;
    }

}