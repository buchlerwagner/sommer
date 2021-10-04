<?php
class viewOrderForm extends formBuilder {
    public $cart;

    public function setupKeyFields() {
        $this->setKeyFields(['cart_id']);
    }

    public function setup() {
        $this->title = 'LBL_VIEW_ORDER';
		$this->dbTable = 'cart';
        $this->boxed = false;

        $this->addExtraField('cart_key');
        $this->addExtraField('cart_order_number');

        $this->includeBefore = 'view-order';


        $this->addButtons(
            new buttonCancel('BTN_BACK')
        );
	}

    public function onAfterInit() {
        $this->setSubtitle($this->values['cart_order_number']);

        $this->owner->cart->init($this->values['cart_key'], false);
        $this->cart = $this->owner->cart;
    }

}
