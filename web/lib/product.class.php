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
        $this->getProperties();
        $this->getProductVariants();

        if($this->data['hasVariants'] && $this->data['variants']){
            $this->data['prices'] = [];
            foreach ($this->data['variants'] as $variant) {
                $this->data['prices'][] = $variant['price'];
            }
        }

        return $this->data;
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
			return  DIR_UPLOAD_IMG . 'products/' . $this->shopId . '/' . $this->categoryId . '/' . $this->productId . '/';
		}else{
			return '/uploads/products/' . $this->shopId . '/' . $this->categoryId . '/' . $this->productId . '/';
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
            $url = '/termekek/' . $row['cat_url'] . '/' . $row['prod_id'] . '-' . $row['prod_url'];

			$path = $this->getImagePath();

			$price = [
				'value' => $row['prod_price'],
				'discount' => $row['prod_price_discount'],
				'currency' => $row['prod_currency'],
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
					'url' => '/termekek/' . $row['cat_url'] . '/',
				],
                'prices' => [
                    0 => $price
                ],

                'hasVariants' => $row['prod_variants'],
                'variants' => false,

				'defaultImage' => $path . $row['prod_img'],
				'images' => [],

				'weight' => [
					'value' => $row['prod_weight'],
					'unit' => $row['prod_weight_unit'],
				],
				'stats' => [
					'views' => $row['prod_views'],
					'orders' => $row['prod_orders'],
				],
				'rating' => [
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
			foreach ($result AS $row) {
                $variants[$row['pv_id']] = [
					'id' => $row['pv_id'],
					'name' => $row['pv_name'],
					'imgId' => $row['pv_pimg_id'],
					'price' => [
						'value' => $row['pv_price'],
						'discount' => $row['pv_price_discount'],
						'currency' => $row['pv_currency'],
					],
					'weight' => [
						'value' => $row['pv_weight'],
						'unit' => $row['pv_weight_unit'],
					],
				];
			}

            $this->data['variants'] = $variants;
		}

		return $variants;
	}

    public function getProperties(){
        $properties = [];

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'product_properties',
                [
                    'prop_id',
                    'prop_name',
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
                $properties[$row['prop_id']] = $row['prop_name'];
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
