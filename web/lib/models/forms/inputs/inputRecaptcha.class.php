<?php
class inputRecaptcha extends formElement {
    const Type = 'recaptcha';

    public function getType():string{
        return $this::Type;
    }

    protected function init() {
    }
}