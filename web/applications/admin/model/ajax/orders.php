<?php
/**
 * @var $this router
 */
$this->output = OUTPUT_JSON;
$action = trim($this->params[1]);
$data = [];

switch ($action) {
    case 'loadUserData':
        if($_REQUEST['id']){
            $user = $this->db->getFirstRow(
                $this->db->genSQLSelect(
                    'users',
                    [
                        'us_firstname',
                        'us_lastname',
                        'us_email',
                        'us_email2',
                        'us_phone',
                        'us_country',
                        'us_zip',
                        'us_city',
                        'us_address',
                        'us_invoice_name',
                        'us_invoice_country',
                        'us_invoice_zip',
                        'us_invoice_city',
                        'us_invoice_address',
                        'us_vat',
                    ],
                    [
                        'us_id' => (int) $_REQUEST['id'],
                        'us_shop_id' => $this->shopId,
                        'us_group' => USER_GROUP_CUSTOMERS,
                        'us_deleted' => 0
                    ]
                )
            );

            if($user){
                foreach ($user AS $field => $value){
                    if($field == 'us_email2' && !Empty($value)){
                        $field = 'us_email';
                    }

                    $this->data['#' . $field]['value'] = $value;
                }
            }
        }
        break;

    case 'addProduct':
        if($_REQUEST['id'] && $_REQUEST['cartKey']){
            $productId = (int) $_REQUEST['id'];
            $variantId = (int) $_REQUEST['variant'];
            if(!$variantId) $variantId = 0;

            /**
             * @var $product product
             */
            $product = $this->addByClassName('product');
            $item = $product->init($productId)->getProduct();
            if(count($item['variants']) > 1){

                $this->data['orders']['showVariants']['productId'] = $productId;
                $this->data['orders']['showVariants']['cartId'] = (int) $_REQUEST['cartId'];
                $this->data['orders']['showVariants']['tableName'] = 'cartItems';
            }else{
                foreach($item['variants'] AS $variant){
                    $variantId = $variant['id'];
                    break;
                }

                $this->cartHandler->init($_REQUEST['cartKey'], false);
                $item = $this->cartHandler->addProduct($productId, $variantId, 1);

                $this->data['orders']['refreshTable']['tableName'] = 'cartItems';
                $this->data['orders']['refreshTable']['cartId'] = (int) $_REQUEST['cartId'];
            }
        }
        break;

    case 'removeProduct':
        if($_REQUEST['id'] && $_REQUEST['cartKey']){
            $id = (int) $_REQUEST['id'];

            $this->cartHandler->init($_REQUEST['cartKey'], false);
            $this->cartHandler->removeProduct($id);

            $this->data['orders']['refreshTable']['tableName'] = 'cartItems';
            $this->data['orders']['refreshTable']['cartId'] = (int) $_REQUEST['cartId'];
        }
        break;

    case 'changeProduct':
        if($_REQUEST['id'] && $_REQUEST['cartKey'] && $_REQUEST['quantity'] > 0){
            $id = (int) $_REQUEST['id'];
            $quantity = (int) $_REQUEST['quantity'];

            $this->cartHandler->init($_REQUEST['cartKey'], false);
            $this->cartHandler->changeProductQuantity($id, $quantity);

            $this->data['orders']['refreshTable']['tableName'] = 'cartItems';
            $this->data['orders']['refreshTable']['cartId'] = (int) $_REQUEST['cartId'];
        }
        break;

    case 'setPaymentMode':
        if($_REQUEST['id'] && $_REQUEST['cartKey']){
            $id = (int) $_REQUEST['id'];
            $this->cartHandler->init($_REQUEST['cartKey'], false);
            $this->cartHandler->setPaymentMode($id);

            $this->data['orders']['refreshTable']['tableName'] = 'cartItems';
            $this->data['orders']['refreshTable']['cartId'] = (int) $_REQUEST['cartId'];
        }
        break;

    case 'setShippingMode':
        if($_REQUEST['id'] && $_REQUEST['cartKey']){
            $id = (int) $_REQUEST['id'];
            $intervalId = (int) $_REQUEST['intervalId'];
            $this->cartHandler->init($_REQUEST['cartKey'], false);
            $this->cartHandler->setShippingMode($id, $intervalId, ($intervalId == -1 ? $_REQUEST['customInterval'] : false), $_REQUEST['date']);

            $this->data['orders']['refreshTable']['tableName'] = 'cartItems';
            $this->data['orders']['refreshTable']['cartId'] = (int) $_REQUEST['cartId'];
        }
        break;

    case 'setLocalConsumption':
        if($_REQUEST['cartKey']){
            $this->cartHandler->init($_REQUEST['cartKey'], false);
            $this->cartHandler->setLocalConsumption((int) $_REQUEST['localConsumption']);

            $this->data['orders']['refreshTable']['tableName'] = 'cartItems';
            $this->data['orders']['refreshTable']['cartId'] = (int) $_REQUEST['cartId'];
        }
        break;

    case 'getShippingDetails':
        if($_REQUEST['id'] && $_REQUEST['cartKey']){
            $id = (int) $_REQUEST['id'];
            $this->cartHandler->init($_REQUEST['cartKey'], false);
            $shippingModes = $this->cartHandler->getShippingModes();
            if($shippingModes){
                foreach($shippingModes AS $shippingMode){
                    if($shippingMode['id'] == $id){
                        $shippingMode['customIntervalText'] = $this->cartHandler->customInterval;
                        $shippingMode['intervalId'] = $this->cartHandler->getIntervalId();
                        if($this->cartHandler->shippingDate){
                            $shippingMode['selectedShippingDate'] = $this->cartHandler->shippingDate;
                        }else{
                            $shippingMode['selectedShippingDate'] = $shippingMode['shippingDate'];
                        }

                        $this->data['#shipping-details']['html'] = $this->view->renderContent('order-totals-shipping', $shippingMode, false, false);
                        $this->data['#shipping-details']['show'] = true;
                        break;
                    }
                }
            }else{
                $this->data['#shipping-details']['show'] = false;
            }
        }

        break;
}
