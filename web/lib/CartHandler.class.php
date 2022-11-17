<?php
class CartHandler extends ancestor {
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
	private $discount = 0;
	public $shippingFee = 0;
    private $shippingId = false;
    private $intervalId = false;
	public $paymentFee = 0;
    private $paymentId = false;
    private $paymentNoCash = false;
    private $isPaid = false;
    private $isRefunded = false;
    public $packagingFee = 0;
    private $packagingFeeVat = 0;

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
	public $invoiceType = -1;
	public $invoiceProviderId = 0;
	public $invoiceNumber = false;
	public $invoiceFileName = false;
	public $shippingDate;
	public $customInterval;
	public $storeId;
	public $storeName;

	private $options = [];

    /**
     * @var $coupon Coupon
     */
	public $coupon = null;

	public $promoCouponId = false;

    public function init($key = false, $create = true) {
        if($this->owner->user->getGroup() == USER_GROUP_ADMINISTRATORS){
            $this->isAdmin = true;
        }

		$this->currency = $this->owner->currency;

        $this->getKey($key, $create)->loadCart();
        $this->product = $this->owner->addByClassName('product');

		return $this;
	}

    public function createNewOrder(int $orderType = ORDER_TYPE_LOCAL){
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
                    'cart_order_type' => $orderType,
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

			$this->loadCart();
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

				$this->loadCart();
			}
		}

		return $this;
	}

    public function getCart():Cart{
        if(!$this->id){
            throw new Exception('Cart is not loaded!');
        }

        $cart = new Cart($this->id, Cart::PRICE_BASE_GROSS);

        $cart->setOrderStatus($this->orderStatus)
             ->setOrderDate($this->orderDate)
             ->setOrderType($this->orderType)
             ->setShippingDate($this->shippingDate)
             ->setInvoiceNumber($this->invoiceNumber)
             ->setInvoiceFileName($this->invoiceFileName)
             ->setOrderNumber($this->orderNumber)
             ->setTotal($this->total)
             ->setCurrency($this->currency)
             ->setInvoiceProvider($this->invoiceProviderId)
             ->setPaid($this->isPaid());

        $cart->setCustomer( $this->userData );

        $defaultVat = 18;

        if($this->items){
            foreach($this->items AS $item){
                $cartItem = new CartItem($item['id'], $item['productId'], $item['variantId']);
                $cartItem->setName($item['name'], $item['variant']);
                $cartItem->setVat($item['price']['vatKey'], $item['price']['vat']);
                $cartItem->setQuantity($item['quantity']['amount'], $item['quantity']['unit']);
                $cartItem->setUnitPrice($item['price']['unitPrice']);
                $cartItem->setDiscounted(($item['price']['discount'] > 0));

                $cart->addItem($cartItem);

                if($item['price']['vatKey']) {
                    $defaultVat = $item['price']['vatKey'];
                }
            }
        }

        if($this->discount){
            $cart->setDiscount($this->getDiscount(), $defaultVat);
        }

        if($this->packagingFee) {
            $cart->setPackagingFee($this->packagingFee, $this->packagingFeeVat);
        }

        $cart->setShipping($this->getSelectedShippingMode(), $this->shippingFee);

        $cart->setPayment($this->getSelectedPaymentMode(), $this->paymentFee);

        return $cart;
    }

    public function getAppliedCoupon():?Coupon
    {
        static $coupon;

        if(!$coupon) {
            /**
             * @var $discountHandler DiscountHandler
             */
            $discountHandler = $this->owner->addByClassName('DiscountHandler');

            $coupon = $discountHandler->getAppliedCoupon($this->id);
        }

        return $coupon;
    }

    public function getPromoCoupon():?Coupon
    {
        static $coupon = null;

        if(!$coupon) {

            /**
             * @var $discountHandler DiscountHandler
             */
            $discountHandler = $this->owner->addByClassName('DiscountHandler');

            $coupon = $discountHandler->getPromoCoupon($this->promoCouponId, $this->subtotal);
            if(!$this->promoCouponId && $coupon instanceof Coupon){
                $this->promoCouponId = $coupon->getId();

                $this->owner->db->sqlQuery(
                    $this->owner->db->genSQLUpdate(
                        'cart',
                        [
                            'cart_coupon_id' => $this->promoCouponId,
                        ],
                        [
                            'cart_id' => $this->id,
                            'cart_shop_id' => $this->owner->shopId,
                        ]
                    )
                );
            }
        }

        return $coupon;
    }

	public function getCartItems(){
		return $this->items;
	}

	public function getStatus(){
		return $this->status;
	}

    public function setOrderType(int $type){
        $this->orderType = $type;

        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                'cart',
                [
                    'cart_order_type' => $this->orderType,
                ],
                [
                    'cart_id' => $this->id,
                    'cart_shop_id' => $this->owner->shopId,
                ]
            )
        );
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

    public function isRefunded(){
        return $this->isRefunded;
    }

    public function isBankCardPayment(){
        if($payMode = $this->getSelectedPaymentMode()){
            return ($payMode['type'] == PAYMENT_TYPE_CARD && $payMode['providerId']);
        }else{
            return false;
        }
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
        return abs($this->discount) * -1;
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

	public function makeOrder($userId, $invoiceType = -1, $remarks = '', $sendConfirmationEmail = true){
        switch($this->orderType){
            case ORDER_TYPE_LOCAL:
            case ORDER_TYPE_TAKEAWAY:
                $isPaid = 1;
                $orderStatus = self::ORDER_STATUS_FINISHED;
                break;

            default:
                $orderStatus = self::ORDER_STATUS_NEW;
                $isPaid = 0;
                break;
        }

        $this->invoiceType = (int) $invoiceType;
        $this->orderNumber = $this->generateOrderNumber();

        $this->loadCustomerData($userId, $this->invoiceType);

        $data = [
            'cart_us_id' => (int) $userId,
            'cart_order_number' => $this->orderNumber,
            'cart_status' => self::CART_STATUS_ORDERED,
            'cart_order_status' => $orderStatus,
            'cart_ordered' => 'NOW()',
            'cart_invoice_type' => $this->invoiceType,
            'cart_paid' => $isPaid,
            'cart_remarks' => $remarks,
        ];

        if(in_array($this->orderType, [ORDER_TYPE_LOCAL, ORDER_TYPE_TAKEAWAY])){
            $data['cart_sm_id'] = 0;
            $data['cart_si_id'] = 0;
        }

		$this->owner->db->sqlQuery(
			$this->owner->db->genSQLUpdate(
				'cart',
				$data,
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

        if($this->orderType == ORDER_TYPE_ORDER && $sendConfirmationEmail) {
            $this->sendConfirmationEmail();
        }

        $this->destroyKey();
        $this->initPayment();
	}

    public function initPayment(){
        if($this->isBankCardPayment() && $this->orderType == ORDER_TYPE_ORDER){
            if($this->owner->getHostConfig()['isVirtual']){
                $payMode = $this->getSelectedPaymentMode();
                if($payMode['providerId']) {
                    /**
                     * @var $payment Payments
                     */
                    $payment = $this->owner->addByClassName('Payments');

                    try {
                        $payment->init($payMode['providerId'])->createTransaction($this->id, $this->total, $this->currency);
                    } catch (PaymentException $e) {
                        die($e->getMessage());
                    }
                }
            }
        }else{
            $this->issueInvoice();
        }
    }

    public function isRefundable(){
        $out = false;

        if($this->isPaid() && $this->isBankCardPayment() && $this->orderType == ORDER_TYPE_ORDER){
            /**
             * @var $payment Payments
             */
            $payment = $this->owner->addByClassName('Payments');
            if($transaction = $payment->getRefundableTransaction($this->id)){
                if($payment->init($transaction->providerId)->hasRefund()){
                    $out = true;
                }
            }
        }

        return $out;
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

    private function getDomain(){
        $domain = $this->owner->domain;

        if($this->owner->getHostConfig()['application'] == 'admin' && !Empty($this->owner->getHostConfig()['publicSite'])){
            $domain = $this->owner->getHostConfig()['publicSite'];
        }

        return rtrim($domain, '/');
    }

    public function getTemplateData($addPromoCode = false){
        $this->loadCart();

        return [
            'key' => $this->key,
            'id' => $this->id,
            'items' => $this->items,
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'discount' => $this->getDiscount(),
            'coupon' => $this->getAppliedCoupon(),
            'promoCoupon' => ($addPromoCode ? $this->getPromoCoupon() : false),
            'packagingFee' => $this->packagingFee,
            'shippingFee' => $this->shippingFee,
            'paymentFee' => $this->paymentFee,
            'total' => $this->total,
            'isPaid' => $this->isPaid(),
            'isRefunded' => $this->isRefunded(),
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
            'domain' => $this->getDomain(),
            'shopId' => $this->owner->shopId
        ];
    }

    public function sendConfirmationEmail($resend = false){
        $cartMailBody = $this->owner->view->renderContent(
            'mail-order',
            $this->getTemplateData(true),
            false
        );

        $data = [
            'id' => $this->userId,
            'link' => $this->getDomain() . $this->owner->getPageName('finish') . $this->key . '/',
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

        $cartMailBody = $this->owner->view->renderContent(
            'mail-order',
            $mailData,
            false
        );

        $data = [
            'id' => $this->userId,
            'link' => $this->getDomain() . $this->owner->getPageName('finish') . $this->key . '/',
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

        $where = '(doc_optional = 0' . ($this->options['documents'] ? ' OR doc_id IN (' . implode(',', $this->options['documents']) . ')' : '') . ') AND doc_mail_types LIKE "%|order-new|%" AND doc_shop_id = ' . $this->owner->shopId;

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

        $savePath = DIR_UPLOAD . $this->owner->shopId . '/invoices/';

        if($this->invoiceFileName && file_exists($savePath . $this->invoiceFileName)){
            $files[$this->invoiceNumber . '.pdf'] = $savePath . $this->invoiceFileName;
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
                $this->orderType = (int) $cart['cart_order_type'];
                $this->orderStatus = $cart['cart_order_status'];
                $this->orderNumber = $cart['cart_order_number'];
                $this->orderDate = $cart['cart_ordered'];
                $this->total = $cart['cart_total'];
                $this->discount = $cart['cart_discount'];
                $this->packagingFee = $cart['cart_packaging_fee'];
                $this->subtotal = $cart['cart_subtotal'];
                $this->currency = $cart['cart_currency'];
                $this->shippingDate = $cart['cart_shipping_date'];
                $this->shippingFee = $cart['cart_shipping_fee'];
                $this->shippingId = $cart['cart_sm_id'];
                $this->intervalId = $cart['cart_si_id'];
                $this->customInterval = $cart['cart_custom_interval'];
                $this->paymentFee = $cart['cart_payment_fee'];
                $this->paymentId = $cart['cart_pm_id'];
                $this->isPaid = ($cart['cart_paid'] == 1);
                $this->isRefunded = ($cart['cart_paid'] == -1);
                $this->remarks = $cart['cart_remarks'];
                $this->invoiceType = (int) $cart['cart_invoice_type'];
                $this->invoiceProviderId = $cart['cart_invoice_provider'];
                $this->invoiceNumber = $cart['cart_invoice_number'];
                $this->invoiceFileName = $cart['cart_invoice_filename'];
                $this->storeId = $cart['st_code'];
                $this->storeName = $cart['st_name'];
                $this->promoCouponId = (int) $cart['cart_coupon_id'];

                if($this->userId){
                    $this->loadCustomerData($this->userId, $this->invoiceType);
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
                        $itemLocalConsumption = (bool) $row['citem_local_consumption'];

                        if($variant['price']['noCash'] && $this->orderType == ORDER_TYPE_ORDER){
                            $this->paymentNoCash = true;
                        }

                        preg_match_all('!\d+!', $variant['price']['unit'], $matches);
                        $unit = ($matches[0][0] ?? 1);
                        $displayPrice = $row['citem_price'] * $unit;

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
                            'localConsumption' => $itemLocalConsumption,
                            'price' => [
                                'value' => $row['citem_price'],
                                'currency' => $row['citem_currency'],
                                'discount' => $row['citem_discount'],
                                //'displayPrice' => $variant['price']['displayPrice'],
                                //'unitPrice' => $variant['price']['unitPrice'],
                                'displayPrice' => $displayPrice,
                                'unitPrice' => $row['citem_price'],
                                'unit' => $variant['price']['unit'],
                                'isWeightUnit' => $variant['price']['isWeightUnit'],
                                'total' => ($price * $row['citem_quantity']),
                                'vatKey' => ($itemLocalConsumption ? $variant['price']['vatLocal'] : $variant['price']['vatDeliver']),
                                'vat' => ($itemLocalConsumption ? $variant['price']['vatLocal'] : $variant['price']['vatDeliver']) / 100,
                            ],
                            'packaging' => ($itemLocalConsumption ? [] : $packaging),
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

            $this->summarize();
        }

		return $this;
	}

    private function loadCustomerData($userId, $invoiceType){
        if($userId){
            $this->userId = $userId;

            $user = $this->owner->user->getUserProfile($userId);
            $this->userData['contactData'] = [
                'id' => $user['id'],
                'firstName' => $user['firstname'],
                'lastName' => $user['lastname'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
            ];

            $this->userData['type'] = [
                'group' => $user['group'],
                'role' => $user['role'],
            ];

            $this->userData['shippingAddress'] = [
                'name' => $user['name'],
                'country' => $user['country'],
                'zip' => $user['zip'],
                'city' => $user['city'],
                'address' => $user['address'],
            ];

            if($invoiceType){
                $this->userData['invoiceAddress'] = [
                    'name' => $user['invoice_name'],
                    'country' => $user['invoice_country'],
                    'zip' => $user['invoice_zip'],
                    'city' => $user['invoice_city'],
                    'address' => $user['invoice_address'],
                    'vatNumber' => ($invoiceType == 2 ? $user['vat'] : false),
                ];
            }else {
                $this->userData['invoiceAddress'] = $this->userData['shippingAddress'];
            }
        }
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

		$this->loadCart();

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

			$this->loadCart();
		}

		return $key;
	}

    private function applyDiscounts(){
        $this->discount = 0;

        /**
         * @var $discountHandler DiscountHandler
         */
        $discountHandler = $this->owner->addByClassName('DiscountHandler');

        if($this->userId && $this->userData['type']['group'] == USER_GROUP_CUSTOMERS && $this->userData['type']['role'] == USER_ROLE_USER && $this->owner->user->isLoggedIn()){
            $this->discount = $discountHandler->getLoyaltyDiscount($this->userId, $this->subtotal);
        }else{
            $this->discount = 0;
        }

        if($this->coupon = $discountHandler->getAppliedCoupon($this->id)){
            $discount = $this->coupon->getDiscount();
            if($discount > $this->discount){
                $this->discount = $discount;
            }else{
                $discountHandler->clearCoupon($this->id);
                $this->coupon = null;
            }
        }
    }

	public function summarize(){
		$this->total = 0;
		$this->subtotal = 0;
		$this->packagingFee = 0;
		$this->packagingFeeVat = 0;

		if($this->items){
			foreach($this->items AS $item){
                $fee = 0;

                if($item['packaging']['fee']){
                    $fee = ($item['packaging']['fee'] * $item['quantity']['amount']);
                    $this->packagingFeeVat = $item['packaging']['vat'];
                }

				$this->subtotal += $item['price']['total'];
                $this->packagingFee += $fee;
			}
		}

        $this->checkShippingFee();

        if($this->status == ORDER_STATUS_NEW) {
            $this->applyDiscounts();
        }

        $this->total = $this->subtotal + $this->packagingFee + $this->shippingFee + $this->paymentFee - $this->discount;

		$this->owner->db->sqlQuery(
			$this->owner->db->genSQLUpdate(
				'cart',
				[
					'cart_subtotal' => $this->subtotal,
                    'cart_packaging_fee' => $this->packagingFee,
					'cart_shipping_fee' => $this->shippingFee,
                    'cart_payment_fee' => $this->paymentFee,
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
        $date = strtotime($this->shippingDate ?? date('Y-m-d'));

        $out  = ($this->owner->storeId ?: 'X') . '-';
        $out .= date('Ymd', $date) . '-';
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
            if($this->paymentNoCash){
                $pmTypes = [PAYMENT_TYPE_MONEY_TRANSFER, PAYMENT_TYPE_CARD];
            }else {
                $pmTypes = [PAYMENT_TYPE_CASH, PAYMENT_TYPE_MONEY_TRANSFER, PAYMENT_TYPE_CARD];
            }
        }

        $result = $this->owner->db->getRows(
            $this->owner->db->genSQLSelect(
                'payment_modes',
                [
                    'pm_id AS id',
                    'pm_name AS name',
                    'pm_type AS type',
                    'pm_price AS price',
                    'pm_vat AS vat',
                    'pm_text AS text',
                    'pm_default AS def',
                    'pm_limit_max AS limitMax',
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
                if($row['limitMax'] == 0 || $row['limitMax'] >= $this->subtotal || $this->storeId != 'W') {
                    $out[$row['id']] = $row;

                    if ($row['logo']) {
                        $out[$row['id']]['logo'] = FOLDER_UPLOAD . $this->owner->shopId . '/' . $row['logo'];
                    }
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
                        'pm_vat AS vat',
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
                    'sm_vat AS vat',
                    'sm_default AS def',
                    'sm_text AS text',
                    'sm_type AS type',
                    'sm_day_diff AS dayDiff',
                    'sm_intervals AS hasIntervals',
                    'sm_select_date AS hasCustomDate',
                    'sm_custom_interval AS hasCustomInterval',
                    'sm_custom_text AS customIntervalText',
                    'sm_excluded_dates AS excludedDates',
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
                $out[$row['id']]['offDates'] = [];

                if($out[$row['id']]['hasCustomDate']){
                    $out[$row['id']]['offDates'] = $holidays;
                }

                $out[$row['id']]['intervals'] = [];

                if($this->subtotal >= $row['freeLimit'] && !Empty($row['freeLimit'])){
                    $out[$row['id']]['price'] = 0;
                }

                if($this->orderDateStart && $this->orderDateEnd){
                    $out[$row['id']]['hasCustomInterval'] = false;
                    $out[$row['id']]['offDates'] = [];
                    $out[$row['id']]['shippingDate'] = $this->orderDateStart;
                    $out[$row['id']]['shippingLastDate'] = $this->orderDateEnd;
                    $out[$row['id']]['saleLimitText'] = $this->saleLimitText;
                }

                if($row['excludedDates']){
                    $excludedDates = json_decode($row['excludedDates'], true);
                    if($excludedDates){
                        foreach($excludedDates AS $date){
                            if(!in_array($date, $out[$row['id']]['offDates'])){
                                $out[$row['id']]['offDates'][] = $date;
                            }
                        }
                    }
                }

                if($this->orderDayLimits){
                    $out[$row['id']]['dayLimits'] = $this->orderDayLimits;
                    $out[$row['id']]['saleLimitText'] = $this->saleLimitText;
                }

                $out[$row['id']]['offDates'] = (!Empty($out[$row['id']]['offDates']) ? json_encode($out[$row['id']]['offDates']) : false);

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
                        'sm_vat AS vat',
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

    public function setItemLocalConsumption($itemIds, int $isLocal){
        if(!Empty($itemIds)) {
            if(!is_array($itemIds)) $itemIds = [$itemIds];

            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLUpdate(
                    'cart_items',
                    [
                        'citem_local_consumption' => $isLocal,
                    ],
                    [
                        'citem_id' => [
                            'in' => $itemIds
                        ],
                        'citem_cart_id' => $this->id
                    ]
                )
            );
        }

        $this->loadCart();
    }

    public function setCustomer($userId, $invoiceType = 0, $remarks = ''){
        $this->loadCustomerData($userId, $invoiceType);

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

            $this->loadCart();
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

            $this->loadCart();
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
        $this->isPaid = true;

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

    public function setRefunded(float $amount = 0){
        $this->isPaid = false;
        $this->isRefunded = true;

        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLUpdate(
                'cart',
                [
                    'cart_paid' => -1,
                    'cart_refunded' => $amount,
                ],
                [
                    'cart_id' => $this->id,
                    'cart_shop_id' => $this->owner->shopId,
                ]
            )
        );
    }

    public function issueInvoice(){
        if($this->invoiceType != -1){
            /**
             * @var $invoice Invoices
             */
            $invoice = $this->owner->addByClassName('Invoices');
            if($invoice->hasInvoiceProvider()) {
                try {
                    $invoice->init($this->getCart())->createInvoice();
                } catch (Exception $e) {
                    die($e->getMessage());
                }
            }
        }
    }

}
