<?php
class packagingTable extends table {

	public function setup() {
		$this->dbTable = 'packagings';
		$this->keyFields = ['pkg_id'];
        $this->where = 'pkg_shop_id = ' . $this->owner->shopId;

        $this->formName = 'editPackaging';
		$this->subTable = true;
		$this->header = true;
		$this->copy = true;
		$this->copyChangeFields = [
		    'add' => [
		        'pkg_name' => ' (másolat)'
            ]
        ];

		$this->settings['display']    = 50;
		$this->settings['orderfield'] = 'pkg_name';
		$this->settings['orderdir']   = 'asc';

        $this->addColumns(
            (new column('pkg_name', 'LBL_NAME', 8)),
            (new column('pkg_price', 'LBL_PRICE', 2))
                ->addClass('text-right')
                ->setTemplate('{{ _price(val, "' . $this->owner->currency . '") }} / {{ _("LBL_PCS") }}')
        );

        $this->addButton(
            'BTN_NEW_PACKAGING',
            true
        );
	}

    public function onAfterDelete($keyFields, $real = true) {
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                'products',
                [
                    'prod_pkg_id' => 0
                ],
                [
                    'prod_pkg_id' => $keyFields['pkg_id'],
                    'prod_shop_id' => $this->owner->shopId
                ]
            )
        );

        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                'product_variants',
                [
                    'pv_pkg_id' => 0
                ],
                [
                    'pv_pkg_id' => $keyFields['pkg_id']
                ]
            )
        );
    }
}
