<?php
class inputTextarea extends formElement {
    use elementPlaceholder;

    const Type = 'textarea';

    private $maxLength = false;
    private $rows = 4;

    protected function init() {
        $this->addJs('autosize', 'autosize/autosize.min.js');
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

    public function setRows($rows){
        $this->rows = $rows;
        return $this;
    }

    public function getRows():int{
        return $this->rows;
    }

    public function setAutosize(){
        $this->addClass('autosize');
        return $this;
    }
}