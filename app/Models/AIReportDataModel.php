<?php

namespace App\Models;

use CodeIgniter\Model;

class AIReportDataModel extends Model
{
    protected $table = 'ai_report_data';
    protected $primaryKey = 'id';
    protected $allowedFields = ['from_date', 'to_date', 'ai_insight', 'created_at'];
    

}
