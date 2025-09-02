<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\AdminModel;
use App\Models\SettingsModel;

helper('form');

class Auth extends BaseController
{
    public function login()
    {
        $session = session();
        if ($session->has('user_id')  && session('account_type') == 'Admin') {
            return redirect()->to('admin/dashboard');
        } elseif ($session->has('user_id') && session('account_type') == 'Admin' && session('account_type') == 'Franchise') {
            return redirect()->to('admin/dashboard');
        } else {
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            return view('login', $data);
        }
    }

    public function processLogin()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $validationRules = [
            'email' => 'required',
            'password' => 'required'
        ];

        $recaptcha_response = $this->request->getPost('recaptcha_token');
        $settingModel = new SettingsModel();
        $settings = $settingModel->getSettings();

        if ($settings['google_recaptcha_status'] == 1) {
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data1 = array(
                'secret' => $settings['google_recaptcha_secret_key'],
                'response' => $recaptcha_response
            );

            $options = array(
                'http' => array(
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data1)
                )
            );

            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            $result_array = json_decode($result, true);
        } else {
            $result_array['success'] = true;
        }
        if ($result_array['success']) {


            if (!$this->validate($validationRules)) {
                return redirect()->to('admin/auth/login')->withInput()->with('errors', $this->validator->getErrors());
            }
            $adminModel = new AdminModel();
            $user = $adminModel->where('username', $email)->first();

            if ($user && password_verify($password, $user['password'])) {
                $session = session();
                $session->set('user_id', $user['id']);
                $session->set('user_name', $user['fname'] . " " . $user['lname']);
                $session->set('user_email', $user['username']);
                $session->set('account_type', 'Admin');
                $session->set('role_id', $user['role_id']);

                return redirect()->to('admin/dashboard');
            } else {
                return redirect()->to('admin/auth/login')->with('error', 'Invalid email or password');
            }
        } else {
            return redirect()->to('/admin/auth/login')->withInput()->with('error', 'Recaptcha failed reload page');
        }
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to('admin/auth/login');
    }

    public function sendLink()
    {
        $email = $this->request->getPost('email');
        $validationRules = [
            'email' => 'required',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->to('admin/auth/login')->withInput()->with('errors', $this->validator->getErrors());
        }

        


        $settingModel = new SettingsModel();
        $settings = $settingModel->getSettings();

        if ($settings['google_recaptcha_status'] == 1) {
            $recaptcha_response = $this->request->getPost('recaptcha_token');
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data1 = array(
                'secret' => $settings['google_recaptcha_secret_key'],
                'response' => $recaptcha_response
            );

            $options = array(
                'http' => array(
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data1)
                )
            );

            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            $result_array = json_decode($result, true);
        } else {
            $result_array['success'] = true;
        }
        if ($result_array['success']) {
            $adminModel = new AdminModel();
            $admin = $adminModel->where('username', $email)->first();
            if (isset($admin['username'])) {

                // Load email service
                $email = \Config\Services::email();

                $token = rand(0000000000, 9999999999999) . password_hash(md5($admin['username']), PASSWORD_BCRYPT) . rand(0000000000, 9999999999999);
                $expFormat = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y"));
                $expDate = date("Y-m-d H:i:s", $expFormat);
                $data = [
                    'reset_link_token' => $token,
                    'reset_token_exp_date' => $expDate
                ];
                $adminModel->set($data)->update($admin['id']);
                $mail_setting = json_decode($settings['mail_config'], true);
                $link = "<a style='background:#3E3F95;text-decoration:none !important; font-weight:500; margin-top:35px; color:#fff;text-transform:uppercase; font-size:14px;padding:10px 24px;display:inline-block;border-radius:50px;' href='" . base_url() . "admin/auth/forgot-password/link/?token=" . $token . "&email=" . $admin['username'] . "'>Click To Reset password</a>";
                $config = [
                    'protocol' => 'smtp',
                    'SMTPHost' => $mail_setting['host'], // Replace with your SMTP host
                    'SMTPUser' => $mail_setting['username'], // Replace with your SMTP username
                    'SMTPPass' => $mail_setting['password'], // Replace with your SMTP password
                    'SMTPPort' => (int)$mail_setting['port'], // Common SMTP ports are 25, 465 (SSL), or 587 (TLS)
                    'SMTPCrypto' => $mail_setting['encryption'], // Set to 'ssl' if needed
                    'mailType' => 'html', // Set email format to HTML
                    'charset'  => 'utf-8',
                    'wordWrap' => true,
                ];

                // Initialize the email service with configuration
                $email->initialize($config);
                // Set up email configurations (you can also define this in app/Config/Email.php)
                $email->setFrom($mail_setting['username'], $mail_setting['name']); // Sender's email and name
                $email->setTo($admin['username']); // Recipient email address
                $email->setSubject($settings['business_name'] . " Password Reset Mail");
                $email->setMessage('<!doctype html>
                <html lang="en-US">
                
                <head>
                    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
                    <title>Reset Password Request</title>
                    <meta name="description" content="Reset Password Email Template.">
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
                $email->setMailType('html'); // This sets the message type to HTML
                // Optional: Add attachments if needed

                // Sending email and checking the result
                if ($email->send()) {
                    $output['success'] = true;
                    $output['message'] = "Password reset link sent on mail";
                } else {
                    // Print debug information if sending fails
                    $errorInfo = $email->printDebugger(['headers']);

                    $output['success'] = false;
                    $output['message'] = 'Email failed to send: ' . print_r($errorInfo, true);
                }
            } else {
                $output['success'] = false;
                $output['message'] = "Entered mail id is not found";
            }
        } else {
            $output['success'] = false;
            $output['message'] = "Recaptcha failed! Reload page";
        }
        return $this->response->setJSON($output);
    }
    public function resetPasswordLink()
    {

        $session = session();

        if ($session->has('user_id')  && session('account_type') == 'Admin') {
            return redirect()->to('admin/dashboard');
        } elseif ($session->has('user_id') && session('account_type') == 'Seller') {
            return redirect()->to('seller/dashboard');
        } else {
            $email = $this->request->getGet('email');
            $token = $this->request->getGet('token');

            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $data['email'] = $email;
            $data['token'] = $token;
            $adminModel = new AdminModel();
            $user = $adminModel->where('username', $email)->where('reset_link_token', $token)->first();
            $date = date("Y-m-d H:i:s", time());
            if ($user['reset_token_exp_date'] > $date) {
                return view('resetPassword', $data);
            } else {
                return 'Link has been Expired.';
            }
        }
    }
    public function resetPassword()
    {
        $session = session();

        if ($session->has('user_id')  && session('account_type') == 'Admin') {
            return redirect()->to('admin/dashboard');
        } elseif ($session->has('user_id') && session('account_type') == 'Seller') {
            return redirect()->to('seller/dashboard');
        } else {
            $email = $this->request->getPost('email');
            $token = $this->request->getPost('token');
            $pass = $this->request->getPost('pass');
            $cpass = $this->request->getPost('cpass');
            $recaptcha_response = $this->request->getPost('recaptcha_token');
            $settingModel = new SettingsModel();
            $settings = $settingModel->getSettings();

            if ($settings['google_recaptcha_status'] == 1) {
                $url = 'https://www.google.com/recaptcha/api/siteverify';
                $data1 = array(
                    'secret' => $settings['google_recaptcha_secret_key'],
                    'response' => $recaptcha_response
                );

                $options = array(
                    'http' => array(
                        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method' => 'POST',
                        'content' => http_build_query($data1)
                    )
                );

                $context = stream_context_create($options);
                $result = file_get_contents($url, false, $context);
                $result_array = json_decode($result, true);
            } else {
                $result_array['success'] = true;
            }
            if ($result_array['success']) {
                $adminModel = new AdminModel();
                $user = $adminModel->where('username', $email)->where('reset_link_token', $token)->first();
                $date = date("Y-m-d H:i:s", time());
                if ($user['reset_token_exp_date'] > $date) {
                    if ($pass == $cpass) {
                        $data1 = [
                            'password' => password_hash($pass, PASSWORD_BCRYPT),
                            'reset_link_token' => '',
                            'reset_token_exp_date' => ''
                        ];
                        $adminModel->set($data1)->update($user['id']);
                        $output['success'] = true;
                        $output['message'] = 'Password changed successfully';
                    } else {
                        $output['success'] = false;
                        $output['message'] = 'Password not matched';
                    }
                } else {
                    $output['success'] = false;
                    $output['message'] = 'Link is expired';
                }
            } else {
                $output['success'] = false;
                $output['message'] = "Recaptcha failed! Reload page";
            }
            return $this->response->setJSON($output);
        }
    }

    public function permissionNotAllowed()
    {
        $session = session();
        if ($session->has('user_id')  && session('account_type') == 'Admin') {
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            return view('permissionNotAllowed', $data);
        }
    }
}
