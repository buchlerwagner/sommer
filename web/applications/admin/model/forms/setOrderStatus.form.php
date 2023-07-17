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

        $this->owner->cartHandler
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

                $shippingMode = $this->owner->cartHandler->getSelectedShippingMode();
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
        $this->owner->cartHandler
            ->init($this->values['cart_key'], false)
            ->setOrderStatus($this->values['setStatus']);

        $this->sendNotification();

        $this->returnData['frm']['functions']['callback'] = 'pageRedirect';
        $this->returnData['frm']['functions']['arguments'] = '/orders/view|orders/' . $this->keyFields['cart_id'] . '/';
    }

    private function sendNotification(){
        if($this->values['notification']){
            $cartMailBody = $this->owner->view->renderContent(
                'mail-order',
                [
                    'key' => $this->owner->cartHandler->key,
                    'id' => $this->owner->cartHandler->id,
                    'items' => $this->owner->cartHandler->items,
                    'currency' => $this->owner->cartHandler->currency,
                    'subtotal' => $this->owner->cartHandler->subtotal,
                    'discount' => $this->owner->cartHandler->getDiscount(),
                    'packagingFee' => $this->owner->cartHandler->packagingFee,
                    'shippingFee' => $this->owner->cartHandler->shippingFee,
                    'paymentFee' => $this->owner->cartHandler->paymentFee,
                    'total' => $this->owner->cartHandler->total,
                    'shippingMode' => $this->owner->cartHandler->getSelectedShippingMode(),
                    'shippingInterval' => $this->owner->cartHandler->getSelectedShippingInterval(),
                    'customInterval' => $this->owner->cartHandler->customInterval,
                    'paymentMode' => $this->owner->cartHandler->getSelectedPaymentMode(),
                    'orderNumber' => $this->owner->cartHandler->orderNumber,
                    'orderStatus' => $this->values['setStatus'],
                    'contactData' => $this->owner->cartHandler->userData['contactData'],
                    'shippingAddress' => $this->owner->cartHandler->userData['shippingAddress'],
                    'invoiceAddress' => $this->owner->cartHandler->userData['invoiceAddress'],
                    'remarks' => $this->owner->cartHandler->remarks,
                    'domain' => $this->owner->cartHandler->owner->domain,
                ],
                false
            );

            $data = [
                'id' => $this->values['cart_us_id'],
                'link' => rtrim($this->owner->domain, '/') .  $this->owner->getPageName('finish') . $this->owner->cartHandler->key . '/',
                'order' => $cartMailBody,
                'orderNumber' => $this->owner->cartHandler->orderNumber,
                'status' => $this->values['setStatus'],
                'key' => $this->owner->cartHandler->key,
                'total' => $this->owner->cartHandler->total,
                'currency' => $this->owner->cartHandler->currency,
            ];

            $this->owner->email->prepareEmail(
                'order-' . strtolower($this->values['setStatus']),
                $this->values['cart_us_id'],
                $data
            );
        }
    }
}
