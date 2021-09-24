<?php
class productVariantsTable extends table {

	public function setup() {
		$this->dbTable = 'product_variants';
		$this->keyFields = ['pv_id', 'pv_prod_id'];
		$this->foreignKeyFields = ['pv_prod_id'];
		$this->formName = 'editVariant';
		$this->delete = true;
		$this->header = false;
		$this->hideCounter = true;
		$this->subTable = true;
		$this->copy = true;

		$this->settings['display']    = 25;
		$this->settings['orderfield'] = 'pv_name';
		$this->settings['orderdir']   = 'asc';

		$this->addColumns(
            (new column('pv_name', 'LBL_NAME', 6)),
            (new column('pv_price', 'LBL_PRICE', 4))
                ->addClass('text-right')
                ->setTemplate('{% if row.pv_price_discount > 0 %}<del class="text-muted small mr-3">{{ _price(val, row.pv_currency) }}</del><span class="text-success">{{ _price(row.pv_price_discount, row.pv_currency) }}</span>{% else %}{{ _price(val, row.pv_currency) }}{% endif %}'),

            new columnHidden('pv_currency'),
            new columnHidden('pv_price_discount')
        );

        $this->addButton(
            'BTN_NEW_ITEM',
            true,
            [
                'class' => 'btn btn-outline-primary btn-xs'
            ]
        );
	}
}
