<?php
/**
 * @var $this router
 * @var $shop webShop
 */
$shop = $this->addByClassName('webShop');
$shop->init($this->shopId);

$params = $this->getSession('params');
if(!$params || $_REQUEST['clear']) {
    $params = [
        'filters' => [],
        'sorters' => [],
        'pager' => [
            'page' => 1,
            'limit' => 12,
        ],
    ];
}

$params['pager']['limit'] = $this->settings['itemsOnPage'];
$params['filters']['categories'] = [];
$categoryData = [];

if($this->originalPage != $this->page) {
    if ($categoryData = $shop->getCategoryIdByUrl($this->originalPage)) {
        $this->data['category'] = $categoryData;
        $params['filters']['categories'][$categoryData['id']] = $categoryData['id'];
        if($categoryData['isSmart'] && !Empty($categoryData['tags'])){
            $params['filters']['tags'] = $categoryData['tags'];
        }
    }
}else{
    if($this->originalPage == 'products' && !Empty($this->params[0])){
        $this->pageRedirect('/' . $GLOBALS['PAGE_NAMES'][$this->language]['products']['name'] . '/');
    }
}

if($this->params[0]){
    $this->page = 'item';
    list($productId, ) = explode('-', $this->params[0]);

    if($this->data['item'] = $shop->getProductDetails((int)$productId)){
        $this->setPageMetaData($this->data['item']);

        $this->data['relatedProducts'] = $shop->getHighlightedProducts($categoryData['id'], $productId, 3);

        $this->view->addCss('magiczoomplus/magiczoomplus.css', 'magiczoom', false, false);
        $this->view->addJs('magiczoomplus/magiczoomplus.js', 'magiczoom', false, false, false);

        //dd($this->data['relatedProducts']);
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
    }else{
        $params['filters']['query'] = false;
    }
    if(isset($_REQUEST['tags'])){
        $params['filters']['tags'] = $_REQUEST['tags'];
    }
    if(isset($_REQUEST['sort'])){
        $params['sorter'] = $_REQUEST['sort'];
    }else{
        $params['sorter'] = 'price';
    }

    $this->setSession('params', $params);
    $this->setPageMetaData($categoryData);
    $this->data['products'] = $shop->getProducts($params);
}

