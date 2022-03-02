<?php
/**
 * @var $this router
 */

$key = false;
if(!Empty($this->params[0])){
    $key = $this->params[0];
}

if(!$key || is_numeric($key)){
    $this->pageRedirect('/');
}else {
    $this->cartHandler->init($key, false);

    if ($this->cartHandler->getStatus() != CartHandler::CART_STATUS_ORDERED || Empty($this->cartHandler->items)) {
        $this->pageRedirect('/');
    }

    $this->data['paymentEnabled'] = false;

    if($this->cartHandler->isBankCardPayment()) {
        $this->data['isPending'] = false;
        $this->data['transactionHistory'] = $this->cartHandler->getTransactionHistory();

        if($this->data['transactionHistory'][0]) {
            if($this->data['transactionHistory'][0]['status'] === enumPaymentStatus::Pending()->getValue()) {
                $this->data['isPending'] = true;
                $this->data['refreshInterval'] = 10;
                $this->data['transactionId'] = $this->data['transactionHistory'][0]['transactionId'];

                //$this->view->addHeader(' <meta http-equiv="refresh" content="' . $this->data['refreshInterval'] . '; URL=' . $this->domain . 'check-payment/?trid=' . $this->data['transactionHistory'][0]['transactionId'] . '">');
            }
        }

        $this->data['paymentEnabled'] = !$this->cartHandler->isPaid() && !$this->data['isPending'];

        if(!Empty($_GET['pay']) && $this->data['paymentEnabled']){
            $this->cartHandler->initPayment();
        }
    }

    $this->data['sections'] = $this->lib->getWidgetContents('finish');
}
