<?php
class cart extends ancestor {
	const CART_SESSION_KEY = 'cart';

	const CART_STATUS_NEW = 'NEW';
	const CART_STATUS_ABANDONED = 'ABANDONED';
	const CART_STATUS_ORDERED = 'ORDERED';

	/**
	 * @var $products product
	 */
	private $products;

	private $product;

	public $currency;

	public $id;
	public $key;
	public $status;
	public $total = 0;
	public $subtotal = 0;
	public $discount = 0;
	public $shipping = 0;

	public $items = [];

	public function init($key = false, $create = true) {
		$this->currency = $GLOBALS['HOSTS'][$this->owner->host]['currency'];
		$this->getKey($key, $create)->loadCart();

		return $this;
	}

	public function addProduct($id, $variant = 0, $quantity = 1){
		$key = false;
		$new = false;

		if($this->status == self::CART_STATUS_NEW) {
			$id = (int)$id;
			$variant = (int)$variant;
			$quantity = (int)$quantity;

			if ($quantity <= 0) $quantity = 1;

			$this->products = $this->owner->addByClassName('product');
			$this->checkProduct($id);

			if ($this->product) {
				if ($key = $this->isProductInCart($id, $variant)) {
					$this->updateItemInCart($key, $quantity);
				} else {
					$new = true;
					$key = $this->saveItemToCart($id, $variant, $quantity);
				}
			}
		}

		return [
			'id' => $key,
			'new' => $new
		];
	}

	public function removeProduct($id){
		if($this->status == self::CART_STATUS_NEW) {
			if ($this->items[$id]) {
				$sql = "DELETE FROM " . DB_NAME_WEB . ".cart_items WHERE citem_cart_id = '" . $this->id . "' AND citem_id='" . $id . "'";
				$this->owner->db->sqlQuery($sql);
			}

			$this->loadCart()->summarize();
		}

		return $this;
	}

	public function changeProduct($key, $quantity = 0){
		if($this->status == self::CART_STATUS_NEW) {
			$key = (int)$key;

			if ($this->items[$key]) {
				$this->owner->db->sqlQuery(
					$this->owner->db->genSQLUpdate(
						DB_NAME_WEB . '.cart_items',
						[
							'citem_quantity' => $quantity,
						],
						[
							'citem_id' => $key,
							'citem_cart_id' => $this->id
						]
					)
				);

				$this->loadCart()->summarize();
			}
		}

		return $this;
	}

	public function getCartItems(){
		return $this->items;
	}

	public function getCartItemsNumber(){
		$count = 0;

		if($this->status == self::CART_STATUS_NEW) {
			if ($this->items) {
                $count = count($this->items);
			}
		}

		return $count;
	}

	public function makeOrder($userId){
		$this->owner->db->sqlQuery(
			$this->owner->db->genSQLUpdate(
				DB_NAME_WEB . '.cart',
				[
					'cart_us_id' => (int) $userId,
					'cart_status' => 'ORDER',
				],
				[
					'cart_id' => $this->id,
					'cart_key' => $this->key
				]
			)
		);

		$cartMailBody = $this->owner->view->renderContent(
			'order',
			[
				'items' => $this->items,
				'currency' => $this->currency,
				'subtotal' => $this->subtotal,
				'discount' => $this->discount,
				'total' => $this->total,
				'key' => $this->key,
				'id' => $this->id,
				'status' => $this->status,
			],
			false,
			true
		);

		$data = [
			'id' => $userId,
			'link' => $this->owner->domain . '/finish/' .$this->key . '/',
			'cart' => $cartMailBody,
			'status' => $this->status,
			'key' => $this->key,
			'total' => $this->total,
			'currency' => $this->currency,
		];
		$this->owner->email->prepareEmail(
			'order-request',
			$userId,
			$data,
			false,  // from
			false,  // cc
			($GLOBALS['HOSTS'][$this->owner->host]['emails']['orders'] ? $GLOBALS['HOSTS'][$this->owner->host]['emails']['orders'] : false) // bcc
		);

		$key = $this->key;
		$this->destroyKey();

		return $key;
	}

