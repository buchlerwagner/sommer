<?php
abstract class formContainer extends formControl {
    protected $elements = [];

    abstract public function openTag();

    abstract public function closeTag();

    public function __construct($id, $label = '', $class = ''){
        parent::__construct();

        $this->setId($id);
        $this->label = $label;
        $this->class[] = $class;
        $this->elements = [];
        $this->isContainer = true;
    }

    public function addElements(formControl ...$elements):formControl{
        foreach($elements AS $element) {
            $this->elements[$element->getId()] = $element;
        }
        return $this;
    }

    public function getElements():array{
        return $this->elements;
    }

    public function &getElementsByRef():array{
        return $this->elements;
    }

    protected function buildAttributes():string{
        $attr = [];
        if(!Empty($this->attributes)){
            foreach($this->attributes AS $key => $value){
                $attr[] = $key . '="' . $value . '"';
            }
        }
        return (!Empty($attr) ? ' ' . implode(' ', $attr) : '');
    }

    protected function buildClass($additionalClasses = false):string{
        $class = $this->getClass();
        if(!Empty($additionalClasses)){
            $class .= ' ' . $additionalClasses;
        }

        return (!Empty($class) ? ' class="' . trim($class) . '"' : '');
    }
}