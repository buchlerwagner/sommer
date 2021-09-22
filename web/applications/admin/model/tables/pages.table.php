<?php
class pagesTable extends table {

	public function setup() {
		$this->dbTable = 'contents';
		$this->keyFields = ['c_id'];
        $this->where = 'c_shop_id = ' . $this->owner->shopId . ' AND c_parent_id = 0';

        $this->formName = 'editPage';
		$this->header = true;
		$this->copy = true;
		$this->copyChangeFields = [
		    'add' => [
		        'c_title' => ' (másolat)',
		        'c_page_title' => ' (másolat)',
                'c_page_url' => '-masolat'
            ],
            'replace' => [
                'c_published' => 0,
            ]
        ];

        $this->deleteField = 'c_deleted';

		$this->settings['display']    = 50;
		$this->settings['orderfield'] = 'c_order, c_title';
		$this->settings['orderdir']   = 'asc';
        $this->bodyTemplate = 'table-body-contents';

        $this->addColumns(
            (new column('c_published', 'LBL_PUBLISHED', 1, enumTableColTypes::Checkbox()))
                ->addClass('text-center'),
            (new column('c_title', 'LBL_PAGE_TITLE', 9))
                ->setColspan(2)

        );

        $this->addButton(
            'BTN_NEW_PAGE',
            true,
            [
                'form' => 'addPage'
            ]
        );
	}

    public function onAfterLoad() {
        if($this->rows){
            foreach($this->rows AS $key => $row){
                $this->rows[$key]['items'] = $this->getSubmenu($row['c_id']);
            }
        }
    }

    private function getSubmenu($parentId){
        $out = [];
        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                $this->dbTable,
                [
                    'c_id',
                    'c_published',
                    'c_title',
                ],
                [
                    'c_shop_id' => $this->owner->shopId,
                    'c_parent_id' => (int)$parentId
                ],
                [],
                false,
                'c_order, c_title'
            )
        );
        if($result){
            foreach($result AS $row){
                $out[$row['c_id']] = $row;
                $out[$row['c_id']]['options']['delete'] = true;
            }
        }

        return $out;
    }

    public function onAfterDelete($keyFields, $real = true) {
        $this->owner->mem->delete(CACHE_PAGES);
    }

    public function onCheck($keyValues, $field, $value) {
        $this->owner->mem->delete(CACHE_PAGES);
    }
}
