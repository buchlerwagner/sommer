<?php
abstract class formSections {
    protected $id;
    protected $type;
    protected $title;
    protected $active = false;
    protected $text;
    protected $icon;

    protected $elements = [];
    protected $class = [];

    abstract public function getType():string;

    final public function getId():string{
        return $this->id;
    }

    public function getTitle(){
        return $this->title;
    }

    public function getIcon(){
        return $this->icon;
    }

    public function getText(){
        return $this->text;
    }

    final public function isActive():bool{
        return $this->active;
    }

    public function addElements(...$elements):formSections{
        foreach($elements AS $element) {
            $this->elements[$element->getId()] = $element;
        }

        return $this;
    }

    public function getElements():array{
        return $this->elements;
    }

    final public function addClass($class){
        if(!in_array($class, $this->class)) {
            $this->class[] = $class;
        }
        return $this;
    }

    final public function removeClass($class){
        foreach($this->class AS $key => $value){
            if($value == $class){
                unset($this->class[$key]);
                break;
            }
        }

        return $this;
    }

    final public function getClass():string{
        return implode(' ', $this->class);
    }
}