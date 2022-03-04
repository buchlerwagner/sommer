<?php

class Coupon extends ancestor {

    public static function generateCode(int $length = 6):string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
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
                    'c_code' => $code
                ]
            )
        );

        return !Empty($res['c_id']);
    }
}