<?php
class payModesTable extends table {

	public function setup() {
		$this->dbTable = 'payment_modes';
		$this->keyFields = ['pm_id'];
        $this->where = 'pm_shop_id = ' . $this->owner->shopId;

        $this->formName = 'editPayMode';
		$this->subTable = true;
		$this->header = true;
        $this->modalSize = 'lg';
		$this->copy = true;
		$this->copyChangeFields = [
		    'add' => [
		        'pm_name' => ' (másolat)'
            ],
            'replace' => [
                'pm_enabled' => 0
            ]
        ];

		$this->settings['display']    = 50;
		$this->settings['orderfield'] = 'pm_order, pm_name';
		$this->settings['orderdir']   = 'asc';

        $this->addColumns(
            (new column('pm_enabled', 'LBL_ENABLED', 1, enumTableColTypes::Checkbox()))
                ->addClass('text-center'),
            (new column('pm_name', 'LBL_NAME', 7)),
            (new columnOptions('pm_type', 'LBL_TYPE', 2))
                ->addClass('text-center')
                ->setOptions($this->owner->lists->getPaymentTypes())
        );

        $this->addButton(
            'BTN_NEW_PAYMODE',
            true
        );
	}
}
