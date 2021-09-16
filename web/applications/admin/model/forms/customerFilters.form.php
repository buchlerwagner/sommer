<?php
class customerFiltersForm extends filterForm {
    public function setupKeyFields() {
    }

    public function setup() {
		parent::setup();

        $this->customRights = ACCESS_RIGHT_WRITE;
        $this->parentTable = 'customers';

        $this->addControls(
            (new groupRow('row'))->addElements(
                (new inputText('userName', 'LBL_NAME'))->setColSize('col-12 col-sm-6'),
                (new inputText('us_email', 'LBL_EMAIL'))->setColSize('col-12 col-sm-6')
            )
        );
    }
}
