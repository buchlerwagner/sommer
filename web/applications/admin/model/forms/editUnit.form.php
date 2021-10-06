<?php
class editUnitForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['un_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_UNIT';
		$this->dbTable = 'units';

        $this->addControls(
            (new inputText('un_name', 'LBL_NAME'))
                ->setColSize('col-12')
                ->setRequired()
        );

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onBeforeSave() {
        $this->values['un_shop_id'] = $this->owner->shopId;
    }
}
