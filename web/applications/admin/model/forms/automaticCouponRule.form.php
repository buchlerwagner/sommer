<?php
class automaticCouponRuleForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['ac_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_RULE';
		$this->dbTable = 'automatic_coupons';

        $group = (new groupFieldset('general'))->addElements(
            (new groupRow('row1'))->addElements(
                (new inputText('ac_code_length', 'LBL_CODE_LENGTH', 6))
                    ->setColSize('col-12 col-lg-6')
                    ->addClass('text-right')
                    ->onlyNumbers()
                    ->setMaxLength(3)
                    ->setRequired(),

                (new inputText('ac_expiry_days', 'LBL_EXPIRE_AFTER_DAYS', 7))
                    ->setColSize('col-12 col-lg-6')
                    ->addClass('text-right')
                    ->onlyNumbers()
                    ->setAppend('LBL_DAYS')
                    ->setHelpText('LBL_EXPIRY_HELP_TEXT')
            ),

            (new groupRow('row2'))->addElements(
                (new inputText('ac_min_sale_limit', 'LBL_MIN_SALE_LIMIT', 0))
                    ->setColSize('col-12 col-lg-6')
                    ->addClass('text-right')
                    ->onlyNumbers()
                    ->setHelpText('LBL_MIN_SALE_LIMIT_HELP_TEXT')
                    ->setAppend($this->owner->currencySign),

                (new inputText('ac_min_order_limit', 'LBL_MIN_ORDER_LIMIT', 0))
                    ->setColSize('col-12 col-lg-6')
                    ->addClass('text-right')
                    ->onlyNumbers()
                    ->setHelpText('LBL_MIN_ORDER_LIMIT_HELP_TEXT')
                    ->setAppend($this->owner->currencySign)
            ),

            (new groupRow('row3'))->addElements(
                (new inputText('ac_discount_value', 'LBL_DISCOUNT_VALUE', 0))
                    ->setColSize('col-12 col-lg-6')
                    ->addClass('text-right')
                    ->onlyNumbers()
                    ->setAppend($this->owner->currencySign),
                (new inputText('ac_discount_percent', false, 0))
                    ->addEmptyLabel()
                    ->setColSize('col-12 col-lg-6')
                    ->addClass('text-right')
                    ->onlyNumbers()
                    ->setMaskPercent()
            ),

            (new groupRow('row4'))->addElements(
                (new inputSwitch('ac_include_discounted_products', 'LBL_DISCOUNTED_PRODUCTS_INCLUDED'))
                    ->setColSize('col-12'),
                (new inputSwitch('ac_multiple_usage', 'LBL_MULTIPLE_USAGE'))
                    ->setColSize('col-12')
            )
        );

        $this->addControls($group);

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onBeforeSave() {
        $this->values['ac_shop_id'] = $this->owner->shopId;

        if(Empty($this->values['ac_include_discounted_products'])) $this->values['ac_include_discounted_products'] = 0;
        if(Empty($this->values['ac_multiple_usage'])) $this->values['ac_multiple_usage'] = 0;
        if(Empty($this->values['ac_code_length'])) $this->values['ac_code_length'] = 0;
        if(Empty($this->values['ac_expiry_days'])) $this->values['ac_expiry_days'] = 0;
        if(Empty($this->values['ac_min_sale_limit'])) $this->values['ac_min_sale_limit'] = 0;
        if(Empty($this->values['ac_min_order_limit'])) $this->values['ac_min_order_limit'] = 0;
        if(Empty($this->values['ac_discount_value'])) $this->values['ac_discount_value'] = 0;
        if(Empty($this->values['ac_discount_percent'])) $this->values['ac_discount_percent'] = 0;
    }
}
