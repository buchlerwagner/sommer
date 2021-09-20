<?php
class editShippingModeForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['sm_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_SHIPPING_MODE';
		$this->dbTable = 'shipping_modes';

        $group = (new groupFieldset('general'))->addElements(
            (new groupRow('row1'))->addElements(
                (new inputText('sm_name', 'LBL_TITLE'))
                    ->setColSize('col-12')
                    ->setRequired()
            ),
            (new groupRow('row2'))->addElements(
                (new inputText('sm_price', 'LBL_FEE', 0))
                    ->setColSize('col-12 col-lg-3')
                    ->addClass('text-right')
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
        );

        $this->addControls($group);

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onBeforeSave() {
        $this->values['sm_shop_id'] = $this->owner->shopId;
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
