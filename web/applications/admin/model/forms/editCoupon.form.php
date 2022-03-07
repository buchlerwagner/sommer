<?php
class editCouponForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['c_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_COUPON';
		$this->dbTable = 'coupons';

        if($_REQUEST['readonly']){
            $this->readonly = true;
        }

        /**
         * @var $coupon DiscountHandler
         */
        $coupon = $this->owner->addByClassName('DiscountHandler');
        $code = $coupon->generateUniqueCode(6);

        $group = (new groupFieldset('general'))->addElements(
            (new groupRow('row1'))->addElements(
                (new inputText('c_code', 'LBL_COUPON_CODE', $code))
                    ->setColSize('col-12 col-lg-8')
                    ->addClass('text-center font-weight-bolder text-primary')
                    ->setReadonly(($this->keyFields['c_id']))
                    ->setRequired(),

                (new inputDate('c_expiry', 'LBL_EXPIRY'))
                    ->setColSize('col-12 col-lg-4')
                    ->setIcon('fas fa-calendar')
                    ->setRequired()
            ),

            (new groupRow('row2'))->addElements(
                (new inputText('c_min_order_limit', 'LBL_MIN_ORDER_LIMIT', 0))
                    ->setColSize('col-12 col-lg-4')
                    ->addClass('text-right')
                    ->onlyNumbers()
                    ->setHelpText('LBL_MIN_ORDER_LIMIT_HELP_TEXT')
                    ->setAppend($this->owner->currencySign),

                (new inputText('c_discount_value', 'LBL_DISCOUNT_VALUE', 0))
                    ->setColSize('col-12 col-lg-4')
                    ->addClass('text-right')
                    ->onlyNumbers()
                    ->setAppend($this->owner->currencySign),
                (new inputText('c_discount_percent', false, 0))
                    ->addEmptyLabel()
                    ->setColSize('col-12 col-lg-4')
                    ->addClass('text-right')
                    ->onlyNumbers()
                    ->setMaskPercent()
            ),

            (new groupRow('row3'))->addElements(
                (new inputSwitch('c_include_discounted_products', 'LBL_DISCOUNTED_PRODUCTS_INCLUDED'))
                    ->setColSize('col-12'),
                (new inputSwitch('c_multiple_usage', 'LBL_MULTIPLE_USAGE'))
                    ->setColSize('col-12')
            )
        );

        $this->addTabs(
            (new sectionTab('general', 'LBL_COUPON', 'far fa-ticket', true))
                ->addElements($group)
        );

        if(!Empty($this->keyFields['c_id'])){
            $usage = (new subTable('coupon-usage-table'))
                ->addClass('table-responsive')
                ->add($this->loadSubTable('couponUsage'));

            $this->addTabs(
                (new sectionTab('usage', 'LBL_COUPON_USAGE', 'far fa-user-tag'))
                    ->addElements($usage)
            );
        }

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onValidate() {
        if(!Empty($this->values['c_code']) && !$this->keyFields['c_id']){
            /**
             * @var $coupon DiscountHandler
             */
            $coupon = $this->owner->addByClassName('DiscountHandler');
            if($coupon->isCouponExists($this->values['c_code'])){
                $this->addError('ERR_COUPON_EXISTS', self::FORM_ERROR, ['c_code']);
            }
        }
    }

    public function onBeforeSave() {
        $this->values['c_shop_id'] = $this->owner->shopId;
        $this->values['c_code'] = strtoupper($this->values['c_code']);

        if(Empty($this->values['c_include_discounted_products'])) $this->values['c_include_discounted_products'] = 0;
        if(Empty($this->values['c_multiple_usage'])) $this->values['c_multiple_usage'] = 0;
        if(Empty($this->values['c_expiry'])) $this->values['c_expiry'] = null;
        if(Empty($this->values['c_min_order_limit'])) $this->values['c_min_order_limit'] = 0;
        if(Empty($this->values['c_discount_value'])) $this->values['c_discount_value'] = 0;
        if(Empty($this->values['c_discount_percent'])) $this->values['c_discount_percent'] = 0;
    }

    public function onAfterInit() {
        $this->setSubtitle($this->values['c_code']);
    }
}
