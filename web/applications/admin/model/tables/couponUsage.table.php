<?php
class couponUsageTable extends table {

	public function setup() {
		$this->dbTable = 'coupon_usage';
        $this->join  = 'LEFT JOIN users ON (us_id = cu_us_id)';
        $this->join .= ' LEFT JOIN cart ON (cart_id = cu_cart_id)';

        $this->where = 'cart_status = "' . CartHandler::CART_STATUS_ORDERED . '"';

        $this->keyFields = ['cu_id', 'cu_c_id'];
        $this->foreignKeyFields = ['cu_c_id'];

		$this->subTable = true;
		$this->header = true;
        $this->edit = false;
        $this->boxed = false;
        $this->delete = false;
        $this->options = false;
        $this->rowClick = false;

		$this->settings['display']    = 20;
		$this->settings['orderfield'] = 'cu_timestamp';
		$this->settings['orderdir']   = 'asc';

        $this->addColumns(
            (new column('cu_timestamp', 'LBL_DATE', 3))
                ->addClass('text-center')
                ->setTemplate('{{ _date(val, 5) }}'),

            (new column('cart_order_number', 'LBL_NAME', 5))
                ->setTemplate('<a href="/orders/view|orders/{{ row.cart_id }}/" target="_blank">{{ row.cart_order_number }}</a><div class="small text-muted">{{ formatName(row.us_firstname, row.us_lastname) }}</div>'),

            (new column('cart_subtotal', 'LBL_ORDER_VALUE', 2))
                ->addClass('text-right')
                ->setTemplate('{{ _price(val, row.cart_currency) }}'),

            (new column('cu_value', 'LBL_DISCOUNT_VALUE', 2))
                ->addClass('text-right')
                ->setTemplate('{{ _price(val, row.cu_currency) }}'),

            new columnHidden('cart_id'),
            new columnHidden('us_firstname'),
            new columnHidden('us_lastname'),
            new columnHidden('cu_currency'),
            new columnHidden('cart_currency')
        );
	}
}
