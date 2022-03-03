<?php
class ordersTable extends table {

	public function setup() {
		$this->dbTable = 'cart';
        $this->join  = 'LEFT JOIN users AS us1 ON (us1.us_id = cart_us_id)';
        $this->join .= ' LEFT JOIN shipping_modes ON (sm_id = cart_sm_id)';
        $this->join .= ' LEFT JOIN payment_modes ON (pm_id = cart_pm_id)';

        $this->where = 'cart_order_status != "CLOSED" AND cart_shop_id = ' . $this->owner->shopId;
        $isEmployee = ($this->owner->user->getRole() == USER_ROLE_EMPLOYEE);

        if($isEmployee){
            $this->where .= ' AND cart_store_id = "' . $this->owner->storeId . '"';
            $this->where .= ' AND cart_created_by = ' . $this->owner->user->id;
        }else{
            $this->where .= ' AND cart_status = "ORDERED"';
            $this->join  .= ' LEFT JOIN users AS us2 ON (us2.us_id = cart_created_by)';
        }

		$this->keyFields = ['cart_id'];

		$this->subTable = false;
		$this->delete = true;
		$this->header = true;
		$this->edit = false;
		$this->view = true;
		$this->defaultAction = 'view';
		$this->modalSize = 'lg';
		$this->deleteField = 'cart_deleted';
		$this->optionsWidth = 1;

		$this->settings['display']    = 100;
		$this->settings['orderfield'] = 'cart_ordered';
		$this->settings['orderdir']   = 'DESC';

        $this->formName = 'viewOrder';
        $this->summarize = [
            0 => [
                0 => [
                    'caption' => 'LBL_TOTAL',
                    'class' => 'col-9',
                    'colspan' => 5
                ],
                1 => [
                    'field'     => 'cart_total',
                    'class'     => 'col-1',
                    'unitfield' => 'cart_currency',
                    'where'     => $this->where
                ],
                2 => [
                    'class'     => 'col-2',
                ]
            ]
        ];

        $this->addColumns(
            (new column('cart_order_status', 'LBL_STATUS', 1))
                ->setTemplate('{{ orderState(val)|raw }}')
                ->addClass('text-center'),
            (new column('cart_order_number', 'LBL_ORDER_NUMBER', 2))
                ->setTemplate('{{ val }}{% if row.cart_invoice_number %}<div class="small text-muted">{{ row.cart_invoice_number }}</div>{% endif %}'),
            (new column('cart_ordered', 'LBL_ORDER_TIME', 2))
                ->setTemplate('{{ _date(val, 5) }}' . ($isEmployee ? '' : '<div class="small text-muted">{{ row.seller_name }}</div>'))
                ->addClass('text-center'),

            (new column('cart_shipping_date', 'LBL_SHIPPING_DATE', 1))
                ->setTemplate('{{ _date(val, 3) }}')
                ->addClass('text-center'),
            (new column('customer_first_name', 'LBL_NAME', 3))
                ->setSelect('us1.us_firstname AS customer_first_name')
                ->setTemplate('{{ formatName(val, row.customer_last_name) }}'),
            (new column('cart_total', 'LBL_TOTAL', 1))
                ->addClass('text-right')
                ->setTemplate('{{ _price(val, row.cart_currency) }}'),

            (new columnIcons('cart_paid', 'LBL_PAID', 1))
                ->setIcons([
                    0 => [
                        'icon' => 'fas fa-times',
                        'color' => 'danger'
                    ],
                    1 => [
                        'icon' => 'fas fa-check',
                        'color' => 'success'
                    ],
                    -1 => [
                        'icon' => 'fas fa-undo',
                        'color' => 'info'
                    ],
                ])
                ->addClass('text-center'),

            new columnHidden('cart_invoice_number'),
            new columnHidden('cart_currency'),
            (new columnHidden('customer_last_name'))
                ->setSelect('us1.us_lastname AS customer_last_name')

            //new columnHidden('us_invoice_name'),
            //new columnHidden('us_city'),
            //new columnHidden('us_address'),
            //new columnHidden('us_invoice_city'),
            //new columnHidden('us_invoice_address')
        );

        if(!$isEmployee){
            $this->addColumns(
                (new columnHidden('seller_name'))
                    ->setSelect('CONCAT(us2.us_lastname, " ", us2.us_firstname) AS seller_name')
            );
        }
	}

