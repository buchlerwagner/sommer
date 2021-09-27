<?php
class hostsTable extends table {

	public function setup() {
		$this->dbTable = 'hosts';
		$this->keyFields = ['host_id'];
        $this->where = 'host_shop_id = ' . $this->owner->shopId;

		$this->formName = 'editHost';
		$this->header = true;
		$this->copy = false;
		$this->subTable = true;
        $this->modalSize = 'lg';

		$this->settings['display']    = 50;
		$this->settings['orderfield'] = 'host_name';
		$this->settings['orderdir']   = 'asc';

        $this->addColumns(
            (new column('host_name', 'LBL_HOST_SITE_NAME', 4)),
            (new column('host_host', 'LBL_HOST_NAME', 4)),
            (new column('host_application', 'LBL_APPLICATION', 2))
        );

        $this->addButton(
            'BTN_NEW_HOST',
            true
        );
	}

    public function onBeforeDelete($keyFields, $real = true) {
        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'hosts',
                [
                    'host_host'
                ],
                [
                    'host_id' => $keyFields['host_id']
                ]
            )
        );
        if($row){
            $this->owner->mem->delete(HOST_SETTINGS . $row['host_host']);
        }

        return true;
    }
}
