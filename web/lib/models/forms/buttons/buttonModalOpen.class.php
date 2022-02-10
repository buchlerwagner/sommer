<?php
class buttonModalOpen extends buttonStandard {
    public function __construct($id, $caption = '', $class = 'btn btn-light'){
        parent::__construct($id, $caption, $class);
    }

    public function setModal($formName, array $keyFields, $size = false){
        $this->addData('toggle', 'modal');
        $this->addData('target', '#ajax-modal');

        if($size){
            $this->addData('size', $size);
        }

        $this->addAttribute('href', '/ajax/forms/' . $formName . '/' . implode('|', $keyFields) . '/');

        return $this;
    }
}