<?php
class slidersTable extends table {

	public function setup() {
		$this->dbTable = 'sliders';
		$this->keyFields = ['s_id'];
        $this->where = 's_shop_id = ' . $this->owner->shopId;

		$this->formName = 'editSlider';
		$this->header = true;
		$this->copy = true;
		$this->copyChangeFields = [
		    'add' => [
		        's_title' => ' (másolat)',
            ],
            'replace' => [
                's_visible' => 0,
                's_link' => ''
            ]
        ];

		$this->settings['display']    = 50;
		$this->settings['orderfield'] = 's_order, s_title';
		$this->settings['orderdir']   = 'asc';

        $this->addColumns(
            (new column('s_visible', 'LBL_VISIBLE', 1, enumTableColTypes::Checkbox()))
                ->addClass('text-center'),
            (new column('s_title', 'LBL_TITLE', 9))
        );

        $this->addButton(
            'BTN_NEW_SLIDE',
            true,
            [
                'form' => 'addSlider'
            ]
        );
	}

    public function onBeforeDelete($keyFields, $real = true) {
        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'sliders',
                [
                    's_image'
                ],
                [
                    's_id' => $keyFields['s_id']
                ]
            )
        );
        if($row){
            $savePath = DIR_UPLOAD . $this->owner->shopId . '/sliders/';

            if(!Empty($row['s_image']) && file_exists($savePath . $row['s_image'])) {
                unlink($savePath . $row['s_image']);
            }
        }

        $this->owner->mem->delete(CACHE_SLIDERS . $this->owner->shopId);

        return true;
    }

    public function onCheck($keyValues, $field, $value) {
        $this->owner->mem->delete(CACHE_SLIDERS . $this->owner->shopId);
    }

}
