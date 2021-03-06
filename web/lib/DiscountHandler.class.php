<?php

class DiscountHandler extends ancestor {
    const DEFAULT_CODE_LENGTH    = 6;

    const COUPON_ERR_INVALID     = 1;
    const COUPON_ERR_EXPIRED     = 2;
    const COUPON_ERR_USED        = 3;
    const COUPON_ERR_ORDER_LIMIT = 4;
    const COUPON_ERR_NO_DISCOUNT = 5;

    private $error = false;

    public static function generateCode(int $length = self::DEFAULT_CODE_LENGTH):string
    {
        $randomString = '';
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        if(!$length) $length = self::DEFAULT_CODE_LENGTH;

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    public function generateUniqueCode(int $length = 6):string
    {
        do{
            $code = self::generateCode($length);
        }while($this->isCouponExists($code));

        return $code;
    }

    public function isCouponExists(string $code):bool
    {
        $res = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'coupons',
                [
                    'c_id'
                ],
                [
                    'c_shop_id' => $this->owner->shopId,
                    'c_code' => strtoupper($code)
                ]
            )
        );

        return !Empty($res['c_id']);
    }

    public function getError():int
    {
        return $this->error;
    }

    public function hasError():bool
    {
        return ($this->error);
    }

    private function setError(int $errCode):void
    {
        $this->error = $errCode;
    }

    public function applyCoupon(string $code, Cart $cart):?Coupon
    {
        if($coupon = $this->getCoupon($code)){
            if($this->isCouponUsed($coupon, $cart->id)){
                $this->setError(self::COUPON_ERR_USED);
            }else{
                $total = 0;

                /**
                 * @var $item CartItem
                 */
                if($items = $cart->getItems()){
                    foreach($items AS $item){
                        if(($item->isDiscounted() && $coupon->isIncludeDiscountedItems()) || !$item->isDiscounted()){
                            $total += $item->getGrossPrice();
                        }
                    }
                }

                if($coupon->isDiscountApplicable($total)){
                    $discount = $coupon->calcDiscount($total);
                    if($discount > 0 && $cart->getDiscount() < $discount) {
                        $this->saveCoupon($coupon, $cart);

                        return $coupon;
                    }else{
                        $this->setError(self::COUPON_ERR_NO_DISCOUNT);
                    }
                }elseif($total == 0){
                    $this->setError(self::COUPON_ERR_NO_DISCOUNT);
                }else{
                    $this->setError(self::COUPON_ERR_ORDER_LIMIT);
                }
            }
        }

        return null;
    }

    public function getLoyaltyDiscount(int $userId, float $orderAmount = 0):float
    {
        $discount = 0;

        $rule = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'loyalty_rules',
                [
                    'lr_min_order_limit AS minOrderLimit',
                    'lr_discount AS discount',
                    'lr_only_paid AS onlyPaid',
                    'lr_only_finished AS onlyFinished',
                ],
                [
                    'lr_shop_id' => $this->owner->shopId,
                    'lr_valid_from' => [
                        'less=' => 'NOW()'
                    ]
                ]
            )
        );
        if($rule){
            $sumUserOrders = $this->sumUserOrders($userId, (bool) $rule['onlyPaid'], (bool) $rule['onlyFinished']);
            if($sumUserOrders >= $rule['minOrderLimit'] || $rule['minOrderLimit'] == 0){
                $discount = round($orderAmount * ($rule['discount'] / 100));
            }
        }

        return $discount;
    }

    public function getAppliedCoupon(int $cartId):?Coupon
    {
        $coupon = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'coupon_usage',
                [
                    'c_id AS id',
                    'c_code AS code',
                    'c_min_order_limit AS minOrderLimit',
                    'c_discount_value AS discountValue',
                    'c_discount_percent AS discountPercent',
                    'c_include_discounted_products AS includeDiscountedItems',
                    'c_multiple_usage AS isMultipleUsage',
                    'c_expiry AS expiry',
                    'cu_value AS discount',
                    'cart_status AS cartStatus',
                ],
                [
                    'cu_cart_id' => $cartId,
                    'c_shop_id' => $this->owner->shopId,
                ],
                [
                    'coupons' => [
                        'on' => [
                            'c_id' => 'cu_c_id'
                        ]
                    ],
                    'cart' => [
                        'on' => [
                            'cart_id' => 'cu_cart_id'
                        ]
                    ],
                ]
            )
        );
        if($coupon) {
            if (($coupon['expiry'] && $coupon['expiry'] < date('Y-m-d')) && $coupon['cartStatus'] == CartHandler::CART_STATUS_NEW) {
                $this->clearCoupon($cartId);
                return null;
            }

            return new Coupon($coupon);
        }

        return null;
    }

    public function getPromoCoupon(int $couponId, float $orderAmount = 0):?Coupon
    {
        if($couponId){
            $coupon = $this->getCouponById($couponId);
            if($coupon instanceof Coupon){
                if($coupon->isExpired()){
                    $coupon = null;
                }elseif($this->isCouponUsed($coupon)){
                    $coupon = null;
                }
            }
        }else{
            $coupon = $this->generateCoupon($orderAmount);
        }

        return $coupon;
    }

    public function clearCoupon(int $cartId):void
    {
        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLDelete(
                'coupon_usage',
                [
                    'cu_cart_id' => $cartId
                ]
            )
        );
    }

    private function sumUserOrders(int $userId, bool $isPaid = true, bool $isFinished = true):float
    {
        $where = [
            'cart_status' => 'ORDERED',
            'cart_us_id' => $userId
        ];

        if($isPaid){
            $where['cart_paid'] = 1;
        }

        if($isFinished){
            $where['cart_order_status'] = [
                'in' => ["'" . ORDER_STATUS_FINISHED . "'", "'" . ORDER_STATUS_CLOSED . "'"]
            ];
        }

        $orders = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'cart',
                [
                    'SUM(cart_subtotal - cart_discount) AS total'
                ],
                $where
            )
        );

        return (float) $orders['total'];
    }

    private function getCoupon(string $code):?Coupon
    {
        $code = strtoupper(trim($code));

        $coupon = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'coupons',
                [
                    'c_id AS id',
                    'c_code AS code',
                    'c_min_order_limit AS minOrderLimit',
                    'c_discount_value AS discountValue',
                    'c_discount_percent AS discountPercent',
                    'c_include_discounted_products AS includeDiscountedItems',
                    'c_multiple_usage AS isMultipleUsage',
                    'c_expiry AS expiry',
                ],
                [
                    'c_shop_id' => $this->owner->shopId,
                    'c_code' => $code,
                    'c_enabled' => 1
                ]
            )
        );
        if($coupon) {
            if ($coupon['expiry'] && $coupon['expiry'] < date('Y-m-d')) {
                $this->setError(self::COUPON_ERR_EXPIRED);

                return null;
            }

            return new Coupon($coupon);
        }

        $this->setError(self::COUPON_ERR_INVALID);

        return null;
    }

    private function isCouponUsed(Coupon $coupon, int $cartId = 0):bool
    {
        if(!$coupon->isMultipleUsage()) {
            $couponUsage = $this->owner->db->getFirstRow(
                $this->owner->db->genSQLSelect(
                    'coupon_usage',
                    [
                        'cu_id',
                    ],
                    [
                        'cu_cart_id !' => $cartId,
                        'cu_c_id' => $coupon->getId(),
                    ]
                )
            );
            if ($couponUsage) {
                return true;
            }
        }

        return false;
    }

    private function saveCoupon(Coupon $coupon, Cart $cart):void
    {
        $usid = $this->owner->user->id;
        $customer = $cart->getCustomer();
        if($customer['contactData']['id']){
            $usid = $customer['contactData']['id'];
        }

        $this->owner->db->sqlQuery(
            $this->owner->db->genSQLInsert(
                'coupon_usage',
                [
                    'cu_cart_id' => $cart->id,
                    'cu_c_id' => $coupon->getId(),
                    'cu_us_id' => $usid,
                    'cu_timestamp' => 'NOW()',
                    'cu_value' => $coupon->getDiscount(),
                    'cu_currency' => $cart->getCurrency(),
                ],
                [
                    'cu_cart_id' => $cart->id,
                    'cu_c_id' => $coupon->getId(),
                ]
            )
        );
    }

    private function getCouponById(int $id):?Coupon
    {
        $coupon = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'coupons',
                [
                    'c_id AS id',
                    'c_code AS code',
                    'c_min_order_limit AS minOrderLimit',
                    'c_discount_value AS discountValue',
                    'c_discount_percent AS discountPercent',
                    'c_include_discounted_products AS includeDiscountedItems',
                    'c_multiple_usage AS isMultipleUsage',
                    'c_expiry AS expiry',
                ],
                [
                    'c_shop_id' => $this->owner->shopId,
                    'c_id' => $id,
                    'c_enabled' => 1
                ]
            )
        );

        return ($coupon ? new Coupon($coupon) : null);
    }

    private function generateCoupon(float $orderAmount):?Coupon
    {
        $coupon = null;

        if($rule = $this->getApplicableCouponRule($orderAmount)){
            $coupon = new Coupon($rule);

            $coupon->setCode( $this->generateUniqueCode($rule['codeLength']) );

            $this->owner->db->sqlQuery(
                $this->owner->db->genSQLInsert(
                    'coupons',
                    [
                        'c_shop_id' => $this->owner->shopId,
                        'c_code' => $coupon->getCode(),
                        'c_created' => 'NOW()',
                        'c_expiry' => ($coupon->getExpiry() ?: null),
                        'c_min_order_limit' => $coupon->getMinOrderLimit(),
                        'c_discount_value' => $coupon->getDiscountValue(),
                        'c_discount_percent' => $coupon->getDiscountPercent(),
                        'c_include_discounted_products' => ($coupon->isIncludeDiscountedItems() ? 1 : 0),
                        'c_multiple_usage' => ($coupon->isMultipleUsage() ? 1 : 0),
                        'c_enabled' => 1,
                    ],
                    [
                        'c_shop_id' => $this->owner->shopId,
                        'c_code' => $coupon->getCode(),
                    ]
                )
            );

            $coupon->setId( $this->owner->db->getInsertRecordId() );
        }

        return ($coupon ?: null);
    }

    private function getApplicableCouponRule(float $orderAmount = 0):array
    {
        $rule = $this->owner->db->getFirstRow(
            $this->owner->db->genSQLSelect(
                'automatic_coupons',
                [
                    'ac_id AS id',
                    'ac_code_length AS codeLength',
                    'ac_min_order_limit AS minOrderLimit',
                    'ac_discount_value AS discountValue',
                    'ac_discount_percent AS discountPercent',
                    'ac_include_discounted_products AS includeDiscountedItems',
                    'ac_multiple_usage AS isMultipleUsage',
                    'ac_expiry_days AS expiryDays',
                ],
                [
                    'ac_shop_id' => $this->owner->shopId,
                    'ac_min_sale_limit' => [
                        'less' => $orderAmount
                    ],
                    'ac_enabled' => 1
                ]
            )
        );

        return ($rule ?: []);
    }
}