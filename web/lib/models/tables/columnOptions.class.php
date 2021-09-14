<?php
class columnOptions extends column {
    private $options = [];

    public function __construct($field, $caption = false, $width = false){
        parent::__construct($field, $caption, $width, enumTableColTypes::Options());
    }

    public function setOptions(array $options):column{
        $this->options = $options;
        return $this;
    }

    public function getColumn():array{
        $column = parent::getColumn();

        if($this->options){
            $column['options'] = $this->options;
        }

        return $column;
    }
}