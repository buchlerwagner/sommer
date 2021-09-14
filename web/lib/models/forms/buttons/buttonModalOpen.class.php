<?php
class buttonModalOpen extends buttonStandard {
    public function __construct($id, $caption = '', $class = 'btn btn-light'){
        parent::__construct($id, $caption, $class);
    }

    public function setModal($formName, array $keyFields){
        $this->addData('toggle', 'modal');
        $this->addData('target', '#ajax-modal');
        $this->addAttribute('href', '/ajax/forms/' . $formName . '/' . implode('|', $keyFields) . '/');

        return $this;
    }
}