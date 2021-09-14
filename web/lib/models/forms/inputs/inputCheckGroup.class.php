<?php
class inputCheckGroup extends formElement {
    use elementOptions, elementColor;

    const Type = 'checkgroup';

    protected function init() {
    }

    public function getType():string {
        return $this::Type;
    }
}