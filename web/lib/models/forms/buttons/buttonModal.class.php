<?php
abstract class buttonModal extends formButton {
    const Template = 'button';

    public function __construct($id, $caption = '', $class = 'btn btn-danger'){
        $this->id = $id;
        $this->setName($id);
        $this->caption = $caption;
        $this->class[] = $class;

        $this->init();
    }

    public function init() {
        return $this;
    }

    public function getTemplate() {
        return $this::Template;
    }

    protected function postForm($action, $value = false, $additionalAction = ''):formButton {
        if($value){
            $this->setValue($value);
        }

        $this->addData('confirm-' . $action, ($additionalAction ? $additionalAction . ';' : '') . "$('#" . $this->getForm() . "-form').attr('action', './?" . $this->getForm() . "[" . $this->name . "]=" . $this->value . "').submit();");
        return $this;
    }

    protected function postModalForm($action, $value = false, $additionalAction = ''):formButton {
        if($value){
            $this->setValue($value);
        }

        $this->addData('confirm-' . $action, ($additionalAction ? $additionalAction . ';' : '') . 'postModalForm("#' . $this->getForm() . '-form", ' . $this->value . ', "' . $this->name . '")');
        return $this;
    }

    protected function dialogColor(){
        $color = 'btn-danger';
        if($classes = explode(' ', $this->getClass())){
            $validClasses = ['btn-default', 'btn-info', 'btn-warning', 'btn-danger', 'btn-success'];
            foreach ($classes AS $class){
                if(in_array($class, $validClasses)){
                    $color = $class;
                    break;
                }
            }
        }

        $this->addData('confirm-class', $color);
    }

}