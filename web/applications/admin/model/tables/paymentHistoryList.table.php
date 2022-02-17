<?php
class paymentHistoryListTable extends table {

	public function setup() {
		$this->dbTable = 'payment_transactions';
		$this->keyFields = ['pt_id', 'pt_cart_id'];
        $this->foreignKeyFields = ['pt_cart_id'];
        $this->where = 'pt_shop_id = ' . $this->owner->shopId;

		$this->header = true;
		$this->copy = false;
		$this->edit = false;
		$this->subTable = true;
		$this->options = false;

		$this->settings['display']    = 10;
		$this->settings['orderfield'] = 'pt_created';
		$this->settings['orderdir']   = 'desc';

        $this->addColumns(
            (new column('pt_status', 'LBL_STATUS', 2))
                ->setTemplate('{{ paymentState(val)|raw }}')
                ->addClass('text-center'),
            (new column('pt_created', 'LBL_CREATED', 2))
                ->setTemplate('{{ _(val, 5) }}')
                ->addClass('text-center'),
            (new column('pt_transactionid', 'LBL_TRANSACTION_ID', 2))
                ->setTemplate('{{ val }}{% if row.pt_auth_code %}<br>{{ row.pt_auth_code }}{% endif %}'),
            (new column('pt_amount', 'LBL_AMOUNT', 2))
                ->addClass('text-right')
                ->setTemplate('{{ _price(val, row.pt_currency) }}'),
            (new column('pt_message', 'LBL_MESSAGE', 4)),

            new columnHidden('pt_auth_code'),
            new columnHidden('pt_currency')
        );
	}
}
