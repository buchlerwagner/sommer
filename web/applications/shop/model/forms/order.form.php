<?php
class orderForm extends formBuilder {

    public function setupKeyFields() {

    }

    public function setup() {
        $this->addControls(
            (new inputText('lastname', 'LBL_LASTNAME'))
                ->setRequired(),
            (new inputText('firstname', 'LBL_FIRSTNAME'))
                ->setRequired(),
            (new inputText('email', 'LBL_EMAIL'))
                ->setRequired(),
            (new inputText('phone', 'LBL_PHONE'))
                ->onlyNumbers('+')
                ->setRequired(),

            (new inputCheckbox('createaccount', 'LBL_CREATE_ACCOUNT', 0)),

            (new inputText('zip', 'LBL_ZIP'))
                ->onlyNumbers()
                ->setRequired(),
            (new inputText('city', 'LBL_CITY'))
                ->setRequired(),
            (new inputText('address', 'LBL_ADDRESS'))
                ->setRequired(),

            (new inputCheckbox('invoiceaddress', 'LBL_DIFFERENT_INVOICE_ADDRESS', 0)),

            (new inputRadio('invoice_type', 'LBL_DIFFERENT_INVOICE_ADDRESS', 0))
                ->setOptions([
                    1 => 'LBL_INVOICE_PRIVATE',
                    2 => 'LBL_INVOICE_COMPANY'
                ]),
            (new inputText('invoice_name', 'LBL_COMPANY_NAME')),
            (new inputText('vat', 'LBL_VAT_NUMBER'))
                ->onlyNumbers('-'),
            (new inputText('invoice_zip', 'LBL_ZIP')),
            (new inputText('invoice_city', 'LBL_CITY'))
                ->onlyNumbers(),
            (new inputText('invoice_address', 'LBL_ADDRESS')),

            (new inputRadio('shipping', 'LBL_SHIPPING_MODE')),
            (new inputRadio('payment', 'LBL_PAYMENT_MODE')),

            (new inputHidden('key')),

            (new inputTextarea('remarks', 'LBL_REMARKS')),

            (new inputCheckbox('agree_terms', 'LBL_I_AGREE_TERMS_AND_CONDITIONS', 0))
                ->setRequired()
        );

        $this->addButtons(
            new buttonSave()
        );
    }

    public function onAfterInit() {
        if($this->owner->user->isLoggedIn() && !isset($_REQUEST[$this->name])) {
            if($user = $this->owner->user->getUser()){
                $this->values['lastname'] = $user['lastname'];
                $this->values['firstname'] = $user['firstname'];
                $this->values['email'] = $user['email'];
                $this->values['phone'] = $user['phone'];
                $this->values['zip'] = $user['zip'];
                $this->values['city'] = $user['city'];

                $this->values['invoice_type'] = $user['invoice_type'];
                $this->values['invoice_name'] = $user['invoice_name'];
                $this->values['invoice_zip'] = $user['invoice_zip'];
                $this->values['invoice_city'] = $user['invoice_city'];
                $this->values['invoice_address'] = $user['invoice_address'];
                $this->values['vat'] = $user['vat'];
            }
        }
    }

    public function onValidate() {
        if(!Empty($this->values['invoiceaddress'])){
            if(Empty($this->values['invoice_name'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['invoice_name']);
            }
            if($this->values['invoice_type'] == 2 && Empty($this->values['vat'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['vat']);
            }
            if(Empty($this->values['invoice_zip'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['invoice_zip']);
            }
            if(Empty($this->values['invoice_city'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['invoice_city']);
            }
            if(Empty($this->values['invoice_address'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['invoice_address']);
            }
        }else{
            $this->values['invoice_type'] = 0;
        }

        if(Empty($this->values['agree_terms'])){
            $this->addError('ERR_2001', self::FORM_ERROR, ['agree_terms']);
        }
        if(Empty($this->values['shipping'])){
            $this->addError('ERR_2002', self::FORM_ERROR, ['shipping']);
        }
        if(Empty($this->values['payment'])){
            $this->addError('ERR_2003', self::FORM_ERROR, ['payment']);
        }

    }

    public function saveValues() {
        $key = $this->values['key'];
        $remarks = $this->values['remarks'];

        $this->owner->cart->init($key, false);
        $this->owner->cart->setPaymentMode((int)$this->values['payment']);
        $this->owner->cart->setShippingMode((int)$this->values['shipping']);

        $userId = $this->registerUser();
        $this->owner->cart->makeOrder($userId, $this->values['invoice_type'], $remarks);

        $this->owner->pageRedirect($this->owner->getPageName('finish') . $key . '/');
    }

    private function registerUser(){
        $userData = [];

        $sendRegisterNotification = false;
        $this->values['last_order'] = 'NOW()';

        if(!$this->owner->user->isLoggedIn()) {
            if(!empty($this->values['createaccount'])){
                $sendRegisterNotification = true;
                $this->values['role'] = USER_ROLE_USER;
                $this->values['enabled'] = 1;
            }else{
                $this->values['role'] = USER_ROLE_NONE;
                $this->values['enabled'] = 0;
                $this->values['email2'] = $this->values['email'];
                $this->values['email'] = uuid::v4();
            }

            $this->values['shop_id'] = $this->owner->shopId;
            $this->values['group'] = USER_GROUP_CUSTOMERS;
            $this->values['hash'] = uuid::v4();
            $this->values['password'] = password_hash(generateRandomString(32), PASSWORD_DEFAULT);
            $this->values['timezone'] = $this->owner->hostConfig['timeZoneID'];
            $this->values['registered'] = 'NOW()';
        }else{
            unset($this->values['email']);
        }

        $this->clearPostData();

        foreach($this->values AS $key => $value){
            $userData['us_' . $key] = $value;
        }

        if($this->owner->user->isLoggedIn()){
            $userId = $this->owner->user->getUser()['id'];

            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLUpdate(
                    'users',
                    $userData,
                    [
                        'us_id' => $userId,
                        'us_shop_id' => $this->owner->shopId,
                    ]
                )
            );

        }else{
            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLInsert(
                    'users',
                    $userData
                )
            );

            $userId = $this->owner->db->getInsertRecordId();

            if($sendRegisterNotification && $userId){
                $this->owner->email->prepareEmail(
                    'register',
                    $userId,
                    [
                        'id' => $userId,
                        'email' => $this->values['email'],
                        'link' => $this->owner->user->setId($userId)->getPasswordChangeLink('REGISTER'),
                    ]
                );
            }
        }

        return $userId;
    }

    private function clearPostData(){
        unset(
            $this->values['key'],
            $this->values['remarks'],
            $this->values['payment'],
            $this->values['shipping'],
            $this->values['invoiceaddress'],
            $this->values['createaccount'],
            $this->values['agree_terms']
        );
    }
}