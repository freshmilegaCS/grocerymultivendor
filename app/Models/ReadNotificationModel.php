<?php

namespace App\Models;

use CodeIgniter\Model;

class ReadNotificationModel extends Model
{
    protected $table = 'read_notification';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'notification_id'];
    protected $useTimestamps = false;

    public function countReadNotifications($userId)
    {
        return $this->where('user_id', $userId)->countAllResults();
    }
}
