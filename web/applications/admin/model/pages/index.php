<?php
/**
 * @var $this router
 */

//dd($this->user->getUser());

$orderType = (int) $_REQUEST['new'];
$this->data['access'][ORDER_TYPE_STORE] = $this->user->hasFunctionAccess('orders-store');
$this->data['access'][ORDER_TYPE_PHONE] = $this->user->hasFunctionAccess('orders-phone');

if($orderType && $this->data['access'][$orderType]){
    if($id = $this->cart->createNewOrder($orderType)){
        $this->pageRedirect('/orders/view|orders/' . $id . '/');
    }
}

$this->data['storeName'] = $this->hostConfig['storeName'];
$this->data['table'] = $this->loadTable('todayOrders');
