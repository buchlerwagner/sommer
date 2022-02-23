<?php
class cartItemsTable extends table {
    private $cartId;
    public $isEmployee = true;
    public $orderType = 0;

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
            (new column('local', 'LBL_LOCAL_CONSUMPTION'))
                ->addClass('text-center'),
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
        $this->owner->cartHandler->init($this->cartId, false);
        $this->orderType = $this->owner->cartHandler->getOrderType();
        $this->rows = $this->owner->cartHandler->getCartItems();

        if($this->owner->cartHandler->packagingFee) {
            $this->setUpdateField('.cart-item-packaging', enumJSActions::ShowHideElement(), true);
            $this->setUpdateField('.cart-packaging-fee', enumJSActions::SetHtml(), $this->owner->lib->formatPrice($this->owner->cartHandler->packagingFee, $this->owner->cartHandler->currency));
        }else{
            $this->setUpdateField('.cart-item-packaging', enumJSActions::ShowHideElement(), false);
        }

        if($this->owner->cartHandler->shippingFee) {
            $this->setUpdateField('.cart-item-shipping', enumJSActions::ShowHideElement(), true);
            $this->setUpdateField('.cart-shipping-fee', enumJSActions::SetHtml(), $this->owner->lib->formatPrice($this->owner->cartHandler->shippingFee, $this->owner->cartHandler->currency));
        }else{
            $this->setUpdateField('.cart-item-shipping', enumJSActions::ShowHideElement(), false);
        }

        if($this->owner->cartHandler->shippingFee || $this->owner->cartHandler->packagingFee) {
            $this->setUpdateField('.cart-item-subtotal', enumJSActions::ShowHideElement(), true);
        }else{
            $this->setUpdateField('.cart-item-subtotal', enumJSActions::ShowHideElement(), false);
        }

        $this->setUpdateField('.cart-subtotal', enumJSActions::SetHtml(), $this->owner->lib->formatPrice($this->owner->cartHandler->subtotal, $this->owner->cartHandler->currency));
        $this->setUpdateField('.cart-total', enumJSActions::SetHtml(), $this->owner->lib->formatPrice($this->owner->cartHandler->total, $this->owner->cartHandler->currency));
    }
}
