<?php
class todayOrdersTable extends table {

	public function setup() {
		$this->dbTable = 'cart';
        $this->name = 'orders';
        $this->baseURL = 'orders/';

        $this->where  = 'cart_order_status != "CLOSED" AND cart_shop_id = ' . $this->owner->shopId . " AND cart_store_id = '" . $this->owner->storeId . "'";
        $this->where .= ' AND (cart_created >= "' . date('Y-m-d 00:00:00') . '" AND cart_created <= "' . date('Y-m-d 23:59:59') . '")';

        $this->join  = 'LEFT JOIN users AS us1 ON (us1.us_id = cart_us_id)';
        $this->join .= 'LEFT JOIN users AS us2 ON (us2.us_id = cart_created_by)';

        if($this->owner->user->getRole() === USER_ROLE_EMPLOYEE){
            $this->where .= ' AND cart_created_by = ' . $this->owner->user->id;
        }

		$this->keyFields = ['cart_id'];

        $this->headerCaption = 'LBL_TODAY_ORDERS';
		$this->subTable = false;
		$this->delete = true;
		$this->header = true;
		$this->edit = false;
		$this->view = true;
		$this->options = true;
		$this->defaultAction = 'view';
        $this->optionsWidth = 1;
        $this->deleteField = 'cart_deleted';

		$this->settings['display']    = 20;
		$this->settings['orderfield'] = 'cart_created';
		$this->settings['orderdir']   = 'DESC';

        $this->formName = 'viewOrder';
        $this->summarize = [
            0 => [
                0 => [
                    'caption' => 'LBL_TOTAL',
                    'class' => 'col-9',
                    'colspan' => 4
                ],
                1 => [
                    'field'     => 'cart_total',
                    'class'     => 'col-2',
                    'unitfield' => 'cart_currency',
                    'where'     => $this->where
                ],
                2 => [
                    'class'     => 'col-1',
                ]
            ]
        ];

        $this->addColumns(
            (new column('cart_order_status', 'LBL_STATUS', 1))
                ->setTemplate('{{ orderState(val)|raw }}')
                ->addClass('text-center'),
            (new column('cart_order_number', 'LBL_ORDER_NUMBER', 2))
                ->addClass('text-center')
                ->setTemplate('{{ val }}<div class="text-muted"><small>{{ row.seller_name }}</small></div>'),
            (new column('cart_created', 'LBL_ORDER_TIME', 2))
                ->setTemplate('{{ _date(val, 6) }}')
                ->addClass('text-center'),
            (new column('customer_first_name', 'LBL_CUSTOMER', 4))
                ->setSelect('us1.us_firstname AS customer_first_name')
                ->setTemplate('{{ formatName(val, row.customer_last_name) }}'),
            (new column('cart_total', 'LBL_TOTAL', 2))
                ->addClass('text-right')
                ->setTemplate('{{ _price(val, row.cart_currency) }}'),

            (new columnHidden('customer_last_name'))
                ->setSelect('us1.us_lastname AS customer_last_name'),

            new columnHidden('cart_currency'),

            (new columnHidden('seller_name'))
                ->setSelect('CONCAT(us2.us_lastname, " ", us2.us_firstname) AS seller_name')

        );
	}
}
