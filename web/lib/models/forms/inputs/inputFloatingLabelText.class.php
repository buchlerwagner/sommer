<?php
class inputFloatingLabelText extends inputText {
    const Type = 'text';

    public function getType():string {
        return $this::Type;
    }

    public function getTemplate() {
        return 'floatingLabelText';
    }
}