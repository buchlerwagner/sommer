<?php
class setOrderStatusForm extends formBuilder {

    public function setupKeyFields() {
        $this->setKeyFields(['cart_id', 'cart_shop_id']);
    }

    public function setup() {
        $this->title = 'LBL_SET_STATUS';
		$this->dbTable = 'cart';

        $this->keyFields['cart_shop_id'] = $this->owner->shopId;

        $this->addExtraField('cart_order_status');
        $this->addExtraField('cart_key', false);
        $this->addExtraField('cart_us_id', false);

        $this->addControls(
            (new groupHtml('status-info', '')),

            (new inputButton('setStatus', '', 1, 'btn'))
                ->setGroupClass('text-center mt-4')
                ->addData('toggle', 'modal')
                ->addData('target', '#confirm-delete')
                ->addData('confirm-question', $this->owner->translate->getTranslation('LBL_STATUS_CHANGE_QUESTION'))
                ->addData('confirm-button', $this->owner->translate->getTranslation('BTN_YES'))
                ->notDBField(),

            (new inputSwitch('notification', 'LBL_SEND_STATUS_CHANGE_NOTIFICATION', 1))
                ->setGroupClass('mt-4')
                ->notDBField()
        );

        $this->customActions = ['setStatus'];

        $this->customModalButtons = true;

        $this->addButtons(
            new buttonModalClose('btn-close', 'BTN_CANCEL')
        );
	}

    public function onLoadValues() {
        $this->values['notification'] = 1;
        $this->getControl('status-info')->setHtml("<div class='text-center'>{{ _('LBL_CURRENT_STATUS') }}: {{ orderState('" . $this->values['cart_order_status'] . "')|raw }}</div>");

        $this->owner->cart
            ->init($this->values['cart_key'], false);


        $class = false;
        $label = false;
        $actionName = false;

        switch($this->values['cart_order_status']){
            case ORDER_STATUS_NEW:
                $class = 'btn-warning';
                $label = 'BTN_SET_PROCESSING';
                $actionName = ORDER_STATUS_PROCESSING;
                break;

            case ORDER_STATUS_PROCESSING:
                $class = 'btn-success';

                $shippingMode = $this->owner->cart->getSelectedShippingMode();
                if($shippingMode['type'] == 1){
                    $label = 'BTN_SET_RECEIVABLE';
                    $actionName = ORDER_STATUS_RECEIVABLE;
                }else {
                    $label = 'BTN_SET_DELIVERING';
                    $actionName = ORDER_STATUS_DELIVERING;
                }
                break;

            case ORDER_STATUS_RECEIVABLE:
            case ORDER_STATUS_DELIVERING:
                $class = 'btn-primary';
                $label = 'BTN_SET_FINISHED';
                $actionName = ORDER_STATUS_FINISHED;
                break;

            case ORDER_STATUS_FINISHED:
                $class = 'btn-secondary';
                $label = 'BTN_SET_CLOSED';
                $actionName = ORDER_STATUS_CLOSED;

                $this->values['notification'] = 0;
                $this->removeControl('notification');
                break;
        }

        if($actionName) {
            $this->getControl('setStatus')
                ->setLabel($label)
                ->addClass($class)
                ->addData('confirm-action', 'postModalForm("#' . $this->name . '-form", "' . $actionName . '", "setStatus")');
        }else{
            $this->removeControl('setStatus');
        }
    }

    public function setStatus(){
        $this->state = FORM_STATE_SAVED;
        $this->owner->cart
            ->init($this->values['cart_key'], false)
            ->setOrderStatus($this->values['setStatus']);

        $this->sendNotification();

        $this->returnData['frm']['functions']['callback'] = 'pageRedirect';
        $this->returnData['frm']['functions']['arguments'] = 'orders/view|orders/' . $this->keyFields['cart_id'] . '/';
    }

    private function sendNotification(){
        if($this->values['notification']){
            $cartMailBody = $this->owner->view->renderContent(
                'mail-order',
                [
                    'key' => $this->owner->cart->key,
                    'id' => $this->owner->cart->id,
                    'items' => $this->owner->cart->items,
                    'currency' => $this->owner->cart->currency,
                    'subtotal' => $this->owner->cart->subtotal,
                    'discount' => $this->owner->cart->discount,
                    'packagingFee' => $this->owner->cart->packagingFee,
                    'shippingFee' => $this->owner->cart->shippingFee,
                    'paymentFee' => $this->owner->cart->paymentFee,
                    'total' => $this->owner->cart->total,
                    'shippingMode' => $this->owner->cart->getSelectedShippingMode(),
                    'shippingInterval' => $this->owner->cart->getSelectedShippingInterval(),
                    'customInterval' => $this->owner->cart->customInterval,
                    'paymentMode' => $this->owner->cart->getSelectedPaymentMode(),
                    'orderNumber' => $this->owner->cart->orderNumber,
                    'orderStatus' => $this->values['setStatus'],
                    'contactData' => $this->owner->cart->userData['contactData'],
                    'shippingAddress' => $this->owner->cart->userData['shippingAddress'],
                    'invoiceAddress' => $this->owner->cart->userData['invoiceAddress'],
                    'remarks' => $this->owner->cart->remarks,
                    'domain' => $this->owner->cart->owner->domain,
                ],
                false
            );

            $data = [
                'id' => $this->values['cart_us_id'],
                'link' => rtrim($this->owner->domain, '/') .  $this->owner->getPageName('finish') . $this->owner->cart->key . '/',
                'order' => $cartMailBody,
                'orderNumber' => $this->owner->cart->orderNumber,
                'status' => $this->values['setStatus'],
                'key' => $this->owner->cart->key,
                'total' => $this->owner->cart->total,
                'currency' => $this->owner->cart->currency,
            ];

            $this->owner->email->prepareEmail(
                'order-' . strtolower($this->values['setStatus']),
                $this->values['cart_us_id'],
                $data
            );
        }
    }
}
