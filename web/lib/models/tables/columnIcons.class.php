<?php
class columnIcons extends column {
    private $icons = [];

    public function __construct($field, $caption = false, $width = false){
        parent::__construct($field, $caption, $width, enumTableColTypes::Icon());
    }

    public function setIcons(array $icons):column{
        $this->icons = $icons;
        return $this;
    }

    public function getColumn():array{
        $column = parent::getColumn();

        if($this->icons){
            $column['icons'] = $this->icons;
        }

        return $column;
    }
}