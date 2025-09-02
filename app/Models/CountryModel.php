<?php

namespace App\Models;

use CodeIgniter\Model;

class CountryModel extends Model
{
    protected $table = 'country';
    protected $primaryKey = 'id';
    protected $allowedFields = ['country_code', 'validation_no', 'icon', 'country_short', 'currency_shortcut', 'currency', 'currency_symbol', 'country_name', 'is_active', 'language', 'language_shortcut', 'timezone']; // List of fields that can be manipulated

    public function setActiveCountry($country_id){
        $this->where('is_active', 1)->set('is_active', 0)->update();
        return $this->where('id', $country_id)->set('is_active', 1)->update();
    }
}
