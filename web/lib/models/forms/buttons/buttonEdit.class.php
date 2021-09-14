<?php
class buttonEdit extends buttonHref {
    const Template = 'href';

    public function __construct($caption = 'BTN_EDIT', $class = 'btn btn-warning ml-2'){
        $this->setId('edit');
        $this->setName($this->getId());

        $this->setShowInEditor(false);
        $this->setShowInViewer(true);

        $this->caption = $caption;
        $this->class[] = $class;
    }

    public function getTemplate() {
        return $this::Template;
    }
}