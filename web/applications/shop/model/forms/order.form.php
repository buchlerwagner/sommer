<?php
class orderForm extends formBuilder {
    public $options = [];

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
            (new inputDate('date', 'LBL_SHIPPING_CUSTOM_DATE')),
            (new inputSelect('interval', 'LBL_SHIPPING_INTERVAL')),
            (new inputCheckbox('select_custom_interval', 'LBL_CUSTOM_INTERVAL', 0)),
            (new inputText('custom_interval', 'LBL_SHIPPING_CUSTOM_INTERVAL')),
            (new inputRadio('payment', 'LBL_PAYMENT_MODE')),

            (new inputHidden('key')),

            (new inputTextarea('remarks', 'LBL_REMARKS'))
        );

        $this->getFileOptions();

        $this->addControls(
            (new inputCheckbox('agree_terms', 'LBL_I_AGREE_TERMS_AND_CONDITIONS', 0))
                ->setRequired(),
            (new inputCheckbox('agree_privacy', 'LBL_I_AGREE_PRIVACY_POLICY', 0))
                ->setRequired()
        );

        $this->addButtons(
            new buttonSave()
        );
    }

    public function onAfterInit() {
        if ($this->owner->user->isLoggedIn() && !isset($_REQUEST[$this->name])) {
            if ($user = $this->owner->user->getUser()) {
                $this->values['lastname'] = $user['lastname'];
                $this->values['firstname'] = $user['firstname'];
                $this->values['email'] = $user['email'];
                $this->values['phone'] = $user['phone'];
                $this->values['zip'] = $user['zip'];
                $this->values['city'] = $user['city'];
                $this->values['address'] = $user['address'];

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
        if (!empty($this->values['invoiceaddress'])) {
            if (empty($this->values['invoice_name'])) {
                $this->addError('ERR_1000', self::FORM_ERROR, ['invoice_name']);
            }
            if ($this->values['invoice_type'] == 2 && empty($this->values['vat'])) {
                $this->addError('ERR_1000', self::FORM_ERROR, ['vat']);
            }
            if (empty($this->values['invoice_zip'])) {
                $this->addError('ERR_1000', self::FORM_ERROR, ['invoice_zip']);
            }
            if (empty($this->values['invoice_city'])) {
                $this->addError('ERR_1000', self::FORM_ERROR, ['invoice_city']);
            }
            if (empty($this->values['invoice_address'])) {
                $this->addError('ERR_1000', self::FORM_ERROR, ['invoice_address']);
            }
        } else {
            $this->values['invoice_type'] = 0;
        }

        if (empty($this->values['agree_terms'])) {
            $this->addError('ERR_2001', self::FORM_ERROR, ['agree_terms']);
        }
        if (empty($this->values['agree_privacy'])) {
            $this->addError('ERR_2004', self::FORM_ERROR, ['agree_privacy']);
        }
        if (empty($this->values['shipping'])) {
            $this->addError('ERR_2002', self::FORM_ERROR, ['shipping']);
        }
        if (empty($this->values['payment'])) {
            $this->addError('ERR_2003', self::FORM_ERROR, ['payment']);
        }

        if(!Empty($this->values['select_custom_interval'])){
            $this->values['interval'] = -1;
        }

        if ($this->values['interval'] == -1 && Empty($this->values['custom_interval'])) {
            $this->addError('ERR_2005', self::FORM_ERROR, ['custom_interval']);
        }

        if (!empty($this->values['createaccount']) && !empty($this->values['email'])) {
            $res = $this->owner->db->getFirstRow(
                "SELECT us_id FROM users WHERE us_shop_id = " . $this->owner->shopId . " AND us_email LIKE \"" . $this->owner->db->escapeString($this->values['email']) . "\""
            );
            if (!empty($res)) {
                $this->addError('ERR_EMAIL_REGISTERED_ALREADY', self::FORM_ERROR, ['email']);
            }
        }
    }

    public function saveValues() {
        $key = $this->values['key'];
        $remarks = $this->values['remarks'];

        if(!Empty($this->values['select_custom_interval'])){
            $this->values['interval'] = -1;
        }

        $this->owner->cartHandler->init($key, false);
        $this->owner->cartHandler->setPaymentMode((int)$this->values['payment']);
        $this->owner->cartHandler->setShippingMode((int)$this->values['shipping'], (int)$this->values['interval'], $this->values['custom_interval'], $this->values['date']);

        if(!Empty($this->values['options'])) {
            $this->owner->cartHandler->setOption('documents', $this->values['options']);
        }

        $userId = $this->registerUser();
        $this->owner->cartHandler->makeOrder($userId, $this->values['invoice_type'], $remarks);

        $this->owner->pageRedirect($this->owner->getPageName('finish') . $key . '/');
    }

    private function registerUser() {
        $userData = [];

        $sendRegisterNotification = false;
        $this->values['last_order'] = 'NOW()';

        if (!$this->owner->user->isLoggedIn()) {
            if (!empty($this->values['createaccount'])) {
                $sendRegisterNotification = true;
                $this->values['role'] = USER_ROLE_USER;
                $this->values['enabled'] = 1;
            } else {
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
        } else {
            unset($this->values['email']);
        }

        $this->clearPostData();

        foreach ($this->values as $key => $value) {
            $userData['us_' . $key] = $value;
        }

        if ($this->owner->user->isLoggedIn()) {
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

            $this->owner->user->clearUserDataCache($userId);
        } else {
            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLInsert(
                    'users',
                    $userData
                )
            );

            $userId = $this->owner->db->getInsertRecordId();

            if ($sendRegisterNotification && $userId) {
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

    private function clearPostData() {
        unset(
            $this->values['key'],
            $this->values['remarks'],
            $this->values['payment'],
            $this->values['shipping'],
            $this->values['interval'],
            $this->values['date'],
            $this->values['select_custom_interval'],
            $this->values['custom_interval'],
            $this->values['invoiceaddress'],
            $this->values['createaccount'],
            $this->values['agree_terms'],
            $this->values['agree_privacy'],
            $this->values['options']
        );
    }

    private function getFileOptions(){
        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'documents',
                [
                    'doc_id AS id',
                    'doc_select_text AS text'
                ],
                [
                    'doc_optional' => 1,
                    'doc_mail_types' => [
                        'like' => '%|order-new|%'
                    ],
                    'doc_shop_id' => $this->owner->shopId,
                ]
            )
        );
        if($result){
            foreach($result AS $row){
                $this->options[$row['id']] = $row['text'];

                $this->addControls(
                    (new inputCheckbox('options', $row['text']))
                        ->setStateValues($row['id'])
                );
            }
        }
    }
}