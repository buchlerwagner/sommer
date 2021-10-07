<?php
abstract class formControl {
    protected $id;
    protected $sectionId = false;
    protected $name;
    protected $label;
    protected $helpText = [];
    protected $class = [];
    protected $isContainer = false;
    protected $dbField = true;
    protected $hasError = false;

    protected $attributes = [];

    protected $language;
    protected $locals;

    abstract public function getType():string;

    /**
     * formControl constructor.
     * @todo set language properly
     */
    public function __construct(){
        $this->setLanguage('hu');

        if(isset($GLOBALS['REGIONAL_SETTINGS'][$this->language])){
            $this->locals = $GLOBALS['REGIONAL_SETTINGS'][$this->language];
        }else{
            $this->locals = $GLOBALS['REGIONAL_SETTINGS']['default'];
        }
    }

    protected function setId($id){
        $this->id = $id;
        return $this;
    }

    final public function getId():string{
        return str_replace('/', '-', $this->id);
    }

    public function setName($name){
        $this->name = $name;
        return $this;
    }

    final public function getName():string{
        $name = $this->name;
        if(strpos($this->name, '/') !== false){
            $name = str_replace('/', '][', $name);
        }

        return $name;
    }

    public function setLabel($label){
        $this->label = $label;
        return $this;
    }

    public function getLabel(){
        return $this->label;
    }

    final public function setHelpText($text, $icon = true){
        $this->helpText = [
            'text' => $text,
            'icon' => $icon,
        ];
        return $this;
    }

    final public function getHelpText():array{
        return $this->helpText;
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

    final public function isContainer():bool{
        return $this->isContainer;
    }

    final public function addAttribute($key, $value){
        $this->attributes[$key] = $value;
        return $this;
    }

    final public function getAttributes(){
        return $this->attributes;
    }

    final public function addEvent(enumFormEvents $eventType, $action){
        $this->addAttribute($eventType->getValue(), $action);
        return $this;
    }

    final public function addData($key, $value, $translate = false){
        $this->addAttribute('data-' . $key, ($translate ? '_' : '') . $value);
        return $this;
    }

    final public function setError(){
        $this->hasError = true;
        return $this;
    }

    final public function hasError():bool{
        return $this->hasError;
    }

    final public function setLanguage($language){
        $this->language = $language;
        return $this;
    }

    final public function setSectionId($id){
        $this->sectionId = $id;
        return $this;
    }

    final public function getSectionId():string{
        return $this->sectionId;
    }
}