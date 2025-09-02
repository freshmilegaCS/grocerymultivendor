<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table = 'setting';
    protected $primaryKey = 'id';
    protected $allowedFields = [ 
        'title',
        'tax',
        'currency',
        'timezone',
        'minimum_order_value',
        'about_us',
        'privacy_policy',
        'terms_condition',
        'refund_policy',
        'firebase_project_id',
        'firebase_admin_json_file_name',
        'app_maintenance',
        'primary_color',
        'init_wallet_amt',
        'init_wallet_amt_referral',
        'picture_quality',
        'maps_javascript_api_key',
        'google_map_api_key',
        'app_logo',
        'app_header_logo',
        'decrypted_api_key',
        'api_key',
        'new_version_code',
        'is_force_update',
        'is_update_active','google_map_api_key'
    ];



    public function updatePurchasedKey($code, $id)
    {
        $data = [
            'purchased_key' => $code,
        ];
        $this->update($id, $data);
        return $this->affectedRows() > 0;
    }
    public function updateApiKey($encryptedKey, $decryptedKey)
    {
        return $this->update(1, ['api_key' => $encryptedKey, 'decrypted_api_key' => $decryptedKey]);
    }

    public function getSettings()
    {
        return $this->first(); // Fetch all settings
    }
    public function getCurrencyTaxSetting()
    {
        return $this->select('title,  tax, currency, timezone, minimum_order_value, app_logo',)->first(); // Fetch all settings
    }

    public function getAppHeaderLogo()
    {
        return $this->select('app_header_logo')->first();
    }

    public function getAppLogo()
    {
        return $this->select('app_logo')->first();
    }

    // Method to fetch update details
    public function getUpdateDetails()
    {
        return $this->where('is_update_active', 1)->first();
    }
}
