<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;

use App\Models\OtpVerificationModel;
use App\Models\UserModel;
use App\Models\CartsModel;
use App\Models\WalletModel;
use App\Models\SmsGatewayModel;


class Auth extends BaseController
{
    protected $googleClient;

    private function initializeGoogleSigninConfig()
    {
        $settings = $this->settings; // Assuming $this->settings contains your settings array

        $socialLogins = json_decode($settings['social_login'], true);

        // Initialize variables
        $googleClientId = null;
        $googleClientSecret = null;

        // Loop through the social login array to find Google credentials
        foreach ($socialLogins as $login) {
            if ($login['login_medium'] === 'google' && $login['status'] === "1") {
                $googleClientId = $login['client_id'];
                $googleClientSecret = $login['client_secret'];
                break;
            }
        }

        // Check if Google login is enabled and credentials are available
        if ($googleClientId && $googleClientSecret) {
            $this->googleClient = new \Google_Client();

            $this->googleClient->setClientId($googleClientId);
            $this->googleClient->setClientSecret($googleClientSecret);
            $this->googleClient->setRedirectUri(base_url('/googlesignin'));
            $this->googleClient->addScope('profile');
            $this->googleClient->addScope('email');
        }
    }

    public function googleSignin()
    {
        
        session()->remove(['email', 'name', 'is_email_verified', 'mobile', 'is_mobile_verified', 'login_type']);
        date_default_timezone_set($this->timeZone['timezone']); // Set the timezone

        $this->initializeGoogleSigninConfig();

        $state = $this->request->getGet('state');
        $guest_id = null;
        if ($state) {
            $decodedState = json_decode($state, true);
            $guest_id = $decodedState['guest_id'] ?? null;
        }

        try {
            // Fetch token using the auth code
            $token = $this->googleClient->fetchAccessTokenWithAuthCode($this->request->getGet('code'));


            if (isset($token['error'])) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid Google authentication token.']);
            }

            // Set the access token and fetch user information
            $this->googleClient->setAccessToken($token['access_token']);
            $googleService = new \Google_Service_Oauth2($this->googleClient);
            $googleData = $googleService->userinfo->get();

            if (empty($googleData['email'])) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to retrieve email from Google.']);
            }

            $userModel = new UserModel();
            $existingUser = $userModel->where('email', $googleData['email'])->first();

            // Check if the user already exists
            if ($existingUser) {
                if ($existingUser['is_delete'] == 1) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Account has been deleted.']);
                }

