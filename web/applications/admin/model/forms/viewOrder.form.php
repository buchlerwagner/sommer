<?php
class viewOrderForm extends formBuilder {
    public $cart;
    public $cartKey;
    public $isOpen = false;
    public $isEmployee = false;
    public $hasInvoiceProvider = false;
    public $isRefundable = false;
    public $invoiceDir = '';

    const PAYMENT_ICONS = [
        1 => 'fa-money-bill',
        2 => 'fa-laptop',
        3 => 'fa-credit-card',
    ];

    const SHIPPING_ICONS = [
        1 => 'fa-store',
        2 => 'fa-truck',
    ];

    public function setupKeyFields() {
        $this->setKeyFields(['cart_id']);
    }

    public function setup() {
		$this->dbTable = 'cart';
        $this->boxed = false;

        $this->isEmployee = ($this->owner->user->getRole() == USER_ROLE_EMPLOYEE);
        $this->checkCart();

        $this->addExtraField('cart_key');
        $this->addExtraField('cart_order_number');
        $this->addExtraField('cart_store_id');
        $this->addExtraField('cart_created_by');
        $this->addExtraField('cart_created');
        $this->addExtraField('cart_status');

        if($this->isOpen) {
            $this->title = 'LBL_CREATE_NEW_ORDER';
            $cart = $this->owner->cartHandler->init($this->cartKey, false);

            $search = (new groupFieldset('search-product'))->addElements(
                (new inputAutocomplete('product'))
                    ->notDBField()
                    ->setClearable()
                    ->setPlaceholder('LBL_SEARCH_PRODUCT')
                    ->addClass('form-control-lg')
                    ->callback('addProduct')
                    ->insertFields('#cartId, #cartKey')
                    ->setList('searchProducts', $this->cartKey, true)
            );

            $items = (new groupFieldset('cart-items'))->addElements(
                (new subTable('items'))
                    ->addClass('table-responsive')
                    ->add($this->loadSubTable('cartItems')),

                (new groupInclude('order-totals', [
                    'cart' => $this->owner->cartHandler,
                    'paymentIcons' => self::PAYMENT_ICONS,
                    'shippingIcons' => self::SHIPPING_ICONS,
                ]))
            );

            $this->addControls(
                $search,
                $items
            );

            $this->addButtons(
                (new buttonConfirm('delete', 'BTN_DELETE_ORDER', 'btn btn-danger mr-5'))
                    ->setName('delete')
                    ->setForm('viewOrderForm')
                    ->setAction(enumModalActions::PostForm())
                    ->setTexts('LBL_DELETE_ORDER', 'BTN_DELETE'),
                (new buttonModalOpen('finishOrder', 'BTN_FINISH_ORDER', 'btn btn-primary'))
                    ->setModal('finishOrder', [$this->keyFields['cart_id']], 'lg')
            );

            $this->includeBefore = 'create-order';

            if($cart->orderNumber){
                $this->addButtons(
                    new buttonCancel('BTN_BACK')
                );
            }else {
                $this->addButtons(
                    (new buttonCancel('BTN_BACK'))
                        ->setUrl('/')
                );
            }
        }else {
            $this->title = 'LBL_VIEW_ORDER';
            $this->includeBefore = 'view-order';

            $this->invoiceDir = FOLDER_UPLOAD . $this->owner->shopId . '/invoices/';

            /**
             * @var $invoice Invoices
             */
            $invoice = $this->owner->addByClassName('Invoices');
            $this->hasInvoiceProvider = $invoice->hasInvoiceProvider();

            $this->addButtons(
                (new buttonConfirm('openOrder', 'BTN_EDIT_ORDER', 'btn btn-primary mr-2 float-start'))
                    ->setIcon('fas fa-cart-plus')
                    ->setName('openOrder')
                    ->setForm('viewOrderForm')
                    ->setAction(enumModalActions::PostForm())
                    ->setTexts('LBL_OPEN_ORDER_TO_EDIT', 'BTN_EDIT_ORDER'),

                new buttonCancel('BTN_BACK')
            );
        }
	}

    public function onAfterInit() {

        if($this->isEmployee && $this->values['cart_created_by'] != $this->owner->user->id){
            $this->owner->pageRedirect('/');
        }

        if(!$this->isOpen) {
            $this->setSubtitle($this->values['cart_order_number']);

            $this->owner->cartHandler->init($this->values['cart_key'], false);
            $this->cart = $this->owner->cartHandler;

            if($this->owner->user->hasFunctionAccess('orders-refund')){
                $this->isRefundable = $this->cart->isRefundable();
            }

            if($this->cart->isPaid() || !Empty($this->cart->invoiceNumber)) {
                $this->removeButton('openOrder');
            }
        }else{
            if($this->values['cart_order_number']) {
                $this->title = 'LBL_EDIT_ORDER';
                $this->setSubtitle($this->values['cart_order_number']);
            }
        }
    }

    private function checkCart(){
        $cart = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'cart',
                [
                    'cart_key AS cartKey',
                    'cart_status AS status',
                    'cart_store_id AS storeId'
                ],
                [
                    'cart_id' => $this->keyFields['cart_id']
                ]
            )
        );
        if($cart){
            $this->cartKey = $cart['cartKey'];
            if($cart['status'] == CartHandler::CART_STATUS_NEW && ($cart['storeId'] == $this->owner->storeId || $this->owner->user->hasRole([USER_ROLE_SUPERVISOR, USER_ROLE_ADMIN]))){
                $this->isOpen = true;
            }
        }
    }

    public function delete(){
        if($this->isOpen){
            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLDelete(
                    'cart_items',
                    [
                        'citem_cart_id' => $this->keyFields['cart_id']
                    ]
                )
            );

            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLDelete(
                    'cart',
                    [
                        'cart_id' => $this->keyFields['cart_id'],
                        'cart_shop_id' => $this->owner->shopId,
                    ]
                )
            );

            $this->owner->pageRedirect('/');
        }
    }

    public function openOrder(){
        if(!$this->isOpen){
            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLUpdate(
                    'cart',
                    [
                        'cart_status' => CartHandler::CART_STATUS_NEW
                    ],
                    [
                        'cart_id' => $this->keyFields['cart_id'],
                    ]
                )
            );

            $this->owner->pageRedirect('/orders/view|orders/' . $this->keyFields['cart_id'] . '/');
        }
    }
}
