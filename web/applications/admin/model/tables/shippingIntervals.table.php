<?php
class shippingIntervalsTable extends table {

	public function setup() {
		$this->dbTable = 'shipping_intervals';
		$this->keyFields = ['si_id', 'si_sm_id'];
		$this->foreignKeyFields = ['si_sm_id'];
		$this->formName = 'editInterval';
		$this->delete = true;
		$this->header = true;
		$this->hideCounter = true;
		$this->subTable = true;
		$this->edit = true;
		$this->boxed = false;
        $this->rowClick = false;
        $this->optionTemplate = 'table_options_shipping_intervals';

		$this->settings['display']    = 10;
		$this->settings['orderfield'] = 'si_time_start';
		$this->settings['orderdir']   = 'asc';

		$this->addColumns(
            (new column('si_time_start', 'LBL_INTERVAL', 10))
                ->setTemplate('{{ _date(val, 6, false) }} - {{ _date(row.si_time_end, 6, false) }}'),
            new columnHidden('si_time_end')
        );

        $this->addInlineForm('editInterval', ['smId' => $this->parameters['foreignkeys'][0]]);
	}
}
