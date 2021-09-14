<?php
class inputFloatingLabelTextarea extends inputTextarea {
    const Type = 'textarea';

    public function getType():string {
        return $this::Type;
    }

    public function getTemplate() {
        return 'floatingLabelTextarea';
    }
}