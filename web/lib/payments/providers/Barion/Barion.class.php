<?php
require_once 'library/BarionClient.php';

class Barion extends PaymentProvider
{
    const API_VERSION = 2;

    const HAS_REFUND = true;

    private $client;

    private $barionResponse;

    public static function isAvailable(): bool
    {
        return true;
    }

    public static function getName(): string
    {
        return 'Barion';
    }

    public function hasRefund(): bool
    {
        return self::HAS_REFUND;
    }

    protected function init(): void
    {
        if($this->settings->isTestMode()){
            // Test environment
            $environment = BarionEnvironment::Test;
        }else{
            // Production environment
            $environment = BarionEnvironment::Prod;
        }

        $this->client = new BarionClient($this->settings->getPassword(), self::API_VERSION, $environment);
    }

    protected function onBeforePayment(): void
    {
        $trans = new PaymentTransactionModel();
        $trans->POSTransactionId = "TRANS-" . $this->transactionId;
        $trans->Payee = $this->settings->merchantId;
        $trans->Total = $this->amount;
        $trans->Currency = $this->settings->currency;

        $cartItems = $this->getParam('items');
        if($cartItems) {
            foreach ($cartItems as $cartItem) {
                $item = new ItemModel();
                $item->Name = $cartItem['name'];
                $item->Description = $cartItem['variant'] . (!empty($cartItem['type']) ? ' (' . $cartItem['type'] . ')' : '');
                $item->Quantity = $cartItem['quantity']['amount'];
                $item->Unit = $cartItem['quantity']['unit'];
                $item->UnitPrice = $cartItem['price']['unitPrice'];
                $item->ItemTotal = $cartItem['price']['total'];
                //$item->SKU = "ITEM-01";

                $trans->AddItem($item);
            }
        }

        $ppr = new PreparePaymentRequestModel();
        $ppr->GuestCheckout = true;
        $ppr->PaymentType = PaymentType::Immediate;
        $ppr->FundingSources = array(FundingSourceType::All);
        $ppr->PaymentRequestId = "PAYMENT-" . $this->transactionId;
        $ppr->PayerHint = $this->getParam('customerEmail');
        $ppr->Locale = UILocale::HU;
        $ppr->OrderNumber = $this->getParam('orderNumber');
        $ppr->Currency = $this->settings->currency;
        $ppr->RedirectUrl = $this->settings->urlReturn;
        $ppr->CallbackUrl = $this->settings->urlCallback;
        $ppr->AddTransaction($trans);

        $this->barionResponse = $this->client->PreparePayment($ppr);
        $this->saveLog((array)$this->barionResponse, 'preparePayment', 'rs');

        if(!Empty($this->barionResponse->PaymentId)){
            $this->providerTransactionId = $this->barionResponse->PaymentId;
        }
    }

    protected function pay(): void
    {
        if($this->barionResponse->Status == PaymentStatus::Prepared && !Empty($this->barionResponse->PaymentRedirectUrl)){
            header('Location: ' . $this->barionResponse->PaymentRedirectUrl);
            exit();
        }
    }

    public function callback($data = []): enumPaymentStatus
    {
        return $this->check();
    }

    protected function check(): enumPaymentStatus
    {
        $status = enumPaymentStatus::Pending();

        $paymentDetails = $this->client->GetPaymentState($this->providerTransactionId);

        $this->saveLog((array) $paymentDetails, 'paymentState', 'rs');
        $this->saveResponse(json_encode((array)$paymentDetails));

        if (!empty($paymentDetails)) {
            $this->setResult($paymentDetails->PaymentId, $paymentDetails->Status, ($paymentDetails->FundingInformation->AuthorizationCode ?? ''));

            $status = $this->getStatus($paymentDetails->Status);
        }

        return $status;
    }

    protected function refund(float $amount, Transaction $transaction): enumPaymentStatus
    {
        $response = json_decode($transaction->response, true);
        if($response['Transactions'][0]['TransactionId'] && $response['Transactions'][0]['POSTransactionId']){
            $trans = new TransactionToRefundModel();
            $trans->TransactionId = $response['Transactions'][0]['TransactionId'];
            $trans->POSTransactionId = $response['Transactions'][0]['POSTransactionId'];
            $trans->AmountToRefund = $amount;
            //$trans->Comment = "Refund of ORDER-0001 upon customer complaint";

            $rr = new RefundRequestModel($transaction->providerTransactionId);
            $rr->AddTransaction($trans);

            $refundResult = $this->client->RefundPayment($rr);

            $this->saveLog((array) $refundResult, 'paymentRefund', 'rs');

            $status = $this->getStatus($refundResult->RefundedTransactions[0]->Status);
        }else{
            $status = enumPaymentStatus::Failed();
        }

        return $status;
    }

    private function getStatus(string $status): enumPaymentStatus
    {
        switch ($status) {
            case PaymentStatus::Succeeded:
            case PaymentStatus::PartiallySucceeded:
                $status = enumPaymentStatus::OK();
                break;
            case PaymentStatus::Canceled:
                $status = enumPaymentStatus::Canceled();
                break;
            case PaymentStatus::Expired:
                $status = enumPaymentStatus::Timeout();
                break;
            case PaymentStatus::Prepared:
            case PaymentStatus::Started:
            case PaymentStatus::InProgress:
            case PaymentStatus::Waiting:
            case PaymentStatus::Reserved:
            case PaymentStatus::Authorized:
                $status = enumPaymentStatus::Pending();
                break;
            case PaymentStatus::Failed:
            default:
                $status = enumPaymentStatus::Failed();
                break;
        }

        return $status;
    }

    protected function onAfterPayment(): void
    {
        // TODO: Implement onAfterPayment() method.
    }

    public function sendCallbackResponse($data = []): void
    {
        http_response_code(200);
    }
}