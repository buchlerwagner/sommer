<?php
class dailyOrdersTable extends table {

	public function setup() {
		$this->dbTable = 'daily_orders';
        $this->groupBy = 'productId, variantId';
        $this->where  = 'shopId = ' . $this->owner->shopId;
        $this->where .= ' AND shippingDate = "' . date('Y-m-d') . '"';

        $this->subTable = false;
		$this->options = false;
		$this->delete = false;
		$this->header = true;
		$this->edit = false;
		$this->view = true;

        $this->includeBefore = 'daily-orders-action-buttons';

        $this->settings['display']    = 200;
		$this->settings['orderfield'] = 'categoryId';
		$this->settings['orderdir']   = 'DESC';

        $this->addGroup('categoryId', 'categoryName');

        $this->addColumns(
            (new column('productName', 'LBL_PRODUCT_TITLE', 5)),
            (new column('variantName', 'LBL_PRODUCT_VARIANT', 5)),
            (new column('totalQuantity', 'LBL_TOTAL_QUANTITY', 2))
                ->setSelect('SUM(quantity) AS totalQuantity')
                ->setTemplate('{{ val }} {{ row.unit }}')
                ->addClass('text-right'),

            new columnHidden('productId'),
            new columnHidden('unit')
        );

	}

    public function loadRows() {
        $this->settings['filters'] = $this->createQuery();
        parent::loadRows();
    }

    public function getFilters(){
        return $this->getSession('dailyOrderFilters');
    }

    private function createQuery(){
        $where = [];

        $filterValues = $this->getFilters();

        if (!empty($filterValues)) {
            $this->where  = 'shopId = ' . $this->owner->shopId;

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
                    case 'shippingDate_min':
                        $where[$field] = substr($field, 0, -4) . " >= '" . standardDate($values) . "'";
                        break;

                    case 'shippingDate_max':
                        $where[$field] = substr($field, 0, -4) . " <= '" . standardDate($values) . "'";
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
