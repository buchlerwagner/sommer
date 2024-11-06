<?php
/**
 * @var $this router
 */

$transactionId = false;
$data = file_get_contents('php://input');
$json = json_decode($data, true);

if($json['orderRef']){
    $transactionId = $json['orderRef'];
}

// Barion
if(!Empty($_REQUEST['paymentId'])){
    $paymentId = urldecode($_REQUEST['paymentId']);

    $result = $this->db->getFirstRow(
        $this->db->genSQLSelect(
            'payment_transactions',
            [
                'pt_transactionid'
            ],
            [
                'pt_provider_transactionid' => $paymentId,
            ]
        )
    );

    if(!Empty($result['pt_transactionid'])){
        $transactionId = $result['pt_transactionid'];
    }
}

if($transactionId) {
    /**
     * @var $paymentHandler PaymentHandler
     */
    $paymentHandler = $this->addByClassName('PaymentHandler');
    $paymentHandler->handleCallback($transactionId, $data);
}

exit();