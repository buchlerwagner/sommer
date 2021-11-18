<?php
class editShippingModeForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['sm_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_SHIPPING_MODE';
		$this->dbTable = 'shipping_modes';

        $group = (new groupFieldset('general-1'))->addElements(
            (new groupRow('row1'))->addElements(
                (new inputText('sm_name', 'LBL_TITLE'))
                    ->setColSize('col-12')
                    ->setRequired()
            ),
            (new groupRow('row2'))->addElements(
                (new inputSelect('sm_type', 'LBL_TYPE', 1))
                    ->setOptions($this->owner->lists->reset()->setEmptyItem('LBL_SELECT')->getShippingTypes())
                    ->setRequired()
                    ->setColSize('col-4 col-lg-4'),
                (new inputText('sm_price', 'LBL_FEE', 0))
                    ->setColSize('col-4 col-lg-3')
                    ->addClass('text-right')
                    ->onlyNumbers()
                    ->setAppend($this->owner->currencySign),
                (new inputText('sm_free_limit', 'LBL_FREE_LIMIT', 0))
                    ->setColSize('col-4 col-lg-4')
                    ->addClass('text-right')
                    ->setHelpText('LBL_HELP_SHIPPING_FREE_LIMIT')
                    ->onlyNumbers()
                    ->setAppend($this->owner->currencySign)
            ),
            (new groupRow('row3'))->addElements(
                (new inputText('sm_order', 'LBL_POSITION', $this->getMaxOrder()))
                    ->setColSize('col-12 col-lg-3')
                    ->addClass('text-right')
                    ->onlyNumbers()
            ),
            (new groupRow('row4'))->addElements(
                (new inputTextarea('sm_text', 'LBL_DESCRIPTION'))
                    ->setColSize('col-12')
                    ->setRows(4),
                (new inputTextarea('sm_email_text', 'LBL_EMAIL_TEXT'))
                    ->setColSize('col-12')
                    ->setRows(4)
            )
            //(new inputSwitch('sm_default', 'LBL_DEFAULT_SHIPPING_MODE', 0))
        );

        $this->addTabs(
            (new sectionTab('general', 'LBL_GENERAL', 'fal fa-dolly', true))
                ->addElements($group)
        );

        if($this->keyFields['sm_id']){
            $this->addTabs(
                (new sectionTab('intervals', 'LBL_INTERVALS', 'fal fa-shipping-timed'))
                    ->addElements(
                        (new groupRow('row5'))->addElements(
                            (new inputText('sm_day_diff', 'LBL_DAY_DIFF'))
                                ->setPrepend('LBL_FROM_ACTUAL_DAY')
                                ->onlyNumbers()
                                ->setColSize('col-6')
                                ->addClass('text-right')
                                ->setAppend('LBL_DAYS')
                        ),

                        (new inputSwitch('sm_select_date', 'LBL_SHIPPING_CUSTOM_DATE', 0)),

                        (new inputSwitch('sm_intervals', 'LBL_SHIPPING_INTERVALS', 0))
                         ->changeState(1, enumChangeAction::Show(), '#interval-items')
                        ->changeDefaultState(enumChangeAction::Hide(), '#interval-items'),

                        (new groupFieldset('interval-items'))->addElements(
                            (new subTable('interval-table'))
                                ->addClass('table-responsive')
                                ->add($this->loadSubTable('shippingIntervals')),

                            (new inputSwitch('sm_custom_interval', 'LBL_ENABLE_CUSTOM_INTERVAL', 0))
                                ->setGroupClass('mb-0 mt-4')
                                ->changeState(1, enumChangeAction::Editable(), '#sm_custom_text')
                                ->changeDefaultState(enumChangeAction::Readonly(), '#sm_custom_text'),

                            (new inputText('sm_custom_text', 'LBL_CUSTOM_INTERVAL_INFO'))

                        )
                    )
            );

        }

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onBeforeSave() {
        $this->values['sm_shop_id'] = $this->owner->shopId;
        if(Empty($this->values['sm_select_date'])) $this->values['sm_select_date'] = 0;
        if(Empty($this->values['sm_intervals'])) $this->values['sm_intervals'] = 0;
        if(Empty($this->values['sm_custom_interval'])) $this->values['sm_custom_interval'] = 0;
        if(Empty($this->values['sm_day_diff'])) $this->values['sm_day_diff'] = 0;
        if(Empty($this->values['sm_price'])) $this->values['sm_price'] = 0;
        if(Empty($this->values['sm_free_limit'])) $this->values['sm_free_limit'] = 0;
        if(Empty($this->values['sm_order'])) $this->values['sm_order'] = $this->getMaxOrder();

        /*
        if(Empty($this->values['sm_default'])) {
            $this->values['sm_default'] = 0;
        }else{
            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLUpdate(
                    $this->dbTable,
                    [
                        'sm_default' => 0
                    ],
                    [
                        'sm_shop_id' => $this->owner->shopId,
                    ]
                )
            );
        }
        */
    }

    private function getMaxOrder(){
        $order = 0;
        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                $this->dbTable,
                [
                    'MAX(sm_order) AS maxOrder'
                ],
                [
                    'sm_shop_id' => $this->owner->shopId
                ]
            )
        );
        if($row){
            $order = (int)$row['maxOrder'];
        }

        return ++$order;
    }

}
