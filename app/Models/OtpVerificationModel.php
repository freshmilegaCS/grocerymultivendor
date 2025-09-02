<?php

namespace App\Models;

use CodeIgniter\Model;

class OtpVerificationModel extends Model
{
    protected $table = 'otp_verification';
    protected $primaryKey = 'id';
    protected $allowedFields = ['email', 'mobile', 'otp', 'verify_by', 'created_at'];

    // Function to update OTP for the given mobile number
    public function updateOtp($mobile, $otp, $timestamp)
    {
        // Check if the mobile number already exists
        $existingOtp = $this->where('mobile', $mobile)->first();

        if ($existingOtp) {
            // Update the existing OTP
            $this->set(['otp' => $otp, 'date' => $timestamp])
                ->where('mobile', $mobile)
                ->update();
        } else {
            // Insert a new OTP entry
            $this->insert(['mobile' => $mobile, 'otp' => $otp, 'date' => $timestamp]);
        }
    }


    // Function to verify OTP
    public function verifyOtp($mobile, $otp)
    {
        $this->where(['mobile' => $mobile, 'otp' => $otp]);
        return $this->countAllResults() === 1;
    }
}
