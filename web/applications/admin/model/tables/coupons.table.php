<?php
class couponsTable extends table {

	public function setup() {
		$this->dbTable = 'coupons';
        $this->join = 'LEFT JOIN coupon_usage ON (c_id = cu_c_id)';

		$this->keyFields = ['c_id'];
        $this->where = 'c_shop_id = ' . $this->owner->shopId;
        $this->groupBy = 'c_id';

        $this->formName = 'editCoupon';
		$this->subTable = true;
		$this->header = true;

		$this->settings['display']    = 100;
		$this->settings['orderfield'] = 'c_created';
		$this->settings['orderdir']   = 'asc';

        $this->modalSize = 'lg';

        $this->addColumns(
            (new column('c_enabled', 'LBL_ENABLED', 1, enumTableColTypes::Checkbox()))
                ->addClass('text-center'),
            (new column('c_code', 'LBL_COUPON_CODE', 2))
                ->addClass('text-center')
                ->setTemplate('<b class="text-primary">{{ val }}</b>'),
            (new column('c_expiry', 'LBL_EXPIRY', 2))
                ->addClass('text-center')
                ->setTemplate('{% if val %}{{ _date(val) }}{% else %}{{ _("LBL_NO_EXPIRY") }}{% endif %}'),
            (new column('c_min_order_limit', 'LBL_MIN_ORDER_LIMIT', 2))
                ->addClass('text-right')
                ->setTemplate('{{ _price(val, "' . $this->owner->currencySign . '") }}'),
            (new column('c_discount_percent', 'LBL_DISCOUNT', 2))
                ->setTemplate('{% if val %}{{ val }}%{% if row.c_discount_value %} <span class="small text-muted">{{ _("LBL_AND") }}</span> {% endif %}{% endif %}{% if row.c_discount_value %}{{ _price(row.c_discount_value, "' . $this->owner->currencySign . '") }}{% endif %}')
                ->addClass('text-center'),

            (new column('cu_id', 'LBL_USED', 1, enumTableColTypes::YesNo()))
                ->addClass('text-center'),

            new columnHidden('c_discount_value'),
            new columnHidden('cu_timestamp')
        );

        $this->addButton(
            'BTN_NEW_COUPON',
            true,
            [
                'size' => 'lg'
            ]
        );
	}

    public function onAfterLoad() {
        if($this->rows){
            foreach($this->rows AS $id => $row){
                if($row['c_expiry'] && $row['c_expiry'] < date('Y-m-d') && Empty($row['cu_id'])){
                    $this->rows[$id]['options']['isDeleted'] = true;
                }
                if($row['cu_id']){
                    $this->rows[$id]['options']['delete'] = false;
                }
            }
        }
    }
}
