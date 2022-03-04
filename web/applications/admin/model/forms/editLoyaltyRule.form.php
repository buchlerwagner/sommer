<?php
class editLoyaltyRuleForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['lr_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_RULE';
		$this->dbTable = 'loyalty_rules';

        $group = (new groupFieldset('general'))->addElements(
            (new groupRow('row1'))->addElements(
                (new inputDate('lr_valid_from', 'LBL_VALID_FROM'))
                    ->setColSize('col-12 col-lg-4')
                    ->setIcon('fas fa-calendar')
                    ->setRequired()
            ),
            (new groupRow('row2'))->addElements(
                (new inputText('lr_min_order_limit', 'LBL_MIN_ORDER_LIMIT', 0))
                    ->setColSize('col-12 col-lg-6')
                    ->addClass('text-right')
                    ->setHelpText('LBL_MIN_ORDER_LIMIT_HELP_TEXT')
                    ->onlyNumbers()
                    ->setAppend($this->owner->currencySign),

                (new inputText('lr_discount', 'LBL_DISCOUNT', 0))
                    ->setColSize('col-12 col-lg-6')
                    ->addClass('text-right')
                    ->onlyNumbers()
                    ->setMaskPercent()
            ),
            (new groupRow('row3'))->addElements(
                (new inputSwitch('lr_only_paid', 'LBL_ONLY_PAID_ORDERS'))
                    ->setColSize('col-12'),
                (new inputSwitch('lr_only_finished', 'LBL_ONLY_FINISHED_ORDERS'))
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
        $this->values['lr_shop_id'] = $this->owner->shopId;

        if(Empty($this->values['lr_only_paid'])) $this->values['lr_only_paid'] = 0;
        if(Empty($this->values['lr_only_finished'])) $this->values['lr_only_finished'] = 0;
    }
}
