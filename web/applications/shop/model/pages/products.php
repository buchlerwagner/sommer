<?php
/**
 * @var $this router
 * @var $shop webShop
 */



$shop = $this->addByClassName('webShop');
$shop->init($this->shopId);

$params = $this->getSession('params');
if(!$params) {
    $params = [
        'filters' => [],
        'sorters' => [],
        'pager' => [
            'page' => 1,
            'limit' => 12,
        ],
    ];
}

$params['filters']['categories'] = [];
$categoryData = [];

if($this->originalPage != $this->page) {
    if ($categoryData = $shop->getCategoryIdByUrl($this->originalPage)) {
        $this->data['category'] = $categoryData;
        $params['filters']['categories'][$categoryData['id']] = $categoryData['id'];
    }else{
        $this->pageRedirect('/' . $GLOBALS['PAGE_NAMES'][$this->language]['products']['name'] . '/');
    }
}

if($this->params[0]){
    $this->page = 'item';
    list($productId, ) = explode('-', $this->params[0]);

    if($this->data['item'] = $shop->getProductDetails((int)$productId)){
        $this->setPageMetaData($this->data['item']);
    }else{
        $this->addHttpHeader(enumHTTPHeaders::NotFound404());
        $this->page = '404';
    }
}else{
    if($_REQUEST['page']){
        $params['pager']['page'] = abs($_REQUEST['page']);
        if($params['pager']['page'] < 1) $params['pager']['page'] = 1;
    }
    if(isset($_REQUEST['search'])){
        $params['filters']['query'] = trim($_REQUEST['search']);
    }

    $this->setSession('params', $params);
    $this->setPageMetaData($categoryData);
    $this->data['products'] = $shop->getProducts($params);
}

d($this->data['category']);
dd($this->data['products']);