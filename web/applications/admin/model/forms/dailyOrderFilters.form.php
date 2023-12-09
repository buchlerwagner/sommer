<?php
class dailyOrderFiltersForm extends filterForm {
    public function setupKeyFields() {
    }

    public function setup() {
		parent::setup();
        $this->customRights = ACCESS_RIGHT_WRITE;
        $this->parentTable = 'dailyOrders';
        $this->hasFilter = true;

        $defaultDate = date('Y-m-d');

        $this->addControls(
            (new groupRow('row1'))->addElements(
                (new inputDate('orderDate_min', 'LBL_ORDER_DATE', $defaultDate))
                    ->setIcon('fa fa-calendar')
                    ->setAppend('tól')
                    ->setMaxDate(date('Y-m-d'))
                    ->setColSize('col-6 col-lg-2'),
                (new inputDate('orderDate_max', '', $defaultDate))
                    ->setIcon('fa fa-calendar')
                    ->setColSize('col-6 col-lg-2')
                    ->setMaxDate(date('Y-m-d'))
                    ->addEmptyLabel()
                    ->setAppend('ig'),
                (new inputDate('shippingDate_min', 'LBL_SHIPPING_DATE', $defaultDate))
                    ->setIcon('fa fa-calendar')
                    ->setAppend('tól')
                    ->setColSize('col-6 col-lg-2'),
                (new inputDate('shippingDate_max', '', $defaultDate))
                    ->setIcon('fa fa-calendar')
                    ->setColSize('col-6 col-lg-2')
                    ->addEmptyLabel()
                    ->setAppend('ig')
            ),
            (new groupRow('row2'))->addElements(
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
