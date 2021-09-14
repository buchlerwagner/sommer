<?php
class subTable extends formContainer {
    const Type = 'table';
    private $table;

    public function add($table){
        $this->table = $table;
        return $this;
    }

    public function getTable(){
        return $this->table;
    }

    public function getType():string{
        return $this::Type;
    }

    public function openTag():string {
        return '<div id="' . $this->getId() . '"' . $this->buildClass() . '>' . ($this->getLabel() ? '<h4 class="text-primary">' . $this->getLabel() . '<h4>' : '');
    }

    public function closeTag():string {
        return '</div>';
    }
}