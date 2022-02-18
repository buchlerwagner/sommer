<?php
class cart extends ancestor {
	const CART_SESSION_KEY = 'cart';

	const CART_STATUS_NEW = 'NEW';
	const CART_STATUS_ABANDONED = 'ABANDONED';
	const CART_STATUS_ORDERED = 'ORDERED';

	const ORDER_STATUS_NEW = 'NEW';
	const ORDER_STATUS_FINISHED = 'FINISHED';
	const ORDER_STATUS_CLOSED = 'CLOSED';

	/**
	 * @var $product product
	 */
	private $product;

	public $currency;

	public $id;
	public $key;
	public $status;
	public $orderStatus;
	public $orderDate;
	public $orderNumber;
	public $total = 0;
	public $subtotal = 0;
	public $discount = 0;
	public $shippingFee = 0;
    private $shippingId = false;
    private $intervalId = false;
	public $paymentFee = 0;
    private $paymentId = false;
    private $isPaid = false;
    public $packagingFee = 0;

    private $earliestTakeover = false;
    private $orderDateStart = false;
    private $orderDateEnd = false;
    private $orderDayLimits = [];
    private $saleLimitText = false;

    private $userId;
    private $isAdmin = false;

    public $userData = [];
	public $items = [];
	public $remarks;
	public $orderType = 0;
	public $invoiceType = 0;
	public $localConsumption = false;
	public $shippingDate;
	public $customInterval;
	public $storeId;
	public $storeName;

	private $options = [];

    public function init($key = false, $create = true) {
        if($this->owner->user->getGroup() == USER_GROUP_ADMINISTRATORS){
            $this->isAdmin = true;
        }

		$this->currency = $this->owner->currency;

        $this->getKey($key, $create)->loadCart();
        $this->product = $this->owner->addByClassName('product');

		return $this;
	}

    public function createNewOrder(int $type = ORDER_TYPE_STORE){
        $pmId = 0;
        $paymentModes = $this->getPaymentModes([PAYMENT_TYPE_CASH]);
        if($paymentModes){
            foreach($paymentModes AS $paymentMode){
                $pmId = $paymentMode['id'];
                break;
            }
        }

        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLInsert(
                'cart',
                [
                    'cart_key' => uuid::v4(),
                    'cart_shop_id' => $this->owner->shopId,
                    'cart_store_id' => $this->owner->storeId,
                    'cart_us_id' => 0,
                    'cart_pm_id' => $pmId,
                    'cart_order_type' => $type,
                    'cart_local_consumption' => ($type == ORDER_TYPE_STORE ? 1 : 0),
                    'cart_created_by' => $this->owner->user->getUser()['id'],
                    'cart_created' => 'NOW()',
                    'cart_ordered' => 'NOW()',
                    'cart_shipping_date' => 'NOW()',
                    'cart_status' => self::CART_STATUS_NEW,
                    'cart_currency' => $this->currency,
                ],
                [
                    'cart_key',
                    'cart_shop_id'
                ]
            )
        );

