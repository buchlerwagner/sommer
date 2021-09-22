<?php
class categoriesTable extends table {

	public function setup() {
		$this->dbTable = 'product_categories';
		$this->keyFields = ['cat_id'];
        $this->where = 'cat_shop_id = ' . $this->owner->shopId;

		$this->formName = 'editCategory';
		$this->header = true;
		$this->copy = true;
		$this->copyChangeFields = [
		    'add' => [
		        'cat_title' => ' (másolat)',
		        'cat_url' => '-masolat'
            ],
            'replace' => [
                'cat_visible' => 0
            ]
        ];

		$this->settings['display']    = 50;
		$this->settings['orderfield'] = 'cat_order, cat_title';
		$this->settings['orderdir']   = 'asc';

        $this->addColumns(
            (new column('cat_visible', 'LBL_VISIBLE', 1, enumTableColTypes::Checkbox()))
                ->addClass('text-center'),
            (new column('cat_title', 'LBL_TITLE', 9))
        );

        $this->addButton(
            'BTN_NEW_CATEGORY',
            true,
            [
                'form' => 'addCategory'
            ]
        );
	}

    public function onBeforeDelete($keyFields, $real = true) {
        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'product_categories',
                [
                    'cat_page_img'
                ],
                [
                    'cat_id' => $keyFields['cat_id']
                ]
            )
        );
        if($row){
            $savePath = DIR_UPLOAD . $this->owner->shopId . '/products/' . $keyFields['cat_id'] . '/';

            if(!Empty($row['cat_page_img']) && file_exists($savePath . $row['cat_page_img'])) {
                unlink($savePath . $row['cat_page_img']);
            }
        }

        $this->owner->mem->delete(CACHE_CATEGORIES);

        return true;
    }

    public function onCheck($keyValues, $field, $value) {
        $this->owner->mem->delete(CACHE_CATEGORIES);
    }
}
