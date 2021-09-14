<?php
class inputSelect extends formElement {
    use elementOptions, elementChangeState;

    const Type = 'select';

    private $multiple = false;

    protected function init() {
        $this->addClass('custom-select');
    }

    public function getType():string{
        return $this::Type;
    }

    public function setMultiple($actionBox = false) {
        $this->multiple = true;

        if($actionBox){
            $this->addData('actions-box', 'true');
        }

        return $this;
    }

    public function isMultiple(): bool {
        return $this->multiple;
    }

    public function connectTo($elementIds) {
        if(is_array($elementIds)){
            $elementIds = implode(',', $elementIds);
        }

        $this->addClass('connected-select');
        $this->addData('connected-select', $elementIds);
        return $this;
    }

    public function makeSelectPicker($search = true, $maxVisibleItems = false, $ticker = false) {
        $this->removeClass('custom-select')->addClass('select-picker')->addClass('form-control');

        if($search){
            $this->searchable();
        }

        if($maxVisibleItems){
            $this->maxVisibleItems($maxVisibleItems);
        }

        if($ticker){
            $this->ticker();
        }

        return $this;
    }

    public function makeSelect2($search = true, $visibleItems = false, $ticker = false) {
        $this->addClass('select2');

        if($search){
            $this->searchable();
        }

        if($visibleItems){
            $this->maxVisibleItems($visibleItems);
        }

        if($ticker){
            $this->ticker();
        }

        return $this;
    }

    public function setClearable(){
        $this->addData('allow-clear', 'true');
        return $this;
    }

    public function setSource($url, $default = false) {
        $this->addData('list', $url);

        if($default === false){
            $default = $this->default;
        }

        $this->addData('default-value', $default);
        return $this;
    }

    public function showSubtext() {
        $this->addData('show-subtext', 'true');
        return $this;
    }

    private function searchable() {
        $this->addData('live-search', 'true');
        return $this;
    }

    private function ticker() {
        $this->addClass('show-tick');
        return $this;
    }

    private function maxVisibleItems(int $number = 10) {
        $this->addData('size', $number);
        return $this;
    }
}