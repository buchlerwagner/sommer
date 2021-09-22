<?php
class product extends ancestor {
	public $productId = 0;
	public $categoryId = 0;

    private $shopId = 0;
	private $data = [];

	public function init($productId){
        $this->productId = (int) $productId;
        $this->shopId = $this->owner->shopId;

        $this->loadProduct();

        return $this;
	}

    public function getProduct(){
        $this->getImages();
        $this->getProperties(true);

        if($this->data['hasVariants']){
            $this->getProductVariants();
        }

        return $this->data;
    }

    public function getMinSale($variantId = 0){
        if($this->data['variants'][$variantId]){
            return $this->data['variants'][$variantId]['minSale'];
        }

        return false;
    }

    public function getMaxSale($variantId = 0){
        if($this->data['variants'][$variantId]){
            return $this->data['variants'][$variantId]['maxSale'];
        }

        return false;
    }

    public function getStock($variantId = 0){
        if($this->data['variants'][$variantId]){
            return $this->data['variants'][$variantId]['stock'];
        }

        return false;
    }

    public function getVariant($variantId = 0){
        if($this->data['variants'][$variantId]){
            return $this->data['variants'][$variantId];
        }

        return false;
    }

	public function reorderImages($orderList = false){
		if(!$orderList){
			$result = $this->owner->db->getRows(
				"SELECT pimg_id FROM " . DB_NAME_WEB . ".product_images LEFT JOIN " . DB_NAME_WEB . ".products ON (pimg_prod_id = prod_id) WHERE pimg_prod_id='" . $this->productId . "' ORDER BY pimg_order"
			);
			if($result){
				$i = 0;
				$orderList = [];
				foreach($result AS $row) {
					$orderList[] = [
						'id' => $row['pimg_id'],
						'index' => $i++,
					];
				}
			}
		}

		if($orderList){
			foreach($orderList AS $order){
				$this->owner->db->sqlQuery(
					$this->owner->db->genSQLUpdate(
						DB_NAME_WEB . '.product_images',
						[
							'pimg_order' => $order['index'] + 1,
						],
						[
							'pimg_id' => $order['id'],
							'pimg_prod_id' => $this->productId,
						]
					)
				);
			}
		}

		return $this;
	}

	public function setDefaultImg($img = false){
		if(!$img){
			$img = $this->getFirstImage();
		}

		$this->owner->db->sqlQuery(
			$this->owner->db->genSQLUpdate(
				DB_NAME_WEB . '.products',
				[
					'prod_img' => $img,
				],
				[
					'prod_id' => $this->productId
				]
			)
		);

		return $this;
	}

	public function getFirstImage(){
		$img = '';
		$row = $this->owner->db->getFirstRow(
			"SELECT pimg_filename, pimg_extension FROM " . DB_NAME_WEB . ".product_images WHERE pimg_prod_id='" . $this->productId. "' ORDER BY pimg_order LIMIT 1"
		);
		if($row){
			$img = $row['pimg_filename'] . '.' . $row['pimg_extension'];
		}

		return $img;
	}

    public function getImage($imgId){
        $row = $this->owner->db->getFirstRow(
            "SELECT prod_id, prod_key, pimg_filename, pimg_extension, pimg_order FROM " . DB_NAME_WEB . ".product_images LEFT JOIN " . DB_NAME_WEB . ".products ON (pimg_prod_id = prod_id) WHERE pimg_id='" . (int) $imgId . "' LIMIT 1"
        );
        if($row){
            return $row;
        }else{
            return false;
        }
    }

	public function getImages(){
		$img = [];

		$sql = "SELECT * FROM " . DB_NAME_WEB . ".product_images WHERE pimg_prod_id='" . $this->productId . "' ORDER BY pimg_order LIMIT 8";
		$result = $this->owner->db->getRows($sql);
		if($result){
			$i = 0;
			foreach($result AS $row){
				$path = $this->getImagePath();

				$img[$i] = [
					'name' => $row['pimg_orig_filename'],
					'size' => $row['pimg_size'],
					'type' => $row['pimg_mimetype'],
					'file' => $path . $row['pimg_filename'] . '.' . $row['pimg_extension'],
					'data' => [
                        'thumbnail' => $path . $row['pimg_filename'] . '_thumbnail.' . $row['pimg_extension'],
						'name' => $row['pimg_filename'] . '.' . $row['pimg_extension'],
						'id' => $row['pimg_id'],
					]
				];

				$i++;
			}

            $this->data['images'] = $img;
		}

		return $img;
	}

	public function getImagePath($absolutePath = false){
        if($absolutePath) {
			return  DIR_UPLOAD . $this->owner->shopId . '/products/' . $this->categoryId . '/' . $this->productId . '/';
		}else{
			return FOLDER_UPLOAD . $this->owner->shopId . '/products/' . $this->categoryId . '/' . $this->productId . '/';
		}
	}

