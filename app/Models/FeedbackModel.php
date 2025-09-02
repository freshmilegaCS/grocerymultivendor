<?php

namespace App\Models;

use CodeIgniter\Model;

class FeedbackModel extends Model
{
    protected $table = 'feedback';
    protected $primaryKey = 'id';
    protected $allowedFields = ['order_id', 'user_id', 'rate', 'message'];

    // Fetch all feedback with user details
    public function getFeedbackWithUserDetails()
    {
        // Assuming a JOIN between feedback and user tables
        return $this->select('feedback.*, user.name, user.mobile')
                    ->join('user', 'user.id = feedback.user_id')
                    ->findAll();
    }
    public function getTotalFeedbacks()
    {
        return $this->countAllResults();
    }
}
