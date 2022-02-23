<?php
/**
 * @var $this router
 */

$this->output = OUTPUT_JSON;
$this->data = [];
$params = [];

$this->cartHandler->init();

$json = file_get_contents('php://input');
if($json) {
	$params = json_decode($json, true);
}

$action = strtolower(trim($this->params[1]));
switch($action) {
	case 'add':
		if($params['id'] && $params['quantity'] > 0){
			$item = $this->cartHandler->addProduct($params['id'], $params['variant'], $params['quantity']);
            if(!$item['error']){
                $id = $item['id'];
                $num = $this->cartHandler->getNumberOfCartItems();

                if($item['new']){
                    $this->data['.shopping-cart-items']['append'] = $this->view->renderContent('cart-item', ['item' => $this->cartHandler->getItem($id)]);
                }else {
                    $this->data['.item-price-' . $id]['html'] = $this->lib->formatPrice($this->cartHandler->getItem($id)['price']['finalPrice'], $this->cartHandler->currency);
                    $this->data['.item-total-' . $id]['html'] = $this->lib->formatPrice($this->cartHandler->getItem($id)['price']['total'], $this->cartHandler->currency);
                    $this->data['.item-quantity-' . $id]['html'] = $this->cartHandler->getItem($id)['quantity']['amount'];
                }

                $this->data['.cart-total']['html'] = $this->lib->formatPrice($this->cartHandler->subtotal, $this->cartHandler->currency);
                $this->data['.cart-counter']['html'] = $num;
                $this->data['.cart-counter']['show'] = true;

                $this->data['.on-empty-cart']['show'] = false;
                $this->data['.on-cart']['show'] = true;

                $this->data['shoppingCart']['viewPopup'] = true;
            }
        }
		break;

	case 'remove':
		if($params['id']){
			$id = (int) $params['id'];
			$this->cartHandler->removeProduct($id);

			$num = $this->cartHandler->getNumberOfCartItems();
            $this->data['.cart-counter']['html'] = $num;
            $this->data['.cart-item-' . $id]['remove'] = true;
            $this->data['.cart-total']['html'] = $this->lib->formatPrice($this->cartHandler->subtotal, $this->cartHandler->currency);

            if($num){
                $this->data['.on-cart']['show'] = true;
                $this->data['.on-empty-cart']['show'] = false;
            }else{
                $this->data['.on-cart']['show'] = false;
                $this->data['.on-empty-cart']['show'] = true;
            }
		}
		break;

	case 'change':
		if($params['id'] && $params['quantity'] > 0){
			$id = (int) $params['id'];
			$this->cartHandler->changeProductQuantity($id, (int) $params['quantity']);

			$num = $this->cartHandler->getNumberOfCartItems();
            $this->data['.cart-counter']['html'] = $num;
            $this->data['.item-quantity-' . $id]['html'] = $this->cartHandler->getItem($id)['quantity']['amount'];
            $this->data['.item-price-' . $id]['html'] = $this->lib->formatPrice($this->cartHandler->getItem($id)['price']['finalPrice'], $this->cartHandler->currency);
            $this->data['.item-total-' . $id]['html'] = $this->lib->formatPrice($this->cartHandler->getItem($id)['price']['total'], $this->cartHandler->currency);
            $this->data['.cart-total']['html'] = $this->lib->formatPrice($this->cartHandler->subtotal, $this->cartHandler->currency);

            if($this->cartHandler->packagingFee > 0){
                $this->data['.cart-item-packaging']['show'] = true;
                $this->data['.cart-packaging-fee']['html'] = $this->lib->formatPrice($this->cartHandler->packagingFee, $this->cartHandler->currency);
            }else{
                $this->data['.cart-item-packaging']['show'] = false;
                $this->data['.cart-packaging-fee']['html'] = 0;
            }

            if($num){
                $this->data['.on-cart']['show'] = true;
                $this->data['.on-empty-cart']['show'] = false;
            }else{
                $this->data['.on-cart']['show'] = false;
                $this->data['.on-empty-cart']['show'] = true;
            }
		}
		break;

    case 'setpaymentmode':
        if($params['id']) {
            $id = (int)$params['id'];
            $this->cartHandler->setPaymentMode($id);

            if($this->cartHandler->paymentFee) {
                $this->data['.payment-fee']['html'] = $this->lib->formatPrice($this->cartHandler->paymentFee, $this->cartHandler->currency);
                $this->data['.payment-item']['show'] = true;
            }else{
                $this->data['.payment-fee']['html'] = $this->translate->getTranslation('LBL_FREE');
                $this->data['.payment-item']['show'] = false;
            }
            $this->data['.cart-total']['html'] = $this->lib->formatPrice($this->cartHandler->total, $this->cartHandler->currency);
        }
        break;

    case 'setshippingmode':
        if($params['id']) {
            $id = (int)$params['id'];
            $this->cartHandler->setShippingMode($id);
            $this->data['.item-shipping-fee']['show'] = true;

            if($this->cartHandler->shippingFee > 0) {
                $this->data['.shipping-fee']['html'] = $this->lib->formatPrice($this->cartHandler->shippingFee, $this->cartHandler->currency);
            }else{
                $this->data['.shipping-fee']['html'] = $this->translate->getTranslation('LBL_FREE');
            }
            $this->data['.cart-total']['html'] = $this->lib->formatPrice($this->cartHandler->total, $this->cartHandler->currency);
        }
        break;

	case 'popup':
		$this->output = OUTPUT_RAW;

		$this->data = $this->view->renderContent('modal', [
            'noFooter' => true,
            'content' => 'cart-popup',
        ]);

		break;
}
