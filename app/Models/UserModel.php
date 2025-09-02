<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'country_code', 'mobile', 'email', 'password', 'login_type', 'img', 'ref_code', 'ref_by', 'wallet', 'created_at', 'updated_at', 'is_active', 'is_delete', 'is_email_verified', 'is_mobile_verified', 'password_reset_link', 'apple_user_id', 'language'];

    public function getTotalUsers()
    {
        return $this->countAllResults();
    }
    public function searchUserByNameOrEmailOrMobile($name)
    {
        return $this->like('name', $name)
            ->orLike('email', $name)
            ->orLike('mobile', $name)
            ->findAll();
    }
    public function getUsersWithWallet()
    {
        $users = $this->select('*')->where('is_delete', 0)->findAll();

        // Initialize an array to hold user details with wallet information
        $usersWithWallet = [];

        // Iterate over each user to fetch their latest wallet closing amount
        foreach ($users as $user) {
            // Fetch the latest closing amount from the wallet for the current user
            $latestWallet = $this->db->table('wallet')
                ->select('closing_amount')
                ->where('user_id', $user['id'])
                ->orderBy('id', 'DESC')  // Order by id to get the latest entry
                ->limit(1)  // Limit to the most recent entry
                ->get()
                ->getRowArray();

            // Add the latest closing amount to the user's data (default to 0 if not found)
            $user['closing_amount'] = $latestWallet['closing_amount'] ?? 0;

            // Add the user with wallet information to the results array
            $usersWithWallet[] = $user;
        }

        // Return the complete array with user details and their latest wallet information
        return $usersWithWallet;

        return $builder->get()->getResultArray();
    }
    public function getUserById($userId)
    {
        return $this->where([
            'id' => $userId,
            'is_delete' => 0,
            'is_active' => 1
        ])->first();
    }
    public function getUserAppKey($user_id)
    {
        return $this->select('app_key')
            ->where('id', $user_id)
            ->first();
    }
    public function getUserByMobile($mobile)
    {
        return $this->where(['mobile' => $mobile, 'is_delete' => 0])->first();
    }

    // Fetch user profile picture by mobile number
    public function getProfilePictureByMobile($mobile)
    {
        return $this->select('img')
            ->where('mobile', $mobile)
            ->where('is_active', 1)
            ->where('is_delete', 0)
            ->first();
    }

    public function getReferralCodeByMobile($mobile)
    {
        return $this->select('ref_code')
            ->where('mobile', $mobile)
            ->where('is_delete', 0)
            ->first();
    }

    // Function to verify user login credentials
    public function getUserByMobileAndPassword($mobile, $password)
    {
        return $this->where([
            'mobile' => $mobile,
            'password' => $password,
            'is_delete' => 0
        ])->first();
    }

    // Function to update user's token and app key
    public function updateUserToken($userId, $token, $fcmToken)
    {
        return $this->where('id', $userId)->set(['user_token' => $token, 'app_key' => $fcmToken])->update();
    }

    // Method to update user details
    public function updateUser($mobile, $data)
    {
        return $this->where('mobile', $mobile)
            ->where('is_delete', 0)
            ->set($data)
            ->update();
    }

    // Function to update user's profile image
    public function updateProfileImage($mobile, $imagePath)
    {
        return $this->where('mobile', $mobile)
            ->where('is_active', 1)
            ->where('is_delete', 0)
            ->set('img', $imagePath)
            ->update();
    }



    // Function to check if mobile is already registered
    public function isMobileRegistered($mobile)
    {
        return $this->where(['mobile' => $mobile, 'is_delete' => 0])->countAllResults() > 0;
    }

    // Function to register a user
    public function registerUser($name, $mobile, $password, $user_token, $fcmToken, $ref_code)
    {
        $data = [
            'name' => $name,
            'mobile' => $mobile,
            'registration_date' => date("Y-m-d H:i:s"),
            'password' => $password,
            'user_token' => $user_token,
            'app_key' => $fcmToken,
            'ref_code' => $ref_code
        ];
        $this->insert($data);
        return $this->insertID();
    }

    // Function to get user ID by referral code
    public function getUserIdByReferralCode($ref_code)
    {
        return $this->where('ref_code', $ref_code)->first()['id'] ?? null;
    }

    // Function to generate random string
    public function generateRandomString($length = 10)
    {
        return bin2hex(random_bytes($length / 2));
    }

    public function verifyOtp($mobile, $otp)
    {
        return $this->where('mobile', $mobile)
            ->where('pin', $otp)
            ->where('is_active', 1)
            ->where('is_delete', 0)
            ->first();
    }

    public function getUserDetails($userId)
    {
        return $this->select('name, mobile')
            ->where('id', $userId)
            ->get()
            ->getRowArray();
    }
}