	private function loadProduct(){
		$this->data = [];

		$sql = "SELECT * FROM " . DB_NAME_WEB . ".products
					LEFT JOIN " . DB_NAME_WEB . ".product_categories ON (prod_cat_id = cat_id) 
						WHERE prod_id = '" . $this->productId . "' AND prod_shop_id = " . $this->shopId;
		$row = $this->owner->db->getFirstRow($sql);
		if($row){
            $this->categoryId = $row['prod_cat_id'];
            $url = '/' . $GLOBALS['PAGE_NAMES'][$this->owner->language]['products'] . '/' . $row['cat_url'] . '/' . $row['prod_id'] . '-' . $row['prod_url'] . '/';
			$path = $this->getImagePath();

            $packageUnits = $this->owner->lists->getUnits();
            $weightUnits = $this->owner->lists->getWeights();

            $variant = [
                'id' => 0,
                'imgId' => 0,
                'price' => [
                    'value' => $row['prod_price'],
                    'discount' => $row['prod_price_discount'],
                    'currency' => $row['prod_currency'],
                ],
                'minSale' => $row['prod_min_sale'],
                'maxSale' => $row['prod_max_sale'],
                'stock' => $row['prod_stock'],
                'packaging' => [
                    'quantity' => $row['prod_pack_quantity'],
                    'packageUnit' => $row['prod_pack_unit'],
                    'packageUnitName' => $packageUnits[$row['prod_pack_unit']],
                    'weight' => $packageUnits[$row['prod_weight']],
                    'weightUnit' => $packageUnits[$row['prod_weight_unit']],
                    'weightUnitName' => $weightUnits[$row['prod_weight_unit']],
                ],
            ];

            $this->data = [
				'id' => $row['prod_id'],
				'key' => $row['prod_key'],
				'name' => $row['prod_name'],
				'brand' => $row['prod_brand_name'],
				'description' => $row['prod_description'],

				'category' => [
					'id' => $row['cat_id'],
					'name' => $row['cat_name'],
					'url' => '/' . $GLOBALS['PAGE_NAMES'][$this->owner->language]['products'] . '/' . $row['cat_url'] . '/',
				],

                'hasVariants' => ($row['prod_variants']),
                'variants' => [
                    $variant
                ],

				'defaultImage' => $path . $row['prod_img'],
				'images' => [],

				'stats' => [
					'views' => $row['prod_views'],
					'orders' => $row['prod_orders'],
				],
				'ratings' => [
					'value' => $row['prod_rating'],
					'reviews' => $row['prod_reviews'],
				],
				'seo' => [
					'title' => $row['prod_page_title'],
					'description' => $row['prod_page_description'],
				],
                'url' => [
                    'relative' => $url,
                    'absolute' => 'https://' . HOST_CLIENTS . $url,
                ]
			];

		}

		return $this;
	}

	public function getProductVariants(){
		$variants = [];

		$sql = "SELECT pv_id, pv_name, pv_price, pv_price_discount, pv_currency, pv_pimg_id
                    FROM " . DB_NAME_WEB . ".product_variants
    						WHERE pv_prod_id='" . $this->productId . "'
	    						ORDER BY pv_price, pv_name";
		$result = $this->owner->db->getRows($sql);
		if($result) {
            $packageUnits = $this->owner->lists->getUnits();
            $weightUnits = $this->owner->lists->getWeights();

			foreach ($result AS $row) {
                $variants[$row['pv_id']] = [
                    'id' => $row['pv_id'],
                    'imgId' => $row['pv_pimg_id'],
                    'price' => [
                        'value' => $row['pv_price'],
                        'discount' => $row['pv_price_discount'],
                        'currency' => $row['pv_currency'],
                    ],
                    'minSale' => $row['pv_min_sale'],
                    'maxSale' => $row['pv_max_sale'],
                    'stock' => $row['pv_stock'],
                    'packaging' => [
                        'quantity' => $row['pv_pack_quantity'],
                        'packageUnit' => $row['pv_pack_unit'],
                        'packageUnitName' => $packageUnits[$row['pv_pack_unit']],
                        'weight' => $packageUnits[$row['pv_weight']],
                        'weightUnit' => $packageUnits[$row['pv_weight_unit']],
                        'weightUnitName' => $weightUnits[$row['pv_weight_unit']],
                    ],
                ];
			}

            $this->data['variants'] = $variants;
		}

		return $variants;
	}

    public function getProperties($withIcons = false){
        $properties = [];

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'product_properties',
                [
                    'prop_id',
                    'prop_name',
                    'prop_icon',
                ],
                [
                    'pp_prod_id' => $this->productId,
                    'prop_shop_id' => $this->shopId
                ],
                [
                    'properties' => [
                        'on' => [
                            'prop_id' => 'pp_prop_id'
                        ]
                    ]
                ]
            )
        );
        if($result){
            foreach($result AS $row){
                if($withIcons) {
                    $properties[$row['prop_id']]['name'] = $row['prop_name'];
                    $properties[$row['prop_id']]['icon'] = $row['prop_icon'];
                }else{
                    $properties[$row['prop_id']] = $row['prop_name'];
                }
            }

            $this->data['properties'] = $properties;
        }

        return $properties;
    }

    public function setProperties(array $properties){
        if($properties){
            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLDelete(
                    'product_properties',
                    [
                        'pp_prod_id' => $this->productId
                    ]
                )
            );

            foreach($properties AS $propId) {
                $this->owner->db->sqlQuery(
                    $this->owner->db->genSQLInsert(
                        'product_properties',
                        [
                            'pp_prod_id' => $this->productId,
                            'pp_prop_id' => $propId,
                        ]
                    )
                );
            }
        }
    }
}
