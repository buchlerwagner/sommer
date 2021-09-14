<?php
class link extends formElement {
    const Type = 'link';

    private $url = '';

    public function __construct($id, $label = '', $url = '', $class = ''){
        parent::__construct($id, $label, null, $class);
        $this->setUrl($url);
    }

    public function init() {
        $this->notDBField();
    }

    public function getType():string{
        return $this::Type;
    }

    public function setUrl($url){
        $this->url = $url;
        return $this;
    }

    public function getUrl(){
        return $this->url;
    }

    public function setTarget($target){
        $this->addAttribute('target', $target);
        return $this;
    }

}