	private function loadCart(){
		$this->items = [];
		$this->id = 0;
		$sql = "SELECT * FROM " . DB_NAME_WEB . ".cart WHERE cart_key = '" . $this->owner->db->escapeString($this->key) . "'";
		$cart = $this->owner->db->getFirstRow($sql);
		if($cart) {
			$this->id = $cart['cart_id'];
			$this->status = $cart['cart_status'];
			$this->total = $cart['cart_total'];
			$this->subtotal = $cart['cart_subtotal'];
			$this->currency = $cart['cart_currency'];

			$sql = "SELECT * FROM " . DB_NAME_WEB . ".cart_items WHERE citem_cart_id = '" . $this->id . "' ORDER BY citem_prod_id, citem_pv_id";
			$result = $this->owner->db->getRows($sql);
			if ($result) {
				$packUnits = $this->owner->lib->getList('pack-units');

				foreach ($result AS $row) {
					$this->items[$row['citem_id']] = [
						'id' => $row['citem_id'],
						'cartid' => $row['citem_cart_id'],
						'productid' => $row['citem_prod_id'],
						'variantid' => $row['citem_pv_id'],
						'name' => $row['citem_prod_name'],
						'type' => $row['citem_prod_type'],
						'variant' => $row['citem_prod_variant'],
						'img' => $row['citem_prod_img'],
						'imgbase64' => base64_encode($row['citem_prod_img']),
						'price' => [
							'value' => $row['citem_price'],
							'currency' => $row['citem_currency'],
							'discount' => $row['citem_discount'],
							'total' => ($row['citem_price'] * $row['citem_quantity']),
							'formated' => $this->owner->lib->formatPrice($row['citem_price'] * $row['citem_quantity'], $this->currency),
						],
						'quantity' => [
							'amount' => $row['citem_quantity'],
							'unit' =>$packUnits[$row['citem_pack_unit']],
							'unitcode' => $row['citem_pack_unit'],
						],
						'weight' => [
							'value' => $row['citem_weight'],
							'unit' => $row['citem_weight_unit'],
							'total' => ($row['citem_weight'] * $row['citem_quantity']),
						]
					];
				}
			}
		}

		return $this;
	}

	private function isProductInCart($prodctId, $variantId = 0){
		$out = false;
		if($this->items){
			foreach($this->items AS $key => $item){
				if($item['productid'] == $prodctId && $item['variantid'] == $variantId){
					$out = $key;
					break;
				}
			}
		}
		return $out;
	}

	private function saveItemToCart($prodctId, $variantId = 0, $quantity = 1){
		if($this->product['variants'][$variantId]){
			$price = $this->product['variants'][$variantId]['price']['value'];
			$weight = $this->product['variants'][$variantId]['weight']['value'];
			$weightUnit = $this->product['variants'][$variantId]['weight']['unit'];
			$type = $this->product['variants'][$variantId]['type'];
			$variant = $this->product['variants'][$variantId]['name'];
		}else{
			$price = $this->product['price']['value'];
			$weight = $this->product['weight']['value'];
			$weightUnit = $this->product['weight']['unit'];

			$type = '';
			$variant = '';
		}
		$img = $this->product['img'];

		$this->owner->db->sqlQuery(
			$this->owner->db->genSQLInsert(
				DB_NAME_WEB . '.cart_items',
				[
					'citem_cart_id' => $this->id,
					'citem_prod_id' => $prodctId,
					'citem_pv_id' => $variantId,
					'citem_prod_name' => $this->product['name'],
					'citem_prod_img' => $img,
					'citem_prod_type' => $type,
					'citem_prod_variant' => $variant,
					'citem_price' => $price,
					'citem_currency' => $this->product['price']['currency'],
					'citem_quantity' => $quantity,
					'citem_pack_unit' => $this->product['pack']['unitcode'],
					'citem_weight' => $weight,
					'citem_weight_unit' => $weightUnit,
				]
			)
		);
		$key = $this->owner->db->getInsertRecordId();

		$this->loadCart()->summarize();

		return $key;
	}

