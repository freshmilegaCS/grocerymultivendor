<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\CountryModel;
use App\Models\LanguageModel;
use App\Models\SettingsModel;
use App\Models\SmsGatewayModel;
use App\Models\TimeZoneModel;


class Setting extends BaseController
{
    function index()
    {

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('setting')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $settingModel = new SettingsModel();
        $data['settings'] = $settingModel->getSettings();

        $country = new CountryModel();
        $data['country'] = $country->findAll();
        $timezone = new TimeZoneModel();
        $languageModel = new LanguageModel();
        $data['timezone'] = $timezone->findAll();
        $data['languages'] = $languageModel->findAll();

        return view('/setting', $data);
    }
    function storeIndex()
    {

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('setting')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $settingModel = new SettingsModel();
        $data['settings'] = $settingModel->getSettings();

        $country = new CountryModel();
        $data['country'] = $country->findAll();
        $timezone = new TimeZoneModel();
        $data['timezone'] = $timezone->findAll();



        return view('/storeSetting', $data);
    }

    public function countrySetting()
    {
        $output = [
            'success' => false,
            'message' => ''
        ];
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            $output = ['success' => false, "message" => "Session expired! Please login again."];
            return $this->response->setJSON($output);
        }
        if (!can_edit('setting')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }


        $country = $this->request->getPost('country');
        $timezone = $this->request->getPost('timezone');

        $countryModel = new CountryModel();
        $countryModel->setActiveCountry($country);

        $timeZoneModel = new TimeZoneModel();
        $update = $timeZoneModel->setActiveTimeZone($timezone);

        if ($update) {
            $output['success'] = true;
            $output['message'] = 'Setting updated successfully';
        } else {
            $output['success'] = false;
            $output['message'] = "Error while updating the settings";
        }

