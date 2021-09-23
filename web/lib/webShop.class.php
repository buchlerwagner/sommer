<?php
class webShop extends ancestor {
	/**
	 * @var $product product
	 */
	private $product;

	private $shopId;
	private $isAdmin = false;

	public function init($shopId) {
        $this->shopId = (int) $shopId;
		$this->product = $this->owner->addByClassName('product');

        if($_REQUEST['show'] == 'all' && $this->owner->user->getGroup() == USER_GROUP_ADMINISTRATORS){
            $this->isAdmin = true;
        }

		return $this;
	}

	public function getAllCategories(){
		$out = [];
		$result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'product_categories',
                [
                    'cat_id AS id',
                    'cat_title AS name',
                    'cat_url AS url'
                ],
                [
                    'cat_visible' => 1,
                    'cat_shop_id' => $this->shopId
                ],
                [],
                false,
                'cat_order, cat_title'
            )
        );
		if($result){
			foreach($result AS $row){
				$out[$row['id']] = $row;
			}
		}
		return $out;
	}

	public function getCategoryIdByUrl($url){
        $where = [
            'cat_url' => $url,
            'cat_shop_id' => $this->shopId
        ];

        if(!$this->isAdmin){
            $where['cat_visible'] = 1;
        }

		$cat = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'product_categories',
                [
                    'cat_id AS id',
                    'cat_title AS name',
                    'cat_page_title AS pageTitle',
                    'cat_page_description AS pageDescription',
                    'cat_page_img AS image',
                    'cat_url AS pageUrl'
                ],
                $where
            )
        );

        if($cat['image']){
            $cat['pageImage']['relative'] = FOLDER_UPLOAD . $this->shopId . '/products/' . $cat['id'] . '/' . $cat['image'];
            $cat['pageImage']['absolute'] = 'https://' . $this->owner->host . $cat['pageImage']['relative'];
        }

        if($cat['pageUrl']){
            $cat['pageUrl'] = 'https://' . $this->owner->host . $GLOBALS['PAGE_NAMES'][$this->owner->language]['name'] . '/' . $cat['pageUrl'];
        }

        return $cat;
	}

	public function getActiveCategories(){
        $where = [
            'prod_shop_id' => $this->shopId
        ];

        if(!$this->isAdmin){
            $where['cat_visible'] = 1;
            $where['prod_visible'] = 1;
        }

		$out = [];
		$result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'products',
                [
                    'cat_id AS id',
                    'cat_title AS name',
                    'cat_url AS url'
                ],
                $where,
                [
                    'product_categories' => [
                        'on' => [
                            'prod_cat_id' => 'cat_id'
                        ]
                    ]
                ],
                false,
                'cat_order, cat_title'
            )
        );
		if($result){
			foreach($result AS $row){
				$out[$row['id']] = $row;
			}
		}
		return $out;
	}

    public function getActiveTags(){

    }

	public function getHighlightedProducts(){
		$out = [];
		$sql = "SELECT prod_id, prod_shop_id, prod_name, prod_url, prod_price, prod_currency, prod_price_discount, prod_brand_name, prod_img, prod_variants, cat_name, cat_id, cat_url FROM " . DB_NAME_WEB . ".products 
					LEFT JOIN " . DB_NAME_WEB . ".categories ON (prod_cat_id = cat_id) 
						WHERE prod_highlight = 1 AND prod_visible = 1 AND prod_shop_id = " . $this->shopId . "
							ORDER BY prod_price, prod_name";
		$result = $this->owner->db->getRows($sql);
		if($result){
			foreach($result AS $row){
				$price = [];

				if($row['prod_variants']){
					$variants = $this->product->setProductId($row['prod_id'])->getProductVariants();
					if($variants){
						foreach ($variants as $variant) {
							$price = $variant['price'];
							break;
						}
					}
				}else{
					$price = [
						'value' => $row['prod_price'],
						'compared' => $row['prod_price_compare'],
						'currency' => $row['prod_currency'],
					];
				}

				$out[$row['prod_id']] = [
					'id' => $row['prod_id'],
					'name' => $row['prod_name'],
					'brand' => $row['prod_brand_name'],
					//'url' => '/item/' . $row['prod_id'] . '/' . $row['prod_url'],
					'price' => $price,
					'category' => [
						'id' => $row['cat_id'],
						'name' => $row['cat_name'],
					],
					'shop' => [
						'id' => $row['prod_shop_id']
					]
				];
			}
		}
		return $out;
	}

	public function getProducts($params) {
		$out = [
			'filters' => $this->getFilters($params['filters']),
			'items' => [],
			'pager' => [],
		];

		$fields = [
			'prod_id',
			'prod_key',
			'prod_shop_id',
			'prod_cat_id',
			'prod_name',
			//'prod_description',
			'prod_url',
			'prod_price',
			'prod_currency',
			'prod_price_discount',
			'prod_brand_name',
			'prod_img',
			'prod_variants',
			'prod_stock',
			'prod_pack_quantity',
			'prod_pack_unit',
			'prod_weight',
			'prod_weight_unit',
            'cat_id',
			'cat_title',
			'cat_url',
		];

		$where = $this->buildFilterQuery($params['filters']);
		$res = $this->owner->db->getFirstRow(
			"SELECT COUNT(" . $fields[0] . ") as cnt FROM " . DB_NAME_WEB . ".products " . $where
		);
		if($res) {
			$params['pager']['items'] = (int) $res['cnt'];
			if($params['pager']['limit']) {
				$params['pager']['totalPages'] = ceil($params['pager']['items'] / $params['pager']['limit']);
				if ($params['pager']['page'] < 1) {
					$params['pager']['page'] = 1;
				}
				if ($params['pager']['page'] > $params['pager']['totalPages']) {
					$params['pager']['page'] = $params['pager']['totalPages'];
				}
			}else{
				$params['pager'] = [];
			}

			$sql = "SELECT " . implode(', ', $fields) . " FROM " . DB_NAME_WEB . ".products 
					LEFT JOIN " . DB_NAME_WEB . ".product_categories ON (prod_cat_id = cat_id) 
						" . $this->buildFilterQuery($params['filters']) . "
							" . $this->buildSorterQuery($params['sorters'], $params['pager']);
			$result = $this->owner->db->getRows($sql);
			if ($result) {
				foreach ($result AS $row) {
					$id = $row['prod_id'];
                    $out['items'][$id] = $this->product->init($id)->buildItem($row);
                    if($out['items'][$id]['hasVariants']){
                        $out['items'][$id]['variants'] = $this->product->getProductVariants();
                    }

				}
			}
		}

		$out['pager'] = $params['pager'];
		return $out;
	}

	public function getProductDetails($productId){
		if($this->isProductAvailable($productId)) {
			$this->updateViewCounter($productId);
			return $this->product->init($productId)->getProduct();
		}else{
			return false;
		}
	}

	public function getFilters($params){
		return [
			'search' => [
				'query' => $params['query']
			],
			'categories' => [
				'available' => $this->getActiveCategories(),
				'selected' => $params['categories']
			]
		];
	}

	private function isProductAvailable($productId){
        $where = [
            'prod_id' => $productId,
            'prod_archived' => 0,
            'prod_shop_id' => $this->shopId
        ];

        if(!$this->isAdmin){
            $where['prod_visible'] = 1;
        }

        return $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'products',
                [
                    'prod_id AS id',
                ],
                $where
            )
        );

	}

	private function buildFilterQuery($filters = []){
		$where = [];

        if(!$this->isAdmin) {
            $where[] = 'prod_visible = 1';
        }
		$where[] = 'prod_archived = 0';
		$where[] = 'prod_shop_id = ' . $this->shopId;

		if($filters['categories']){
			$ids = [];
			foreach($filters['categories'] AS $category){
				if(!in_array($category, $ids)){
					$ids[] = $category;
				}
			}

			if(!Empty($ids)) {
				$where[] = 'prod_cat_id IN (' . implode(',', $ids) . ')';
			}
		}

		if($filters['query']) {
			$query = preg_replace("/[^a-zA-Z0-9]+/", "", $filters['query']);
			$query = $this->owner->db->escapeString($query);
			$query = explode(' ', $query);
			if($query){
				$tmp = [];
				foreach($query AS $text){
					$tmp[] = '(
						prod_name LIKE "%' . $text . '%" OR 
						prod_brand_name LIKE "%' . $text . '%" OR 
						prod_description LIKE "%' . $text . '%" OR 
						prod_code LIKE "%' . $text . '%"
						prod_id LIKE "%' . $text . '%"
					)';
				}
				$where[] = '(' . implode(' OR ', $tmp) . ')';
			}
		}

		return ' WHERE ' . implode(' AND ', $where);
	}

	private function buildSorterQuery($sorters = [], $pager = []){
		$orderby = [];
		$limit = false;
		$from = 0;

		if($sorters){
			foreach($sorters AS $key => $type){
				$orderby[] = $key . ' ' . (strtolower($type) == 'DESC' ? 'DESC' : 'ASC');
			}
		}

		if($pager['limit']){
			$limit = true;
			$from = (abs($pager['page']) - 1) * $pager['limit'];
		}

		return ($orderby ? ' ORDER BY ' .implode(', ', $orderby) : '' ) . ($limit ? ' LIMIT ' . $from . ', ' . $pager['limit'] : '');
	}

	private function updateViewCounter($productId){
		if(!$this->owner->getSession('pw-' . $productId) && !$this->isAdmin){
			$this->owner->db->sqlQuery(
				$this->owner->db->genSQLUpdate(
					'products',
					[
						'prod_views' => 'INCREMENT',
					],
					[
						'prod_id' => $productId
					]
				)
			);

			$this->owner->setSession('pw-' . $productId, true);
		}
	}
}
