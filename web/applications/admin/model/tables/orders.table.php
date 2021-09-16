<?php
class ordersTable extends table {

	public function setup() {
		$this->dbTable = 'cart';
        $this->join  = 'LEFT JOIN users ON (us_id = cart_us_id)';
        $this->join .= ' LEFT JOIN shipping_modes ON (sm_id = cart_sm_id)';
        $this->join .= ' LEFT JOIN payment_modes ON (pm_id = cart_pm_id)';

        $this->where = 'cart_status = "ORDERED" AND sm_shop_id = ' . $this->owner->shopId;

		$this->keyFields = ['cart_id'];

		$this->subTable = true;
		$this->delete = true;
		$this->header = true;
		$this->view = true;
		$this->modalSize = 'lg';
		$this->deleteField = 'cart_deleted';

		$this->settings['display']    = 100;
		$this->settings['orderfield'] = 'cart_ordered';
		$this->settings['orderdir']   = 'DESC';

        $this->formName = 'editCart';

        $this->addColumns(
            (new column('cart_ordered', 'LBL_ORDER_TIME', 2))->setTemplate('{{ _date(val, 5) }}')->addClass('text-center'),
            (new column('us_firstname', 'LBL_NAME', 5))->setTemplate('{{ formatName(val, row.us_lastname) }}'),
            new columnHidden('us_lastname')
        );
	}

    public function loadRows() {
        $this->settings['filters'] = $this->getFilters();
        parent::loadRows();
    }

    private function getFilters(){
        $where = [];

        $filterValues = $this->getSession('customerFilters');

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
                    case 'userName':
                        $where[] = "(us_firstname LIKE '%" . $values . "%' OR us_lastname LIKE '%" . $values . "%' OR CONCAT(us_lastname, ' ', us_firstname) LIKE '%" . $values . "%')";
                        break;

                    default:
                        $where[$field] = $field . " = '$values'";
                        break;
                }
            }

        }

        return $where;
    }
}
