<?php
class viewMyOrderForm extends formBuilder {
    public $cart;

    public function setupKeyFields() {
        $this->setKeyFields(['cart_id', 'cart_us_id', 'cart_shop_id']);
    }

    public function setup() {
		$this->dbTable = 'cart';
        $this->boxed = false;

        $this->keyFields['cart_us_id'] = $this->owner->user->id;
        $this->keyFields['cart_shop_id'] = $this->owner->shopId;

        $this->addExtraField('cart_key');
        $this->includeBefore = 'view-order';

        $this->addButtons(
            new buttonCancel('BTN_BACK', 'btn-simple')
        );
	}

    public function onAfterInit() {
        if($this->values['cart_key']) {
            $this->owner->cartHandler->init($this->values['cart_key'], false);
            $this->cart = $this->owner->cartHandler;
        }else{
            $this->owner->pageRedirect($this->owner->getPageName('orders'));
        }
    }
}
