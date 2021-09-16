<?php
class editPayModeForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['pm_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_PAYMODE';
		$this->dbTable = 'payment_modes';

        $group = (new groupFieldset('general'))->addElements(
            (new groupRow('row1'))->addElements(
                (new inputSelect('pm_type', 'LBL_TYPE', 0))
                    ->setColSize('col-12')
                    ->makeSelectPicker(false)
                    ->setOptions($this->owner->lists->getPaymentTypes())
                    ->setRequired(),
                (new inputText('pm_name', 'LBL_TITLE'))
                    ->setColSize('col-12')
                    ->setRequired()
            ),
            (new groupRow('row2'))->addElements(
                (new inputText('pm_price', 'LBL_PRICE', 0))
                    ->setColSize('col-12 col-lg-3')
                    ->addClass('text-right')
                    ->onlyNumbers()
                    ->setAppend($this->owner->currencySign)
            ),
            (new groupRow('row3'))->addElements(
                (new inputText('pm_order', 'LBL_POSITION', $this->getMaxOrder()))
                    ->setColSize('col-12 col-lg-3')
                    ->addClass('text-right')
                    ->onlyNumbers()
            ),
            (new groupRow('row4'))->addElements(
                (new inputTextarea('pm_text', 'LBL_DESCRIPTION'))
                    ->setColSize('col-12')
                    ->setRows(4),
                (new inputTextarea('pm_email_text', 'LBL_EMAIL_TEXT'))
                    ->setColSize('col-12')
                    ->setRows(4)
            )
        );

        $this->addControls($group);

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onBeforeSave() {
        $this->values['pm_shop_id'] = $this->owner->shopId;
    }

    private function getMaxOrder(){
        $order = 0;
        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                $this->dbTable,
                [
                    'MAX(pm_order) AS maxOrder'
                ],
                [
                    'pm_shop_id' => $this->owner->shopId
                ]
            )
        );
        if($row){
            $order = (int)$row['maxOrder'];
        }

        return ++$order;
    }

}
