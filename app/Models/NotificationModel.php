<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notification';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'title', 'img', 'msg', 'date', 'is_system_generated'];

    public function getNotifications($is_system_generated)
    {
        return  $this->select('id, user_id, title, msg, date')
                        ->orderBy('id', 'DESC')
                        ->where('is_system_generated',  $is_system_generated)
                        ->findAll();

    }
    public function deleteNotification($noti_id)
    {
        return $this->where('id', $noti_id)->delete();
    }
    public function getUserNotifications($userId, $createdAt)
    {
        return $this->where('date >=', $createdAt)
                    ->groupStart()
                        ->where('user_id', 0)
                        ->orWhere('user_id', $userId)
                    ->groupEnd()
                    ->orderBy('id', 'desc')
                    ->findAll();
    }

    public function countNotificationsSince($registrationDate, $userId)
    {
        return $this->where('date >=', $registrationDate)
                    ->groupStart()
                        ->where('user_id', 0)
                        ->orWhere('user_id', $userId)
                    ->groupEnd()
                    ->countAllResults();
    }

  
      // Method to check if a notification is read
      public function isNotificationRead($userId, $notificationId)
      {
          return $this->db->table('read_notification')
                          ->where('user_id', $userId)
                          ->where('notification_id', $notificationId)
                          ->countAllResults() > 0;
      }
  
      // Method to mark a notification as read
      public function markNotificationAsRead($userId, $notificationId)
      {
          return $this->db->table('read_notification')
                          ->insert(['user_id' => $userId, 'notification_id' => $notificationId]);
      }
}
