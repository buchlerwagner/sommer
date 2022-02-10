<?php
class orderFiltersForm extends filterForm {
    public function setupKeyFields() {
    }

    public function setup() {
		parent::setup();

        $isEmployee = ($this->owner->user->getRole() == USER_ROLE_EMPLOYEE);

        $this->customRights = ACCESS_RIGHT_WRITE;
        $this->parentTable = 'orders';

        $this->addControls(
            (new groupRow('row1'))->addElements(
                (new inputSelect('cart_order_status', 'LBL_STATUS'))
                    ->setOptions($this->owner->lists->setEmptyItem('LBL_ANY')->getOrderStatuses())
                    ->setColSize('col-12 col-lg-2'),
                (new inputText('freeText', 'LBL_FREE_TEXT'))
                    ->setColSize('col-12 col-lg-3'),
                (new inputText('userName', 'LBL_CUSTOMER_NAME'))
                    ->setColSize('col-12 col-lg-3'),
                (new inputText('us_email', 'LBL_CUSTOMER_EMAIL'))
                    ->setColSize('col-12 col-lg-3')
            ),
            (new groupRow('row2'))->addElements(
                (new inputDate('cart_ordered_min', 'LBL_ORDER_DATE'))
                    ->setIcon('fa fa-calendar')
                    ->setAppend('tól')
                    ->setMaxDate(date('Y-m-d'))
                    ->setColSize('col-6 col-lg-2'),
                (new inputDate('cart_ordered_max', ''))
                    ->setIcon('fa fa-calendar')
                    ->setColSize('col-6 col-lg-2')
                    ->setMaxDate(date('Y-m-d'))
                    ->addEmptyLabel()
                    ->setAppend('ig'),
                (new inputDate('cart_shipping_date_min', 'LBL_SHIPPING_DATE'))
                    ->setIcon('fa fa-calendar')
                    ->setAppend('tól')
                    ->setColSize('col-6 col-lg-2'),
                (new inputDate('cart_shipping_date_max', ''))
                    ->setIcon('fa fa-calendar')
                    ->setColSize('col-6 col-lg-2')
                    ->addEmptyLabel()
                    ->setAppend('ig')
            )
        );

        if(!$isEmployee){
            $this->addControls(
                (new groupRow('row3'))->addElements(
                    (new inputSelect('cart_store_id', 'LBL_STORE'))
                        ->setOptions($this->owner->lists->setEmptyItem('LBL_ANY')->getStores(true))
                        ->setColSize('col-12 col-lg-2'),
                    (new inputSelect('cart_sm_id', 'LBL_DELIVERY_PLACE'))
                        ->setOptions($this->owner->lists->setEmptyItem('LBL_ANY')->getShippingModes())
                        ->setColSize('col-12 col-lg-2'),
                    (new inputSelect('cart_pm_id', 'LBL_PAYMENT_MODE'))
                        ->setOptions($this->owner->lists->setEmptyItem('LBL_ANY')->getPaymentModes())
                        ->setColSize('col-12 col-lg-2'),
                    (new inputAutocomplete('cart_created_by', 'LBL_SALESCLERK'))
                        ->setColSize('col-12 col-lg-3')
                        ->setList('searchEmployees')
                )
            );
        }
    }
}
