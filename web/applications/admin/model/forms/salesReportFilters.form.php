<?php
class salesReportFiltersForm extends filterForm {
    public function setupKeyFields() {
    }

    public function setup() {
		parent::setup();
        $this->customRights = ACCESS_RIGHT_WRITE;
        $this->parentTable = 'salesReport';
        $this->hasFilter = true;

        $defaultDate = date('Y-m-d');

        $this->addControls(
            (new groupRow('row2'))->addElements(

                (new inputDate('shippingDate_min', 'LBL_SHIPPING_DATE', $defaultDate))
                    ->setIcon('fa fa-calendar')
                    ->setAppend('tól')
                    ->setColSize('col-6 col-lg-2'),
                (new inputDate('shippingDate_max', '', $defaultDate))
                    ->setIcon('fa fa-calendar')
                    ->setColSize('col-6 col-lg-2')
                    ->addEmptyLabel()
                    ->setAppend('ig'),
                (new inputSelect('shippingCode', 'LBL_DELIVERY_PLACE'))
                    ->setOptions($this->owner->lists->setEmptyItem('LBL_ANY')->getShippingModes(true))
                    ->setColSize('col-12 col-lg-4'),
                (new inputSelect('categoryId', 'LBL_CATEGORY'))
                    ->makeSelectPicker()
                    ->setOptions($this->owner->lists->setEmptyItem('LBL_ANY')->getCategories(true))
                    ->setColSize('col-12 col-sm-4')

            )
        );
    }
}
