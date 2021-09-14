<?php
class column {
    private $field;
    private $caption;
    private $type = false;
    private $headerClass = [];
    private $cellClass = [];
    private $width = 1;
    private $template = false;

    public function __construct($field, $caption = false, $width = false, enumTableColTypes $type = null){
        $this->field = $field;
        $this->caption = $caption;

        $this->setWidth($width);
        $this->setType(($type ?: enumTableColTypes::General()));
    }

    final protected function setType(enumTableColTypes $type):column{
        $this->type = $type;
        return $this;
    }

    public function addClass(string $class):column{
        $this->setCellClass($class);
        $this->setHeaderClass($class);
        return $this;
    }

    public function setCellClass(string $class):column{
        if(!in_array($class, $this->cellClass)) {
            $this->cellClass[] = $class;
        }
        return $this;
    }

    public function setHeaderClass(string $class):column{
        if(!in_array($class, $this->headerClass)) {
            $this->headerClass[] = $class;
        }
        return $this;
    }

    public function setWidth(int $width):column{
        $this->width = $width;
        return $this;
    }

    public function setTemplate(string $template):column{
        $this->template = $template;
        return $this;
    }

    final public function getField(){
        return $this->field;
    }

    public function getColumn():array{
        $column = [
            'field'   => $this->field,
            'caption' => $this->caption,
            'width'   => $this->width,
        ];

        if($this->type){
            $column['type'] = $this->type;
        }

        if(!Empty($this->cellClass)){
            $column['class'] = implode(' ', $this->cellClass);
        }

        if(!Empty($this->headerClass)){
            $column['headerClass'] = implode(' ', $this->headerClass);
        }

        if($this->template){
            $column['templatestring'] = $this->template;
        }

        if($this->type == enumTableColTypes::Radio()){
            $column['method'] = 'mark';
        }

        return $column;
    }
}