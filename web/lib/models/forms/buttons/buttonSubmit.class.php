<?php
class buttonSubmit extends formButton {
    const Template = 'button';

    public function __construct($id, $caption = '', $class = 'btn btn-primary'){
        $this->setType(enumButtonTypes::Submit());

        $this->setShowInEditor(true);
        $this->setShowInViewer(false);

        $this->setId($id);
        $this->setName($id);
        $this->caption = $caption;
        $this->class[] = $class;
    }

    public function getTemplate() {
        return $this::Template;
    }

    public function init() {
        if($this->readonly){
            $this->setHidden();
        }

        return $this;
    }
}