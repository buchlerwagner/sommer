<?php
class myOrdersTable extends table {

	public function setup() {
		$this->dbTable = 'cart';
        $this->join  = 'LEFT JOIN users ON (us_id = cart_us_id)';
        $this->join .= ' LEFT JOIN shipping_modes ON (sm_id = cart_sm_id)';
        $this->join .= ' LEFT JOIN payment_modes ON (pm_id = cart_pm_id)';

        $this->where = 'cart_status = "ORDERED" AND cart_shop_id = ' . $this->owner->shopId . " AND cart_us_id = " . $this->owner->user->id;

		$this->keyFields = ['cart_id'];

        $this->headerCaption = 'LBL_MY_ORDERS';
        $this->header = true;
        $this->subTable = false;
        $this->options = false;
        $this->delete = false;
		$this->edit = false;
		$this->view = true;
		$this->defaultAction = 'view';

		$this->settings['display']    = 100;
		$this->settings['orderfield'] = 'cart_ordered';
		$this->settings['orderdir']   = 'DESC';

        $this->formName = 'viewMyOrder';

        $this->addColumns(
            (new column('cart_ordered', 'LBL_ORDER_TIME', 3))
                ->setTemplate('{{ _date(val, 5) }}')
                ->addClass('text-center'),
            (new column('cart_order_number', 'LBL_ORDER_NUMBER', 4)),
            (new column('cart_total', 'LBL_TOTAL', 3))
                ->addClass('text-right')
                ->setTemplate('{{ _price(val, row.cart_currency) }}'),
            (new column('cart_order_status', 'LBL_STATUS', 2))
                ->setTemplate('{{ orderState(val)|raw }}')
                ->addClass('text-center'),
            new columnHidden('cart_currency')
        );
	}
}
