<?php
class productsTable extends table {

	public function setup() {
		$this->dbTable = 'products';
        $this->join  = 'LEFT JOIN product_categories ON (cat_id = prod_cat_id)';
        $this->where = 'prod_shop_id = ' . $this->owner->shopId;

        $this->keyFields = ['prod_id'];
		$this->formName = 'editProduct';
		$this->header = true;
		$this->copy = true;
		$this->copyChangeFields = [
		    'add' => [
		        'prod_name' => ' (másolat)'
            ],
            'replace' => [
                'prod_visible' => 0,
                'prod_available' => 0,
            ]
        ];

        $this->delete = true;
        $this->deleteField = 'prod_archived';

        $this->rowGroups = [
            0 => [
                'field'   => 'cat_title',
                'alias'   => 'cat_title',
            ],
        ];

		$this->settings['display']    = 100;
		$this->settings['orderfield'] = 'cat_order, prod_name';
		$this->settings['orderdir']   = 'asc';

        $this->addColumns(
            (new column('prod_visible', 'LBL_ENABLED', 1, enumTableColTypes::Checkbox())),
            (new column('prod_available', 'LBL_AVAILABLE', 1, enumTableColTypes::Checkbox())),
            (new column('prod_name', 'LBL_PRODUCT_TITLE', 8))
        );

        $this->addButton(
            'BTN_NEW_PRODUCT',
            true,
            [
                'form' => 'addProduct'
            ]
        );
	}

    public function loadRows() {
        $this->settings['filters'] = $this->getFilters();
        parent::loadRows();
    }

    private function getFilters(){
        $where = [];

        $filterValues = $this->getSession('productsFilters');

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
                            "prod_code",
                            "prod_name",
                            "prod_brand_name",
                            "prod_description",
                        ];

                        foreach($searchFields AS $field){
                            $query[] = $field . " LIKE '%" . $values . "%'";
                        }

                        $where[] = "(" . implode(' OR ', $query) . ")";
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
        /**
         * @todo delete images and relevant records
         */
    }
}
