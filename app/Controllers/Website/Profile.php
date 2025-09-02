<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;

use App\Models\CartsModel;
use App\Models\UserModel;


class Profile extends BaseController
{
    public function index()
    {

        if (
            (empty(session()->get('email')) || (int)session()->get('is_email_verified') !== 1) &&
            (empty(session()->get('mobile')) || (int)session()->get('is_mobile_verified') !== 1)
        ) {
            return redirect()->to('/login');
        }

        $data['settings'] = $this->settings;
        $cartsModel = new CartsModel();
        $userModel = new UserModel();
        $data['country'] = $this->country;

        $user = null;

        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }
        if (!$user) {
            $data['cartItemCount'] = 0;
        } else {
            $cartItemCount = $cartsModel->where('user_id', $user['id'])->countAllResults();
            $data['cartItemCount'] = $cartItemCount;
            $data['user'] = $user;
        }

        $data['user_name'] = $user['name'];
        $data['user_mobile'] = $user['mobile'];
        $data['user_email'] = $user['email'];
        $data['is_email_verified'] = $user['is_email_verified'];
        $data['is_mobile_verified'] = $user['is_mobile_verified'];

        return view('website/profile/profile', $data);
    }

    public function updateProfile()
    {
        $data['settings'] = $this->settings;
        $userModel = new UserModel();
        $user = null;
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (!$user) {
            $data['cartItemCount'] = 0;
        } else {
            $data = $this->request->getJSON(true);
            $profileData = [
                'name' => $data['contactFName'],
            ];

            if ($user['is_email_verified'] == 0) {
                $existingUser = $userModel->where('email', $data['contactEmail'])->first();

                if($existingUser){
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'This Email already Used'
                    ]); 
                }else{
                    $profileData = [
                        'email' => $data['contactEmail'],
                    ];
                }
            }
    
            if ($user['is_mobile_verified'] == 0) { 
                $existingUser = $userModel->where('mobile', $data['contactPhone'])->first();

                if($existingUser){
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'This Mobile already Used'
                    ]);
                }else{
                    $profileData = [
                        'mobile' => $data['contactPhone'],
                    ];
                }
            }
            

            if ($userModel->set($profileData)->where('id', $user['id'])->update()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Profile updated successfully.']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update profile. Please try again later.']);
            }
        }
    }

    public function deleteAccount()
    {
        $data['settings'] = $this->settings;
        $userModel = new UserModel();
        $user = null;
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }
        if (!$user) {
            $data['cartItemCount'] = 0;
        } else {
            $data = $this->request->getJSON(true);
            $profileData = [
                'is_delete' => 1
            ];

            if ($userModel->set($profileData)->where('id', $user['id'])->update()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Account deleted successfully.']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update account. Please try again later.']);
            }
        }
    }

    public function changePassword()
    {
        $data = $this->request->getJSON(true);

        // Check if password is provided and meets the criteria
        if (!isset($data['password']) || strlen($data['password']) < 6) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Password must be at least 6 characters long.'
            ]);
        }

        // Find the user from the database
        $userModel = new UserModel();
        $user = null;

        if (session()->get('login_type') == 'email') {
            $user = $userModel
                ->where('email', session()->get('email'))
                ->where('is_active', 1)
                ->where('login_type', 'normal')
                ->where('is_delete', 0)
                ->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel
                ->where('mobile', session()->get('mobile'))
                ->where('is_active', 1)
                ->where('login_type', 'mobile')
                ->where('is_delete', 0)
                ->first();
        }

        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found or account is inactive.'
            ]);
        }

        // Update the password
        $updateData = [
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $updateStatus = $userModel->update($user['id'], $updateData);

        if ($updateStatus) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Password updated successfully.'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to update password. Please try again later.'
            ]);
        }
    }

    public function uploadUserProfilePic()
    {
        $file = $this->request->getFile('file');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid file upload'
            ]);
        }

        // Validate the file type and size
        $allowedTypes = ['image/png', 'image/jpg', 'image/jpeg'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid file type. Only PNG, JPG, and JPEG are allowed.'
            ]);
        }

        if ($file->getSize() > 2 * 1024 * 1024) { // Limit size to 2MB
            // return $this->respond(['error' => 'File size exceeds 2MB'], 400);
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File size exceeds 2MB'
            ]);
        }

        // Move the file to the uploads directory
        $newName = $file->getRandomName();
        $file->move(WRITEPATH . '../public/uploads/user_profile', $newName);

        // Save the path to the database if necessary (example)
        // Assuming a `users` table with an `avatar` column
        $filePath = 'uploads/user_profile/' . $newName;

        $userModel = new UserModel();

        $isUpdated = $userModel->where('email', session()->get('email'))
            ->where('is_active', 1)
            ->where('is_delete', 0)
            ->set(['img' => $filePath])
            ->update();

        if ($isUpdated) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Avatar updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to update avatar'
            ]);
        }
    }
}
