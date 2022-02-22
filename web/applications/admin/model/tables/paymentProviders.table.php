<?php
class paymentProvidersTable extends table {

	public function setup() {
		$this->dbTable = 'payment_providers';
		$this->keyFields = ['pp_id'];
        $this->where = 'pp_shop_id = ' . $this->owner->shopId;

		$this->formName = 'editPaymentProvider';
		$this->header = true;
		$this->copy = true;
        $this->copyChangeFields = [
            'add' => [
                'pp_name' => ' (copy)'
            ],
        ];
		$this->subTable = true;

		$this->settings['display']    = 50;
		$this->settings['orderfield'] = 'pp_name';
		$this->settings['orderdir']   = 'asc';

        $this->addColumns(
            (new column('pp_name', 'LBL_NAME', 4)),
            (new columnOptions('pp_provider', 'LBL_PROVIDER', 4))
                ->setOptions(Payments::getProviders()),
            (new column('pp_currency', 'LBL_CURRENCY', 1))
                ->addClass('text-center'),
            (new column('pp_live', 'LBL_PRODUCTION_MODE', 1, enumTableColTypes::YesNo()))
                ->setSelect('NOT pp_test_mode AS pp_live')
                ->addClass('text-center')
        );

        $this->addButton(
            'BTN_NEW_PAYMENT_PROVIDER',
            true
        );
	}

    public function onBeforeDelete($keyFields, $real = true) {
        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'payment_providers',
                [
                    'pp_private_key'
                ],
                [
                    'pp_id' => $keyFields['pp_id']
                ]
            )
        );
        if($row){
            $savePath = DIR_PRIVATE_KEYS . $this->owner->shopId . '/';

            if(!Empty($row['pp_private_key']) && file_exists($savePath . $row['pp_private_key'])) {
                unlink($savePath . $row['pp_private_key']);
            }
        }

        return true;
    }
}
