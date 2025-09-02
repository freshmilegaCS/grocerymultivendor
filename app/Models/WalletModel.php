<?php

namespace App\Models;

use CodeIgniter\Model;

class WalletModel extends Model
{
    protected $table = 'wallet';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'ref_user_id', 'amount', 'closing_amount', 'flag', 'remark', 'date'];

    // Fetch the latest wallet amount for a specific user
    public function getLatestWalletAmount($userId)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('id', 'DESC')
                    ->first();
    }
    public function calculateActualWalletAmount($user_id, $walletAmount, $flag)
    {
        $lastWallet = $this->where('user_id', $user_id)
                           ->orderBy('id', 'DESC')
                           ->first();
    
        if ($lastWallet) {
            if ($flag == 'debit') {
                if ($lastWallet['closing_amount'] < $walletAmount) {
                    return ['error' => 'Insufficient wallet balance'];
                }
                return $lastWallet['closing_amount'] - $walletAmount;
            } else {
                return $walletAmount + $lastWallet['closing_amount'];
            }
        }
    
        return ($flag == 'debit' && $walletAmount > 0) ? ['error' => 'Insufficient wallet balance'] : $walletAmount;
    }

    public function insertWalletTransaction($user_id, $walletAmount, $actualWalletAmount, $flag, $remark)
    {
        $data = [
            'user_id'        => $user_id,
            'ref_user_id'    => 0,
            'amount'         => $walletAmount,
            'closing_amount' => $actualWalletAmount,
            'flag'           => $flag,
            'remark'         => $remark,
            'date'           => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }

    public function fetchWalletHistoryByUserId($userId)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }

    public function insertTransaction($data)
    {
        // Insert data into the 'wallet' table
        return $this->insert($data);
    }

    // Initialize wallet for new user without referral
    public function initializeWallet($user_id)
    {
        $data = [
            'user_id' => $user_id,
            'ref_user_id' => 0,
            'amount' => 0,
            'closing_amount' => 0,
            'flag' => 'not_referral',
            'remark' => 'No Referral Code used',
            'date' => date("Y-m-d H:i:s")
        ]; 
        $this->insert($data);
    }

    // Add referral bonus to both users
    public function addReferralBonus($new_user_id, $ref_user_id, $settings)
    {
        $timestamp = date("Y-m-d H:i:s");

        // Add bonus for new user
        $this->insert([
            'user_id' => $new_user_id,
            'ref_user_id' => $ref_user_id,
            'amount' => $settings['init_wallet_amt'],
            'closing_amount' => $settings['init_wallet_amt'],
            'flag' => 'referral',
            'remark' => 'Referral Signup',
            'date' => $timestamp
        ]);

        // Update referred user's wallet
        $current_balance = $this->where('user_id', $ref_user_id)->orderBy('id', 'DESC')->first()['closing_amount'] ?? 0;
        $new_balance = $current_balance + $settings['init_wallet_amt_referral'];

        $this->insert([
            'user_id' => $ref_user_id,
            'ref_user_id' => 0,
            'amount' => $settings['init_wallet_amt_referral'],
            'closing_amount' => $new_balance,
            'flag' => 'referred',
            'remark' => 'Referral Code Used',
            'date' => $timestamp
        ]);
    }

}
