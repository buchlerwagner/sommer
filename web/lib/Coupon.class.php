<?php

class Coupon {
    private $code = '';
    private $id;
    private $minOrderLimit = 0;
    private $discountValue = 0;
    private $discountPercent = 0;
    private $includeDiscountedItems = false;
    private $isMultipleUsage = false;
    private $discount = 0;
    private $expiry;

    private $expiryDays = 0;

    public function __construct(array $settings)
    {
        $this->id = null;

        if($settings){
            foreach($settings AS $key => $value){
                if(property_exists($this, $key)){
                    $method = 'set' . ucfirst($key);
                    if(method_exists($this, $method)) {
                        $this->$method($value);
                    }else {
                        $this->{$key} = $value;
                    }
                }
            }
        }
    }

    /**
     * @param float $cartAmount
     * @return float
     */
    public function calcDiscount(float $cartAmount):float
    {
        $this->discount = 0;

        if($cartAmount > $this->minOrderLimit || $this->minOrderLimit == 0 ){
            $this->setDiscount( $this->discountValue + round($cartAmount * ($this->discountPercent / 100)) );
        }

        return $this->getDiscount();
    }

    public function isDiscountApplicable(float $cartAmount):bool
    {
        return ($cartAmount > $this->minOrderLimit || $this->minOrderLimit == 0 );
    }

    /**
     * @param float $discount
     * @return Coupon
     */
    public function setDiscount(float $discount): self
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @param mixed $code
     * @return Coupon
     */
    public function setCode(string $code):self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCode():string
    {
        return $this->code;
    }

    /**
     * @param int $id
     * @return Coupon
     */
    public function setId(int $id):self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId():int
    {
        return $this->id;
    }

    /**
     * @param float $minOrderLimit
     * @return Coupon
     */
    public function setMinOrderLimit(float $minOrderLimit):self
    {
        $this->minOrderLimit = ($minOrderLimit ?: 0);
        return $this;
    }

    /**
     * @return float
     */
    public function getMinOrderLimit(): float {
        return $this->minOrderLimit;
    }

    /**
     * @param float $discountValue
     * @return Coupon
     */
    public function setDiscountValue(float $discountValue): self
    {
        $this->discountValue = ($discountValue ?: 0);
        return $this;
    }

    /**
     * @return float
     */
    public function getDiscountValue(): float
    {
        return $this->discountValue;
    }

    /**
     * @param float $discountPercent
     * @return Coupon
     */
    public function setDiscountPercent(float $discountPercent): self
    {
        $this->discountPercent = $discountPercent;
        return $this;
    }

    /**
     * @return int
     */
    public function getDiscountPercent(): float
    {
        return $this->discountPercent;
    }

    /**
     * @param int $includeDiscountedItems
     * @return Coupon
     */
    public function setIncludeDiscountedItems(int $includeDiscountedItems): self
    {
        $this->includeDiscountedItems = ($includeDiscountedItems);
        return $this;
    }

    /**
     * @return bool
     */
    public function isIncludeDiscountedItems(): bool
    {
        return $this->includeDiscountedItems;
    }

    /**
     * @param int $isMultipleUsage
     * @return Coupon
     */
        public function setIsMultipleUsage(int $isMultipleUsage): self
    {
        $this->isMultipleUsage = ($isMultipleUsage);
        return $this;
    }

    /**
     * @return bool
     */
    public function isMultipleUsage(): bool
    {
        return $this->isMultipleUsage;
    }


    /**
     * @param mixed $expiry
     * @return Coupon
     */
    public function setExpiry($expiry):self
    {
        $this->expiry = ($expiry ? standardDate($expiry) : false);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    public function isExpired(string $checkDate = ''):bool
    {
        if($this->expiry === false){
            return false;
        }

        if(!Empty($checkDate)){
            $date = standardDate($checkDate);
        }else {
            $date = date('Y-m-d');
        }

        return ($this->expiry < $date);
    }

    /**
     * @param int $expiryDays
     * @return Coupon
     */
    public function setExpiryDays(int $expiryDays): self
    {
        $this->expiryDays = $expiryDays;

        if($this->expiryDays > 0){
            $expiryDate = dateAddDays('now', $this->expiryDays);
        }else{
            $expiryDate = false;
        }

        $this->setExpiry($expiryDate);

        return $this;
    }

}