<?php
class columnHidden extends column {
    private $options = [];

    public function __construct($field){
        parent::__construct($field, false, false, enumTableColTypes::Hidden());
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