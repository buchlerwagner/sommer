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

                (new inputDate('orderDate_min', 'LBL_ORDER_DATE', $defaultDate))
                    ->setIcon('fa fa-calendar')
                    ->setAppend('tól')
                    ->setColSize('col-6 col-lg-2'),
                (new inputDate('orderDate_max', '', $defaultDate))
                    ->setIcon('fa fa-calendar')
                    ->setColSize('col-6 col-lg-2')
                    ->addEmptyLabel()
                    ->setAppend('ig'),
                (new inputSelect('orderOrigin', 'LBL_STORE'))
                    ->setOptions($this->owner->lists->setEmptyItem('LBL_ANY')->getStores(true))
                    ->setColSize('col-12 col-lg-2'),
                (new inputSelect('sellerId', 'LBL_SALESCLERK'))
                    ->setOptions($this->owner->lists->setEmptyItem('LBL_ANY')->getEmployees())
                    ->setColSize('col-12 col-lg-4'),
                (new inputSelect('isPaid', 'LBL_PAYMENT_STATUS'))
                    ->setOptions($this->owner->lists->setEmptyItem('LBL_ANY')->getPaymentStatus())
                    ->setColSize('col-12 col-lg-2')
            )
        );
    }
}
