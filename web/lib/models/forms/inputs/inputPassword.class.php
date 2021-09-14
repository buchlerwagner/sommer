<?php
class inputPassword extends inputText {
    const Type = 'password';
    private $showTogglePassword = false;

    public function getType():string{
        return $this::Type;
    }

    public function getTemplate() {
        return 'text';
    }

    public function showTogglePassword(bool $show = true){
        $this->showTogglePassword = $show;
        return $this;
    }

    public function isTogglePasswordVisible():bool{
        return $this->showTogglePassword;
    }
}