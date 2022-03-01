<?php
class finishOrderForm extends formBuilder {
    private $cartId;
    private $cartKey;
    private $userRole;
    private $orderType = 1;
    private $close = false;

    /**
     * @var CartHandler
     */
    private $order = null;

    public function setupKeyFields() {
        $this->setKeyFields(['us_id']);
        $this->cartId = $this->parameters['keyvalues'][0];
        unset($this->parameters['keyvalues']);
    }

    public function setup() {
        $this->loadOrderData();

        $this->dbTable = 'users';
        $this->title = 'LBL_CLOSE_ORDER';
        $this->reloadPage = true;

        $this->addExtraField('us_email2', false);
        $this->addExtraField('us_role', false);

        $defaultInvoiceOption = 0;
        $defaultHidden = '.invoice-data';
        $invoiceOptions = [
            -1 => 'LBL_INVOICE_NO',
            1 => 'LBL_INVOICE_PRIVATE',
            2 => 'LBL_INVOICE_COMPANY',
        ];

        if($this->orderType === ORDER_TYPE_ORDER) {
            $defaultInvoiceOption = 1;
            $defaultHidden = '.invoice-vat';
            unset($invoiceOptions[-1]);
        }

        $invoice = (new groupFieldset('invoice'))->addElements(
            (new inputSelect('invoiceType', 'LBL_INVOICING', $defaultInvoiceOption))
                ->notDBField()
                //->setColor(enumColors::Primary())
                ->setOptions($invoiceOptions)
                ->changeState(1, enumChangeAction::Hide(), '.invoice-vat')
                ->changeState(1, enumChangeAction::Show(), '.invoice-data')
                ->changeState(2, enumChangeAction::Show(), '.invoice-data, .invoice-vat')
                ->changeDefaultState(enumChangeAction::Hide(), $defaultHidden)
        );
        $this->addControls($invoice);

        $search = (new groupFieldset('search-user', false, 'invoice-data'))->addElements(
            (new inputAutocomplete('user'))
                ->notDBField()
                ->setClearable()
                ->setPlaceholder('LBL_SEARCH_CUSTOMER')
                ->addClass('form-control-lg')
                ->callback('loadUserData')
                ->setList('searchCustomers'),
            (new inputButton('clear-user', 'BTN_NEW_CUSTOMER', 0, 'btn btn-light'))
                ->notDBField()
                ->setIcon('fal fa-user-times')
                ->addClass('clear-inputs')
        );

        $general = (new groupFieldset('general-data', 'LBL_CONTACT_DATA', 'invoice-data'))->addElements(
            (new groupRow('row1'))->addElements(
                (new inputText('us_lastname', 'LBL_LASTNAME'))
                    ->setColSize('col-6'),
                (new inputText('us_firstname', 'LBL_FIRSTNAME'))
                    ->setColSize('col-6')
            ),
            (new inputText('us_email', 'LBL_EMAIL')),
            (new inputText('us_phone', 'LBL_PHONE'))
        );

        $this->addControls(
            $search,
            $general
        );

        if($this->orderType === ORDER_TYPE_ORDER) {
            $shippingAddress = (new groupFieldset('address-data', 'LBL_SHIPPING_ADDRESS', 'invoice-data'))
                ->addTools('LBL_SAME_AS_INVOICE_ADDRESS', 'copy-invoice-address', 'fal fa-copy')
                ->addElements(
                (new groupRow('row3'))->addElements(
                    (new inputSelect('us_country', 'LBL_COUNTRY', 'HU'))
                        ->setOptions($this->owner->lists->getCountries())
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

            $this->addControls($shippingAddress);
        }

        $invoiceAddress = (new groupFieldset('invoice-address-data', 'LBL_INVOICE_ADDRESS', 'invoice-data'))
            ->addElements(
                (new groupRow('row6', false, 'invoice-vat'))->addElements(
                    (new inputText('us_vat', 'LBL_VAT_NUMBER'))
                        ->addClass('check-tax-number')
                        ->setCustomMask('99999999-9-99')
                        ->setColSize('col-12 col-lg-4')
                ),

                (new groupRow('row4'))->addElements(
                    (new inputText('us_invoice_name', 'LBL_INVOICE_NAME'))
                        ->setColSize('col-12'),
                    (new inputSelect('us_invoice_country', 'LBL_COUNTRY', 'HU'))
                        ->setOptions($this->owner->lists->getCountries())
                        ->setColSize('col-3'),
                    (new inputText('us_invoice_zip', 'LBL_ZIP'))
                        ->onlyNumbers()
                        ->setColSize('col-2'),
                    (new inputText('us_invoice_city', 'LBL_CITY'))
                        ->setColSize('col-7'),
                    (new inputText('us_invoice_address', 'LBL_ADDRESS'))
                        ->setColSize('col-12')
                )
        );
        if($this->orderType === ORDER_TYPE_ORDER) {
            $invoiceAddress->addTools('LBL_SAME_AS_SHIPPING_ADDRESS', 'copy-shipping-address', 'fal fa-copy');
        }

        $this->addControls($invoiceAddress);
        $this->addControls(
            (new inputTextarea('remarks', 'LBL_REMARKS'))
                ->notDBField()
        );

        $this->customModalButtons = true;

        $this->addButtons(
            new buttonModalClose('btn-close', 'BTN_CLOSE'),
            (new buttonModalSubmit('save', 'BTN_SAVE', 'btn btn-save btn-primary float-right')),
            (new buttonModalSubmit('saveAndClose', 'BTN_CLOSE_ORDER', 'btn btn-save btn-warning float-right mr-4'))
        );
    }

    public function onAfterInit() {
        if($this->values['us_role'] == USER_ROLE_NONE){
            $this->values['us_email'] = $this->values['us_email2'];
        }

        if(Empty($_POST)) {
            $this->values['invoiceType'] = $this->order->invoiceType;
            $this->values['remarks'] = $this->order->remarks;
        }
    }

    public function onValidate() {
        if(!Empty($this->values['user']['id'])){
            $this->keyFields['us_id'] = (int) $this->values['user']['id'];
        }

        if($this->keyFields['us_id']) {
            $this->userRole = $this->getUserRole($this->keyFields['us_id']);
            if ($this->userRole === USER_ROLE_USER) {
                $res = $this->owner->db->getFirstRow(
                    "SELECT us_id FROM users WHERE us_shop_id = " . $this->owner->shopId . " AND us_id != " . $this->keyFields['us_id'] . " AND us_email LIKE \"" . $this->owner->db->escapeString($this->values['us_email']) . "\""
                );
                if (!empty($res)) {
                    $this->addError('ERR_10009', self::FORM_ERROR, ['us_email']);
                }

            } else {
                $this->values['us_email2'] = $this->values['us_email'];
            }
        }

        if($this->values['invoiceType'] != -1) {
            if (empty($this->values['us_lastname'])) {
                $this->addError('ERR_1000', self::FORM_ERROR, ['us_lastname']);
            }
            if (empty($this->values['us_firstname'])) {
                $this->addError('ERR_1000', self::FORM_ERROR, ['us_firstname']);
            }
            if (empty($this->values['us_email'])) {
                $this->addError('ERR_1000', self::FORM_ERROR, ['us_email']);
            } else {
                if (!checkEmail($this->values['us_email'])) {
                    $this->addError('ERR_1001', self::FORM_ERROR, ['us_email']);
                }
            }
            if (empty($this->values['us_phone'])) {
                $this->addError('ERR_1000', self::FORM_ERROR, ['us_phone']);
            }
            if (empty($this->values['us_invoice_name'])) {
                $this->addError('ERR_1000', self::FORM_ERROR, ['us_invoice_name']);
            }
            if (empty($this->values['us_invoice_zip'])) {
                $this->addError('ERR_1000', self::FORM_ERROR, ['us_invoice_zip']);
            }
            if (empty($this->values['us_invoice_city'])) {
                $this->addError('ERR_1000', self::FORM_ERROR, ['us_invoice_city']);
            }
            if (empty($this->values['us_invoice_address'])) {
                $this->addError('ERR_1000', self::FORM_ERROR, ['us_invoice_address']);
            }
            if ($this->values['invoiceType'] == 2) {
                if(Empty($this->values['us_vat'])) {
                    $this->addError('ERR_1000', self::FORM_ERROR, ['us_vat']);
                }else {
                    /**
                     * @var $invoice Invoices
                     */
                    $invoice = $this->owner->addByClassName('Invoices');
                    if($invoice->hasInvoiceProvider()) {
                        try {
                            $buyer = $invoice->init()->getTaxPayer($this->values['us_vat']);
                            if (!$buyer->isValid()) {
                                $this->addError('ERR_INVALID_VAT_NUMBER', self::FORM_ERROR, ['us_vat']);
                            }
                        } catch (Exception $e) {
                            $this->addError('ERR_INVALID_VAT_NUMBER', self::FORM_ERROR, ['us_vat']);
                        }
                    }
                }
            }
        }

        if($this->orderType === ORDER_TYPE_ORDER) {
            if(Empty($this->values['us_zip'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['us_zip']);
            }
            if(Empty($this->values['us_city'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['us_city']);
            }
            if(Empty($this->values['us_address'])){
                $this->addError('ERR_1000', self::FORM_ERROR, ['us_address']);
            }
        }
    }

    public function onBeforeSave() {
        $this->values['us_shop_id'] = $this->owner->shopId;
        if(!Empty($this->values['user']['id'])){
            $this->keyFields['us_id'] = (int) $this->values['user']['id'];
        }

        if(in_array($this->orderType, [ORDER_TYPE_TAKEAWAY, ORDER_TYPE_LOCAL]) && $this->values['invoiceType'] == -1) {
            $this->dbTable = false;
            unset($this->keyFields);

        }elseif(!$this->keyFields['us_id']){
            $this->values['us_group'] = USER_GROUP_CUSTOMERS;
            $this->values['us_role'] = USER_ROLE_NONE;
            $this->values['us_email2'] = $this->values['us_email'];
            $this->values['us_email'] = uuid::v4();
            $this->values['us_hash'] = uuid::v4();
            $this->values['us_registered'] = 'NOW()';
            $this->values['us_enabled'] = 0;
        }
    }

    public function saveAndClose(){
        $this->close = true;
        parent::saveValues();
    }

    public function onAfterSave($statement) {
        if($this->keyFields['us_id']){
            $userId = (int) $this->keyFields['us_id'];
        }else{
            $userId = 0;
        }

        if($this->close){
            $this->order->makeOrder($userId, $this->values['invoiceType'], $this->values['remarks']);
        }else {
            $this->order->setCustomer($userId, $this->values['invoiceType'], $this->values['remarks']);
        }
    }

    private function loadOrderData(){
        if($this->cartId){
            $this->keyFields['us_id'] = 0;

            $row = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'cart',
                    [
                        'cart_us_id AS userId',
                        'cart_key AS cartKey',
                        'cart_order_type AS orderType',
                    ],
                    [
                        'cart_id' => $this->cartId
                    ]
                )
            );
            if($row){
                $this->cartKey = $row['cartKey'];
                $this->orderType = (int) $row['orderType'];
                $this->keyFields['us_id'] = (int) $row['userId'];

                $this->order = $this->owner->cartHandler->init($this->cartKey, false);
            }
        }
    }

    private function getUserRole($id){
        $user = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'users',
                [
                    'us_role AS role'
                ],
                [
                    'us_id' => (int) $id
                ]
            )
        );

        return ($user ? $user['role'] : false);
    }
}