	private function updateItemInCart($key, $quantity = 0){
		$key = (int) $key;

		if($this->items[$key]) {
			$this->items[$key]['quantity']['amount'] += $quantity;
			$this->items[$key]['price']['total'] = $this->items[$key]['price']['value'] * $this->items[$key]['quantity']['amount'];

			$this->owner->db->sqlQuery(
				$this->owner->db->genSQLUpdate(
					DB_NAME_WEB . '.cart_items',
					[
						'citem_quantity' => $this->items[$key]['quantity']['amount'],
					],
					[
						'citem_id' => $key,
						'citem_cart_id' => $this->id,
					]
				)
			);

			$this->loadCart()->summarize();
		}

		return $key;
	}

	private function summarize(){
		$this->total = 0;
		$this->subtotal = 0;
		$this->discount = 0;
		$this->shipping = 0;

		if($this->items){
			foreach($this->items AS $item){
				$this->total += $item['price']['total'];
				$this->subtotal += $item['price']['total'];
			}
		}

		$this->owner->db->sqlQuery(
			$this->owner->db->genSQLUpdate(
				DB_NAME_WEB . '.cart',
				[
					'cart_us_id' => (int) $this->owner->user->getProfile()['id'],
					'cart_subtotal' => $this->subtotal,
					'cart_total' => $this->total,
					'cart_shipping' => $this->shipping,
					'cart_discount' => $this->discount,
				],
				[
					'cart_id' => $this->id,
					'cart_key' => $this->key
				]
			)
		);

		return $this;
	}

	private function getKey($key = false, $create = true){
		if(!Empty($key)) {
			if (!$this->key = $this->checkCartKey($key)) {
				if($create) {
					$this->setKey();
				}
			}
		}else {
			if (!$this->key = $this->owner->getSession(self::CART_SESSION_KEY)) {
				if (!$this->key = $_COOKIE[self::CART_SESSION_KEY]) {
					if ($create) {
						$this->setKey();
					}
				}
			}
		}

		return $this;
	}

	private function setKey(){
		if(!$this->key){
			$this->key = uuid::v4();
			$this->owner->setSession(self::CART_SESSION_KEY, $this->key);
			setcookie(self::CART_SESSION_KEY, $this->key, 0, '/');

			$this->initCart();
		}

		return $this;
	}

	private function destroyKey(){
		$this->owner->delSession(self::CART_SESSION_KEY);
		setcookie(self::CART_SESSION_KEY, '', time() - 3600, '/');
		$this->key = false;
	}

	private function checkCartKey($key){
		$row = $this->owner->db->getFirstRow("SELECT cart_key FROM " . DB_NAME_WEB . ".cart WHERE cart_key='" . $this->owner->db->escapeString($key) . "'");
		if($row['cart_key']){
			return $row['cart_key'];
		}else{
			return false;
		}
	}

	private function initCart(){
		if($this->owner->user->isLoggedIn()) {
			$userId = $this->owner->user->getProfile()['id'];
		}else{
			$userId = 0;
		}

		$this->owner->db->sqlQuery(
			$this->owner->db->genSQLInsert(
				DB_NAME_WEB . '.cart',
				[
					'cart_key' => $this->key,
					'cart_shop_id' => $this->owner->shopId,
					'cart_us_id' => $userId,
					'cart_created' => 'NOW()',
					'cart_status' => 'NEW',
					'cart_currency' => $this->currency,
				],
				[
					'cart_key'
				]
			)
		);
	}

	public function checkProduct($productId){
		$sql = "SELECT prod_id, prod_shop_id FROM " . DB_NAME_WEB . ".products WHERE prod_id = '" . $productId . "' AND prod_available = 1";
		$row = $this->owner->db->getFirstRow($sql);
		if($row){
			$this->product = $this->products->setProductId($row['prod_id'], $row['prod_shop_id'])->getProduct(false);
		}

		return $this;
	}

}
