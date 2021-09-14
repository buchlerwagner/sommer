<?php
class buttonCancel extends buttonHref {
    const Template = 'href';

    public function __construct($caption = 'BTN_CANCEL', $class = 'btn btn-light ml-2'){
        $this->setId('cancel');
        $this->setName($this->getId());

        $this->setShowInEditor(true);
        $this->setShowInViewer(true);

        $this->caption = $caption;
        $this->class[] = $class;
    }

    public function getTemplate() {
        return $this::Template;
    }
}