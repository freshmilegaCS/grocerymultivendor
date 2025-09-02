<?php

namespace App\Models;

use CodeIgniter\Model;

class SellerModel extends Model
{
    protected $table      = 'seller';
    protected $primaryKey = 'id';

    // Fields that are allowed for insert/update
    protected $allowedFields = ['name', 'store_name', 'slug', 'email', 'password', 'mobile', 'balance', 'logo', 'store_address', 'city_id', 'deliverable_area_id', 'account_number', 'bank_ifsc_code', 'account_name', 'branch', 'bank_name', 'commission', 'status', 'require_products_approval', 'fcm_app_key', 'national_id_proof', 'address_proof', 'pan_number', 'tax_number', 'tax_name', 'map_address', 'latitude', 'longitude', 'view_customer_details', 'registered_at', 'created_at', 'updated_at', 'is_delete', 'status_reason', 'order_status_permission', 'reset_link_token', 'reset_token_exp_date'];

    // Use timestamps for automatic handling of created_at and updated_at fields
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
