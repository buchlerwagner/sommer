<?php
class inputText extends formElement {
    use elementPlaceholder;

    const Type = 'text';
    private $maxLength = false;
    private $clearable = false;

    protected function init() {
    }

    public function getType():string {
        return $this::Type;
    }

    public function setMaxLength(int $length){
        $this->maxLength = $length;
        return $this;
    }

    public function getMaxLength():int{
        return $this->maxLength;
    }

    public function setClearable(bool $clear = true){
        $this->clearable = $clear;
        return $this;
    }

    public function getClearable():bool{
        return $this->clearable;
    }

    public function onlyNumbers($chars = ''){
        $this->addClass('numbersonly');

        if(!Empty($chars)){
            $this->addData('chars', $chars);
        }

        return $this;
    }

    public function onlyAlphaNumeric(){
        $this->addClass('alphanumeric-only');
        return $this;
    }
}