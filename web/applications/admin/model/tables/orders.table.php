<?php
class ordersTable extends table {

	public function setup() {
		$this->dbTable = 'cart';
        $this->join  = 'LEFT JOIN users ON (us_id = cart_us_id)';
        $this->join .= ' LEFT JOIN shipping_modes ON (sm_id = cart_sm_id)';
        $this->join .= ' LEFT JOIN payment_modes ON (pm_id = cart_pm_id)';

        $this->where = 'cart_status = "ORDERED" AND sm_shop_id = ' . $this->owner->shopId;

		$this->keyFields = ['cart_id'];

		$this->subTable = false;
		$this->delete = true;
		$this->header = true;
		$this->edit = false;
		$this->view = true;
		$this->defaultAction = 'view';
		$this->modalSize = 'lg';
		$this->deleteField = 'cart_deleted';

		$this->settings['display']    = 100;
		$this->settings['orderfield'] = 'cart_ordered';
		$this->settings['orderdir']   = 'DESC';

        $this->formName = 'viewOrder';

        $this->addColumns(
            (new column('cart_order_number', 'LBL_ORDER_NUMBER', 1)),
            (new column('cart_order_status', 'LBL_STATUS', 1))
                ->setTemplate('{{ orderState(val)|raw }}')
                ->addClass('text-center'),
            (new column('cart_ordered', 'LBL_ORDER_TIME', 2))
                ->setTemplate('{{ _date(val, 5) }}')
                ->addClass('text-center'),
            (new column('us_firstname', 'LBL_NAME', 5))
                ->setTemplate('{{ formatName(val, row.us_lastname) }}'),
            (new column('cart_total', 'LBL_TOTAL', 1))
                ->addClass('text-right')
                ->setTemplate('{{ _price(val, row.cart_currency) }}'),
            new columnHidden('cart_currency'),
            new columnHidden('us_lastname'),
            new columnHidden('us_invoice_name'),
            new columnHidden('us_city'),
            new columnHidden('us_address'),
            new columnHidden('us_invoice_city'),
            new columnHidden('us_invoice_address')
        );
	}

    public function loadRows() {
        $this->settings['filters'] = $this->getFilters();
        parent::loadRows();
    }

    private function getFilters(){
        $where = [];

        $filterValues = $this->getSession('orderFilters');

        if (!empty($filterValues)) {
            $this->showDeletedRecords = true;

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
                    case 'freeText':
                        $query = [];
                        $searchFields = [
                            "cart_id",
                            "cart_key",
                            "cart_order_number",
                            "cart_remarks",
                        ];

                        foreach($searchFields AS $field){
                            $query[] = $field . " LIKE '%" . $values . "%'";
                        }

                        $where[] = "(" . implode(' OR ', $query) . ")";
                        break;
                    case 'userName':
                        $query = [];
                        $searchFields = [
                            "us_firstname",
                            "us_lastname",
                            "CONCAT(us_lastname, ' ', us_firstname)",
                            "us_invoice_name",
                            "us_city",
                            "us_address",
                            "us_invoice_city",
                            "us_invoice_address",
                        ];

                        foreach($searchFields AS $field){
                            $query[] = $field . " LIKE '%" . $values . "%'";
                        }

                        $where[] = "(" . implode(' OR ', $query) . ")";
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
