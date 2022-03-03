<?php
class lists extends ancestor {
    private $list = [];
    private $options = [];
    private $json = false;
    private $emptyListLabel = false;
    private $emptyListValue = 0;

    public function getTitles(){
        $list = array_combine($GLOBALS['PERSONAL_TITLES'], $GLOBALS['PERSONAL_TITLES']);
        return $this->setList($list)->getList();
    }

    public function getDaysOfWeek(){
        $dow = [];
        for($i = 1; $i <= 7; $i++){
            $dow[$i] = 'LBL_DAY_' . $i;
        }
        $this->setList($dow);

        return $this->getList();
    }

    public function numberRange($start, $end){
        $list = array_combine(range($start,$end), range($start, $end));

        return $this->setList($list)->getList();
    }

    public function getLanguages(){
        $this->list = [];
        if($this->owner->hostConfig['languages']){
            foreach($this->owner->hostConfig['languages'] AS $lang){
                $this->list[$lang] = $GLOBALS['REGIONAL_SETTINGS'][$lang]['name'];
            }
        }

        return $this->getList();
    }

    public function getAllLanguages(){
        $this->list = [];
        foreach($GLOBALS['REGIONAL_SETTINGS'] AS $lang => $setting){
            if($lang != 'default') {
                $this->list[$lang] = $setting['name'];
            }
        }

        return $this->getList();
    }

    public function getCurrencies($withSign = true){
        $this->list = [];
        if($this->owner->hostConfig['currencies']){
            foreach($this->owner->hostConfig['currencies'] AS $currency){
                $this->list[$currency] = ($withSign ? $GLOBALS['CURRENCIES'][$currency]['sign'] : $currency);
            }
        }

        return $this->getList();
    }

    public function getAllCurrencies($withSign = true){
        $this->list = [];
        foreach($GLOBALS['CURRENCIES'] AS $key => $currency){
            $this->list[$key] = ($withSign ? $currency['sign'] : $key);
        }

        return $this->getList();
    }

    public function getApplications(){
        $this->setList([
            'admin' => 'LBL_APPLICATION_ADMIN',
            'shop'  => 'LBL_APPLICATION_SHOP',
            'api'   => 'LBL_APPLICATION_API',
        ]);

        return $this->getList();
    }

    public function getPaymentStatus(){
        $this->setList([
            0  => 'LBL_ANY',
            1  => 'LBL_PAID',
            2  => 'LBL_NOT_PAID',
            -1 => 'LBL_REFUNDED',
        ]);

        return $this->getList();
    }

    public function getShippingTypes(){
        $this->setList([
            1 => 'LBL_SHIPPING_TYPE_PERSONAL',
            2 => 'LBL_SHIPPING_TYPE_DELIVERY',
            //3 => 'LBL_SHIPPING_TYPE_POST',
        ]);

        return $this->getList();
    }

