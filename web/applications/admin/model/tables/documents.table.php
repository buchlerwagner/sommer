<?php
class documentsTable extends table {

	public function setup() {
		$this->dbTable = 'documents';
		$this->keyFields = ['doc_id'];
        $this->join  = 'LEFT JOIN document_types ON (dt_id = doc_dt_id)';
        $this->where = 'doc_shop_id = ' . $this->owner->shopId;

		$this->formName = 'editDocument';
		$this->subTable = true;
		$this->header = true;
		$this->modalSize = 'lg';

		$this->settings['display']    = 50;
		$this->settings['orderfield'] = 'dt_name, doc_filename';
		$this->settings['orderdir']   = 'asc';

        $this->rowGroups = [
            0 => [
                'field'   => 'dt_name',
                'alias'   => 'dt_name',
            ],
        ];

        $this->addColumns(
            (new column('doc_filename', 'LBL_FILENAME', 10))
        );

        $this->addButton(
            'BTN_NEW_DOCUMENT',
            true
        );
	}

    public function onBeforeDelete($keyFields, $real = true) {
        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'documents',
                [
                    'doc_hash'
                ],
                [
                    'doc_id' => $keyFields['doc_id']
                ]
            )
        );
        if($row){
            $savePath = DIR_UPLOAD . $this->owner->shopId . '/documents/';

            if(!Empty($row['doc_hash']) && file_exists($savePath . $row['doc_hash'])) {
                unlink($savePath . $row['doc_hash']);
            }
        }

        return true;
    }
}
