<?php
class cartItemsTable extends table {
    private $cartId;
    public $isEmployee = true;

	public function setup() {
        $this->cartId = (int) $this->parameters['foreignkeys'][0];
        $this->isEmployee = ($this->owner->user->getRole() == USER_ROLE_EMPLOYEE);

        $this->subTable = true;
		$this->header = true;
        $this->tableClass = 'table';
        $this->tableType = 'inline';
        $this->bodyTemplate = 'table-body-cart-items';
        $this->hideCounter = true;

        $this->addColumns(
            (new column('name', 'LBL_PRODUCT_TITLE', 3))
                ->setColspan(2),
            (new column('unitprice', 'LBL_UNIT_PRICE', 2))
                ->addClass('text-right'),
            (new column('quantity', 'LBL_QUANTITY', 3))
                ->addClass('text-center'),
            (new column('price', 'LBL_TOTAL', 2))
                ->addClass('text-right')
        );
	}

    public function loadRows() {
        $this->owner->cart->init($this->cartId, false);
        $this->rows = $this->owner->cart->items;

        if($this->owner->cart->packagingFee) {
            $this->setUpdateField('.cart-item-packaging', enumJSActions::ShowHideElement(), true);
            $this->setUpdateField('.cart-packaging-fee', enumJSActions::SetHtml(), $this->owner->lib->formatPrice($this->owner->cart->packagingFee, $this->owner->cart->currency));
        }else{
            $this->setUpdateField('.cart-item-packaging', enumJSActions::ShowHideElement(), false);
        }

        if($this->owner->cart->shippingFee) {
            $this->setUpdateField('.cart-item-shipping', enumJSActions::ShowHideElement(), true);
            $this->setUpdateField('.cart-shipping-fee', enumJSActions::SetHtml(), $this->owner->lib->formatPrice($this->owner->cart->shippingFee, $this->owner->cart->currency));
        }else{
            $this->setUpdateField('.cart-item-shipping', enumJSActions::ShowHideElement(), false);
        }

        if($this->owner->cart->shippingFee || $this->owner->cart->packagingFee) {
            $this->setUpdateField('.cart-item-subtotal', enumJSActions::ShowHideElement(), true);
        }else{
            $this->setUpdateField('.cart-item-subtotal', enumJSActions::ShowHideElement(), false);
        }

        $this->setUpdateField('.cart-subtotal', enumJSActions::SetHtml(), $this->owner->lib->formatPrice($this->owner->cart->subtotal, $this->owner->cart->currency));
        $this->setUpdateField('.cart-total', enumJSActions::SetHtml(), $this->owner->lib->formatPrice($this->owner->cart->total, $this->owner->cart->currency));
    }
}