                if (
                    $existingUser['login_type'] === 'google' &&
                    $existingUser['is_active'] == 1 &&
                    $existingUser['is_delete'] == 0 &&
                    $existingUser['is_email_verified'] == 1
                ) {

                    $cartsModel = new CartsModel();
                    $cartsModel->set('user_id', $existingUser['id'])
                        ->where('guest_id', $guest_id)
                        ->update();

                    // Set session for an existing user
                    session()->set([
                        'email' => $existingUser['email'],
                        'name' => $existingUser['name'],
                        'is_email_verified' => $existingUser['is_email_verified'],
                        'guest_id' => $guest_id,
                         'login_type' => 'email'
                    ]);

                    return redirect()->to('/')->with('success', 'Logged in successfully.' . $guest_id);
                } else {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'This email must be accessed using a password.']);
                }
            }

            // Create a new user
            $refCode = strtoupper(substr($googleData['name'], 0, 4)) . strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));

            $data = [
                'email' => $googleData['email'],
                'name' => $googleData['name'],
                'img' => $googleData['picture'] ?? null,
                'login_type' => 'google',
                'ref_code' => $refCode,
                'is_active' => 1, // Mark the new user as active
                'is_delete' => 0,
                'is_email_verified' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if ($userModel->insert($data)) {
                $userId = $userModel->insertID();
                $walletModel = new WalletModel();
                $walletModel->insert(['user_id' => $userId, 'amount' => 0, 'closing_amount' => 0, 'date' => date("Y-m-d H:i:s")]);
                $cartsModel = new CartsModel();
                $cartsModel->set('user_id', $userId)
                    ->where('guest_id', $guest_id)
                    ->update();

                // Set session for the new user
                session()->set([
                    'email' => $googleData['email'],
                    'name' => $googleData['name'],
                    'is_email_verified' => 1,
                     'login_type' => 'email'
                ]);

                return redirect()->to(base_url('/'))->with('success', 'Account created successfully.');
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to create account.']);
            }
        } catch (\Exception $e) {
            // Handle exceptions gracefully
            log_message('error', 'Google Sign-in error: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Something went wrong. Please try again later.']);
        }
    }


    public function index()
    {
        if ((session()->has('email') && session()->get('is_email_verified') == 1) || (session()->has('mobile') && session()->get('is_mobile_verified') == 1)) {
            return redirect()->to('/');
        }

        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        date_default_timezone_set($this->timeZone['timezone']);
        $settings = $this->settings;
        $socialLogins = json_decode($settings['social_login'], true);

        // Loop through the social login array to find Google credentials
        foreach ($socialLogins as $login) {
            if ($login['login_medium'] === 'google' && $login['status'] === "1") {
                $this->initializeGoogleSigninConfig();
                $data['authUrl'] = $this->googleClient->createAuthUrl();
                break;
            }
        }

        $data['cartItemCount'] = 0;

        // Display the form on GET request
        if ($this->request->is('get')) {
            return view('website/auth/login', $data);
        }

        // Handle form submission on POST request
        if ($this->request->is('post')) {
            // Get the input data
            $dataInput = $this->request->getJSON(true);

            // Initialize the models 
            $userModel = new UserModel();

            // Find user by email
            $user = $userModel->where('email', $dataInput['email'])
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->where('is_email_verified', 1)
                ->first();

            // Check if user exists and verify the password
            if ($user && password_verify($dataInput['password'], $user['password'])) {
                // Set session data (e.g., email, name, is_email_verified)
                $cartsModel = new CartsModel();
                $cartsModel->set('user_id', $user['id'])
                    ->where('guest_id', $dataInput['guest_id'])
                    ->update();

                session()->remove(['email', 'name', 'is_email_verified', 'mobile', 'is_mobile_verified', 'login_type']);

                session()->set([
                    'email' => $user['email'],
                    'name' => $user['name'],
                    'is_email_verified' => $user['is_email_verified'],
                    'login_type' => 'email'
                ]);

                return $this->response->setJSON(['status' => 'success', 'message' => 'Login successful.']);
            } else {
                // Respond with an error if authentication fails
                return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid credentials or account not activated.']);
            }
        }
    }

    public function signup()
    {
        if($this->settings['direct_login'] == 0){
            return redirect()->to('/login');
        }

        if ((session()->has('email') && session()->get('is_email_verified') == 1) ||
            (session()->has('mobile') && session()->get('is_mobile_verified') == 1)
        ) {
            return redirect()->to('/');
        }

        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        date_default_timezone_set($this->timeZone['timezone']);
        $data['cartItemCount'] = 0;

        if ($this->request->is('get')) {
            return view('website/auth/signup', $data);
        }

        if ($this->request->is('post')) {
            $dataInput = $this->request->getJSON(true);

            $validationRules = [
                'name' => 'required',
                'mobile' => 'required|regex_match[/^[0-9]{' . $this->country['validation_no'] . '}$/]',
                'email' => 'required|valid_email',
                'password' => 'required|min_length[6]',
            ];

            if (!empty($dataInput['referal'])) {
                $validationRules['referal'] = 'trim|exact_length[8]';
            }

            if (!$this->validate($validationRules)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Validation failed: ' . implode(', ', $this->validator->getErrors())
                ]);
            }

            $userModel = new UserModel();
            $otpVerificationModel = new OtpVerificationModel();

            $referedUser['id'] = 0;
            if (!empty($dataInput['referal']) && $this->settings['refer_and_earn_status'] == 1) {
                $referedUser = $userModel->where('ref_code', $dataInput['referal'])->first();

                if (!$referedUser) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Referral code is not valid.'
                    ]);
                }
            }

            $existingUserHaveSameMobile = $userModel->where('mobile', $dataInput['mobile'])->first();
            if ($existingUserHaveSameMobile) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'This mobile number is already used. Please use a different one.'
                ]);
            }

            $existingUser = $userModel->where('email', $dataInput['email'])->first();
            if ($existingUser) {
                if ($existingUser['is_delete'] == 1) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Account has been deleted.'
                    ]);
                }

                if ($existingUser['is_active'] == 1 && $existingUser['is_email_verified'] == 1) {
                    if ($existingUser['login_type'] == 'normal') {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'This email is already registered. Please use "Forgot Password" option.'
                        ]);
                    } elseif ($existingUser['login_type'] == 'google') {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'This email is registered via Google login.'
                        ]);
                    }
                }

                if ($existingUser['is_active'] == 0 && $existingUser['is_email_verified'] == 0) {
                    $userModel->update($existingUser['id'], [
                        'name' => $dataInput['name'],
                        'country_code' => $this->country['country_code'],
                        'mobile' => $dataInput['mobile'],
                        'password' => password_hash($dataInput['password'], PASSWORD_BCRYPT),
                        'login_type' => 'normal',
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                    if ($userModel->db->affectedRows() > 0) {
                        session()->remove(['email', 'name', 'is_email_verified', 'mobile', 'is_mobile_verified', 'login_type']);
                        session()->set(['email' => $dataInput['email'], 'login_type' => 'email']);

                        $otp = random_int(100000, 999999);
                        $otpVerificationModel->insert([
                            'email' => $dataInput['email'],
                            'otp' => $otp,
                            'verify_by' => 'email',
                            'created_at' => date('Y-m-d H:i:s')
                        ]);

                        $this->sendMailOTP($dataInput['email'], $otp);

                        return $this->response->setJSON(['status' => 'success', 'message' => 'OTP sent to registered Email ID']);
                    } else {
                        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update account. Please try again.']);
                    }
                }
            }

            $refCode = strtoupper(substr($dataInput['name'], 0, 4)) . strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));
            $userData = [
                'name' => $dataInput['name'],
                'country_code' => $this->country['country_code'],
                'mobile' => $dataInput['mobile'],
                'email' => $dataInput['email'],
                'password' => password_hash($dataInput['password'], PASSWORD_BCRYPT),
                'login_type' => 'normal',
                'ref_code' => $refCode,
                'ref_by' => $referedUser['id'] ?? 0,
                'is_active' => 0,
                'is_delete' => 0,
                'is_email_verified' => 0,
                'is_mobile_verified' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if ($userModel->insert($userData)) {
                $userId = $userModel->insertID();

                $walletModel = new WalletModel();
                $walletModel->insert(['user_id' => $userId, 'amount' => 0, 'closing_amount' => 0, 'date' => date("Y-m-d H:i:s")]);

                session()->remove(['email', 'name', 'is_email_verified', 'mobile', 'is_mobile_verified', 'login_type']);
                session()->set(['email' => $dataInput['email'], 'login_type' => 'email']);

                $otp = random_int(100000, 999999);
                $otpVerificationModel->insert([
                    'email' => $dataInput['email'],
                    'otp' => $otp,
                    'verify_by' => 'email'
                ]);

                $this->sendMailOTP($dataInput['email'], $otp);

                return $this->response->setJSON(['status' => 'success', 'message' => 'OTP sent to registered Email ID']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to create account. Please try again.']);
            }
        }
    }

    private function sendMailOTP($sendEmail, $otp)
    {
        $email = \Config\Services::email();
        $settings = $this->settings;
        $mailSetting = json_decode($settings['mail_config'], true);
        date_default_timezone_set($this->timeZone['timezone']); // Set the timezone
        $link = "<a style='background:#3E3F95;text-decoration:none !important; font-weight:700; margin:35px 0px; color:#fff;text-transform:uppercase; font-size:20px; letter-spacing: 10px; padding:10px 24px;display:inline-block;border-radius:50px;' href='#'>" . $otp . "</a>";
        $config = [
            // 'protocol' => $mailSetting['driver'],
            'SMTPHost' => $mailSetting['host'], // Replace with your SMTP host
            'SMTPUser' => $mailSetting['username'], // Replace with your SMTP username
            'SMTPPass' => $mailSetting['password'], // Replace with your SMTP password
            'SMTPPort' => $mailSetting['port'], // Common SMTP ports are 25, 465 (SSL), or 587 (TLS)
            'SMTPCrypto' => $mailSetting['encryption'], // Set to 'ssl' if needed
            'mailType' => 'html', // Set email format to HTML
            'charset'  => 'utf-8',
            'wordWrap' => true,
        ];

        // Initialize the email service with configuration
        $email->initialize($config);
        // Set up email configurations (you can also define this in app/Config/Email.php)
        $email->setFrom($mailSetting['username'], $settings['logo']); // Sender's email and name
        $email->setTo($sendEmail); // Recipient email address
        $email->setSubject('OTP for ' . $settings['business_name']);
        $email->setMessage('<!doctype html>
        <html lang="en-US">
        
        <head>
            <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
            <title>OTP for ' . $settings['logo'] . '</title>
            <meta name="description" content="OTP for ' . $settings['logo'] . '">
            <style type="text/css">
                a:hover {text-decoration: underline !important;}
            </style>
        </head>
        
        <body marginheight="0" topmargin="0" marginwidth="0" style="margin: 0px; background-color: #f2f3f8;" leftmargin="0">
            <table cellspacing="0" border="0" cellpadding="0" width="100%" bgcolor="#f2f3f8"
                style="@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: "Open Sans", sans-serif;">
                <tr>
                    <td>
                        <table style="background-color: #f2f3f8; max-width:670px;  margin:0 auto;" width="100%" border="0"
                            align="center" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="height:80px;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:center;">
                                  <a href="' . base_url() . '" title="logo" target="_blank">
                                    <img width="60" src="' . base_url($settings['logo']) . '" title="' . $settings['business_name'] . '" alt="' . $settings['business_name'] . '">
                                  </a>
                                </td>
                            </tr>
                            <tr>
                                <td style="height:20px;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td>
                                    <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0"
                                        style="max-width:670px;background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);">
                                        <tr>
                                            <td style="height:40px;">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:0 35px;">
                                                <h1 style="color:#1e1e2d; font-weight:500; margin:0;font-size:32px;font-family:"Rubik",sans-serif;">Your Verification Code</h1>
                                                <span
                                                    style="display:inline-block; vertical-align:middle; margin:29px 0 26px; border-bottom:1px solid #cecece; width:100px;"></span>
                                                <p style="color:#455056; font-size:15px;line-height:24px; margin:0;">
                                                    We’re excited to have you on board! To confirm your account, please use the following One-Time Password (OTP):
                                                </p>
                                                ' . $link . '
                                                <p style="color:#455056; font-size:15px;line-height:24px; margin:0;">
                                                    For security reasons, please do not share it with anyone.
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height:40px;">&nbsp;</td>
                                        </tr>
                                    </table>
                                </td>
                            <tr>
                                <td style="height:20px;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="text-align:center;">
                                    <p style="font-size:14px; color:rgba(69, 80, 86, 0.7411764705882353); line-height:18px; margin:0 0 0;">&copy; <strong> <a href="' . base_url() . '">' . base_url() . '</a> </strong></p>
                                </td>
                            </tr>
                            <tr>
                                <td style="height:80px;">&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        
        </html>');
        $email->setMailType('html');
        $email->send();
    }

    public function signupOtp()
    {
        if($this->settings['direct_login'] == 0){
            return redirect()->to('/login');
        }

        if ((session()->has('email') && session()->get('is_email_verified') == 1) ||
            (session()->has('mobile') && session()->get('is_mobile_verified') == 1)
        ) {
            return redirect()->to('/');
        }

        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        date_default_timezone_set($this->timeZone['timezone']);
        $data['cartItemCount'] = 0;

        if ($this->request->is('get')) {
            return view('website/auth/signupOtp', $data);
        }

        if ($this->request->is('post')) {
            $dataInput = $this->request->getJSON(true);

            $dataInput = $this->request->getJSON(true);

            $userModel = new UserModel();
            $otpVerificationModel = new OtpVerificationModel();

            $existingUser = $otpVerificationModel->where('email', session()->get('email'))
                ->where('otp', $dataInput['otp'])
                ->orderBy('id', 'desc')
                ->first();

            if ($existingUser) {
                $user = $userModel->where('email', session()->get('email'))->first();

                if ($user) {
                    if ($this->settings['refer_and_earn_status'] == 1 && $user['ref_by'] > 0) {
                        $referedUser = $userModel->where('id', $user['ref_by'])->first();
                        $walletModel = new WalletModel();

                        $walletModel->insert([
                            'user_id' => $user['id'],
                            'ref_user_id' => $referedUser['id'],
                            'amount' => $this->settings['referer_earning'],
                            'closing_amount' => $this->settings['referer_earning'],
                            'date' => date("Y-m-d H:i:s"),
                            'flag' => 'credit',
                            'remark' => 'Referral Amount credited'
                        ]);

                        if ((int)$this->settings['refered_earning'] > 0) {
                            $walletModel->insert([
                                'user_id' => $referedUser['id'],
                                'amount' => $this->settings['refered_earning'],
                                'closing_amount' => $referedUser['wallet'] + $this->settings['refered_earning'],
                                'date' => date("Y-m-d H:i:s"),
                                'flag' => 'credit',
                                'remark' => 'Referred Amount credited'
                            ]);

                            $userModel->update($referedUser['id'], [
                                'wallet' => $referedUser['wallet'] + $this->settings['refered_earning'],
                            ]);
                        }

                        $userModel->update($user['id'], [
                            'wallet' => $this->settings['referer_earning'],
                        ]);
                    }

                    $userModel->update($user['id'], [
                        'is_active' => 1,
                        'is_email_verified' => 1
                    ]);



                    $cartsModel = new CartsModel();
                    $cartsModel->set('user_id', $user['id'])
                        ->where('guest_id', $dataInput['guest_id'])
                        ->update();

                    session()->remove(['mobile', 'is_mobile_verified']);
                    session()->set([
                        'email' => $user['email'],
                        'name' => $user['name'],
                        'is_email_verified' => 1,
                        'login_type' => 'email'
                    ]);

                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Email successfully verified.',
                        'data' => [
                            'email' => $user['email'],
                            'name' => $user['name'],
                            'is_email_verified' => $user['is_email_verified']
                        ]
                    ]);
                } else {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'User not found. Please try again.'
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid OTP. Please try again.'
                ]);
            }
        }
    }

    public function resetPassword()
    {
        if($this->settings['direct_login'] == 0){
            return redirect()->to('/login');
        }

        if ((session()->has('email') && session()->get('is_email_verified') == 1) || (session()->has('mobile') && session()->get('is_mobile_verified') == 1)) {
            return redirect()->to('/');
        }

        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        date_default_timezone_set($this->timeZone['timezone']);
        $data['cartItemCount'] = 0;

        // Display the form on GET request
        if ($this->request->is('get')) {
            return view('website/auth/resetPassword', $data);
        }

        if ($this->request->is('post')) {
            // Get the input data
            $dataInput = $this->request->getJSON(true);

            $userModel = new UserModel();
            $existingUser = $userModel->where('email', $dataInput['email'])->first();

            // Check if the user exists
            if (!$existingUser) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'This email is not registered.']);
            }

            // Check if the email is verified
            if ($existingUser['is_email_verified'] != 1) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'This email is not verified.']);
            }

            // Check if the account is active
            if ($existingUser['is_active'] != 1) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'This account is not active.']);
            }

            // Check if the user registered via Google login
            if ($existingUser['login_type'] == 'google') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'This email is registered via Google login.']);
            }

            // Check if the account is deleted
            if ($existingUser['is_delete'] == 1) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'This account has been deleted.']);
            }

            // If all checks pass, create the reset token
            $token = rand(1000000000, 9999999999) . password_hash(md5($existingUser['email']), PASSWORD_BCRYPT) . rand(1000000000, 9999999999);

            $updateData = [
                'password_reset_link' => $token,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $userModel->update($existingUser['id'], $updateData);

            // Send the password reset email
            $is_send = $this->sendMailPasswordResetLink($existingUser['email'], $token);

            if ($is_send) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Reset password link sent to your email.']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Unable to send password reset link. Please try again later.']);
            }
        }
    }

    private function sendMailPasswordResetLink($userEmail, $token)
    {
        $email = \Config\Services::email();
        $settings = $this->settings;
        $mailSetting = json_decode($settings['mail_config'], true); 

        $link = "<a style='background:#3E3F95;text-decoration:none !important; font-weight:500; margin-top:35px; color:#fff;text-transform:uppercase; font-size:14px;padding:10px 24px;display:inline-block;border-radius:50px;' href='" . base_url("/resetPassword/link/?token=" . $token . "&email=" . $userEmail) . "'>Click To Reset password</a>";
        $config = [
            'protocol' => 'smtp',
            'SMTPHost' => $mailSetting['host'], // Replace with your SMTP host
            'SMTPUser' => $mailSetting['username'], // Replace with your SMTP username
            'SMTPPass' => $mailSetting['password'], // Replace with your SMTP password
            'SMTPPort' => (int)$mailSetting['port'], // Common SMTP ports are 25, 465 (SSL), or 587 (TLS)
            'SMTPCrypto' => $mailSetting['encryption'], // Set to 'ssl' if needed
            'mailType' => 'html', // Set email format to HTML
            'charset'  => 'utf-8',
            'wordWrap' => true,
        ];

        // Initialize the email service with configuration
        $email->initialize($config);
        // Set up email configurations (you can also define this in app/Config/Email.php)
        $email->setFrom($mailSetting['username'], $settings['business_name']); // Sender's email and name
        $email->setTo($userEmail); // Recipient email address
        $email->setSubject($settings['business_name'] . " Password Reset Mail");
        $email->setMessage('<!doctype html>
            <html lang="en-US">
            
            <head>
                <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
                <title>Reset Password Request</title>
                <meta name="description" content="Reset Password Request">
                <style type="text/css">
                    a:hover {text-decoration: underline !important;}
                </style>
            </head>
            
            <body marginheight="0" topmargin="0" marginwidth="0" style="margin: 0px; background-color: #f2f3f8;" leftmargin="0">
                <table cellspacing="0" border="0" cellpadding="0" width="100%" bgcolor="#f2f3f8"
                    style="@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: "Open Sans", sans-serif;">
                    <tr>
                        <td>
                            <table style="background-color: #f2f3f8; max-width:670px;  margin:0 auto;" width="100%" border="0"
                                align="center" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="height:80px;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center;">
                                    <a href="' . base_url() . '" title="logo" target="_blank">
                                        <img width="60" src="' . base_url($settings['logo']) . '" title="' . $settings['business_name'] . '" alt="' . $settings['business_name'] . '">
                                    </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="height:20px;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>
                                        <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0"
                                            style="max-width:670px;background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);">
                                            <tr>
                                                <td style="height:40px;">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 35px;">
                                                    <h1 style="color:#1e1e2d; font-weight:500; margin:0;font-size:32px;font-family:"Rubik",sans-serif;">You have
                                                        requested to reset your password</h1>
                                                    <span
                                                        style="display:inline-block; vertical-align:middle; margin:29px 0 26px; border-bottom:1px solid #cecece; width:100px;"></span>
                                                    <p style="color:#455056; font-size:15px;line-height:24px; margin:0;">
                                                        We cannot simply send you your old password. A unique link to reset your
                                                        password has been generated for you. To reset your password, click the
                                                        following link and follow the instructions.
                                                    </p>
                                                    ' . $link . '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="height:40px;">&nbsp;</td>
                                            </tr>
                                        </table>
                                    </td>
                                <tr>
                                    <td style="height:20px;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center;">
                                        <p style="font-size:14px; color:rgba(69, 80, 86, 0.7411764705882353); line-height:18px; margin:0 0 0;">&copy; <strong> <a href="' . base_url() . '">' . base_url() . '</a> </strong></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="height:80px;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            
            </html>');
        $email->setMailType('html');
        return $email->send();
    }

    public function logout()
    {
        session()->remove(['email', 'name', 'is_email_verified', 'mobile', 'is_mobile_verified', 'login_type']);

        return redirect()->to('/');
    }

    public function resetPasswordLink()
    {
        if($this->settings['direct_login'] == 0){
            return redirect()->to('/login');
        }

        if ((session()->has('email') && session()->get('is_email_verified') == 1) || (session()->has('mobile') && session()->get('is_mobile_verified') == 1)) {
            return redirect()->to('/');
        }

        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        date_default_timezone_set($this->timeZone['timezone']);
        $data['cartItemCount'] = 0;

        if ($this->request->is('get')) {
            // Get the token and email from the URL
            $data['token'] = $this->request->getGet('token');
            $data['email'] = $this->request->getGet('email');

            // Check if token or email are missing
            if (!$data['token'] || !$data['email']) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid or expired link. Please request a new password reset link.']);
            }

            return view('website/auth/resetPasswordLink', $data);
        }

        if ($this->request->is('post')) {
            // Get form inputs


            $dataInput = $this->request->getJSON(true);

            $email = $dataInput['email'];
            $token = $dataInput['token'];
            $pass = $dataInput['password'];
            $cpass = $dataInput['confirmPassword'];
            // Validate password match
            if ($pass !== $cpass) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'The passwords do not match. Please re-enter the passwords.']);
            }

            // Check if the reset link token and email are valid
            $userModel = new UserModel();
            $user = $userModel->where('email', $email)->first();

            // Check if user exists and handle specific conditions
            if (!$user) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No account found with this email.']);
            }

            // Check if the account is deleted
            if ($user['is_delete'] == 1) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'This account has been deleted.']);
            }

            // Check if the account is active
            if ($user['is_active'] == 0) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'This account is inactive. Please contact support.']);
            }

            // Check if the email is verified
            if ($user['is_email_verified'] == 0) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Email not verified. Please verify your email before resetting the password.']);
            }

            // Check if the login type is Google (or any other third-party)
            if ($user['login_type'] != 'normal') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'This email is registered via Google login. Please log in with Google.']);
            }

            // Check if the password reset link token is valid
            if ($user['password_reset_link'] !== $token) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid or expired password reset link. Please request a new link.']);
            }

            // At this point, all conditions are met and the user can reset the password
            $data1 = [
                'password' => password_hash($pass, PASSWORD_BCRYPT),
                'password_reset_link' => null // Clear the token after reset
            ];

            // Update the user's password
            $userModel->set($data1)->update($user['id']);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Password changed successfully! You can now log in with your new password.']);
        }
    }

    public function mobileLogin()
    {
        if($this->settings['phone_login'] == 0){
            return redirect()->to('/login');
        }

        if ((session()->has('email') && session()->get('is_email_verified') == 1) || (session()->has('mobile') && session()->get('is_mobile_verified') == 1)) {
            return redirect()->to('/');
        }

        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        date_default_timezone_set($this->timeZone['timezone']);

        $data['cartItemCount'] = 0;
        session()->remove(['email', 'name', 'is_email_verified', 'mobile', 'is_mobile_verified', 'login_type']);

        // Display the form on GET request
        if ($this->request->is('get')) {
            if ($this->settings['phone_login'] == 1) {
                return view('website/auth/mobileLogin', $data);
            } else {
                return redirect()->to('/');
            }
        }

        // Handle form submission on POST request
        if ($this->request->is('post')) {
            $dataInput = $this->request->getJSON(true);

            // Validation for required fields
            $validationRules = [
                'mobile' => 'required|regex_match[/^[0-9]{' . $this->country['validation_no'] . '}$/]',
            ];

            if (!$this->validate($validationRules)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Validation failed: ' . implode(', ', $this->validator->getErrors())
                ]);
            }

            $userModel = new UserModel();
            $otpVerificationModel = new OtpVerificationModel();

            // Check if the email already exists
            $existingUser = $userModel->where('mobile', $dataInput['mobile'])->first();
            if ($existingUser) {
                if ($existingUser['is_delete'] == 1) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Account has been deleted.']);
                }

                session()->set('mobile', $dataInput['mobile']); // Store mobile in session
                session()->set('login_type', 'mobile'); // Store email in session

                $otp = random_int(100000, 999999);

                $otpData = [
                    'mobile' => $dataInput['mobile'],
                    'otp' => $otp,
                    'verify_by' => 'mobile',
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $otpVerificationModel->insert($otpData);

                $smsGatewayModel = new SmsGatewayModel();
                $smsGateway = $smsGatewayModel->where('is_active', 1)->first();

                if ($smsGateway['id'] == 1) {
                    return $this->twilio($smsGateway['value'], $otp, $dataInput['mobile']);
                } else if ($smsGateway['id'] == 2) {
                    return $this->nexmo($smsGateway['value'], $otp, $dataInput['mobile']);
                } else if ($smsGateway['id'] == 3) {
                    return $this->twoFactor($smsGateway['value'], $otp, $dataInput['mobile']);
                } elseif ($smsGateway['id'] == 4) {
                    return $this->msg91($smsGateway['value'], $otp, $dataInput['mobile']);
                } elseif ($smsGateway['id'] == 5) {
                    return $this->fast2Sms($smsGateway['value'], $otp, $dataInput['mobile']);
                } else {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to load SMS Setting']);
                }

                // return $this->response->setJSON(['status' => 'success', 'message' => 'OTP sent to registered Mobile']);
            }

            // If no existing user, create a new user
            $random_char = '';
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            for ($i = 0; $i < 4; $i++) {
                $random_char .= $characters[rand(0, strlen($characters) - 1)];
            }

            $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $random_char); // Remove spaces & special characters
            $refCode = strtoupper(substr($cleanName, 0, 4)) . strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));

            $userData = [
                'country_code' => $this->country['country_code'],
                'mobile' => $dataInput['mobile'],
                'login_type' => 'mobile',
                'ref_code' => $refCode,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            // Insert into database
            if ($userModel->insert($userData)) {
                $userId = $userModel->insertID();
                $walletModel = new WalletModel();
                $walletModel->insert(['user_id' => $userId, 'amount' => 0, 'closing_amount' => 0, 'date' => date("Y-m-d H:i:s")]);
                session()->set('mobile', $dataInput['mobile']); // Store email in session
                session()->set('login_type', 'mobile'); // Store email in session

                $otp = random_int(100000, 999999);
                $otpData = [
                    'mobile' => $dataInput['mobile'],
                    'otp' => $otp,
                    'verify_by' => 'mobile'
                ];
                $otpVerificationModel->insert($otpData);

                $smsGatewayModel = new SmsGatewayModel();
                $smsGateway = $smsGatewayModel->where('is_active', 1)->first();

                if ($smsGateway['id'] == 1) {
                    return $this->twilio($smsGateway['value'], $otp, $dataInput['mobile']);
                } else if ($smsGateway['id'] == 2) {
                    return $this->nexmo($smsGateway['value'], $otp, $dataInput['mobile']);
                } else if ($smsGateway['id'] == 3) {
                    return $this->twoFactor($smsGateway['value'], $otp, $dataInput['mobile']);
                } elseif ($smsGateway['id'] == 4) {
                    return $this->msg91($smsGateway['value'], $otp, $dataInput['mobile']);
                } elseif ($smsGateway['id'] == 5) {
                    return $this->fast2Sms($smsGateway['value'], $otp, $dataInput['mobile']);
                } else {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to load SMS Setting']);
                }

                // return $this->response->setJSON(['status' => 'success', 'message' => 'OTP sent to registered Mobile']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to create account. Please try again later.']);
            }
        }
    }

    public function mobileOtp()
    {
        if($this->settings['phone_login'] == 0){
            return redirect()->to('/login');
        }

        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        date_default_timezone_set($this->timeZone['timezone']);

        $data['cartItemCount'] = 0;

        $userModel = new UserModel();
        $user = $userModel->where('mobile', session()->get('mobile'))->first();


        // Show form on GET request
        if (
            $this->request->is('get') &&
            $this->settings['phone_login'] == 1 &&
            session()->has('mobile') &&
            session()->get('login_type') == 'mobile'
        ) {
            $data['is_mobile_verified'] = $user['is_mobile_verified'];
            $data['is_active'] = $user['is_active'];
            return view('website/auth/mobileOtp', $data);
        }

        // Handle POST (OTP Verification)
        if ($this->request->is('post')) {
            $dataInput = $this->request->getJSON(true);

            // Validation Rules
            $validationRules = [
                'otp' => 'required|trim|exact_length[6]',
            ];

            // Name required only if referral is off and mobile is not verified
            if ($this->settings['refer_and_earn_status'] == 0 && $user['is_mobile_verified'] == 0) {
                $validationRules['name'] = 'required';
            }

            // If referral is entered, validate it
            if (!empty($dataInput['referal'])) {
                $validationRules['referal'] = 'trim|exact_length[8]';
            }

            if (!$this->validate($validationRules)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Validation failed: ' . implode(', ', $this->validator->getErrors()),
                ]);
            }

            // Check OTP
            $otpVerificationModel = new OtpVerificationModel();
            $existingOtp = $otpVerificationModel
                ->where('mobile', session()->get('mobile'))
                ->where('otp', $dataInput['otp'])
                ->orderBy('id', 'desc')
                ->first();

            if (!$existingOtp) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid OTP. Please try again.'
                ]);
            }

            // If referral is entered, validate referral code
            $referedUser = null;
            if (!empty($dataInput['referal'])) {
                $referedUser = $userModel->where('ref_code', $dataInput['referal'])->first();

                if (!$referedUser) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Referal code you used is not valid.'
                    ]);
                }
            }

            // Update user data after OTP verification
            $updateData = [
                'is_active' => 1,
                'is_mobile_verified' => 1
            ];

            // Update name if provided and user is not verified yet
            if (!empty($dataInput['name']) && $user['is_mobile_verified'] == 0) {
                $updateData['name'] = $dataInput['name'];
            }

            // If referral active and valid, assign ref_by and wallet
            if (
                $user['is_mobile_verified'] == 0 &&
                $this->settings['refer_and_earn_status'] == 1 &&
                $referedUser
            ) {
                $updateData['ref_by'] = $referedUser['id'];
                $updateData['wallet'] = $this->settings['referer_earning'];

                // Log wallet entry
                $walletModel = new WalletModel();
                $walletModel->insert([
                    'user_id' => $user['id'],
                    'amount' => 0,
                    'closing_amount' => 0,
                    'date' => date("Y-m-d H:i:s")
                ]);
            }

            $userModel->update($user['id'], $updateData);

            // Update session
            session()->set([
                'mobile' => $user['mobile'],
                'name' => $dataInput['name'] ?? $user['name'],
                'is_mobile_verified' => 1
            ]);
            session()->set('login_type', 'mobile');
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Mobile successfully verified.',
                'data' => [
                    'mobile' => $user['mobile'],
                    'name' => $dataInput['name'] ?? $user['name'],
                    'is_mobile_verified' => 1
                ]
            ]);
        }
    }

    private function twilio($smsSetting, $otp, $mobile)
    {
        // Start with basic validation
        if (empty($smsSetting) || empty($otp) || empty($mobile)) {
            log_message('error', 'Twilio: Missing required parameters');
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Missing required parameters for SMS'
            ]);
        }
        
        try {
            // Parse SMS settings
            $settings = json_decode($smsSetting, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'Twilio: Invalid SMS settings JSON: ' . json_last_error_msg());
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid SMS gateway configuration'
                ]);
            }
            
            // Extract Twilio credentials
            $accountSid = $settings['accountSid'] ?? null;
            $authToken = $settings['authToken'] ?? null;
            $twilioNumber = $settings['twilioNumber'] ?? null;
            $messageTemplate = $settings['message'] ?? 'Your OTP is #OTP#';
            
            // Validate required Twilio credentials
            if (empty($accountSid) || empty($authToken) || empty($twilioNumber)) {
                log_message('error', 'Twilio: Missing Twilio configuration');
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Missing Twilio configuration'
                ]);
            }
            
            // Format phone number with country code
            $mobile = $this->country['country_code'] . $mobile;
            log_message('debug', 'Twilio: Sending to formatted number: ' . $mobile);
            
            // Prepare the message
            $message = str_replace('#OTP#', $otp, $messageTemplate);
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";
            
            // Prepare request data
            $postData = [
                'To' => $mobile,
                'From' => $twilioNumber,
                'Body' => $message
            ];
            
            // Initialize curl and set options
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($postData),
                CURLOPT_USERPWD => "{$accountSid}:{$authToken}",
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            
            // Execute the request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            // Debug log the raw response
            log_message('debug', 'Twilio API Response: ' . $response);
            log_message('debug', 'Twilio HTTP Code: ' . $httpCode);
            
            // Check for curl execution errors
            if ($error) {
                curl_close($ch);
                log_message('error', 'Twilio: cURL error: ' . $error);
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to connect to SMS gateway: ' . $error
                ]);
            }
            
            curl_close($ch);
            
            // Process the response
            $result = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'Twilio: Invalid JSON response: ' . json_last_error_msg());
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid response from SMS gateway'
                ]);
            }
            
            // Check for Twilio API errors
            if ($httpCode < 200 || $httpCode >= 300 || isset($result['error_code'])) {
                $errorMsg = $result['message'] ?? $result['error_message'] ?? 'Unknown error';
                $errorCode = $result['code'] ?? $result['error_code'] ?? 'unknown';
                
                log_message('error', "Twilio API Error: {$errorMsg} (Code: {$errorCode})");
                
                return [
                    'status' => 'error',
                    'message' => "Failed to send SMS: {$errorMsg}"
                ];
            }
            
            // Success!
            log_message('info', 'Twilio: SMS sent successfully. Message SID: ' . ($result['sid'] ?? 'unknown'));
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'OTP sent to your mobile number',
                'sid' => $result['sid'] ?? null
            ]);
        } catch (\Exception $e) {
            // Catch any exceptions that might occur
            log_message('error', 'Twilio exception: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error processing SMS request: ' . $e->getMessage()
            ]);
        }
    }
    private function nexmo($smsSetting, $otp, $mobile)
    {
        // Parse settings from JSON
        $settings = json_decode($smsSetting, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid SMS gateway configuration'
            ]);
        }

        // Extract settings with validation
        $vonageApiKey = $settings['vonageApiKey'] ?? null;
        $vonageApiSecret = $settings['vonageApiSecret'] ?? null;
        $smsSenderId = $settings['smsSenderId'] ?? null;

        // Validate required settings
        if (empty($vonageApiKey) || empty($vonageApiSecret) || empty($smsSenderId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Missing Vonage API credentials'
            ]);
        }

        // Prepare message
        $messageText = $settings['messageText'] ?? 'Your OTP is #OTP#';
        $message = str_replace('#OTP#', $otp, $messageText);

        // Format phone number
        $countryCode = ltrim($this->country['country_code'], '+');
        $formattedMobile = $countryCode . $mobile;

        // Initialize curl
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://rest.nexmo.com/sms/json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'from' => $smsSenderId,
                'to' => $formattedMobile,
                'text' => $message,
                'api_key' => $vonageApiKey,
                'api_secret' => $vonageApiSecret
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Check for curl execution errors
        if ($error) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to connect to Vonage API: ' . $error
            ]);
        }

        // Parse the response
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid JSON response from Vonage API'
            ]);
        }

        // Check for API errors - Vonage returns status codes in each message
        if ($httpCode !== 200 || !isset($result['messages']) || empty($result['messages'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No response from Vonage API'
            ]);
        }

        // Check the status of the first message
        $messageStatus = $result['messages'][0]['status'] ?? null;
        if ($messageStatus != '0') { // Vonage uses '0' for success
            $errorMsg = $result['messages'][0]['error-text'] ?? 'Unknown error';

            // Log the error for debugging
            log_message('error', "Vonage API Error: " . json_encode($result['messages'][0]));

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to send OTP: ' . $errorMsg
            ]);
        }

        // Success response
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'OTP sent to registered mobile',
            'message_id' => $result['messages'][0]['message-id'] ?? null
        ]);
    }
    private function twoFactor($smsSetting, $otp, $mobile)
    {
        // Validate inputs
        if (empty($smsSetting) || empty($otp) || empty($mobile)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Missing required parameters for SMS'
            ]);
        }

        // Parse settings
        $settings = json_decode($smsSetting, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid SMS gateway configuration'
            ]);
        }

        // Extract API key with validation
        $apiKey = $settings['apiKey'] ?? null;
        if (empty($apiKey)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Missing 2Factor API key'
            ]);
        }

        // Get OTP template name or use default
        $otp_template_name = $settings['otp_template_name'] ?? 'OTP1';

        // Format phone number with country code
        $formattedMobile = $this->country['country_code'] . $mobile;

        // Prepare API URL 
        $apiUrl = "https://2factor.in/API/V1/{$apiKey}/SMS/{$formattedMobile}/{$otp}/{$otp_template_name}";

        // Initialize curl
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        // Execute the request
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        // Check for curl execution errors
        if ($curlError) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to connect to 2Factor API: ' . $curlError
            ]);
        }

        // Parse API response
        $apiResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid response from 2Factor API'
            ]);
        }

        // Check for API errors
        if (empty($apiResponse['Status']) || $apiResponse['Status'] !== 'Success') {
            $errorMsg = $apiResponse['Details'] ?? 'Unknown error';

            // Log the error for debugging
            log_message('error', "2Factor API Error: {$errorMsg}");

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to send OTP: ' . $errorMsg
            ]);
        }

        // Success response
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'OTP sent to registered mobile',
            'session_id' => $apiResponse['Details'] ?? null // 2Factor sometimes provides a session ID
        ]);
    }
    private function msg91($smsSetting, $otp, $mobile)
    {
        // Parse settings from JSON
        $settings = json_decode($smsSetting, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid SMS gateway configuration'
            ]);
        }

        // Extract settings with validation
        $authKey = $settings['authKey'] ?? '';
        if (empty($authKey)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Missing MSG91 authentication key'
            ]);
        }

        // Get template ID or use default
        $otpTemplateId = $settings['otpTemplateId'] ?? 'OTP1';

        // Format country code (remove + if present)
        $countryCode = ltrim($this->country['country_code'], '+');

        // Format phone number
        $formattedMobile = $countryCode . $mobile;

        // Prepare API URL
        $apiUrl = "https://control.msg91.com/api/v5/otp";

        // Prepare request parameters
        $params = [
            'otp' => $otp,
            'otp_length' => 6,
            'template_id' => $otpTemplateId,
            'mobile' => $formattedMobile,
            'authkey' => $authKey
        ];

        // Initialize curl
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl . '?' . http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        // Execute the request
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        // Check for curl execution errors
        if ($curlError) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to connect to MSG91 API: ' . $curlError
            ]);
        }

        // Parse API response
        $apiResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid response from MSG91 API'
            ]);
        }

        // Check for API errors
        if ($httpCode !== 200 || empty($apiResponse['type']) || $apiResponse['type'] !== 'success') {
            $errorMsg = $apiResponse['message'] ?? 'Unknown error';

            // Log the error for debugging
            log_message('error', "MSG91 API Error: " . json_encode($apiResponse));

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to send OTP: ' . $errorMsg
            ]);
        }

        // Success response
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'OTP sent to registered mobile',
            'request_id' => $apiResponse['request_id'] ?? null
        ]);
    }
    private function fast2Sms($smsSetting, $otp, $mobile)
    {
        // Parse settings from JSON
        $settings = json_decode($smsSetting, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid SMS gateway configuration'
            ]);
        }

        // Extract settings with validation
        $apiKey = $settings['apiKey'] ?? '';
        if (empty($apiKey)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Missing Fast2SMS API key'
            ]);
        }

        // Get sender ID and message ID from settings or use defaults
        $senderId = $settings['sender_id'] ?? 'TXTIND';
        $messageId = $settings['message_id'] ?? '1234567890';

        // Prepare request data
        $postData = [
            "sender_id" => $senderId,
            "message" => $messageId,
            'variables_values' => $otp,
            'route' => 'dlt',
            'numbers' => $mobile
        ];

        // Initialize curl
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 2,  // Enable proper SSL verification
            CURLOPT_SSL_VERIFYPEER => true,  // Enable proper SSL verification
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => [
                "authorization: $apiKey",
                "accept: application/json",
                "cache-control: no-cache",
                "content-type: application/json"
            ],
        ]);

        // Execute the request
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        // Check for curl execution errors
        if ($error) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to connect to Fast2SMS API: ' . $error
            ]);
        }

        // Parse API response
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid JSON response from Fast2SMS API'
            ]);
        }

        // Check for API errors
        if ($httpCode !== 200 || !isset($result['return']) || $result['return'] !== true) {
            $errorMsg = $result['message'] ?? 'Unknown error';

            // Log the error for debugging
            log_message('error', "Fast2SMS API Error: " . json_encode($result));

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to send OTP: ' . $errorMsg
            ]);
        }

        // Success response
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'OTP sent to registered mobile',
            'request_id' => $result['request_id'] ?? null
        ]);
    }
}
