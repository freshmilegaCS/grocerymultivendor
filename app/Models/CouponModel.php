<?php

namespace App\Models;

use CodeIgniter\Model;

class CouponModel extends Model
{
    protected $table = 'coupon';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'coupon_type', 'coupon_img', 'is_multitimes', 'date', 'description', 'value', 'coupon_code', 'status', 'coupon_title', 'min_order_amount', 'is_delete'];

    // Fetch coupons with user details if applicable
    public function getCoupons()
    {

        // Join with user table for user-specific coupons
        $builder = $this->select('coupon.*, user.name as user_name')
            ->join('user', 'user.id = coupon.user_id', 'left')
            ->where('coupon.is_delete', 0);

        return $builder->get()->getResultArray();
    }
    public function insertCoupon($data)
    {
        return $this->insert($data);
    }

    public function deleteCoupon($id)
    {
        return $this->update($id, ['is_delete' => 1]);
    }
    public function getCouponsForApp($userId, $today_date)
    {

        // Join with user table for user-specific coupons
        $builder = $this->select('coupon.*, user.name as user_name')
            ->join('user', 'user.id = coupon.user_id', 'left')
            ->where('coupon.is_delete', 0)
            ->groupStart() // Start grouping conditions
            ->where('coupon.user_id', $userId)
            ->orWhere('coupon.user_id', 0) // Show coupons with user_id = 0
            ->groupEnd() // End grouping
            ->where('coupon.date >=',  $today_date);

        return $builder->get()->getResultArray();
    }
}
