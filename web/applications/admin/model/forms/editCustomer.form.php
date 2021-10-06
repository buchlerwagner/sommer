<?php
class editCustomerForm extends formBuilder {
    public function setupKeyFields() {
        $this->setKeyFields(['us_id']);
    }

    public function setup() {
		$this->dbTable = 'users';
		$this->rights = 'users';
		$this->title = 'LBL_EDIT_USER';

        $general = (new groupFieldset('general-data'))->addElements(
            (new groupRow('row1'))->addElements(
                (new inputSelect('us_title', 'LBL_PERSON_TITLE'))
                    ->setColSize('col-3')
                    ->setOptions($this->owner->lib->getList('titles')),
                (new inputText('us_lastname', 'LBL_LASTNAME'))
                    ->setRequired()
                    ->setColSize('col-5'),
                (new inputText('us_firstname', 'LBL_FIRSTNAME'))
                    ->setRequired()
                    ->setColSize('col-4')
            )
            /*
            (new groupRow('row2'))->addElements(
                (new inputText('us_birth_place', 'LBL_BIRTH_PLACE'))
                    ->setColSize('col-5'),
                (new inputDate('us_birth_date', 'LBL_BIRTH_DATE'))
                    ->setIcon('fas fa-calendar')
                    ->setMaxDate(date('Y-m-d'))
                    ->setColSize('col-3'),
                (new inputText('us_mother_name', 'LBL_MOTHER_NAME'))
                    ->setColSize('col-4')
            )
            */
        );

        $contact = (new groupFieldset('contact-data', 'LBL_CONTACT'))->addElements(
            (new inputText('us_email', 'LBL_EMAIL'))
                ->setRequired(),
            (new inputText('us_email2', 'LBL_EMAIL')),
            (new inputText('us_phone', 'LBL_PHONE'))
        );

        $address = (new groupFieldset('address-data', 'LBL_SHIPPING_ADDRESS'))->addElements(
            (new groupRow('row3'))->addElements(
                (new inputSelect('us_country', 'LBL_COUNTRY', 'HU'))
                    ->setOptions($this->owner->lib->getList('countries'))
                    ->setColSize('col-3'),
                (new inputText('us_zip', 'LBL_ZIP'))
                    ->onlyNumbers()
                    ->setColSize('col-2'),
                (new inputText('us_city', 'LBL_CITY'))
                    ->setColSize('col-7'),
                (new inputText('us_address', 'LBL_ADDRESS'))
                    ->setColSize('col-12')
            )
        );

        $invoiceAddress = (new groupFieldset('invoice-address-data', 'LBL_INVOICE_ADDRESS'))->addElements(
            (new groupRow('row4'))->addElements(
                (new inputText('us_invoice_name', 'LBL_INVOICE_NAME'))
                    ->setColSize('col-12'),
                (new inputSelect('us_invoice_country', 'LBL_COUNTRY', 'HU'))
                    ->setOptions($this->owner->lib->getList('countries'))
                    ->setColSize('col-3'),
                (new inputText('us_invoice_zip', 'LBL_ZIP'))
                    ->onlyNumbers()
                    ->setColSize('col-2'),
                (new inputText('us_invoice_city', 'LBL_CITY'))
                    ->setColSize('col-7'),
                (new inputText('us_invoice_address', 'LBL_ADDRESS'))
                    ->setColSize('col-12'),
                (new inputText('us_vat', 'LBL_VAT_NUMBER'))
                    ->onlyNumbers('-')
                    ->setColSize('col-12')
            )
        );

        $role = (new groupFieldset('user-role', 'LBL_ROLE'))->addElements(
            (new inputSelect('us_role', '', 'USER'))
                ->setOptions($this->owner->lib->getList('roles', ['group' => USER_GROUP_CUSTOMERS]))
        );

        $this->addControls(
            $general,
            $contact,
            $address,
            $invoiceAddress,
            $role
        );

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

	public function onAfterInit() {
        $this->setSubtitle($this->values['us_lastname'] . ' ' . $this->values['us_firstname']);
        $this->owner->setPageTitle($this->values['us_lastname'] . ' ' . $this->values['us_firstname']);


	}

	public function onValidate() {
        if($this->values['us_role'] != USER_ROLE_NONE) {
            if (!empty($this->values['us_email'])) {
                $res = $this->owner->db->getFirstRow(
                    "SELECT us_id FROM " . DB_NAME_WEB . ".users WHERE us_shop_id = " . $this->owner->shopId . " us_email LIKE \"" . $this->owner->db->escapeString($this->values['us_email']) . "\" AND us_id != '" . $this->keyFields['us_id'] . "'"
                );
                if (!empty($res)) {
                    $this->addError('ERR_10009', self::FORM_ERROR, ['us_email']);
                }
            }
        }
	}

    public function onBeforeSave() {
        if(!$this->values['us_birth_date']) $this->values['us_birth_date'] = null;
        $this->values['us_shop_id'] = $this->owner->shopId;
    }

    public function onAfterLoadValues() {
        if(!$this->values['us_birth_date']) $this->values['us_birth_date'] = '';

        if($this->values['us_role'] == USER_ROLE_NONE){
            $this->removeControl('us_email');
        }else{
            $this->removeControl('us_email2');
        }
    }
}
