<?php
class storesTable extends table {

	public function setup() {
		$this->dbTable = 'stores';
		$this->keyFields = ['st_id'];
        $this->where = 'st_shop_id = ' . $this->owner->shopId;

        $this->formName = 'editStore';
		$this->subTable = true;
		$this->header = true;
		$this->copy = true;
		$this->copyChangeFields = [
		    'add' => [
		        'st_name' => ' (másolat)'
            ]
        ];

		$this->settings['display']    = 50;
		$this->settings['orderfield'] = 'st_name';
		$this->settings['orderdir']   = 'asc';

        $this->addColumns(
            (new column('st_name', 'LBL_NAME', 8)),
            (new column('st_code', 'LBL_CODE', 1))
                ->addClass('text-center'),
            (new column('st_virtual', 'LBL_VIRTUAL', 1, enumTableColTypes::YesNo()))
                ->addClass('text-center')
        );

        $this->addButton(
            'BTN_NEW_STORE',
            true
        );
	}
}
