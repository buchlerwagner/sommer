<?php
class groupInclude extends formContainer {
    const Type = 'include';
    private $file;
    private $data = [];

    public function __construct($includeFileName, array $data = []){
        $this->setId($includeFileName);

        $this->file = $includeFileName;
        $this->data = $data;
        $this->isContainer = true;
        return $this;
    }

    public function getInclude():string{
        return $this->file;
    }

    public function setData(array $data){
        if(is_array($data) && !Empty($data)){
            foreach($data AS $key => $value){
                $this->data[$key] = $value;
            }
        }
        return $this;
    }

    public function getData(){
        return $this->data;
    }

    public function getType():string{
        return $this::Type;
    }

    public function openTag():string {
        return '';
    }

    public function closeTag():string {
        return '';
    }
}