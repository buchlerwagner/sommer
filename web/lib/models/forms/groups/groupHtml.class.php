<?php
class groupHtml extends formContainer {
    const Type = 'html';
    private $html;

    public function __construct($id, $html = '', $class = ''){
        $this->id = $id;
        $this->html = $html;
        if($class) {
            $this->addClass($class);
        }
        $this->isContainer = true;
    }

    public function setHtml($html){
        $this->html = $html;
        return $this;
    }

    public function getType():string{
        return $this::Type;
    }

    public function openTag():string {
        return $this->html;
    }

    public function closeTag():string {
        return '';
    }
}