<?php
class shippingModesTable extends table {

	public function setup() {
		$this->dbTable = 'shipping_modes';
		$this->keyFields = ['sm_id'];
        $this->where = 'sm_shop_id = ' . $this->owner->shopId;

        $this->formName = 'editShippingMode';
		$this->subTable = true;
		$this->header = true;
        $this->modalSize = 'lg';
		$this->copy = true;
		$this->copyChangeFields = [
		    'add' => [
		        'sm_name' => ' (másolat)'
            ],
            'replace' => [
                'sm_enabled' => 0
            ]
        ];

		$this->settings['display']    = 50;
		$this->settings['orderfield'] = 'sm_order, sm_name';
		$this->settings['orderdir']   = 'asc';

        $this->addColumns(
            (new column('sm_enabled', 'LBL_ENABLED', 1, enumTableColTypes::Checkbox()))
                ->addClass('text-center'),
            (new column('sm_name', 'LBL_NAME', 9))
        );

        $this->addButton(
            'BTN_NEW_SHIPPING_MODE',
            true
        );
	}
}
