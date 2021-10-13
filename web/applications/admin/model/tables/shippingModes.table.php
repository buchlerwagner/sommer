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

    public function onAfterDelete($keyFields, $real = true) {
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLDelete(
                'shipping_intervals',
                [
                    'si_sm_id' => $keyFields['sm_id'],
                    'si_shop_id' => $this->owner->shopId
                ]
            )
        );
    }

    public function onAfterCopy($keyFields, $newId) {
        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'shipping_intervals',
                [
                    'si_shop_id',
                    'si_time_start',
                    'si_time_end',
                ],
                [
                    'si_sm_id' => $keyFields['sm_id'],
                    'si_shop_id' => $this->owner->shopId
                ]
            )
        );
        if($result){
            foreach($result AS $row){
                $row['si_sm_id'] = $newId;

                $this->owner->db->sqlQuery(
                    $this->owner->db->genSQLInsert(
                        'shipping_intervals',
                        $row
                    )
                );
            }
        }
    }
}
