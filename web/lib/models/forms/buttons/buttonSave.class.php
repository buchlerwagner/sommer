<?php
class buttonSave extends buttonSubmit {
    const Template = 'button';

    public function __construct($caption = 'BTN_SAVE', $class = 'btn btn-primary btn-progress'){
        $this->setType(enumButtonTypes::Submit());

        $this->setId('save');
        $this->setName($this->getId());

        $this->setShowInEditor(true);
        $this->setShowInViewer(false);

        $this->caption = $caption;
        $this->class[] = $class;

        $this->init();
    }

    public function getTemplate() {
        return $this::Template;
    }
}