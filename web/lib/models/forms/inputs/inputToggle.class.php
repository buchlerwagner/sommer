<?php
class inputToggle extends inputCheckbox {
    const Type = 'checkbox';

    public function getType():string {
        return $this::Type;
    }

    public function getTemplate() {
        return 'toggle';
    }
}