        return json_encode($output);
    }
    public function updateSetting()
    {
        $output = [
            'success' => false,
            'message' => ''
        ];
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            $output = ['success' => false, "message" => "Session expired! Please login again."];
            return $this->response->setJSON($output);
        }
        if (!can_edit('setting')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $settingModel = new SettingsModel();
        $settings = $settingModel->getSettings();
        if ($settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $postData = $this->request->getPost();

        // Loop through each key-value pair to update settings
        foreach ($postData as $key => $value) {
            if ($key !== 'logo' || $key !== 'main_header_banner_img') {
                $settingModel->where('key', $key)
                    ->set(['value' => $value])
                    ->update();
            }
        }
        // Handle file upload
        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/logo/', $newName);

            // Save file path to the database
            $db_file_path = 'uploads/logo/' . $newName;
            $settingModel->where('key', 'logo')
                ->set(['value' => $db_file_path])
                ->update();
        }
        
        $file = $this->request->getFile('main_header_banner_img');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/main_header_banner_img/', $newName);

            // Save file path to the database
            $db_file_path = 'uploads/main_header_banner_img/' . $newName;
            $settingModel->where('key', 'main_header_banner_img')
                ->set(['value' => $db_file_path])
                ->update();
        }


        $output['success'] = true;
        $output['message'] = 'Setting updated successfully';


        return json_encode($output);
    }


    function customerAppPolicy()
    {

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('customer-app-policy')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $settingModel = new SettingsModel();
        $data['settings'] = $settingModel->getSettings();



        return view('/customerPolicy', $data);
    }
    function deliveryAppPolicy()
    {

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('delivery-app-policy')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $settingModel = new SettingsModel();
        $data['settings'] = $settingModel->getSettings();
        return view('/deliveryBoyPolicy', $data);
    }

    public function updateCustomerAppPolicy()
    {
        $output = [
            'success' => false,
            'message' => ''
        ];
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            $output = ['success' => false, "message" => "Session expired! Please login again."];
            return $this->response->setJSON($output);
        }
        if (!can_edit('customer-app-policy')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $postData = $this->request->getPost();

        // Loop through each key-value pair to update settings
        $settingModel = new SettingsModel();
        foreach ($postData as $key => $value) {
            if ($key !== 'logo') {
                $settingModel->where('key', $key)
                    ->set(['value' => $value])
                    ->update();
            }
        }



        $output['success'] = true;
        $output['message'] = 'Setting updated successfully';


        return json_encode($output);
    }

    public function updateDeliveryAppPolicy()
    {
        $output = [
            'success' => false,
            'message' => ''
        ];
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            $output = ['success' => false, "message" => "Session expired! Please login again."];
            return $this->response->setJSON($output);
        }
        if (!can_edit('delivery-app-policy')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $postData = $this->request->getPost();

        // Loop through each key-value pair to update settings
        $settingModel = new SettingsModel();
        foreach ($postData as $key => $value) {
            if ($key !== 'logo') {
                $settingModel->where('key', $key)
                    ->set(['value' => $value])
                    ->update();
            }
        }



        $output['success'] = true;
        $output['message'] = 'Setting updated successfully';


        return json_encode($output);
    }
    public function mailTest()
    {
        $output = [
            'success' => false,
            'message' => ''
        ];
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            $output = ['success' => false, "message" => "Session expired! Please login again."];
            return $this->response->setJSON($output);
        }
        $test_mail_id = $this->request->getPost('test_mail_id');


        // Loop through each key-value pair to update settings
        $settingModel = new SettingsModel();
        $settings = $settingModel->getSettings();


        $email = \Config\Services::email();

        $mail_setting = json_decode($settings['mail_config'], true);
        $config = [
            'protocol' => 'smtp',
            'SMTPHost' => $mail_setting['host'], // Replace with your SMTP host
            'SMTPUser' => $mail_setting['username'], // Replace with your SMTP username
            'SMTPPass' => $mail_setting['password'], // Replace with your SMTP password
            'SMTPPort' => (int) $mail_setting['port'], // Common SMTP ports are 25, 465 (SSL), or 587 (TLS)
            'SMTPCrypto' => $mail_setting['encryption'], // Set to 'ssl' if needed
            'mailType' => 'html', // Set email format to HTML
            'charset'  => 'utf-8',
            'wordWrap' => true,
        ];

        // Initialize the email service with configuration
        $email->initialize($config);
        // Set up email configurations (you can also define this in app/Config/Email.php)
        $email->setFrom($mail_setting['username'], $mail_setting['name']); // Sender's email and name
        $email->setTo($test_mail_id); // Recipient email address
        $email->setSubject($settings['business_name'] . "SMTP test mail");
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
                                                    <h1 style="color:#1e1e2d; font-weight:500; margin:0;font-size:32px;font-family:"Rubik",sans-serif;">Test SMTP Mail</h1>
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
            $output['message'] = "Test mail send successfully";
        } else {
            // Print debug information if sending fails
            $errorInfo = $email->printDebugger(['headers']);

            $output['success'] = false;
            $output['message'] = 'Email failed to send: ' . print_r($errorInfo, true);
        }
        return $this->response->setJSON($output);
    }

    function smsGateway()
    {

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('setting')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $settingModel = new SettingsModel();
        $data['settings'] = $settingModel->getSettings();
        $smsGatewayModel = new SmsGatewayModel();
        $smsGateways = $smsGatewayModel->getAllSMSGateway();

        return view('smsGateway', [
            'settings' => $settingModel->getSettings(),
            'smsGateways' => $smsGateways
        ]);



        return view('/setting', $data);
    }

    function updateSmsGateway()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {

            if (!can_edit('payment-setting')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $smsGatewayModel = new SmsGatewayModel();

            $id = $this->request->getPost('sms_gateway_id');
            $isActive = $this->request->getPost('is_active');

            // Collect all dynamic fields
            $postData = $this->request->getPost();
            unset($postData['sms_gateway_id'], $postData['is_active'], $postData['submit']); // Remove unwanted fields

            $jsonValue = json_encode($postData);

            if ($isActive == 1) {
                $smsGatewayModel->set([
                    'is_active' => 0
                ])->where('is_active', 1)->update();
            }
            $updateData = [
                'value' => $jsonValue,
                'is_active' => $isActive
            ];

            $success =  $smsGatewayModel->update($id, $updateData);





            if ($success) {
                return redirect()->to('admin/sms-gateway')->with('success', 'SMS setting updated successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to update.');
            }
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function languageList()
    {
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_edit('setting')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $languageModel = new LanguageModel();

        $languages = $languageModel->findAll();

        $data = [];
        foreach ($languages as $lang) {
            $data[] = [
                $lang['id'],
                $lang['language'] . ' (' . $lang['lang_short'] . ')',
                $lang['is_default'] ? "<span class='badge badge-success'>Yes</span>" : "<span class='badge badge-danger'>No</span>",
                $lang['is_rtl'] ? "<span class='badge badge-success'>Yes</span>" : "<span class='badge badge-danger'>No</span>",
                $lang['is_active'] ? "<span class='badge badge-success'>Active</span>" : "<span class='badge badge-danger'>Inactive</span>",
                "<button type='button' class='btn btn-primary-light btn-sm' onclick='makeLanguageDefault(" . $lang['id'] . ")'>Make Default</button>
             <button type='button' class='btn btn-danger-light btn-sm' onclick='changeStatus(" . $lang['id'] . ")'>Change Status</button>"
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function makeDefaultLanguage()
    {

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('setting')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $id = $this->request->getPost('id');
        $languageModel = new LanguageModel();

        if ($languageModel->updateDefault($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Default language updated successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to update default language']);
    }

    public function toggleLanguageStatus()
    {
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('setting')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $id = $this->request->getPost('id');
        $languageModel = new LanguageModel();
        $language = $languageModel->find($id);

        if ($language) {
            $newStatus = $language['is_active'] ? 0 : 1;
            $languageModel->update($id, ['is_active' => $newStatus]);
            return $this->response->setJSON(['success' => true, 'message' => 'Language status updated']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Language not found']);
    }
}
