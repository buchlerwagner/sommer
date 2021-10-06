<?php
class propertiesTable extends table {

	public function setup() {
		$this->dbTable = 'properties';
		$this->keyFields = ['prop_id'];
        $this->where = 'prop_shop_id = ' . $this->owner->shopId;

		$this->formName = 'editProperty';
		$this->subTable = true;
		$this->header = true;

		$this->settings['display']    = 50;
		$this->settings['orderfield'] = 'prop_name';
		$this->settings['orderdir']   = 'asc';

        $this->addColumns(
            (new column('prop_name', 'LBL_NAME', 10))
                ->setTemplate('<i class="{{ row.prop_icon }} fa-fw mr-1"></i> {{ val }}'),
            (new columnHidden('prop_icon'))
        );

        $this->addButton(
            'BTN_NEW_PROPERTY',
            true
        );
	}

    public function onAfterDelete($keyFields, $real = true) {
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLDelete(
                'product_properties',
                [
                    'pp_prop_id' => $keyFields['prop_id']
                ]
            )
        );
    }
}
