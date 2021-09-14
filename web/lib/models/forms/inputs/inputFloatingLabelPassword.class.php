<?php
class inputFloatingLabelPassword extends inputText {
    const Type = 'password';

    public function getType():string {
        return $this::Type;
    }

    public function getTemplate() {
        return 'floatingLabelText';
    }
}