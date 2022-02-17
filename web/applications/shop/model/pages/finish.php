<?php
/**
 * @var $this router
 */

$key = false;
if(!Empty($this->params[0])){
    $key = $this->params[0];
}

if(!$key){
    $this->pageRedirect('/');
}else {
    $this->cart->init($key, false);

    if ($this->cart->getStatus() != cart::CART_STATUS_ORDERED || Empty($this->cart->items)) {
        $this->pageRedirect('/');
    }

    $this->data['paymentEnabled'] = false;

    if($this->cart->isBankCardPayment()) {
        $this->data['isPending'] = false;
        $this->data['transactionHistory'] = $this->cart->getTransactionHistory();

        if($this->data['transactionHistory']) {
            if($this->data['transactionHistory'][0]['status'] === enumPaymentStatus::Pending()->getValue()) {
                $this->data['isPending'] = true;
                $this->view->addHeader(' <meta http-equiv="refresh" content="10; URL=' . $this->domain . 'check-payment/?trid=' . $this->data['transactionHistory'][0]['transactionId'] . '">');
            }
        }

        $this->data['paymentEnabled'] = !$this->cart->isPaid() && !$this->data['isPending'];

        if(!Empty($_GET['pay']) && $this->data['paymentEnabled']){
            $this->cart->initPayment();
        }
    }

    $this->data['sections'] = $this->lib->getWidgetContents('finish');
}
