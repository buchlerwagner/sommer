<?php
class inputRadioList extends formElement {
    use elementOptions, elementColor, elementChangeState;

    const Type = 'radio-list';

    protected function init() {
        $this->setConstraints('ui-enabled', 'false');
    }

    public function getType():string {
        return $this::Type;
    }
}