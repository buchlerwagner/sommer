<?php
class PrintOrder extends docs {
    /**
     * @var $cart CartHandler
     */
    private $cart;

    public function setCart(CartHandler $cart){
        $this->cart = $cart;
        return $this;
    }

    protected function generateContent() {
        $this->setTemplate('print-order');
        $this->setData($this->cart->getTemplateData());
    }
}