<?php
class PrintOrder extends docs {
    /**
     * @var $cart cart
     */
    private $cart;

    public function setCart(cart $cart){
        $this->cart = $cart;
        return $this;
    }

    protected function generateContent() {
        $this->setTemplate('print-order');
        $this->setData($this->cart->getTemplateData());
    }
}