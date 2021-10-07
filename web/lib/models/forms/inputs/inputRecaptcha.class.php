<?php
class inputRecaptcha extends formElement {
    const Type = 'hidden';

    private $siteKey;
    private $action;

    public function __construct($token, $siteKey, $action){
        $this->siteKey = $siteKey;
        $this->action = $action;

        parent::__construct($token);
    }

    public function getType():string{
        return $this::Type;
    }

    protected function init() {
        $this->addJs('recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . $this->siteKey);
    }

    public function setInlineJs() {
        return "grecaptcha.ready(function() {
                    grecaptcha.execute('" . $this->siteKey . "', {action: '" . $this->action . "'}).then(function(token) {
                        $('#" . $this->getId() . "').val(token);									
                    });
                });";
    }
}