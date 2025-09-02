<?php

namespace App\Models;

use CodeIgniter\Model;

class LanguageModel extends Model
{
    protected $table = 'language';
    protected $primaryKey = 'id';
    protected $allowedFields = ['language', 'lang_short', 'is_rtl', 'is_active', 'is_default'];
    
    public function updateDefault($id)
    {
        $this->where('is_default', 1)->set(['is_default' => 0])->update();
        return $this->update($id, ['is_default' => 1]);
    }
}
