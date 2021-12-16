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
                    'cat_url AS url',
                    'cat_smart AS isSmart',
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
            'cat_url' => strtolower($url),
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
                    'cat_description AS description',
                    'cat_page_description AS pageDescription',
                    'cat_page_img AS image',
                    'cat_url AS pageUrl',
                    'cat_smart AS isSmart',
                    'cat_tags AS tags',
                ],
                $where
            )
        );

        if($cat['image']){
            $cat['pageImage']['relative'] = FOLDER_UPLOAD . $this->shopId . '/products/' . $cat['id'] . '/' . $cat['image'];
            $cat['pageImage']['absolute'] = 'https://' . $this->owner->host . $cat['pageImage']['relative'];
        }

        if($cat['isSmart']){
            $cat['tags'] = explode('|', trim($cat['tags'], '|'));
        }else{
            $cat['tags'] = false;
        }

        if($cat['pageUrl']){
            $cat['pageUrl'] = 'https://' . $this->owner->host . $GLOBALS['PAGE_NAMES'][$this->owner->language]['name'] . '/' . $cat['pageUrl'];
            $cat['pageUrl'] = trim($cat['pageUrl'], '/') . '/';
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

    public function getActiveTags($params = false){
        $out = [];

        $where = [
            'prod_shop_id' => $this->shopId,
            'prop_shop_id' => $this->shopId
        ];

        if($params['categories']){
            if(!is_array($params['categories'])) $categories = [$params['categories']];

            $where['prod_cat_id'] = [
                'in' => $params['categories']
            ];
        }

        if(!$this->isAdmin){
            $where['cat_visible'] = 1;
            $where['prod_visible'] = 1;
        }

        if($params['query']) {
            $query = preg_replace("/[^a-zA-Z0-9]+/", "", $params['query']);
            $query = $this->owner->db->escapeString($query);
            $query = explode(' ', $query);
            if($query){
                $tmp = [];
                foreach($query AS $text){
                    $tmp[] = '(
						prod_name LIKE "%' . $text . '%" OR 
						prod_brand_name LIKE "%' . $text . '%" OR 
						prod_description LIKE "%' . $text . '%" 
					)';
                }
                $where['custom'] = '(' . implode(' OR ', $tmp) . ')';
            }
        }

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'product_properties',
                [
                    'DISTINCT(prop_id) AS id',
                    'prop_name AS name',
                    'prop_icon AS icon',
                ],
                $where,
                [
                    'properties' => [
                        'on' => [
                            'prop_id' => 'pp_prop_id'
                        ]
                    ],
                    'products' => [
                        'on' => [
                            'prod_id' => 'pp_prod_id'
                        ]
                    ],
                    'product_categories' => [
                        'on' => [
                            'cat_id' => 'prod_cat_id'
                        ]
                    ],
                ],
                false,
                'prop_name'
            )
        );

        if($result){
            foreach($result AS $row){
                $out[$row['id']] = $row;
            }
        }

        if($params['categories']){
            $result = $this->owner->db->getRows(
                $this->owner->db->genSQLSelect(
                    'product_categories',
                    [
                        'cat_tags AS tags',
                    ],
                    [
                        'cat_id' => [
                            'in' => $params['categories']
                        ],
                        'cat_smart' => 1,
                        'cat_visible' => 1,
                    ]
                )
            );
            if($result) {
                $tags = [];

                foreach ($result as $row) {
                    $t = explode('|', trim($row['tags'], '|'));
                    foreach($t AS $key) {
                        if (!in_array($key, $tags)) $tags[] = $key;
                    }
                }

                $result = $this->owner->db->getRows(
                    $this->owner->db->genSQLSelect(
                        'properties',
                        [
                            'prop_id AS id',
                            'prop_name AS name',
                            'prop_icon AS icon',
                        ],
                        [
                            'prop_id' => [
                                'in' => $tags
                            ]
                        ],
                        [],
                        false,
                        'prop_name'
                    )
                );

                if ($result) {
                    foreach ($result as $row) {
                        $out[$row['id']] = $row;
                    }
                }
            }
        }

        return $out;
    }

	public function getHighlightedProducts($categoryId = false, $exclude = [], $limit = 5, $random = true){
		$out = [];

        $where = [
            'prod_shop_id' => $this->shopId,
            'prod_highlight' => 1,
            'prod_archived' => 0,
        ];

        if($exclude) {
            if (!is_array($exclude)) {
                $exclude = [$exclude];
            }

            $where['prod_id'] = [
                'notin' => $exclude
            ];
        }

        if($categoryId){
            $where['prod_cat_id'] = (int)$categoryId;
        }

        if(!$this->isAdmin){
            $where['cat_visible'] = 1;
            $where['prod_visible'] = 1;
        }

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'products',
                [
                    'prod_id',
                    'prod_key',
                    'prod_shop_id',
                    'prod_cat_id',
                    'prod_name',
                    'prod_url',
                    'prod_price',
                    'prod_currency',
                    'prod_price_discount',
                    'prod_brand_name',
                    'prod_img',
                    'prod_variants',
                    'prod_available',
                    'prod_stock',
                    'prod_pack_quantity',
                    'prod_pack_unit',
                    'prod_weight',
                    'prod_weight_unit',
                    'cat_id',
                    'cat_title',
                    'cat_url',
                    'cat_stop_sale',
                ],
                $where,
                [
                    'product_categories' => [
                        'on' => [
                            'cat_id' => 'prod_cat_id'
                        ]
                    ],
                ],
                false,
                ($random ? 'RAND()' : ''),
                ($limit ?: false)
            )
        );

        if($result){
            foreach($result AS $row){
                $out[$row['prod_id']] = $this->product->init($row['prod_id'])->buildItem($row);
                if($out[$row['prod_id']]['hasVariants']){
                    $out[$row['prod_id']]['variants'] = $this->product->getProductVariants();
                }
            }
        }

		return $out;
	}

    public function getPopularProducts($categoryId = false, $minViews = 10, $exclude = [], $limit = 5, $random = true) {
        $out = [];

        $where = [
            'prod_shop_id' => $this->shopId,
            'prod_views >' => $minViews,
        ];

        if($exclude) {
            if (!is_array($exclude)) {
                $exclude = [$exclude];
            }

            $where['prod_id'] = [
                'notin' => $exclude
            ];
        }

        if($categoryId){
            $where['prod_cat_id'] = (int)$categoryId;
        }

        if(!$this->isAdmin){
            $where['cat_visible'] = 1;
            $where['prod_visible'] = 1;
        }

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'products',
                [
                    'prod_id',
                    'prod_key',
                    'prod_shop_id',
                    'prod_cat_id',
                    'prod_name',
                    'prod_url',
                    'prod_price',
                    'prod_currency',
                    'prod_price_discount',
                    'prod_brand_name',
                    'prod_img',
                    'prod_variants',
                    'prod_available',
                    'prod_stock',
                    'prod_pack_quantity',
                    'prod_pack_unit',
                    'prod_weight',
                    'prod_weight_unit',
                    'cat_id',
                    'cat_title',
                    'cat_url',
                ],
                $where,
                [
                    'product_categories' => [
                        'on' => [
                            'cat_id' => 'prod_cat_id'
                        ]
                    ],
                ],
                false,
                ($random ? 'RAND()' : ''),
                ($limit ?: false)
            )
        );

        if($result){
            foreach($result AS $row){
                $out[$row['prod_id']] = $this->product->init($row['prod_id'])->buildItem($row);
                if($out[$row['prod_id']]['hasVariants']){
                    $out[$row['prod_id']]['variants'] = $this->product->getProductVariants();
                }
            }
        }

        return $out;
    }

    public function getProducts($params) {
		$out = [
			'filters' => $this->getFilters($params['filters']),
			'sorter' => $params['sorter'],
			'items' => [],
			'pager' => [],
		];

		$fields = [
			'prod_id',
			'prod_key',
			'prod_shop_id',
			'prod_cat_id',
			'prod_name',
			'prod_url',
			'prod_price',
			'prod_currency',
			'prod_price_discount',
			'prod_brand_name',
			'prod_img',
			'prod_variants',
			'prod_available',
			'prod_stock',
			'prod_pack_quantity',
			'prod_pack_unit',
			'prod_weight',
			'prod_weight_unit',
            'cat_id',
			'cat_title',
			'cat_url',
			'cat_stop_sale',
			'cat_limit_sale',
			'cat_limit_sale_text',
			'cat_date_start',
			'cat_date_end',
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
				if ($params['pager']['page'] > $params['pager']['totalPages'] && $params['pager']['totalPages'] > 0) {
					$params['pager']['page'] = $params['pager']['totalPages'];
				}
            }else{
				$params['pager'] = [];
			}

			$sql = "SELECT " . implode(', ', $fields) . " FROM " . DB_NAME_WEB . ".products 
					LEFT JOIN " . DB_NAME_WEB . ".product_categories ON (prod_cat_id = cat_id) 
						" . $this->buildFilterQuery($params['filters']) . "
							" . $this->buildSorterQuery($params['sorter'], $params['pager']);
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
			return $this->product->init($productId)->updateViewCounter()->getProduct();
		}else{
			return false;
		}
	}

	public function getFilters($params){
        $isFiltered = false;

        if(!Empty($params['query']) || !Empty($params['tags'])){
            $isFiltered = true;
        }

		return [
			'search' => [
				'query' => $params['query']
			],
			'categories' => [
				'available' => $this->getActiveCategories(),
				'selected' => $params['categories']
			],
            'tags' => [
                'available' => $this->getActiveTags($params),
                'selected' => $params['tags']
            ],
            'filtered' => $isFiltered
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

		//$where[] = '(prod_price > 0 OR prod_price_discount > 0)';
		$where[] = 'prod_archived = 0';
		$where[] = 'prod_shop_id = ' . $this->shopId;

		if($filters['categories']){
            $allCategories = $this->getAllCategories();

			$ids = [];
			foreach($filters['categories'] AS $category){
				if(!in_array($category, $ids) && !$allCategories[$category]['isSmart']){
					$ids[] = $category;
				}
			}

			if(!Empty($ids)) {
				$where[] = 'prod_cat_id IN (' . implode(',', $ids) . ')';
			}
		}

        if(!Empty($filters['tags']) && is_array($filters['tags'])){
            $where[] = 'prod_id IN (' . implode(',', $this->getProductsByTags($filters['tags'])) . ')';
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
						prod_code LIKE "%' . $text . '%" OR
						prod_id LIKE "%' . $text . '%"
					)';
				}
				$where[] = '(' . implode(' OR ', $tmp) . ')';
			}
		}

		return ' WHERE ' . implode(' AND ', $where);
	}

	private function buildSorterQuery($sorter = false, $pager = []){
		$orderBy = [];
		$limit = false;
		$from = 0;

        if(!$sorter) $sorter = 'price';

        switch ($sorter){
            case 'price-desc':
                $field = 'IF(prod_price_discount > 0, prod_price_discount, prod_price)';
                $type = 'DESC';
                break;
            case 'price':
            default:
                $field = 'IF(prod_price_discount > 0, prod_price_discount, prod_price)';
                $type = 'ASC';
                break;
        }

        $orderBy[] = $field . ' ' . $type;

		if($pager['limit']){
			$limit = true;
			$from = (abs($pager['page']) - 1) * $pager['limit'];
		}

		return ($orderBy ? ' ORDER BY ' .implode(', ', $orderBy) : '' ) . ($limit ? ' LIMIT ' . (int) $from . ', ' . $pager['limit'] : '');
	}

    private function getProductsByTags(array $tags):array{
        $ids = [];

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'product_properties',
                [
                    'DISTINCT pp_prod_id AS id'
                ],
                [
                    'pp_prop_id' => [
                        'in' => $tags
                    ]
                ]
            )
        );

        if ($result) {
            foreach ($result as $row) {
                if(!in_array($row['id'], $ids)){
                    $ids[] = $row['id'];
                }
            }
        }

        return $ids;
    }

}
