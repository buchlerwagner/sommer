<?php
class viewOrderForm extends formBuilder {
    public $cart;
    public $cartKey;
    public $isOpen = false;
    public $isEmployee = false;

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
            $this->owner->cartHandler->init($this->cartKey, false);

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
                    'cart' => $this->owner->cartHandler
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

            $this->addButtons(
                (new buttonCancel('BTN_BACK'))
                    ->setUrl('/')
            );

        }else {
            $this->title = 'LBL_VIEW_ORDER';
            $this->includeBefore = 'view-order';

            $this->addButtons(
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
            if($cart['status'] == CartHandler::CART_STATUS_NEW && $cart['storeId'] == $this->owner->storeId){
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
}
