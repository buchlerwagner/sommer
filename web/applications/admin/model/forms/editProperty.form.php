<?php
class editPropertyForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['prop_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_PROPERTY';
		$this->dbTable = 'properties';

        $this->addControls(
            (new inputText('prop_name', 'LBL_NAME'))
                ->setColSize('col-12')
                ->setRequired(),
            (new inputText('prop_icon', 'LBL_ICON'))
                ->setColSize('col-12')
        );

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onBeforeSave() {
        $this->values['prop_shop_id'] = $this->owner->shopId;
    }
}