        return $this->owner->db->getInsertRecordId();
    }

	public function addProduct($productId, $variantId = 0, $quantity = 1){
		$key = false;
		$new = false;
		$error = false;

		if($this->status != self::CART_STATUS_ORDERED && !$this->owner->settings['stopSale']) {
			$productId = (int)$productId;
			$variantId = (int)$variantId;
			$quantity = (int)$quantity;

            $item = $this->product->init($productId)->getProduct();

			if ($this->product->isAvailable()) {
                $quantity = $this->checkQuantity($quantity, $variantId);

				if ($key = $this->isProductInCart($productId, $variantId)) {
					$this->updateItemInCart($key, $quantity);
				} else {
                    $key = $this->addItemToCart($item, $variantId, $quantity);
                    $new = true;
                }
			}else{
                $error = 2;
            }
		}else{
            $error = 1;
        }

		return [
			'id' => $key,
			'new' => $new,
			'error' => $error
		];
	}

	public function removeProduct($id){
		if($this->status == self::CART_STATUS_NEW) {
			if ($this->items[$id]) {
				$this->owner->db->sqlQuery(
                    $this->owner->db->genSQLDelete(
                        'cart_items',
                        [
                            'citem_cart_id' => $this->id,
                            'citem_id' => $id
                        ]
                    )
                );
			}

			$this->loadCart()->summarize();
		}

		return $this;
	}

	public function changeProductQuantity($key, $quantity = 0){
		if($this->status == self::CART_STATUS_NEW) {
			$key = (int)$key;
			if ($this->items[$key]) {
                $productId = $this->items[$key]['productId'];
                $variantId = $this->items[$key]['variantId'];

                $this->product->init($productId)->getProduct();
                $quantity = $this->checkQuantity($quantity, $variantId);

                $row = $this->owner->db->getFirstRow(
                    $this->owner->db->genSQLSelect(
                        'cart_items',
                        [
                            'citem_price',
                        ],
                        [
                            'citem_id' => $key,
                            'citem_cart_id' => $this->id
                        ]
                    )
                );
                $price = $row['citem_price'];

				$this->owner->db->sqlQuery(
					$this->owner->db->genSQLUpdate(
						'cart_items',
						[
							'citem_quantity' => $quantity,
							'citem_subtotal' => $price * $quantity,
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

	public function getStatus(){
		return $this->status;
	}

	public function getOrderType(){
		return $this->orderType;
	}

    public function isEmpty(){
        return Empty($this->items);
    }

    public function isPaid(){
        return $this->isPaid;
    }

    public function isBankCardPayment(){
        if($payMode = $this->getSelectedPaymentMode()){
            return ($payMode['type'] == PAYMENT_TYPE_CARD && $payMode['providerId']);
        }else{
            return false;
        }
    }

    public function isLocalConsumption(){
        return $this->localConsumption;
    }

    public function getShippingId(){
        return $this->shippingId;
    }

    public function getIntervalId(){
        return $this->intervalId;
    }

    public function getPaymentId(){
        return $this->paymentId;
    }

    public function getDiscount(){
        return $this->discount;
    }

	public function getNumberOfCartItems(){
		$count = 0;

		if($this->status == self::CART_STATUS_NEW) {
			if ($this->items) {
                $count = count($this->items);
			}
		}

		return $count;
	}

    public function getNumberOfNewOrders(){
        $out = 0;
        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'cart',
                [
                    'COUNT(cart_id) AS num',
                ],
                [
                    'cart_status' => self::CART_STATUS_ORDERED,
                    'cart_order_status' => self::ORDER_STATUS_NEW,
                    'cart_shop_id' => $this->owner->shopId
                ]
            )
        );

        if($row){
            $out = (int) $row['num'];
        }

        return $out;
    }

	public function makeOrder($userId, $invoiceType = 0, $remarks = ''){
        $orderStatus = ($this->orderType == ORDER_TYPE_STORE ? self::ORDER_STATUS_FINISHED : self::ORDER_STATUS_NEW);
        $isPaid = ($this->orderType == ORDER_TYPE_STORE ? 1 : 0);

		$this->owner->db->sqlQuery(
			$this->owner->db->genSQLUpdate(
				'cart',
				[
					'cart_us_id' => (int) $userId,
					'cart_order_number' => $this->generateOrderNumber(),
					'cart_status' => self::CART_STATUS_ORDERED,
					'cart_order_status' => $orderStatus,
					'cart_ordered' => 'NOW()',
					'cart_invoice_type' => (int) $invoiceType,
					'cart_paid' => $isPaid,
					'cart_remarks' => $remarks,
				],
				[
					'cart_id' => $this->id,
					'cart_shop_id' => $this->owner->shopId,
					'cart_key' => $this->key
				]
			)
		);

        if($this->items){
            foreach($this->items AS $item){
                $this->product->init($item['productId'])->updateOrderCounter();
            }
        }

        if($this->orderType != ORDER_TYPE_STORE) {
            $this->sendConfirmationEmail();
        }

        $this->destroyKey();
        $this->initPayment();
	}

    public function initPayment(){
        if($this->isBankCardPayment()){
            $payMode = $this->getSelectedPaymentMode();
            if($payMode['providerId']) {
                /**
                 * @var $payment Payments
                 */
                $payment = $this->owner->addByClassName('Payments');

                try {
                    $payment->init($payMode['providerId'])->createTransaction($this->id, $this->total, $this->currency);
                } catch (Exception $e) {
                    die($e);
                }
            }
        }
    }

    public function getPaymentStatus(){
        /**
         * @var $payment Payments
         */
        $payment = $this->owner->addByClassName('Payments');
        if(!$transactionId = $payment->hasPendingTransaction($this->id)){
            return $payment->getTransaction($transactionId);
        }

        return false;
    }

    public function getTransactionHistory(){
        /**
         * @var $payment Payments
         */
        $payment = $this->owner->addByClassName('Payments');

        return $payment->getTransactionHistory($this->id);
    }

    public function getTemplateData(){
        $this->loadCart();

        return [
            'key' => $this->key,
            'id' => $this->id,
            'items' => $this->items,
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'packagingFee' => $this->packagingFee,
            'shippingFee' => $this->shippingFee,
            'paymentFee' => $this->paymentFee,
            'total' => $this->total,
            'isPaid' => $this->isPaid(),
            'shippingMode' => $this->getSelectedShippingMode(),
            'shippingInterval' => $this->getSelectedShippingInterval(),
            'shippingDate' => $this->shippingDate,
            'customInterval' => $this->customInterval,
            'paymentMode' => $this->getSelectedPaymentMode(),
            'orderNumber' => $this->orderNumber,
            'orderStatus' => $this->orderStatus,
            'contactData' => $this->userData['contactData'],
            'shippingAddress' => $this->userData['shippingAddress'],
            'invoiceAddress' => $this->userData['invoiceAddress'],
            'remarks' => $this->remarks,
            'domain' => rtrim($this->owner->domain, '/'),
        ];
    }

    public function sendConfirmationEmail($resend = false){
        $cartMailBody = $this->owner->view->renderContent(
            'mail-order',
            $this->getTemplateData(),
            false
        );

        $data = [
            'id' => $this->userId,
            'link' => rtrim($this->owner->domain, '/') .  $this->owner->getPageName('finish') . $this->key . '/',
            'order' => $cartMailBody,
            'orderNumber' => $this->orderNumber,
            'status' => $this->status,
            'key' => $this->key,
            'total' => $this->total,
            'currency' => $this->currency,
        ];

        return $this->owner->email->prepareEmail(
            'order-new',
            $this->userId,
            $data,
            false,  // from
            false,  // cc
            ($this->owner->settings['incomingEmail'] ?: false), // bcc
            $this->getMailAttachments()
        );
    }

    public function sendPaymentConfirmationEmail(Transaction $transaction){
        $mailData = $this->getTemplateData();

        $mailData['showPaymentInfo'] = true;
        $mailData['transaction'] = $transaction;

        /*
        $mailData['paymentStatus'] = $transaction->getStatus();
        $mailData['transactionId'] = $transaction->transactionId;
        $mailData['authCode'] = $transaction->authCode;
        $mailData['resultMessage'] = $transaction->message;
        */
        $cartMailBody = $this->owner->view->renderContent(
            'mail-order',
            $mailData,
            false
        );

        $data = [
            'id' => $this->userId,
            'link' => rtrim($this->owner->domain, '/') .  $this->owner->getPageName('finish') . $this->key . '/',
            'order' => $cartMailBody,
            'orderNumber' => $this->orderNumber,
            'status' => $this->status,
            'key' => $this->key,
            'total' => $this->total,
            'currency' => $this->currency,
            'paymentStatus' => $transaction->getStatus(),
            'transactionId' => $transaction->transactionId,
            'authCode' => $transaction->authCode,
        ];

        return $this->owner->email->prepareEmail(
            'payment',
            $this->userId,
            $data,
            false,  // from
            false,  // cc
            ($this->owner->settings['incomingEmail'] ?: false), // bcc
            $this->getMailAttachments()
        );
    }

    private function getMailAttachments(){
        $files = [];
        $savePath = DIR_UPLOAD . $this->owner->shopId . '/documents/';

        $where = '(doc_optional = 0' . ($this->options['documents'] ? ' OR doc_id IN (' . implode(',', $this->options['documents']) . ')' : '') . ') AND doc_mail_types LIKE "%|new-order|%" AND doc_shop_id = ' . $this->owner->shopId;

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'documents',
                [
                    'doc_filename AS filename',
                    'doc_hash AS hash'
                ],
                $where
            )
        );
        if($result){
            foreach($result AS $row){
                $files[$row['filename']] = $savePath . $row['hash'];
            }
        }

        return $files;
    }

	private function loadCart(){
		$this->items = [];
		$this->id = 0;

        if($this->key) {
            $where = [
                'cart_key' => $this->key,
                'cart_shop_id' => $this->owner->shopId,
            ];

            if($this->owner->hostConfig['isVirtual']){
                $where['cart_store_id'] = $this->owner->storeId;
            }

            $cart = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'cart',
                    [],
                    $where,
                    [
                        'stores' => [
                            'on' => [
                                'st_code' => 'cart_store_id'
                            ]
                        ]
                    ]
                )
            );
            if ($cart) {
                $this->id = $cart['cart_id'];
                $this->userId = $cart['cart_us_id'];
                $this->status = $cart['cart_status'];
                $this->orderStatus = $cart['cart_order_status'];
                $this->orderNumber = $cart['cart_order_number'];
                $this->orderDate = $cart['cart_ordered'];
                $this->total = $cart['cart_total'];
                $this->packagingFee = $cart['cart_packaging_fee'];
                $this->subtotal = $cart['cart_subtotal'];
                $this->currency = $cart['cart_currency'];
                $this->shippingDate = $cart['cart_shipping_date'];
                $this->shippingFee = $cart['cart_shipping_fee'];
                $this->shippingId = $cart['cart_sm_id'];
                $this->intervalId = $cart['cart_si_id'];
                $this->paymentFee = $cart['cart_payment_fee'];
                $this->paymentId = $cart['cart_pm_id'];
                $this->isPaid = $cart['cart_paid'];
                $this->remarks = $cart['cart_remarks'];
                $this->customInterval = $cart['cart_custom_interval'];
                $this->orderType = $cart['cart_order_type'];
                $this->invoiceType = $cart['cart_invoice_type'];
                $this->localConsumption = (bool) $cart['cart_local_consumption'];
                $this->storeId = $cart['st_code'];
                $this->storeName = $cart['st_name'];

                if($this->userId){
                    $user = $this->owner->user->getUserProfile($this->userId);
                    $this->userData['contactData'] = [
                        'id' => $user['id'],
                        'firstName' => $user['firstname'],
                        'lastName' => $user['lastname'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'phone' => $user['phone'],
                    ];

                    $this->userData['shippingAddress'] = [
                        'name' => $user['name'],
                        'country' => $user['country'],
                        'zip' => $user['zip'],
                        'city' => $user['city'],
                        'address' => $user['address'],
                    ];

                    if($cart['cart_invoice_type']){
                        $this->userData['invoiceAddress'] = [
                            'name' => $user['invoice_name'],
                            'country' => $user['invoice_country'],
                            'zip' => $user['invoice_zip'],
                            'city' => $user['invoice_city'],
                            'address' => $user['invoice_address'],
                            'vatNumber' => ($cart['cart_invoice_type'] == 2 ? $user['vat'] : false),
                        ];
                    }else {
                        $this->userData['invoiceAddress'] = $this->userData['shippingAddress'];
                    }
                }

                $result = $this->owner->db->getRows(
                    $this->owner->db->genSQLSelect(
                        'cart_items',
                        [],
                        [
                            'citem_cart_id' => $this->id
                        ],
                        [],
                        false,
                        'citem_prod_id, citem_pv_id'
                    )
                );
                if ($result) {
                    $this->product = $this->owner->addByClassName('product');

                    foreach ($result as $row) {
                        $this->product->init($row['citem_prod_id'])->getProduct();

                        $price = (!empty($row['citem_discount']) && $row['citem_discount'] < $row['citem_price'] ? $row['citem_discount'] : $row['citem_price']);

                        $packaging = $this->product->getPackaging($row['citem_pv_id']);

                        if($takeover = $this->product->getEarliestTakeover()){
                            if(!$this->earliestTakeover || $this->earliestTakeover > $takeover){
                                $this->earliestTakeover = $takeover;
                            }
                        }

                        if($dateLimits = $this->product->getOrderDateLimitations()){
                            if($this->orderDateStart === false || $this->orderDateStart < $dateLimits['start'] ){
                                $this->orderDateStart = $dateLimits['start'];
                            }
                            if($this->orderDateEnd === false || $this->orderDateEnd > $dateLimits['end'] ){
                                $this->orderDateEnd = $dateLimits['end'];
                            }

                            if($dateLimits['days']){
                                if(Empty($this->orderDayLimits)){
                                    $this->orderDayLimits = $dateLimits['days'];
                                }else {
                                    $this->orderDayLimits = array_intersect($this->orderDayLimits, $dateLimits['days']);
                                }
                            }

                            $this->saleLimitText = $this->product->getSaleLimitText();
                        }

                        $variant = $this->product->getVariant($row['citem_pv_id']);

                        $this->items[$row['citem_id']] = [
                            'id' => $row['citem_id'],
                            'cartId' => $row['citem_cart_id'],
                            'productId' => $row['citem_prod_id'],
                            'variantId' => $row['citem_pv_id'],
                            'name' => $row['citem_prod_name'],
                            'type' => $row['citem_prod_type'],
                            'variant' => $row['citem_prod_variant'],
                            'image' => $row['citem_prod_img'],
                            'url' => $row['citem_url'],
                            'minSale' => $this->product->getMinSale($row['citem_pv_id']),
                            'maxSale' => $this->product->getMaxSale($row['citem_pv_id']),
                            'price' => [
                                'value' => $row['citem_price'],
                                'currency' => $row['citem_currency'],
                                'discount' => $row['citem_discount'],
                                'displayPrice' => $variant['price']['displayPrice'],
                                'unit' => $variant['price']['unit'],
                                'isWeightUnit' => $variant['price']['isWeightUnit'],
                                'total' => ($price * $row['citem_quantity']),
                            ],
                            'packaging' => $packaging,
                            'quantity' => [
                                'baseAmount' => $variant['packaging']['quantity'],
                                'amount' => $row['citem_quantity'],
                                'unit' => $row['citem_pack_unit'],
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
        }

		return $this;
	}

	private function isProductInCart($productId, $variantId = 0){
		$out = false;
		if($this->items){
			foreach($this->items AS $key => $item){
				if($item['productId'] == $productId && $item['variantId'] == $variantId){
					$out = $key;
					break;
				}
			}
		}
		return $out;
	}

	private function addItemToCart($item, $variantId = 0, $quantity = 1){
        if(!$this->id) return false;

        $variant = [];

        foreach($item['variants'] AS $var){
            if($var['id'] == $variantId){
                $variant = $var;
                break;
            }
        }

        $image = $item['thumbnail'];
        if($variant['imgId'] && $item['images']){
            foreach($item['images'] AS $img){
                if($img['data']['id'] == $variant['imgId']){
                    $image = $img['data']['thumbnail'];
                    break;
                }
            }
        }

        //$subtotal = (!Empty($variant['price']['discount']) && $variant['price']['discount'] < $variant['price']['value'] ? $variant['price']['discount'] : $variant['price']['value']);

        $this->owner->db->sqlQuery(
			$this->owner->db->genSQLInsert(
				'cart_items',
				[
					'citem_cart_id' => $this->id,
					'citem_prod_id' => $item['id'],
					'citem_pv_id' => $variantId,
					'citem_prod_name' => $item['name'],
					'citem_prod_img' => $image,
					'citem_prod_variant' => $variant['name'],
					'citem_price' => $variant['price']['unitPrice'],
					'citem_discount' => $variant['price']['discount'],
					'citem_subtotal' => $variant['price']['unitPrice'] * $quantity,
					'citem_currency' => $variant['price']['currency'],
					'citem_quantity' => $quantity,
					'citem_pack_unit' => $variant['packaging']['packageUnitName'],
					'citem_weight' => $variant['packaging']['weight'],
					'citem_weight_unit' => $variant['packaging']['weightUnitName'],
					'citem_url' => $item['url'],
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
					'cart_items',
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
		$this->packagingFee = 0;

		if($this->items){
			foreach($this->items AS $item){
                $fee = 0;

                if($item['packaging']['fee'] > 0 && !$this->localConsumption){
                    $fee = ($item['packaging']['fee'] * $item['quantity']['amount']);
                }

				$this->subtotal += $item['price']['total'];
                $this->packagingFee += $fee;
			}
		}

        $this->checkShippingFee();

        $this->total = $this->subtotal + $this->packagingFee + $this->shippingFee + $this->paymentFee - $this->discount;

		$this->owner->db->sqlQuery(
			$this->owner->db->genSQLUpdate(
				'cart',
				[
					'cart_subtotal' => $this->subtotal,
                    'cart_packaging_fee' => $this->packagingFee,
					'cart_shipping_fee' => $this->shippingFee,
                    //'cart_payment_fee' => $this->paymentFee,
                    'cart_discount' => $this->discount,
                    'cart_total' => $this->total,
                ],
				[
					'cart_id' => $this->id,
                    'cart_shop_id' => $this->owner->shopId,
					'cart_key' => $this->key
				]
			)
		);

        //$this->subtotal += $this->packagingFee;

        return $this;
	}

	private function getKey($key = false, $create = true){
        if(!Empty($key)) {
            $cart = $this->checkCartKey($key);

            if($cart['cartKey']){
                $this->setKey($cart['cartKey'], ($cart['cartStatus'] != self::CART_STATUS_ORDERED));
            }
        }else{
            if (!$this->key = $this->owner->getSession(self::CART_SESSION_KEY)) {
                $this->key = $_COOKIE[self::CART_SESSION_KEY];
            }
        }

        if($create){
            if(!$this->key) {
                $this->setKey();
            }

            $this->initCart();
        }

		return $this;
	}

    public function setOption($key, $value){
        if(!isset($this->options[$key])){
            $this->options[$key] = [];
        }

        $this->options[$key] = $value;

        return $this;
    }

	private function setKey($key = false, $storeKey = true){
        if($key){
            $this->key = $key;
        }else {
            if (!$this->key) {
                $this->key = uuid::v4();
            }
        }

        if($storeKey) {
            $this->owner->setSession(self::CART_SESSION_KEY, $this->key);
            setcookie(self::CART_SESSION_KEY, $this->key, 0, '/');
        }

        return $this;
	}

    private function generateOrderNumber(){
        $out  = ($this->owner->storeId ?: 'X') . '-';
        $out .= date('Ymd') . '-';
        $out .= $this->getSelectedShippingMode()['code'] . '-';
        $out .= str_pad($this->getTodaySumOrders() + 1, 4, '0', STR_PAD_LEFT);

        return $out;
    }

    private function getTodaySumOrders(){
        $row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'cart',
                [
                    'COUNT(cart_id) AS num'
                ],
                [
                    'cart_status' => self::CART_STATUS_ORDERED,
                    'cart_ordered>' => date('Y-m-d 00:00:00'),
                    'cart_ordered<' => date('Y-m-d 23:59:59'),
                ]
            )
        );
        return (int) $row['num'];
    }

	public function destroyKey(){
		$this->owner->delSession(self::CART_SESSION_KEY);
		setcookie(self::CART_SESSION_KEY, '', time() - 3600, '/');
	}

	private function checkCartKey($key){
        $out = [
            'key' => false,
            'status' => false,
        ];

        $where = [
            'cart_shop_id' => $this->owner->shopId
        ];

        if(is_numeric($key) && $key && $this->isAdmin){
                $where['cart_id'] = (int) $key;
        }else{
            $where['cart_key'] = $key;
        }

		$row = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'cart',
                [
                    'cart_key AS cartKey',
                    'cart_status AS cartStatus'
                ],
                $where
            )
        );

        if($row){
			$out = $row;
		}

        return $out;
	}

	private function initCart(){
		if($this->owner->user->isLoggedIn()) {
			$userId = $this->owner->user->getUser()['id'];
		}else{
			$userId = 0;
		}

		$this->owner->db->sqlQuery(
			$this->owner->db->genSQLInsert(
				'cart',
				[
					'cart_key' => $this->key,
					'cart_shop_id' => $this->owner->shopId,
					'cart_store_id' => $this->owner->storeId,
					'cart_us_id' => $userId,
					'cart_created' => 'NOW()',
					'cart_status' => self::CART_STATUS_NEW,
					'cart_currency' => $this->currency,
				],
				[
					'cart_key',
					'cart_shop_id'
				]
			)
		);
	}

    private function checkQuantity($quantity, $variantId = 0){
        if($this->product->isProductLoaded()) {
            if ($quantity < $this->product->getMinSale($variantId)) {
                $quantity = $this->product->getMinSale($variantId);
            }
            $maxSale = $this->product->getMaxSale($variantId);
            if ($quantity > $maxSale && $maxSale > 0) {
                $quantity = $this->product->getMaxSale($variantId);
            }
        }

        if ($quantity <= 0) $quantity = 1;

        return $quantity;
    }

    private function checkShippingFee(){
        if($this->shippingId){
            $row = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'shipping_modes',
                    [
                        'sm_id AS id',
                        'sm_name AS name',
                        'sm_free_limit AS freeLimit',
                        'sm_price AS price',
                        'sm_default AS def',
                        'sm_text AS text',
                    ],
                    [
                        'sm_shop_id' => $this->owner->shopId,
                        'sm_enabled' => 1,
                        'sm_id' => $this->shippingId,
                    ]
                )
            );
            if($row){
                if($row['price'] > 0 ){
                    if($this->subtotal >= $row['freeLimit'] && !Empty($row['freeLimit'])) {
                        $this->shippingFee = 0;
                    }else {
                        $this->shippingFee = $row['price'];
                    }
                }else{
                    $this->shippingFee = 0;
                }
            }
        }
    }

    public function getItem($id){
        return $this->items[$id];
    }

    public function getPaymentModes(array $types = []){
        $out = [];

        if(!Empty($types)){
            $pmTypes = $types;
        }else{
            $pmTypes = [PAYMENT_TYPE_CASH, PAYMENT_TYPE_MONEY_TRANSFER, PAYMENT_TYPE_CARD];
        }

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'payment_modes',
                [
                    'pm_id AS id',
                    'pm_name AS name',
                    'pm_type AS type',
                    'pm_price AS price',
                    'pm_text AS text',
                    'pm_default AS def',
                    'pm_logo AS logo',
                ],
                [
                    'pm_shop_id' => $this->owner->shopId,
                    'pm_enabled' => 1,
                    'pm_type' => [
                        'in' => $pmTypes
                    ],
                ],
                [],
                false,
                'pm_order'
            )
        );
        if ($result) {
            foreach($result AS $row){
                $out[$row['id']] = $row;

                if($row['logo']){
                    $out[$row['id']]['logo'] = FOLDER_UPLOAD . $this->owner->shopId . '/' . $row['logo'];
                }
            }
        }

        return $out;
    }

    public function getSelectedPaymentMode(){
        static $out = [];

        if($this->paymentId && Empty($out)) {
            $out = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'payment_modes',
                    [
                        'pm_id AS id',
                        'pm_pp_id AS providerId',
                        'pm_name AS name',
                        'pm_type AS type',
                        'pm_text AS text',
                        'pm_email_text AS emailText',
                        'pm_logo AS logo',
                    ],
                    [
                        'pm_shop_id' => $this->owner->shopId,
                        'pm_id' => $this->paymentId,
                    ]
                )
            );

            if($out['logo']){
                $out['logo'] = FOLDER_UPLOAD . $this->owner->shopId . '/' . $out['logo'];
            }

        }

        return $out;
    }

    public function getShippingModes(){
        $out = [];
        $holidays = $this->getHolidays();

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'shipping_modes',
                [
                    'sm_id AS id',
                    'sm_name AS name',
                    'sm_free_limit AS freeLimit',
                    'sm_price AS price',
                    'sm_default AS def',
                    'sm_text AS text',
                    'sm_type AS type',
                    'sm_day_diff AS dayDiff',
                    'sm_intervals AS hasIntervals',
                    'sm_select_date AS hasCustomDate',
                    'sm_custom_interval AS hasCustomInterval',
                    'sm_custom_text AS customIntervalText',
                ],
                [
                    'sm_shop_id' => $this->owner->shopId,
                    'sm_enabled' => 1,
                ],
                [],
                false,
                'sm_default DESC, sm_order'
            )
        );
        if ($result) {
            foreach($result AS $row){
                $out[$row['id']] = $row;
                $out[$row['id']]['shippingDate'] = $this->getNextShippingDate(($row['dayDiff'] ? dateAddDays('now', $row['dayDiff']) : date('Y-m-d')));
                $out[$row['id']]['shippingLastDate'] = false;

                if($out[$row['id']]['hasCustomDate']){
                    $out[$row['id']]['offDates'] = json_encode($holidays);
                }

                $out[$row['id']]['intervals'] = [];

                if($this->subtotal >= $row['freeLimit'] && !Empty($row['freeLimit'])){
                    $out[$row['id']]['price'] = 0;
                }

                if($this->orderDateStart && $this->orderDateEnd){
                    $out[$row['id']]['hasCustomInterval'] = false;
                    $out[$row['id']]['offDates'] = false;
                    $out[$row['id']]['shippingDate'] = $this->orderDateStart;
                    $out[$row['id']]['shippingLastDate'] = $this->orderDateEnd;
                    $out[$row['id']]['saleLimitText'] = $this->saleLimitText;
                }

                if($this->orderDayLimits){
                    $out[$row['id']]['dayLimits'] = $this->orderDayLimits;
                    $out[$row['id']]['saleLimitText'] = $this->saleLimitText;
                }

                if($row['hasIntervals']){
                    $where = [
                        'si_shop_id' => $this->owner->shopId,
                        'si_sm_id' => $row['id'],
                    ];

                    if(!Empty($this->earliestTakeover)){
                        $where['si_time_start'] = [
                            'greater=' => $this->earliestTakeover
                        ];
                    }

                    $res = $this->owner->db->getRows(
                        $this->owner->db->genSQLSelect(
                            'shipping_intervals',
                            [
                                'si_id AS id',
                                'si_time_start AS timeStart',
                                'si_time_end AS timeEnd',
                            ],
                            $where,
                            [],
                            false,
                            'si_time_start'
                        )
                    );
                    if($res){
                        foreach($res AS $r){
                            $out[$row['id']]['intervals'][$r['id']] = $r;
                        }
                    }
                }
            }
        }

        return $out;
    }

    public function getSelectedShippingMode(){
        static $out = [];

        if($this->shippingId && Empty($out)) {
            $out = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'shipping_modes',
                    [
                        'sm_id AS id',
                        'sm_code AS code',
                        'sm_name AS name',
                        'sm_text AS text',
                        'sm_type AS type',
                        'sm_email_text AS emailText',
                        'sm_day_diff AS dayDiff',
                        'sm_select_date AS hasCustomDate',
                        'sm_custom_text AS customIntervalText',
                    ],
                    [
                        'sm_shop_id' => $this->owner->shopId,
                        'sm_id' => $this->shippingId,
                    ]
                )
            );
            if($out){
                $out['shippingDate'] = $this->getNextShippingDate(($out['dayDiff'] ? dateAddDays($this->orderDate, $out['dayDiff']) : date('Y-m-d')));
            }
        }

        if(Empty($out)){
            $out = [
                'id' => 0,
                'code' => $this->owner->storeId,
                'shippingDate' => date('Y-m-d')
            ];
        }

        return $out;
    }

    public function getSelectedShippingInterval(){
        static $out = [];

        if(Empty($out)) {
            $out['id'] = $this->intervalId;

            if ($this->intervalId > 0) {
                $out = $this->owner->db->getFirstRow(
                    $this->owner->db->genSQLSelect(
                        'shipping_intervals',
                        [
                            'si_id AS id',
                            'si_time_start AS timeStart',
                            'si_time_end AS timeEnd',
                        ],
                        [
                            'si_shop_id' => $this->owner->shopId,
                            'si_sm_id' => $this->shippingId,
                            'si_id' => $this->intervalId,
                        ]
                    )
                );
            } elseif ($this->intervalId == -1) {
                $out['customText'] = $this->customInterval;
            }
        }

        return $out;
    }

    public function setLocalConsumption($isLocal){
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                'cart',
                [
                    'cart_local_consumption' => (int) $isLocal,
                ],
                [
                    'cart_id' => $this->id,
                    'cart_shop_id' => $this->owner->shopId,
                    'cart_key' => $this->key
                ]
            )
        );

        $this->loadCart()->summarize();
    }

    public function setCustomer($userId, $invoiceType = 0, $remarks = ''){
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                'cart',
                [
                    'cart_us_id' => (int) $userId,
                    'cart_invoice_type' => (int) $invoiceType,
                    'cart_remarks' => $remarks,
                ],
                [
                    'cart_id' => $this->id,
                    'cart_shop_id' => $this->owner->shopId,
                    'cart_key' => $this->key
                ]
            )
        );
    }

    public function setPaymentMode($id){
        $paymentModes = $this->getPaymentModes();
        if($paymentModes[$id]){
            $this->paymentId = $id;
            $this->paymentFee = $paymentModes[$id]['price'];

            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLUpdate(
                    'cart',
                    [
                        'cart_pm_id' => (int) $id,
                        'cart_payment_fee' => $this->paymentFee,
                    ],
                    [
                        'cart_id' => $this->id,
                        'cart_shop_id' => $this->owner->shopId,
                        'cart_key' => $this->key
                    ]
                )
            );

            $this->loadCart()->summarize();
        }
    }

    public function setShippingMode($id, $intervalId = 0, $customInterval = '', $shippingDate = null){
        $shippingModes = $this->getShippingModes();
        if($shippingModes[$id]){
            $this->shippingId = $id;
            $this->shippingFee = $shippingModes[$id]['price'];
            $this->shippingDate = $shippingModes[$id]['shippingDate'];

            if($shippingDate) {
                $this->shippingDate = standardDate($shippingDate);
            }

            if($intervalId > 0) {
                $customInterval = '';
            }

            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLUpdate(
                    'cart',
                    [
                        'cart_sm_id' => (int) $id,
                        'cart_si_id' => (int) $intervalId,
                        'cart_shipping_fee' => $this->shippingFee,
                        'cart_shipping_date' => $this->shippingDate,
                        'cart_custom_interval' => $customInterval,
                    ],
                    [
                        'cart_id' => $this->id,
                        'cart_shop_id' => $this->owner->shopId,
                        'cart_key' => $this->key
                    ]
                )
            );

            $this->loadCart()->summarize();
        }
    }

    public function claimCart(){
        if($this->owner->user->isLoggedIn()) {
            if ($key = $this->getUserSavedCart()) {
                if ($this->key && !$this->userId) {
                    $this->deleteCart();
                }

                $this->setKey($key);
            } else {
                if ($this->key && !$this->userId) {
                    $this->owner->db->sqlQuery(
                        $this->owner->db->genSQLUpdate(
                            'cart',
                            [
                                'cart_us_id' => $this->owner->user->id,
                            ],
                            [
                                'cart_id' => $this->id,
                                'cart_shop_id' => $this->owner->shopId,
                                'cart_key' => $this->key
                            ]
                        )
                    );
                }
            }
        }
    }

    private function getUserSavedCart(){
        $key = false;

        if($this->owner->user->isLoggedIn()) {
            $row = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'cart',
                    [
                        'cart_id AS id',
                        'cart_key AS cartKey'
                    ],
                    [
                        'cart_us_id' => $this->owner->user->id,
                        'cart_shop_id' => $this->owner->shopId,
                        'cart_status' => [
                            'in' => ["'" . self::CART_STATUS_NEW . "'", "'" . self::CART_STATUS_ABANDONED . "'"]
                        ]
                    ]
                )
            );
            if($row){
                $key = $row['cartKey'];
            }
        }

        return $key;
    }

    private function deleteCart(){
        if($this->key && $this->id) {
            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLDelete(
                    'cart',
                    [
                        'cart_id' => $this->id,
                        'cart_shop_id' => $this->owner->shopId,
                        'cart_key' => $this->key
                    ]
                )
            );

            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLDelete(
                    'cart_items',
                    [
                        'citem_cart_id' => $this->id,
                    ]
                )
            );

            $this->destroyKey();
        }
    }

    private function getNextShippingDate($date){
        $dayLimits = [1, 2, 3, 4, 5, 6, 7];
        $holidays = $this->getHolidays();

        if(!Empty($this->orderDayLimits)){
            $dayLimits = $this->orderDayLimits;
        }

        $dow = date('N', strtotime($date));

        if(in_array($date, $holidays) || !in_array($dow, $dayLimits)){
            do{
                $date = date('Y-m-d', strtotime($date . ' +1 days'));
                $dow = date('N', strtotime($date));

            }while(in_array($date, $holidays) || !in_array($dow, $dayLimits));
        }

        return $date;
    }

    public function getHolidays($json = false){
        static $holidays = [];

        if(!$holidays) {
            $res = $this->owner->db->getRows(
                $this->owner->db->genSQLSelect(
                    'holidays',
                    [
                        'h_date',
                    ],
                    [
                        'h_shop_id' => $this->owner->shopId,
                        'h_date' => [
                            'greater' => date('Y-m-d')
                        ]
                    ]
                )
            );
            if ($res) {
                foreach ($res as $r) {
                    $holidays[] = $r['h_date'];
                }
            }
        }

        return ($json ? json_encode($holidays) : $holidays);
    }

    public function setOrderStatus($status){
        if(isset($GLOBALS['ORDER_STATUSES'][$status])){
            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLUpdate(
                    'cart',
                    [
                        'cart_order_status' => $status,
                    ],
                    [
                        'cart_id' => $this->id,
                        'cart_shop_id' => $this->owner->shopId,
                        'cart_key' => $this->key
                    ]
                )
            );
        }
    }

    public function setPaid(){
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                'cart',
                [
                    'cart_paid' => 1,
                ],
                [
                    'cart_id' => $this->id,
                    'cart_shop_id' => $this->owner->shopId,
                ]
            )
        );
    }

}
