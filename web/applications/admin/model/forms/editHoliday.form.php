<?php
class editHolidayForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['h_id']);
    }

    public function setup() {
        $this->title = 'LBL_EDIT_HOLIDAY';
		$this->dbTable = 'holidays';

        $this->addControls(
            (new groupRow('row1'))->addElements(
                (new inputDate('h_date', 'LBL_DATE'))
                    ->setColSize('col-6')
                    ->setIcon('fas fa-calendar-alt')
            )
        );


        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onBeforeSave() {
        $this->values['h_shop_id'] = $this->owner->shopId;
        $this->values['h_date'] = standardDate($this->values['h_date']);
    }
}