    public function getShippingModes($useCode = false){
        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'shipping_modes',
                [
                    ($useCode ? 'sm_code' : 'sm_id') . ' AS list_key',
                    'sm_name AS list_value'
                ],
                [],
                [],
                false,
                'list_value'
            )
        );

        return $this->getList();
    }

    public function getPaymentModes(){
        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'payment_modes',
                [
                    'pm_id AS list_key',
                    'pm_name AS list_value'
                ],
                [],
                [],
                false,
                'list_value'
            )
        );

        return $this->getList();
    }

    public function getPaymentProviders(){
        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'payment_providers',
                [
                    'pp_id AS list_key',
                    'CONCAT(pp_name, " (", pp_currency, ")") AS list_value'
                ],
                [],
                [],
                false,
                'list_value'
            )
        );

        return $this->getList();
    }

    public function getOrderStatuses(){
        $this->list = [];
        foreach($GLOBALS['ORDER_STATUSES'] AS $key => $status){
            $this->list[$key] = $status;
        }

        return $this->getList();
    }

    public function getCountries(){
        $validLanguages = ['en', 'de', 'hu', 'sk', 'bg'];

        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'countries',
                [
                    'country_code AS list_key',
                    'country_name_' . (in_array($this->owner->language, $validLanguages) ? strtolower($this->owner->language) : 'en') . ' AS list_value'
                ],
                [],
                [],
                false,
                'list_value'
            )
        );

        return $this->getList();
    }

    public function getTimeZones(){
        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'timezones',
                [
                    'tz_id AS list_key',
                    'tz_name AS list_value'
                ],
                [],
                [],
                false,
                'list_value'
            )
        );

        return $this->getList();
    }

    public function getThemes(){
        $this->setList([
            'none' => 'none',
            'mimity' => 'Mimity',
            'bellaria' => 'Bellaria',
        ]);

        return $this->getList();
    }

    public function getStores($useCode = false){
        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'stores',
                [
                    ($useCode ? 'st_code' : 'st_id') . ' AS list_key',
                    'st_name AS list_value',
                ],
                'st_shop_id = ' . $this->owner->shopId,
                [],
                [],
                'list_value'

            )
        );

        return $this->getList();
    }

    public function getPaymentTypes(){
        $this->setList([
            PAYMENT_TYPE_CASH => 'LBL_PAYMENT_TYPE_CASH',
            PAYMENT_TYPE_MONEY_TRANSFER => 'LBL_PAYMENT_TYPE_MONEY_TRANSFER',
            PAYMENT_TYPE_CARD => 'LBL_PAYMENT_TYPE_CARD',
        ]);

        return $this->getList();
    }

    public function getVat(){
        $this->setList([
            '0' => '0%',
            '5' => '5%',
            '18' => '18%',
            '27' => '27%',
            //'AAM' => 'AAM',
            //'TAM' => 'TAM',
        ]);

        return $this->getList();
    }

    public function getContentPageWidgets(){
        $this->setList([
            0 => 'LBL_NONE',
            'home' => 'LBL_PAGE_HOME',
            'contact' => 'LBL_PAGE_CONTACT',
            'cart' => 'LBL_PAGE_CART',
            'checkout' => 'LBL_PAGE_CHECKOUT',
            'finish' => 'LBL_PAGE_FINISH',
            'register' => 'LBL_PAGE_REGISTER',
        ]);

        return $this->getList();
    }

    public function getUnits(){
        /*
        $this->setList([
            0 => '-',
            1 => 'db',
            2 => 'szelet',
            3 => 'g',
            4 => 'dkg',
            5 => 'kg',
            6 => 'csomag',
            7 => 'torta',
        ]);
        */

        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'units',
                [
                    'un_id AS list_key',
                    'un_name AS list_value',
                ],
                'un_shop_id = 0 OR un_shop_id = ' . $this->owner->shopId,
                [],
                [],
                'un_id'

            )
        );

        return $this->getList();
    }

    public function getDocumentTypes(){
        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'document_types',
                [
                    'dt_id AS list_key',
                    'dt_name AS list_value',
                ],
                'dt_shop_id = 0 OR dt_shop_id = ' . $this->owner->shopId,
                [],
                [],
                'list_value'

            )
        );

        return $this->getList();
    }

    public function getTemplateTypes(){
        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'templates',
                [
                    'mt_key AS list_key',
                    'CONCAT("LBL_TEMPLATE_", mt_key) AS list_value',
                ],
                [
                    'mt_shop_id' => $this->owner->shopId
                ],
                [],
                'list_value'

            )
        );

        return $this->getList();
    }

    public function getWeights(){
        $this->setList([
            'g'  => 'g',
            'dkg' => 'dkg',
            'kg' => 'kg',
        ]);

        return $this->getList();
    }

    public function getColors(){
        $this->setList(enumColors::toArray());

        return $this->getList();
    }

    public function getCategories($includeSmartCategories = false){
        $where = [
            'cat_shop_id' => $this->owner->shopId,
            'cat_smart' => 0
        ];

        if($includeSmartCategories){
            unset($where['cat_smart']);
        }

        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'product_categories',
                [
                    'cat_id AS list_key',
                    'cat_title AS list_value',
                    'cat_order AS list_order'
                ],
                $where,
                [],
                [],
                [
                    'list_order'
                ]
            )
        );

        return $this->getList();
    }

    public function getProperties(){
        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'properties',
                [
                    'prop_id AS list_key',
                    'prop_name AS list_value'
                ],
                [
                    'prop_shop_id' => $this->owner->shopId
                ],
                [],
                [],
                [
                    'list_value'
                ]
            )
        );

        return $this->getList();
    }

    public function getPackagingOptions($addPrice = true){
        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'packagings',
                [
                    'pkg_id AS list_key',
                    ($addPrice ? 'CONCAT(pkg_name, " (", pkg_price, " ' . $this->owner->currencySign . ' / ' . $this->owner->translate->getTranslation('LBL_PCS') . ')")' : 'pkg_name') . ' AS list_value'
                ],
                [
                    'pkg_shop_id' => $this->owner->shopId
                ],
                [],
                [],
                [
                    'list_value'
                ]
            )
        );

        return $this->getList();
    }

    public function getEmployees(){
        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'users',
                [
                    'us_id AS list_key',
                    'CONCAT(us_lastname, " ", us_firstname) AS list_value'
                ],
                [
                    'us_shop_id' => $this->owner->shopId,
                    'us_group' => USER_GROUP_ADMINISTRATORS,
                ],
                [],
                [],
                [
                    'list_value'
                ]
            )
        );

        return $this->getList();
    }

    public function searchEmployees($q, $params = []){
        $where = '(us_shop_id=' . $this->owner->shopId . ' AND us_group = "' . USER_GROUP_ADMINISTRATORS . '")';
        $where .= ' AND (us_lastname LIKE "%' . $this->owner->db->escapeString($q) . '%" OR us_firstname LIKE "%' . $this->owner->db->escapeString($q) . '%")';

        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'users',
                [
                    'us_id AS list_key',
                    'CONCAT(us_lastname, " ", us_firstname) AS list_value'
                ],
                $where,
                [],
                [],
                [
                    'list_value'
                ]
            )
        );

        return $this->getList();
    }

    public function searchCustomers($q, $params = []){
        $where = '(us_shop_id=' . $this->owner->shopId . ' AND us_group = "' . USER_GROUP_CUSTOMERS . '") AND us_deleted = 0';
        $where .= ' AND (
                us_lastname LIKE "%' . $this->owner->db->escapeString($q) . '%" OR 
                us_firstname LIKE "%' . $this->owner->db->escapeString($q) . '%" OR
                us_address LIKE "%' . $this->owner->db->escapeString($q) . '%" OR
                us_invoice_name LIKE "%' . $this->owner->db->escapeString($q) . '%" OR
                us_invoice_address LIKE "%' . $this->owner->db->escapeString($q) . '%" OR
                us_invoice_city LIKE "%' . $this->owner->db->escapeString($q) . '%" OR
                us_city LIKE "%' . $this->owner->db->escapeString($q) . '%" OR
                us_zip LIKE "%' . $this->owner->db->escapeString($q) . '%" OR
                us_invoice_zip LIKE "%' . $this->owner->db->escapeString($q) . '%" OR
                us_phone LIKE "%' . $this->owner->db->escapeString($q) . '%" OR
                us_email LIKE "%' . $this->owner->db->escapeString($q) . '%" OR
                us_email2 LIKE "%' . $this->owner->db->escapeString($q) . '%")';

        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'users',
                [
                    'us_id AS list_key',
                    'CONCAT(us_lastname, " ", us_firstname) AS list_value',
                    'CONCAT("Cím: ", us_zip, " ", us_city, " ", us_address, "<br>Számlázási cím: ", us_invoice_name, " ", us_invoice_zip, " ", us_invoice_city, " ", us_invoice_address) AS list_subtext',
                ],
                $where,
                [],
                [],
                [
                    'list_value'
                ]
            )
        );

        return $this->getList();
    }

    private function checkProductImage($item){
        if(Empty($item['img'])){
            unset($item['list_image']);
            $item['list_icon'] = 'fa fa-camera fa-2x text-muted';
        }

        return $item;
    }

    public function searchProducts($q, $params = []){
        $where = 'prod_shop_id=' . $this->owner->shopId . ' AND prod_archived = 0';
        $where .= ' AND (
                cat_title LIKE "%' . $this->owner->db->escapeString($q) . '%" OR 
                prod_code LIKE "%' . $this->owner->db->escapeString($q) . '%" OR 
                prod_name LIKE "%' . $this->owner->db->escapeString($q) . '%" OR
                prod_brand_name LIKE "%' . $this->owner->db->escapeString($q) . '%" OR
                prod_intro LIKE "%' . $this->owner->db->escapeString($q) . '%" OR
                prod_description LIKE "%' . $this->owner->db->escapeString($q) . '%")';

        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'products',
                [
                    'prod_id AS list_key',
                    'prod_name AS list_value',
                    'CONCAT("' . FOLDER_UPLOAD . '", prod_shop_id, "/products/", prod_cat_id, "/", prod_id, "/", prod_img) AS list_image',
                    'cat_id AS list_group_id',
                    'cat_title AS list_group',
                    'prod_img AS img'
                ],
                $where,
                [
                    'product_categories' => [
                        'on' => [
                            'cat_id' => 'prod_cat_id'
                        ]
                    ]
                ],
                [],
                [
                    'list_group, list_value'
                ]
            ),

            'checkProductImage'
        );

        return $this->getList();
    }

    public function getProductImages($productId){
        /**
         * @var $product product
         */
        $product = $this->owner->addByClassName('product');
        $product->init($productId);

        $this->list = [];

        if($images = $product->getImages()){
            foreach($images AS $image){
                $attributes = [
                    'data-content' => "<div class='d-flex align-items-center'><img src='" . $image['data']['thumbnail'] . "' class='mr-2' width='50'> " . $image['name'] . "</div>"
                ];
                $this->addItem($image['data']['id'], $image['name'], false, $attributes);
            }
        }

        return $this->getList();
    }

    public function getTopPages($exclude = []){
        if(!is_array($exclude)) $exclude = [$exclude];

        $this->sqlQuery(
            $this->owner->db->genSQLSelect(
                'contents',
                [
                    'c_id AS list_key',
                    'c_title AS list_value',
                    'c_order AS list_order',
                ],
                [
                    'c_parent_id' => 0,
                    'c_id' => [
                        'notin' => $exclude
                    ],
                    'c_shop_id' => $this->owner->shopId
                ],
                [],
                [],
                [
                    'list_order'
                ]
            )
        );

        return $this->getList();
    }

    public function setJson($json = true){
        $this->json = $json;
        return $this;
    }

    public function setEmptyItem($label = false, $value = 0){
        $this->emptyListLabel = $label;
        $this->emptyListValue = $value;
        return $this;
    }

    private function sqlQuery($sql, $preprocessor = null){
        $this->list = [];
        $db = $this->owner->db;
        $res = $db->getRows($sql);
        if (!empty($res)) {
            $i = ($this->emptyListLabel ? 1 : 0);

            foreach($res as $row) {
                if($preprocessor && method_exists($this, $preprocessor)){
                    $row = $this->$preprocessor($row);
                }

                if($this->json){
                    $data = [
                        'id' => $row['list_key'],
                        'text' => $row['list_value'],
                    ];

                    if(!Empty($row['list_group'])){
                        $data['groupId'] = $row['list_group_id'];
                        $data['groupName'] = $row['list_group'];
                    }
                    if(!Empty($row['list_subtext'])){
                        $data['data']['subText'] = $row['list_subtext'];
                    }
                    if(!Empty($row['list_tokens'])){
                        $data['data']['tokens'] = $row['list_tokens'];
                    }
                    if(!Empty($row['list_icon'])){
                        $data['data']['icon'] = $row['list_icon'];
                    }
                    if(!Empty($row['list_image'])){
                        $data['data']['image'] = $row['list_image'];
                    }

                    $this->addItem($i, $data);
                }else {
                    $attributes = false;
                    if($row['list_subtext'] || $row['list_tokens'] || $row['list_icon'] || $row['list_title']){
                        $attributes = [];

                        if($row['list_subtext']) {
                            $attributes['data-subtext'] = $row['list_subtext'];
                        }
                        if($row['list_tokens']) {
                            $attributes['data-tokens'] = $row['list_tokens'];
                        }
                        if($row['list_icon']) {
                            $attributes['data-icon'] = $row['list_icon'];
                        }
                        if($row['list_title']) {
                            $attributes['title'] = $row['list_title'];
                        }
                    }

                    $this->addItem($row['list_key'], $row['list_value'], ($row['list_group'] ?: false), $attributes);
                }
                $i++;
            }
        }

        return $this;
    }

    private function addItem($key, $value, $group = false, $attributes = []){
        if($group) {
            if(isset($this->options[$group])){
                $group = $this->options[$group];
            }

            if($attributes) {
                $this->list[$group][$key]['name'] = $value;
                $this->list[$group][$key]['data'] = $attributes;
            }else {
                $this->list[$group][$key] = $value;
            }
        }else{
            if($attributes){
                $this->list[$key]['name'] = $value;
                $this->list[$key]['data'] = $attributes;

            }else {
                $this->list[$key] = $value;
            }
        }
        return $this;
    }

    private function translateItems(){
        if($this->list) {
            foreach ($this->list as $key => $val) {
                if (empty($val)) continue;
                if (is_array($val)) {
                    foreach ($val as $key2 => $val2) {
                        $this->list[$key][$key2] = $this->owner->translate->getTranslation($val2);
                    }
                } else {
                    $this->list[$key] = $this->owner->translate->getTranslation($val);
                }
            }
        }
        return $this;
    }

    public function reset(){
        $this->list = [];
        $this->json = false;
        $this->emptyListLabel = false;
        $this->emptyListValue = 0;

        return $this;
    }

    private function setGroupOptions($options){
        $this->options = $options;
        return $this;
    }

    private function setList(array $list){
        $this->list = $list;

        return $this;
    }

    private function getList($id = false){
        if($id){
            return $this->list[$id];
        }else {
            $this->addEmptyListItem();
            return $this->list;
        }
    }

    private function addEmptyListItem(){
        if($this->emptyListLabel){
            if($this->json) {
                $firstItem = [
                    0 => [
                        'id' => $this->emptyListValue,
                        'text' => $this->owner->translate->getTranslation($this->emptyListLabel),
                    ]
                ];
            }else{
                $firstItem = [
                    $this->emptyListValue => $this->owner->translate->getTranslation($this->emptyListLabel)
                ];
            }

            $this->list = $firstItem + $this->list;
        }
    }
}