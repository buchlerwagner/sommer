<?php
class issueInvoiceForm extends formBuilder {
    /**
     * @var $cart Cart
     */
    private $cart;

    public function setupKeyFields() {
        $this->setKeyFields(['cart_id']);
    }

    public function setup() {
        $this->title = 'LBL_ISSUE_INVOICE';

        $this->reloadPage = true;
        $this->cart = $this->owner->cartHandler->init($this->keyFields['cart_id'], false)->getCart();

        $today = $dueDate = $fulfillmentDate = date('Y-m-d');
        $paymentMode = $this->cart->getPaymentMode();
        if($paymentMode['type'] == PAYMENT_TYPE_MONEY_TRANSFER){
            $dueDate = dateAddDays($today, 8);
        }elseif($paymentMode['type'] == PAYMENT_TYPE_CARD){
            $fulfillmentDate = $dueDate = $this->cart->getOrderDate();
        }

        $this->addControls(
            (new groupRow('row1'))->addElements(
                (new inputDate('invDate', 'LBL_ISSUE_DATE', $today))
                    ->setIcon('fas fa-calendar')
                    ->setMinDate($today)
                    ->setColSize('col-12 col-lg-4')
                    ->setRequired(),

                (new inputDate('invFulfillmentDate', 'LBL_FULFILLMENT_DATE', $fulfillmentDate))
                    ->setIcon('fas fa-calendar')
                    ->setColSize('col-12 col-lg-4')
                    ->setRequired(),

                (new inputDate('invDueDate', 'LBL_DUE_DATE', $dueDate))
                    ->setIcon('fas fa-calendar')
                    ->setColSize('col-12 col-lg-4')
                    ->setRequired()
            ),

            (new inputSelect('invPaymentMode', 'LBL_PAYMENT_MODE', $paymentMode['type']))
                ->setOptions($this->owner->lists->getPaymentModes())
                ->setRequired(),

            (new inputSwitch('setPaid', 'LBL_SET_PAID', true))
                ->setColor(enumColors::Primary()),

            (new inputSwitch('sendEmail', 'LBL_SEND_EMAIL', true))
                ->setColor(enumColors::Primary())
        );

        $this->addButtons(
            new buttonSave(),
            new buttonCancel()
        );
	}

    public function onBeforeSave() {
        /**
         * @var $invoice Invoices
         */
        $invoice = $this->owner->addByClassName('Invoices');
        try {
            $this->cart->setPaid((bool) $this->values['setPaid']);

            if(!$this->values['sendEmail']){
                $customer = $this->cart->getCustomer();
                unset($customer['contactData']['email']);
                $this->cart->setCustomer($customer);
            }

            $paymentMode = $this->cart->getPaymentMode();
            $paymentMode['type'] = $this->values['invPaymentMode'];
            $this->cart->setPayment($paymentMode);

            $invoice->init($this->cart)->overrideInvoicingAllowance()->createInvoice($this->values['invDate'], $this->values['invDueDate'], $this->values['invFulfillmentDate']);
            $this->owner->addMessage(router::MESSAGE_SUCCESS, 'LBL_INVOICE', 'LBL_INVOICE_ISSUED');
        } catch (Exception $e) {
            $this->owner->addMessage(router::MESSAGE_DANGER, 'LBL_INVOICE_ERROR', $e->getMessage());
        }
    }
}
