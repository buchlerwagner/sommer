<?php
abstract class formElement extends formControl {
    protected $default = null;
    protected $readonly = false;
    protected $disabled = false;
    protected $required = false;

    protected $prepend = false;
    protected $append = false;

    protected $constraints = [];
    protected $css = [];
    protected $js = [];
    protected $groupClasses = [];
    protected $size = false;

    protected $icon = false;
    protected $iconType = 'fa';
    protected $iconPosition = false;
    protected $iconColor = null;

    abstract protected function init();

    public function __construct($id, $label = '', $default = null, $class = ''){
        parent::__construct();

        $this->isContainer = false;
        $this->setId($id);
        $this->setName($id);

        $this->label = $label;
        $this->default = $default;
        $this->class[] = $class;

        $this->init();
    }

    public function getTemplate(){
        return $this->getType();
    }

    public function setInlineJs(){
        return false;
    }

    final public function getDefault(){
        return $this->default;
    }

    final public function setDisabled(bool $disabled = true){
        $this->disabled = $disabled;
        return $this;
    }

    final public function isDisabled():bool{
        return $this->disabled;
    }

    final public function setReadonly(bool $readonly = true){
        $this->readonly = $readonly;
        return $this;
    }

    final public function isReadonly():bool{
        return $this->readonly;
    }

    final public function setRequired(bool $required = true){
        $this->setConstraints('required', $required);
        return $this;
    }

    final public function isRequired():bool{
        return (bool)$this->constraints['required'];
    }

    final public function notDBField(){
        $this->dbField = false;
        return $this;
    }

    final public function isDBField():bool{
        return $this->dbField;
    }

    final public function setConstraints($key, $value){
        $this->constraints[$key] = $value;

        $this->addAttribute('data-parsley-' . $key, $value);
        return $this;
    }

    final public function getConstraints(){
        return $this->constraints;
    }

    final protected function addCss($id, $file){
        $this->css[$id] = $file;
        return $this;
    }

    final protected function addJs($id, $file){
        $this->js[$id] = $file;
        return $this;
    }

    final public function getCss():array{
        return $this->css;
    }

    final public function getJs():array{
        return $this->js;
    }

    final public function setPrepend($tag){
        $this->prepend = $tag;
        return $this;
    }

    final public function getPrepend():string{
        return $this->prepend;
    }

    final public function setAppend($tag){
        $this->append = $tag;
        return $this;
    }

    final public function getAppend():string{
        return $this->append;
    }

    final public function setColSize($columnSize){
        $this->setGroupClass($columnSize);
        return $this;
    }

    final public function setInputSize(enumSizes $inputSize){
        $this->size = $inputSize;
        return $this;
    }

    final public function getInputSize():string{
        return $this->size;
    }

    final public function setGroupClass($class){
        $this->groupClasses[] = $class;
        return $this;
    }

    final public function addEmptyLabel(){
        $this->setGroupClass('pt-empty-label');
        return $this;
    }

    final public function getGroupClasses():string{
        return ($this->groupClasses ? ' ' . implode(' ', $this->groupClasses) : '');
    }

    final public function setIcon($icon, enumIconTypes $type = null, enumColors $color = null, $positionRight = false){
        $this->icon = $icon;
        if(!$type) {
            $this->iconType = enumIconTypes::FontAwesome();
        }else{
            $this->iconType = $type;
        }

        $this->iconColor = $color;
        $this->iconPosition = $positionRight;
        return $this;
    }

    final public function getIcon():string{
        return $this->icon;
    }

    final public function getIconType(){
        return $this->iconType;
    }

    final public function getIconColor(){
        return $this->iconColor;
    }

    final public function getIconPosition():bool{
        return $this->iconPosition;
    }
}