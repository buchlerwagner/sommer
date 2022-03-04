<?php
class loyaltyRulesTable extends table {

	public function setup() {
		$this->dbTable = 'loyalty_rules';
		$this->keyFields = ['lr_id'];
        $this->where = 'lr_shop_id = ' . $this->owner->shopId;

        $this->formName = 'editLoyaltyRule';
		$this->subTable = true;
		$this->header = true;

		$this->settings['display']    = 50;
		$this->settings['orderfield'] = 'lr_min_order_limit';
		$this->settings['orderdir']   = 'asc';

        $this->addColumns(
            (new column('lr_valid_from', 'LBL_VALID_FROM', 2))
                ->setTemplate('{{ _date(val) }}'),
            (new column('lr_min_order_limit', 'LBL_MIN_ORDER_LIMIT', 2))
                ->setTemplate('{{ _price(val, "' . $this->owner->currencySign . '") }}')
                ->addClass('text-right'),
            (new column('lr_discount', 'LBL_DISCOUNT', 2))
                ->setTemplate('{{ val }}%')
                ->addClass('text-center'),
            (new column('lr_only_paid', 'LBL_ONLY_PAID_ORDERS', 2, enumTableColTypes::YesNo()))
                ->addClass('text-center'),
            (new column('lr_only_finished', 'LBL_ONLY_FINISHED_ORDERS', 2, enumTableColTypes::YesNo()))
                ->addClass('text-center')
        );

        $this->addButton(
            'BTN_NEW_RULE',
            true
        );
	}
}
