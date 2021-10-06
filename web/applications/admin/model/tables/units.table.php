<?php
class unitsTable extends table {

	public function setup() {
		$this->dbTable = 'units';
		$this->keyFields = ['un_id'];
        $this->where = '(un_shop_id = 0 OR un_shop_id = ' . $this->owner->shopId . ')';

        $this->formName = 'editUnit';
		$this->subTable = true;
		$this->header = true;

		$this->settings['display']    = 50;
		$this->settings['orderfield'] = 'un_shop_id, un_id';
		$this->settings['orderdir']   = 'asc';

        $this->addColumns(
            (new column('un_name', 'LBL_NAME', 10)),
            new columnHidden('un_shop_id')
        );

        $this->addButton(
            'BTN_NEW_UNIT',
            true
        );
	}

    public function onAfterLoad() {
        if($this->rows){
            foreach($this->rows AS $index => $row){
                if($row['un_shop_id'] == 0){
                    $this->rows[$index]['options']['delete'] = false;
                    $this->rows[$index]['options']['edit'] = false;
                }
            }
        }
    }

    public function onAfterDelete($keyFields, $real = true) {
        /*
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
        */
    }
}
