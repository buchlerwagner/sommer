<?php
class editStoreForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['st_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_STORE';
		$this->dbTable = 'stores';

        $group = (new groupFieldset('general'))->addElements(
            (new groupRow('row1'))->addElements(
                (new inputText('st_name', 'LBL_NAME'))
                    ->setColSize('col-12')
                    ->setRequired()
            ),
            (new groupRow('row2'))->addElements(
                (new inputText('st_code', 'LBL_CODE'))
                    ->setColSize('col-6 col-lg-3')
                    ->setMaxLength(2)
                    ->setRequired()
            ),
            (new groupRow('row3'))->addElements(
                (new inputSwitch('st_virtual', 'LBL_VIRTUAL'))
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
        $this->values['st_shop_id'] = $this->owner->shopId;
        $this->values['st_code'] = strtoupper($this->values['st_code']);
    }
}
