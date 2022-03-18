<?php
class automaticCouponsTable extends table {

	public function setup() {
		$this->dbTable = 'automatic_coupons';
		$this->keyFields = ['ac_id'];
        $this->where = 'ac_shop_id = ' . $this->owner->shopId;

        $this->formName = 'automaticCouponRule';
		$this->subTable = true;
		$this->header = true;

		$this->settings['display']    = 50;
		$this->settings['orderfield'] = 'ac_min_sale_limit';
		$this->settings['orderdir']   = 'asc';
        $this->optionsWidth = 1;

        $this->addColumns(
            (new column('ac_enabled', 'LBL_ENABLED', 1, enumTableColTypes::Checkbox()))
                ->addClass('text-center'),
            (new column('ac_min_sale_limit', 'LBL_MIN_SALE_LIMIT', 2))
                ->addClass('text-right')
                ->setTemplate('{{ _price(val, "' . $this->owner->currencySign . '") }}'),
            (new column('ac_min_order_limit', 'LBL_MIN_ORDER_LIMIT', 2))
                ->addClass('text-right')
                ->setTemplate('{{ _price(val, "' . $this->owner->currencySign . '") }}'),
            (new column('ac_discount_percent', 'LBL_DISCOUNT', 2))
                ->setTemplate('{% if val %}{{ val }}%{% if row.ac_discount_value %} <span class="small text-muted">{{ _("LBL_AND") }}</span> {% endif %}{% endif %}{% if row.ac_discount_value %}{{ _price(row.ac_discount_value, "' . $this->owner->currencySign . '") }}{% endif %}')
                ->addClass('text-center'),
            (new column('ac_include_discounted_products', 'LBL_DISCOUNTED_PRODUCTS_INCLUDED', 2, enumTableColTypes::YesNo()))
                ->addClass('text-center'),
            (new column('ac_multiple_usage', 'LBL_MULTIPLE_USAGE', 2, enumTableColTypes::YesNo()))
                ->addClass('text-center'),
            new columnHidden('ac_discount_value')
        );

        $this->addButton(
            'BTN_NEW_RULE',
            true
        );
	}
}