    public function loadRows() {
        $this->settings['filters'] = $this->getFilters();
        parent::loadRows();
    }

    private function getFilters(){
        $where = [];

        $filterValues = $this->getSession('orderFilters');

        if (!empty($filterValues)) {
            $this->where = 'cart_shop_id = ' . $this->owner->shopId;

            foreach ($filterValues as $field => $values) {
                if (empty($values)) {
                    continue;
                }
                if (is_array($values)) {
                    foreach ($values as $key => $val) {
                        $values[$key] = $this->owner->db->escapestring($val);
                    }
                } else {
                    $values = $this->owner->db->escapestring($values);
                }
                switch ($field) {
                    case 'showDeletedRecords':
                        $this->showDeletedRecords = true;
                        break;
                    case 'freeText':
                        $query = [];
                        $searchFields = [
                            "cart_id",
                            "cart_key",
                            "cart_order_number",
                            "cart_remarks",
                            "cart_invoice_number",
                        ];

                        foreach($searchFields AS $field){
                            $query[] = $field . " LIKE '%" . $values . "%'";
                        }

                        $where[] = "(" . implode(' OR ', $query) . ")";
                        break;
                    case 'userName':
                        $query = [];
                        $searchFields = [
                            "us1.us_firstname",
                            "us1.us_lastname",
                            "CONCAT(us1.us_lastname, ' ', us1.us_firstname)",
                            "us1.us_invoice_name",
                            "us1.us_city",
                            "us1.us_address",
                            "us1.us_invoice_city",
                            "us1.us_invoice_address",
                        ];

                        foreach($searchFields AS $field){
                            $query[] = $field . " LIKE '%" . $values . "%'";
                        }

                        $where[] = "(" . implode(' OR ', $query) . ")";
                        break;

                    case 'cart_created_by':
                        if($values['id']) {
                            $where[$field] = $field . " = '" . $values['id'] . "'";
                        }
                        break;

                    case 'cart_ordered_min':
                        $where[$field] = substr($field, 0, -4) . " >= '" . standardDate($values) . "'";
                        $this->settings['orderfield'] = 'cart_ordered';
                        $this->settings['orderdir']   = 'ASC';
                        break;

                    case 'cart_ordered_max':
                        $where[$field] = substr($field, 0, -4) . " <= '" . standardDate($values) . "'";
                        $this->settings['orderfield'] = 'cart_ordered';
                        $this->settings['orderdir']   = 'ASC';
                        break;

                    case 'cart_shipping_date_min':
                        $where[$field] = substr($field, 0, -4) . " >= '" . standardDate($values) . "'";
                        $this->settings['orderfield'] = 'cart_shipping_date';
                        $this->settings['orderdir']   = 'ASC';
                        break;

                    case 'cart_shipping_date_max':
                        $where[$field] = substr($field, 0, -4) . " <= '" . standardDate($values) . "'";
                        $this->settings['orderfield'] = 'cart_shipping_date';
                        $this->settings['orderdir']   = 'ASC';
                        break;

                    case 'isPaid':
                        if($values == 1){
                            $where[$field] = "cart_paid = 1";
                        }elseif($values == 2){
                            $where[$field] = "cart_paid = 0";
                        }elseif($values == -1){
                            $where[$field] = "cart_paid = -1";
                        }
                        break;

                    default:
                        $where[$field] = $field . " = '$values'";
                        break;
                }
            }
        }

        return $where;
    }

    public function onAfterDelete($keyFields, $real = true) {
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                'cart',
                [
                    'cart_order_status' => ORDER_STATUS_CLOSED,
                ],
                [
                    'cart_id' => $keyFields['cart_id'],
                    'cart_shop_id' => $this->owner->shopId,
                ]
            )
        );
    }
}
