<?php
abstract class formButton {
    protected $id;
    protected $name;
    protected $type;
    protected $url;
    protected $caption;
    protected $class = [];
    protected $attributes = [];
    protected $form;
    protected $icon;
    protected $iconType = false;
    protected $disabled = false;
    protected $readonly = false;
    protected $validate = true;
    protected $hidden = false;
    protected $value = 1;

    protected $showInView = false;
    protected $showInEditor = true;

    abstract public function getTemplate();
    abstract public function init();

    final protected function setType(enumButtonTypes $type):formButton{
        $this->type = $type;
        return $this;
    }

    final public function getType(){
        return $this->type;
    }

    final public function setId($id){
        $this->id = $id;
        return $this;
    }

    final public function getId():string{
        return $this->id;
    }

    final public function setValue($value){
        $this->value = $value;
        return $this;
    }

    final public function getValue():string{
        return $this->value;
    }

    final public function skipValidation(){
        $this->validate = false;
        return $this;
    }

    final public function validate():bool{
        return $this->validate;
    }

    public function setName($name){
        $this->name = $name;
        return $this;
    }

    final public function getName(){
        return $this->name;
    }

    final public function getCaption():string{
        return $this->caption;
    }

    final public function setDisabled(bool $disabled = true):formButton{
        $this->disabled = $disabled;
        return $this;
    }

    final public function isDisabled():bool{
        return $this->disabled;
    }

    final public function setHidden(bool $hidden = true):formButton{
        $this->hidden = $hidden;
        return $this;
    }

    final public function isHidden():bool{
        return $this->hidden;
    }

    final public function addClass($class):formButton{
        $this->class[] = $class;
        return $this;
    }

    final public function getClass():string{
        return implode(' ', $this->class);
    }

    final public function addEvent(enumFormEvents $eventType, $action){
        $this->addAttribute($eventType->getValue(), $action);
        return $this;
    }

    final public function addData($key, $value, $translate = false):formButton{
        $this->addAttribute('data-' . $key, ($translate ? '_' : '') . $value);
        return $this;
    }

    final public function addAttribute($key, $value){
        $this->attributes[$key] = $value;
        return $this;
    }

    final public function getAttributes(){
        return $this->attributes;
    }

    final public function setForm($formName) {
        $this->form = $formName;
        $this->id .= '-' . $formName;
        return $this;
    }

    final public function getForm() {
        return $this->form;
    }

    final public function setIcon($icon, enumIconTypes $type = null){
        $this->icon = $icon;

        if(!$type) {
            $this->iconType = enumIconTypes::FontAwesome();
        }else{
            $this->iconType = $type;
        }

        return $this;
    }

    final public function getIcon(){
        return $this->icon;
    }

    final public function getIconType(){
        return $this->iconType;
    }

    final public function setUrl($url){
        $this->url = $url;
        return $this;
    }

    final public function getUrl(){
        return $this->url;
    }

    final public function setShowInEditor($mode){
        $this->showInEditor = $mode;
        return $this;
    }

    final public function showInEditor(){
        return $this->showInEditor;
    }

    final public function setShowInViewer($mode){
        $this->showInView = $mode;
        return $this;
    }

    final public function showInViewer(){
        return $this->showInView;
    }

    final public function setReadOnly($isReadonly = true){
        $this->readonly = $isReadonly;
        return $this;
    }
}