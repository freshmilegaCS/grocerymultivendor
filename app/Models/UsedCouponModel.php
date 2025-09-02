<?php

namespace App\Models;

use CodeIgniter\Model;

class UsedCouponModel extends Model
{
    protected $table = 'used_coupon';
    protected $primaryKey = 'id';
    protected $allowedFields = ['coupon_id', 'user_id', 'date', 'order_id'];
    
    // Function to count used coupons
    public function countUsedCoupons($couponId, $userId)
    {
        return $this->where('coupon_id', $couponId)
                    ->where('user_id', $userId)
                    ->countAllResults();
    }
    public function insertUsedCoupon($couponData)
    {

        return $this->insert($couponData);
    }
}
