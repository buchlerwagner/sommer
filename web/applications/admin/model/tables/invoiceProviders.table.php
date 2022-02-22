<?php
class invoiceProvidersTable extends table {

	public function setup() {
		$this->dbTable = 'invoice_providers';
		$this->keyFields = ['iv_id'];
        $this->where = 'iv_shop_id = ' . $this->owner->shopId;

		$this->formName = 'editInvoiceProvider';
        $this->subTable = true;
		$this->header = true;
		$this->copy = true;
        $this->copyChangeFields = [
            'add' => [
                'iv_name' => ' (copy)'
            ],
        ];

		$this->settings['display']    = 50;
		$this->settings['orderfield'] = 'iv_name';
		$this->settings['orderdir']   = 'asc';

        $this->addColumns(
            (new column('iv_enabled', 'LBL_ENABLED', 1, enumTableColTypes::YesNo()))
                ->addClass('text-center'),
            (new column('iv_name', 'LBL_NAME', 4)),
            (new columnOptions('iv_provider', 'LBL_PROVIDER', 4))
                ->setOptions(Invoices::getProviders()),
            (new column('iv_live', 'LBL_PRODUCTION_MODE', 1, enumTableColTypes::YesNo()))
                ->setSelect('NOT iv_test_mode AS iv_live')
                ->addClass('text-center')
        );

        $this->addButton(
            'BTN_NEW_INVOICE_PROVIDER',
            true
        );
	}
}
