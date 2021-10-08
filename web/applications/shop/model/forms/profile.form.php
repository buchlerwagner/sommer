<?php
class profileForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['us_id']);
    }

    public function setup() {
        $this->title = 'MENU_PROFILE';
        $this->dbTable = 'users';
        $this->boxed = false;
        $this->displayErrors = true;
        $this->keyFields['us_id'] = $this->owner->user->id;

        $this->addControls(
            (new inputFloatingLabelText('us_lastname', 'LBL_LASTNAME'))
                ->setRequired(),
            (new inputFloatingLabelText('us_firstname', 'LBL_FIRSTNAME'))
                ->setRequired(),
            (new inputFloatingLabelText('us_email', 'LBL_EMAIL'))
                ->setReadonly(),
            (new inputFloatingLabelText('us_phone', 'LBL_PHONE'))
                ->onlyNumbers('+'),

            (new groupFieldset('shipping-address', 'LBL_SHIPPING_ADDRESS', 'mt-4'))
                ->addElements(
                    (new groupRow('row1'))
                        ->addElements(
                            (new inputFloatingLabelText('us_zip', 'LBL_ZIP'))
                                ->onlyNumbers()
                                ->setColSize('col-4 col-lg-2'),
                            (new inputFloatingLabelText('us_city', 'LBL_CITY'))
                                ->setColSize('col-8 col-lg-10'),
                            (new inputFloatingLabelText('us_address', 'LBL_ADDRESS'))
                                ->setColSize('col-12')

                        )
                ),

            (new groupFieldset('invoice-address', 'LBL_INVOICE_ADDRESS', 'mt-4'))
                ->addElements(
                    (new inputRadio('us_invoice_type'))
                        ->changeState(2, enumChangeAction::Editable(), '#us_vat')
                        ->changeDefaultState(enumChangeAction::Readonly(), '#us_vat')
                        ->setOptions([
                            1 => 'LBL_INVOICE_PRIVATE',
                            2 => 'LBL_INVOICE_COMPANY',
                        ]),

                    (new groupRow('row2'))
                        ->addElements(
                            (new inputFloatingLabelText('us_invoice_name', 'LBL_COMPANY_NAME'))
                                ->setColSize('col-12 col-lg-8'),
                            (new inputFloatingLabelText('us_vat', 'LBL_VAT_NUMBER'))
                                ->setColSize('col-12  col-lg-4'),
                            (new inputFloatingLabelText('us_invoice_zip', 'LBL_ZIP'))
                                ->onlyNumbers()
                                ->setColSize('col-4 col-lg-2'),
                            (new inputFloatingLabelText('us_invoice_city', 'LBL_CITY'))
                                ->setColSize('col-8 col-lg-10'),
                            (new inputFloatingLabelText('us_invoice_address', 'LBL_ADDRESS'))
                                ->setColSize('col-12')
                        )
                )

        );

        $this->addButtons(
            new buttonSave('BTN_SAVE', 'btn-theme')
        );
    }

    public function onAfterLoadValues() {
        if(!$this->values['us_invoice_type']) $this->values['us_invoice_type'] = 1;
        if($this->values['us_invoice_type'] == 1) {
            $this->getControl('us_vat')->setReadonly();
        }
    }

    public function onValidate() {

    }

    public function onBeforeSave() {
        $this->values['us_shop_id'] = $this->owner->shopId;
        if($this->values['us_invoice_type'] == 1) {
            $this->values['us_vat'] = '';
        }
    }

    public function onAfterSave($statement) {
        $this->owner->pageRedirect($this->owner->getPageName('account') . '?success');
    }
}