<?php
class holidaysTable extends table {

	public function setup() {
		$this->dbTable = 'holidays';
		$this->keyFields = ['h_id'];
        $this->where = 'h_shop_id = ' . $this->owner->shopId;

        $this->formName = 'editHoliday';
		$this->subTable = true;
		$this->header = true;
		$this->delete = true;
		$this->edit = true;

		$this->settings['display']    = 50;
		$this->settings['orderfield'] = 'h_date';
		$this->settings['orderdir']   = 'asc';

        $this->addColumns(
            (new column('h_date', 'LBL_DATE', 10))
            ->setTemplate('{{ _date(val) }}')
        );

        $this->addButton(
            'BTN_NEW_HOLIDAY',
            true
        );
	}
}
