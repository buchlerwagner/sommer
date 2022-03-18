<?php
/**
 * @var $this router
 */

//dd($this->user->getUser());

$orderType = (int) $_REQUEST['new'];

if($orderType){
    if($id = $this->cartHandler->createNewOrder($orderType)){
        $this->pageRedirect('/orders/view|orders/' . $id . '/');
    }
}

$this->data['storeName'] = $this->hostConfig['storeName'];
$this->data['table'] = $this->loadTable('todayOrders');
