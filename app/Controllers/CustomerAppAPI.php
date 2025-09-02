<?php

namespace App\Controllers;

use App\Libraries\CartSummery;
use App\Models\AddressModel;
use App\Models\BannerModel;
use App\Models\BrandModel;
use App\Models\CategoryModel;
use App\Models\CouponModel;
use App\Models\HomeSectionModel;
use App\Models\NotificationModel;
use App\Models\OrderModel;
use App\Models\OtpVerificationModel;
use App\Models\PaymentMethodModel;
use App\Models\ProductModel;
use App\Models\SubcategoryModel;
use App\Models\TimeslotModel;
use App\Models\UsedCouponModel;
use App\Models\UserModel;
use App\Models\WalletModel;
use CodeIgniter\API\ResponseTrait;
use App\Models\CountryModel;
use App\Models\OrderProductModel;
use App\Models\OrderReturnRequestModel;
use App\Models\OrderStatusesModel;
use App\Models\OrderStatusListsModel;
use App\Models\SellerModel;
use App\Models\DeliverableAreaModel;
use App\Models\ProductImagesModel;
use App\Models\ProductRatingsModel;
use App\Models\ProductVariantsModel;
use App\Models\TaxModel;

use App\Libraries\GeoUtils;
use App\Models\CartsModel;
use App\Models\CityModel;
use App\Models\DeviceTokenModel;
use App\Models\FaqsModel;
use App\Models\HighlightsModel;
use App\Models\ProductSortTypeModel;
use App\Models\ProductTagModel;
use App\Models\TagsModel;
use App\Models\SmsGatewayModel;
use App\Models\DeliveryTrackingModel;
use App\Models\LanguageModel;
use App\Models\HeaderCategoryModel;
use App\Models\CategoryGroupModel;

use Razorpay\Api\Api;
use ReflectionClass;

use CodeIgniter\HTTP\ResponseInterface;

use Cashfree\Cashfree;

use Dompdf\Dompdf;
use Dompdf\Options;

class CustomerAppAPI extends BaseController
{
    use ResponseTrait;

    private $secretKey;

    public function __construct()
    {
        $this->secretKey = getenv('JWT_SECRET');
    }

    private function generateToken($data, $type = "email")
    {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        if ($type == 'email') {
            $payload = json_encode([
                'email' => $data,
                'iat' => time() // Issued at time
            ]);
        } else {
            $payload = json_encode([
                'mobile' => $data,
                'iat' => time() // Issued at time
            ]);
        }

        // Base64 encode header and payload
        $base64UrlHeader = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $base64UrlPayload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');

        // Create signature
        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $this->secretKey, true);
        $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    private function validateToken($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false; // Invalid token format
        }

        [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $parts;

        // Verify signature
        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $this->secretKey, true);
        $expectedSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        if (!hash_equals($expectedSignature, $base64UrlSignature)) {
            return false; // Invalid signature
        }

        // Decode payload
        $payload = json_decode(base64_decode($base64UrlPayload), true);

        return $payload; // Return decoded payload if valid
    }

    private function authorizedToken()
    {
        // Check if the Authorization header exists
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->failUnauthorized('Authorization token is required');
        }

        // Extract the token from the Authorization header
        $token = str_replace('Bearer ', '', $authHeader);

        // Validate the token and get payload
        $payload = $this->validateToken($token);

        if (!$payload) {
            return $this->failUnauthorized('Invalid token');
        }

        // Check if the payload contains either email or mobile
        if (!isset($payload['email']) && !isset($payload['mobile'])) {
            return $this->failUnauthorized('Invalid token payload: missing authentication identifier');
        }

        // if ($payload instanceof \CodeIgniter\HTTP\Response) {
        //     $payload = json_decode($payload->getBody(), true); // Convert response to array
        // }

        return $payload;
    }

    public function fetchCustomerSettings()
    {
        return $this->respond([
            'customerSettings' => $this->customerSettings,
            'countrySettings' => $this->country
        ]);
    }

    public function signup()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input
        date_default_timezone_set($this->timeZone['timezone']); // Set the timezone

        // Validation Rules
        $validationRules = [
            'name' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Name is required.',
                ],
            ],
            'mobile' => [
                'rules' => 'required|regex_match[/^[0-9]{' . ($this->country['validation_no'] ?? 10) . '}$/]',
                'errors' => [
                    'required' => 'Mobile number is required.',
                    'regex_match' => 'Mobile number must be ' . ($this->country['validation_no'] ?? 10) . ' digits.',
                ],
            ],
            'email' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email is required.',
                    'valid_email' => 'Please enter a valid email address.',
                ],
            ],
            'password' => [
                'rules' => 'required|min_length[6]',
                'errors' => [
                    'required' => 'Password is required.',
                    'min_length' => 'Password must be at least 6 characters long.',
                ],
            ],
            'referal' => [
                'rules' => 'permit_empty|trim|exact_length[8]',
                'errors' => [
                    'exact_length' => 'Referral code must be exactly 8 characters.',
                ],
            ],
        ];

        // Validate Input
        if (!$this->validate($validationRules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => $this->validator->getErrors(), // Return detailed errors
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
                    'message' => 'Referral code you used is not valid.',
                ]);
            }
        }

        $existingUser = $userModel->where('mobile', $dataInput['mobile'])->where('is_mobile_verified', 1)->first();
        if ($existingUser) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'This Mobile number already used.'
            ]);
        }


        // Check if email already exists
        $existingUser = $userModel->where('email', $dataInput['email'])->first();
        if ($existingUser) {
            if ($existingUser['is_delete'] == 1) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Account has been deleted.'
                ]);
            }

            if ($existingUser['is_active'] == 0 && $existingUser['is_delete'] == 1) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Your Account is Inactive. Contact Support'
                ]);
            }

            // If user exists but is inactive and email is not verified
            if ($existingUser['is_active'] == 0 && $existingUser['is_email_verified'] == 0) {
                // Send OTP
                $otp = random_int(100000, 999999);

                $otpData = [
                    'email' => $dataInput['email'],
                    'otp' => $otp,
                    'verify_by' => 'email',
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                $otpVerificationModel->insert($otpData);

                $this->sendMailOTP($dataInput['email'], $otp);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'OTP sent to registered Email ID.'
                ]);
            }

            // Default: Email already in use and verified
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Email is already in use. Use "Forgot Password" if needed.'
            ]);
        }


        // Create the new user
        $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $dataInput['name']); // Remove spaces & special characters
        $refCode = strtoupper(substr($cleanName, 0, 4)) . strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));



        $userData = [
            'name' => $dataInput['name'],
            'country_code' => $this->country['country_code'],
            'mobile' => $dataInput['mobile'],
            'email' => $dataInput['email'],
            'password' => password_hash($dataInput['password'], PASSWORD_BCRYPT), // Hash password
            'login_type' => 'normal',
            'ref_code' => $refCode,
            'ref_by' => $referedUser['id'],
            'is_active' => 0,  // Default is inactive
            'is_delete' => 0,  // Not deleted
            'is_email_verified' => 0,
            'is_mobile_verified' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $referalMsg = '';
        if ($userModel->insert($userData)) {
            // add referal amount in wallet
            if (!empty($dataInput['referal']) && $this->settings['refer_and_earn_status'] == 1 && $referedUser['id'] != 0) {
                $insertedId = $userModel->getInsertID();

                $walletModel = new WalletModel();

                // add fund to new user
                $walletModel->insert(['user_id' => $insertedId, 'ref_user_id' => $referedUser['id'], 'amount' => $this->settings['referer_earning'], 'closing_amount' => $this->settings['referer_earning'], 'date' => date("Y-m-d H:i:s"), 'flag' => 'credit', 'remark' => 'Referal Amount credited']);

                if ((int)$this->settings['refered_earning'] > 0) {
                    // add fund to refered user(Existing)
                    $walletModel->insert(['user_id' => $referedUser['id'], 'amount' => $this->settings['refered_earning'], 'closing_amount' => $referedUser['wallet'] + $this->settings['refered_earning'], 'date' => date("Y-m-d H:i:s"), 'flag' => 'credit', 'remark' => 'Refered Amount credited']);

                    $userModel->update($referedUser['id'], [
                        'wallet' => $referedUser['wallet'] + $this->settings['refered_earning'],
                    ]);
                }

                $userModel->update($insertedId, [
                    'wallet' => $this->settings['referer_earning'],
                ]);

                $referalMsg = 'Hurre! Referal applied successfully. & ';
            }

            // Send OTP and return response
            $otp = random_int(100000, 999999);

            $otpData = [
                'email' => $dataInput['email'],
                'otp' => $otp,
                'verify_by' => 'email',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $otpVerificationModel->insert($otpData);

            $this->sendMailOTP($dataInput['email'], $otp); // Send OTP email
            return $this->response->setJSON([
                'status' => 'success',
                'message' => $referalMsg . 'OTP sent to registered Email ID.',
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to create account. Please try again later.',
            ]);
        }
    }

    public function loginWithMobile()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input
        date_default_timezone_set($this->timeZone['timezone']); // Set the timezone

        // Validation Rules
        $validationRules = [
            'mobile' => [
                'rules' => 'required|regex_match[/^[0-9]{' . ($this->country['validation_no'] ?? 10) . '}$/]',
                'errors' => [
                    'required' => 'Mobile number is required.',
                    'regex_match' => 'Mobile number must be ' . ($this->country['validation_no'] ?? 10) . ' digits.',
                ],
            ]
        ];

        // Validate Input
        if (!$this->validate($validationRules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => $this->validator->getErrors(), // Return detailed errors
            ]);
        }

        $userModel = new UserModel();
        $otpVerificationModel = new OtpVerificationModel();

        $is_first_time = 1;
        // Check if email already exists
        $existingUser = $userModel->where('mobile', $dataInput['mobile'])->first();
        if ($existingUser) {
            $is_first_time = 0;

            if ($existingUser['is_delete'] == 1) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Account has been deleted.'
                ]);
            }
            if ($existingUser['is_active'] == 0 && $existingUser['is_delete'] == 1) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Your Account is Inactive. Contact Support'
                ]);
            }

            if ($existingUser['is_active'] == 0 && $existingUser['is_delete'] == 0 && $existingUser['is_mobile_verified'] == 0) {
                $is_first_time = 1;
            }

            $otp = random_int(100000, 999999);

            $otpData = [
                'mobile' => $dataInput['mobile'],
                'otp' => $otp,
                'verify_by' => 'mobile',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $otpVerificationModel->insert($otpData);

            //here code to send via otp
            $smsGatewayModel = new SmsGatewayModel();
            $smsGateway = $smsGatewayModel->where('is_active', 1)->first();

            if ($smsGateway['id'] == 1) {
                return  $this->twilio($smsGateway['value'], $otp, $dataInput['mobile'], $is_first_time);
            } else if ($smsGateway['id'] == 2) {
                return  $this->nexmo($smsGateway['value'], $otp, $dataInput['mobile'], $is_first_time);
            } else if ($smsGateway['id'] == 3) {
                return  $this->twoFactor($smsGateway['value'], $otp, $dataInput['mobile'], $is_first_time);
            } elseif ($smsGateway['id'] == 4) {
                return  $this->msg91($smsGateway['value'], $otp, $dataInput['mobile'], $is_first_time);
            } elseif ($smsGateway['id'] == 5) {
                return  $this->fast2Sms($smsGateway['value'], $otp, $dataInput['mobile'], $is_first_time);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to load SMS Setting']);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'OTP sent to registered Mobile Number.',
                'demo_otp' => $otp,
                'is_first_time' => $is_first_time
            ]);
        }

        // Create the new user
        $dataInput['random_char'] = '';
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < 4; $i++) {
            $dataInput['random_char'] .= $characters[rand(0, strlen($characters) - 1)];
        }

        $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $dataInput['random_char']); // Remove spaces & special characters
        $refCode = strtoupper(substr($cleanName, 0, 4)) . strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));

        $userData = [
            'country_code' => $this->country['country_code'],
            'mobile' => $dataInput['mobile'],
            'login_type' => 'mobile',
            'ref_code' => $refCode,
            'is_active' => 0,  // Default is inactive
            'is_delete' => 0,  // Not deleted
            'is_email_verified' => 0,
            'is_mobile_verified' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($userModel->insert($userData)) {
            // Send OTP and return response
            $otp = random_int(100000, 999999);

            $otpData = [
                'mobile' => $dataInput['mobile'],
                'otp' => $otp,
                'verify_by' => 'mobile',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $otpVerificationModel->insert($otpData);

            //here code to send via otp
            $smsGatewayModel = new SmsGatewayModel();
            $smsGateway = $smsGatewayModel->where('is_active', 1)->first();
            if ($smsGateway['id'] == 1) {
                return  $this->twilio($smsGateway['value'], $otp, $dataInput['mobile'], $is_first_time);
            } else if ($smsGateway['id'] == 2) {
                return  $this->nexmo($smsGateway['value'], $otp, $dataInput['mobile'], $is_first_time);
            } else if ($smsGateway['id'] == 3) {
                return  $this->twoFactor($smsGateway['value'], $otp, $dataInput['mobile'], $is_first_time);
            } elseif ($smsGateway['id'] == 4) {
                return  $this->msg91($smsGateway['value'], $otp, $dataInput['mobile'], $is_first_time);
            } elseif ($smsGateway['id'] == 5) {
                return  $this->fast2Sms($smsGateway['value'], $otp, $dataInput['mobile'], $is_first_time);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to load SMS Setting']);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'OTP sent to registered Mobile Number.',
                'demo_otp' => $otp,
                'is_first_time' => 1
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to create account. Please try again later.',
            ]);
        }
    }

    private function sendMailOTP($sendEmail, $otp)
    {
        $email = \Config\Services::email();
        $settings = $this->settings;
        $mailSetting = json_decode($settings['mail_config'], true);

        $link = "<a style='background:#3E3F95;text-decoration:none !important; font-weight:700; margin:35px 0px; color:#fff;text-transform:uppercase; font-size:20px; letter-spacing: 10px; padding:10px 24px;display:inline-block;border-radius:50px;' href='#'>" . $otp . "</a>";
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
        $email->setTo($sendEmail); // Recipient email address
        $email->setSubject('OTP for ' . $settings['business_name']);
        $email->setMessage('<!doctype html>
        <html lang="en-US">
        
        <head>
            <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
            <title>OTP for ' . $settings['business_name'] . '</title>
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

    public function verifySignupOtp()
    {
        // Set the timezone
        date_default_timezone_set($this->timeZone['timezone']);

        // Retrieve input data
        $dataInput = $this->request->getJSON(true);

        // Validate required fields
        if (!isset($dataInput['email']) || !isset($dataInput['otp'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Email and OTP are required.'
            ]);
        }

        // Load models
        $userModel = new UserModel();
        $otpVerificationModel = new OtpVerificationModel();

        // Fetch the OTP verification record
        $existingOtp = $otpVerificationModel->where('email', $dataInput['email'])
            ->where('otp', $dataInput['otp'])
            ->orderBy('id', 'desc')
            ->first();
        $cartsModel = new CartsModel();

        if ($existingOtp) {
            // OTP matches, proceed to verify the user
            $user = $userModel->where('email', $dataInput['email'])->first();

            if ($user) {
                // Update user status
                $userModel->update($user['id'], [
                    'is_active' => 1,
                    'is_email_verified' => 1,
                ]);
                $cartsModel->set(['user_id' => $user['id']])->where('guest_id', $dataInput['guest_id'])->update();
                $deviceTokenModel = new DeviceTokenModel();

                $deviceTokenModel->insert(['user_type' => 2, 'user_id' => $user['id'], 'app_key' => $dataInput['fcmToken']]);
                $walletModel = new WalletModel();
                $walletModel->insert(['user_id' => $user['id'], 'amount' => 0, 'closing_amount' => 0, 'date' => date("Y-m-d H:i:s")]);
                $token = $this->generateToken($dataInput['email']);

                // Return success response
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Email successfully verified.',
                    'token' => $token
                ]);
            } else {
                // User not found
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User not found. Please try again.',
                ]);
            }
        } else {
            // OTP does not match
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid OTP. Please try again.',
            ]);
        }
    }

    public function verifyMobileOtp()
    {
        date_default_timezone_set($this->timeZone['timezone']);
        $dataInput = $this->request->getJSON(true);

        // Validation Rules
        $validationRules = [];
        if ($dataInput['is_first_time'] == 1) {
            $validationRules = [
                'referal' => [
                    'rules' => 'permit_empty|trim|exact_length[8]',
                    'errors' => [
                        'exact_length' => 'Referral code must be exactly 8 characters.',
                    ],
                ],
                'name' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Name is required.',
                    ],
                ],
                'otp' => [
                    'rules' => 'required|permit_empty|trim|exact_length[6]',
                    'errors' => [
                        'exact_length' => 'OTP must be exactly 6 characters.',
                    ],
                ],
            ];
        } else {
            $validationRules = [
                'otp' => [
                    'rules' => 'required|permit_empty|trim|exact_length[6]',
                    'errors' => [
                        'exact_length' => 'OTP must be exactly 6 characters.',
                    ],
                ],
            ];
        }

        // Validate Input - maintaining exact error response order
        if (!$this->validate($validationRules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => $this->validator->getErrors(),
            ]);
        }

        // Load models
        $userModel = new UserModel();
        $otpVerificationModel = new OtpVerificationModel();
        $cartsModel = new CartsModel();

        // Fetch the OTP verification record
        $existingOtp = $otpVerificationModel->where('mobile', $dataInput['mobile'])
            ->where('otp', $dataInput['otp'])
            ->orderBy('id', 'desc')
            ->first();

        // OTP validation comes first - preserving exact error order
        if (!$existingOtp) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid OTP. Please try again.',
            ]);
        }

        // Get the user
        $user = $userModel->where('mobile', $dataInput['mobile'])->first();
        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found. Please try again.',
            ]);
        }

        // Initialize referral user variable
        $referedUser = ['id' => 0];

        // Process referral code if provided
        if ($dataInput['is_first_time'] == 1 && !empty($dataInput['referal']) && $this->settings['refer_and_earn_status'] == 1) {
            $referedUser = $userModel->where('ref_code', $dataInput['referal'])->first();

            if (!$referedUser) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Referral code you used is not valid.',
                ]);
            }

            // Process referral
            $walletModel = new WalletModel();

            // Update the new user information
            $userModel->update($user['id'], [
                'name' => $dataInput['name'],
                'referal' => $dataInput['referal'],
                'ref_by' => $referedUser['id'],
                'is_active' => 1,
                'is_mobile_verified' => 1,
                'wallet' => $this->settings['referer_earning'],
            ]);

            // Add funds to new user wallet
            $walletModel->insert([
                'user_id' => $user['id'],
                'ref_user_id' => $referedUser['id'],
                'amount' => $this->settings['referer_earning'],
                'closing_amount' => $this->settings['referer_earning'],
                'date' => date("Y-m-d H:i:s"),
                'flag' => 'credit',
                'remark' => 'Referal Amount credited'
            ]);

            // Add funds to referring user's wallet if enabled
            if ((int)$this->settings['refered_earning'] > 0) {
                $walletModel->insert([
                    'user_id' => $referedUser['id'],
                    'amount' => $this->settings['refered_earning'],
                    'closing_amount' => $referedUser['wallet'] + $this->settings['refered_earning'],
                    'date' => date("Y-m-d H:i:s"),
                    'flag' => 'credit',
                    'remark' => 'Refered Amount credited'
                ]);

                $userModel->update($referedUser['id'], [
                    'wallet' => $referedUser['wallet'] + $this->settings['refered_earning'],
                ]);
            }
        } else {
            // Regular update for first time users without referral
            if ($dataInput['is_first_time'] == 1) {
                $userModel->update($user['id'], [
                    'name' => $dataInput['name'],
                    'is_active' => 1,
                    'is_mobile_verified' => 1,
                ]);

                // Initialize wallet if needed
                $walletModel = new WalletModel();
                $walletModel->insert([
                    'user_id' => $user['id'],
                    'amount' => 0,
                    'closing_amount' => 0,
                    'date' => date("Y-m-d H:i:s")
                ]);
            } else {
                // Update for returning users
                $userModel->update($user['id'], [
                    'is_active' => 1,
                    'is_mobile_verified' => 1,
                ]);
            }
        }

        // Update cart with user ID
        $cartsModel->set(['user_id' => $user['id']])->where('guest_id', $dataInput['guest_id'])->update();

        // Save device token
        $deviceTokenModel = new DeviceTokenModel();
        $deviceTokenModel->insert([
            'user_type' => 2,
            'user_id' => $user['id'],
            'app_key' => $dataInput['fcmToken']
        ]);

        // Generate authentication token
        $token = $this->generateToken($dataInput['mobile'], 'mobile');

        // Return success response
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Email successfully verified.',
            'token' => $token
        ]);
    }


    public function login()
    {
        // Get the input data
        $dataInput = $this->request->getJSON(true);

        if (!isset($dataInput['email']) || !isset($dataInput['password'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Email and password are required.'
            ]);
        }

        // Initialize the models 
        $userModel = new UserModel();
        $cartsModel = new CartsModel();
        $deviceTokenModel = new DeviceTokenModel();

        // Find user by email
        $user = $userModel->where('email', $dataInput['email'])
            ->where('is_active', 1)
            ->where('is_delete', 0)
            ->where('is_email_verified', 1)
            ->first();

        // Check if user exists and verify the password
        if ($user && password_verify($dataInput['password'], $user['password'])) {
            $cartsModel->set(['user_id' => $user['id']])->where('guest_id', $dataInput['guest_id'])->update();

            $token = $this->generateToken($dataInput['email']);
            $deviceTokenModel->insert(['user_type' => 2, 'user_id' => $user['id'], 'app_key' => $dataInput['fcmToken']]);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Login successful.', 'token' => $token]);
        } else {
            // Respond with an error if authentication fails
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid credentials or account not activated.']);
        }
    }

    public function sendForgetPasswordOTP()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input
        date_default_timezone_set($this->timeZone['timezone']); // Set the timezone

        // Validation Rules
        $validationRules = [
            'email' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email is required.',
                    'valid_email' => 'Please enter a valid email address.',
                ],
            ]
        ];

        // Validate Input
        if (!$this->validate($validationRules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => $this->validator->getErrors(), // Return detailed errors
            ]);
        }

        $userModel = new UserModel();
        $otpVerificationModel = new OtpVerificationModel();

        // Check if the email already exists
        $existingUser = $userModel->where('email', $dataInput['email'])->first();
        if ($existingUser) {
            if ($existingUser['is_delete'] == 1) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Account has been deleted.']);
            }

            if (!$existingUser['is_email_verified']) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Active Account not found. Please Create account',
                ]);
            }

            // Send OTP and return response
            $otp = random_int(100000, 999999);

            $otpData = [
                'email' => $dataInput['email'],
                'otp' => $otp,
                'verify_by' => 'email',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $otpVerificationModel->insert($otpData);

            $this->sendMailOTP($dataInput['email'], $otp); // Send OTP email
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'OTP sent to registered Email ID.',
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Account not found. Please Create account',
            ]);
        }
    }

    public function verifyForgetPasswordOTP()
    {
        // Set the timezone
        date_default_timezone_set($this->timeZone['timezone']);

        // Retrieve input data
        $dataInput = $this->request->getJSON(true);

        // Validate required fields
        if (!isset($dataInput['email']) || !isset($dataInput['otp'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Email and OTP are required.'
            ]);
        }

        // Load models
        $userModel = new UserModel();
        $otpVerificationModel = new OtpVerificationModel();

        // Fetch the OTP verification record
        $existingOtp = $otpVerificationModel->where('email', $dataInput['email'])
            ->where('otp', $dataInput['otp'])
            ->orderBy('id', 'desc')
            ->first();

        if ($existingOtp) {
            // OTP matches, proceed to verify the user
            $user = $userModel->where('email', $dataInput['email'])->first();

            if ($user) {
                // Update user status
                $userModel->update($user['id'], [
                    'is_active' => 1,
                    'is_email_verified' => 1,
                ]);

                // Return success response
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'OTP Verified Successfully',
                ]);
            } else {
                // User not found
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User not found. Please try again.',
                ]);
            }
        } else {
            // OTP does not match
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid OTP. Please try again.',
            ]);
        }
    }

    public function updatePassword()
    {
        $dataInput = $this->request->getJSON(true);

        $email = $dataInput['email'];
        $pass = $dataInput['password'];
        $cpass = $dataInput['confirmPassword'];
        // Validate password match
        if ($pass !== $cpass) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'The passwords do not match. Please re-enter the passwords.']);
        }

        // Check if the reset link token and email are valid
        $userModel = new UserModel();
        $cartsModel = new CartsModel();

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

        // At this point, all conditions are met and the user can reset the password
        $data1 = [
            'password' => password_hash($pass, PASSWORD_BCRYPT),
        ];

        // Update the user's password
        $userModel->set($data1)->update($user['id']);
        $cartsModel->set(['user_id' => $user['id']])->where('guest_id', $dataInput['guest_id'])->update();
        $token = $this->generateToken($dataInput['email']);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Password changed successfully.', 'token' => $token]);
    }

    public function appleLogin()
    {
        $dataInput = $this->request->getJSON(true);
        date_default_timezone_set($this->timeZone['timezone']); // Set the timezone
        $userModel = new UserModel();
        $cartsModel = new CartsModel();
        $deviceTokenModel = new DeviceTokenModel();

        $existingUser = $userModel->where('apple_user_id', $dataInput['apple_user_id'])->first();

        if ($existingUser) {
            if ($existingUser['is_delete'] == 1) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Account has been deleted.']);
            }

            if (
                $existingUser['login_type'] === 'apple' &&
                $existingUser['is_active'] == 1 &&
                $existingUser['is_delete'] == 0 &&
                $existingUser['is_email_verified'] == 1
            ) {

                $token = $this->generateToken($dataInput['email']);
                $deviceTokenModel->insert(['user_type' => 2, 'user_id' => $existingUser['id'], 'app_key' => $dataInput['fcmToken']]);

                return $this->response->setJSON(['status' => 'success', 'message' => 'Loggin Successfully', 'token' => $token]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'This email must be accessed using a password.']);
            }
        }

        // Check if fullName is null and generate a name
        if (
            $dataInput['fullName'] == null ||
            (is_array($dataInput['fullName']) &&
                empty($dataInput['fullName']['givenName']) &&
                empty($dataInput['fullName']['familyName']))
        ) {

            // Generate a random name
            $generatedName = 'User' . rand(1000, 9999);
            $dataInput['name'] = $generatedName;
        } else {
            // Extract name from fullName object
            $givenName = $dataInput['fullName']['givenName'] ?? '';
            $familyName = $dataInput['fullName']['familyName'] ?? '';

            // Combine names, handle cases where one might be empty
            if (!empty($givenName) && !empty($familyName)) {
                $dataInput['name'] = $givenName . ' ' . $familyName;
            } elseif (!empty($givenName)) {
                $dataInput['name'] = $givenName;
            } elseif (!empty($familyName)) {
                $dataInput['name'] = $familyName;
            } else {
                // Both are empty, generate random name
                $dataInput['name'] = 'User' . rand(1000, 9999);
            }
        }

        // Now generate refCode from the name
        $refCode = strtoupper(substr($dataInput['name'], 0, 4)) . strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));

        $data = [
            'email' => $dataInput['email'],
            'name' => $dataInput['name'],
            'img' => null,
            'login_type' => 'apple',
            'ref_code' => $refCode,
            'is_active' => 1, // Mark the new user as active
            'is_delete' => 0,
            'is_email_verified' => 1,
            'is_mobile_verified' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'apple_user_id' => $dataInput['apple_user_id']
        ];

        if ($user_id = $userModel->insert($data)) {
            $cartsModel->set(['user_id' => $user_id])->where('guest_id', $dataInput['guest_id'])->update();
            $token = $this->generateToken($dataInput['email']);
            $deviceTokenModel->insert(['user_type' => 2, 'user_id' => $user_id, 'app_key' => $dataInput['fcmToken']]);
            $walletModel = new WalletModel();
            $walletModel->insert(['user_id' => $user_id, 'amount' => 0, 'closing_amount' => 0, 'date' => date("Y-m-d H:i:s")]);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Account created successfully.', 'token' => $token]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to create account.']);
        }
    }

    public function googleSignin()
    {
        $dataInput = $this->request->getJSON(true);
        date_default_timezone_set($this->timeZone['timezone']); // Set the timezone
        $userModel = new UserModel();
        $cartsModel = new CartsModel();
        $deviceTokenModel = new DeviceTokenModel();

        $existingUser = $userModel->where('email', $dataInput['email'])->first();

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

                $token = $this->generateToken($dataInput['email']);
                $deviceTokenModel->insert(['user_type' => 2, 'user_id' => $existingUser['id'], 'app_key' => $dataInput['fcmToken']]);

                return $this->response->setJSON(['status' => 'success', 'message' => 'Loggin Successfully', 'token' => $token]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'This email must be accessed using a password.']);
            }
        }

        $refCode = strtoupper(substr($dataInput['name'], 0, 4)) . strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));

        $data = [
            'email' => $dataInput['email'],
            'name' => $dataInput['name'],
            'img' => $dataInput['photo'] ?? null,
            'login_type' => 'google',
            'ref_code' => $refCode,
            'is_active' => 1, // Mark the new user as active
            'is_delete' => 0,
            'is_email_verified' => 1,
            'is_mobile_verified' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($user_id = $userModel->insert($data)) {
            $cartsModel->set(['user_id' => $user_id])->where('guest_id', $dataInput['guest_id'])->update();
            $token = $this->generateToken($dataInput['email']);
            $deviceTokenModel->insert(['user_type' => 2, 'user_id' => $user_id, 'app_key' => $dataInput['fcmToken']]);
            $walletModel = new WalletModel();
            $walletModel->insert(['user_id' => $user_id, 'amount' => 0, 'closing_amount' => 0, 'date' => date("Y-m-d H:i:s")]);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Account created successfully.', 'token' => $token]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to create account.']);
        }
    }

    private function pointInPolygon($latitude, $longitude, $boundaryPoints)
    {
        $n = count($boundaryPoints);
        $inside = false;

        $p1x = $boundaryPoints[0]['latitude'];
        $p1y = $boundaryPoints[0]['longitude'];

        for ($i = 1; $i <= $n; $i++) {
            $p2x = $boundaryPoints[$i % $n]['latitude'];
            $p2y = $boundaryPoints[$i % $n]['longitude'];

            if ($longitude > min($p1y, $p2y)) {
                if ($longitude <= max($p1y, $p2y)) {
                    if ($latitude <= max($p1x, $p2x)) {
                        if ($p1y != $p2y) {
                            $xinters = ($longitude - $p1y) * ($p2x - $p1x) / ($p2y - $p1y) + $p1x;
                            if ($p1x == $p2x || $latitude <= $xinters) {
                                $inside = !$inside;
                            }
                        }
                    }
                }
            }
            $p1x = $p2x;
            $p1y = $p2y;
        }

        return $inside;
    }

    public function fetchDeliverableAreaByLatLongByDeliverableAreaId()
    {
        $dataInput = $this->request->getJSON(true);

        $latitude = $dataInput['latitude'];
        $longitude = $dataInput['longitude'];

        // Load the DeliverableArea model
        $deliverableAreaModel = new DeliverableAreaModel();
        $sellerModel = new SellerModel();
        $productModel = new ProductModel();

        // Fetch all deliverable areas
        $deliverableAreas = $deliverableAreaModel->where('is_delete', 0)->where('id', $dataInput['deliverable_area_id'])->findAll();

        $foundArea = null;
        $cityId = $dataInput['city_id'];
        $deliverableAreaId = $dataInput['deliverable_area_id'];

        // Loop through each deliverable area to check if the point is inside the polygon
        $productExist = false;
        foreach ($deliverableAreas as $area) {
            $boundaryPoints = json_decode($area['boundry_points'], true);

            if ($this->pointInPolygon($latitude, $longitude, $boundaryPoints)) {
                $foundArea = $area;
                $cityId = $area['city_id'];
                $deliverableAreaId = $area['id'];

                $findSellers = $sellerModel->select('COUNT(id) as total_sellers')
                    ->where('deliverable_area_id', $area['id'])
                    ->where('is_delete', 0)
                    ->where('status', 1)
                    ->first();

                if ($findSellers && $findSellers['total_sellers'] > 0) {
                    // Get list of seller IDs
                    $findSellersForProduct = $sellerModel->select('id')
                        ->where('deliverable_area_id', $area['id'])
                        ->findAll();

                    $sellerIds = array_column($findSellersForProduct, 'id');

                    if (!empty($sellerIds)) {
                        // Count products available under these sellers
                        $ProductCount = $productModel->select('COUNT(id) as total_product')
                            ->whereIn('seller_id', $sellerIds)
                            ->first();

                        if ($ProductCount && $ProductCount['total_product'] > 0) {
                            $productExist = true;
                            break; // Stop the loop if products exist
                        }
                    }
                }
            }
        }

        if ($foundArea && $productExist) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Location is within a deliverable area.',
                'city_id' => $cityId,
                'deliverable_area_id' => $deliverableAreaId
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'We are not serviceable at your location.',
                'city_id' => 0,
                'deliverable_area_id' => 0
            ]);
        }
    }

    public function fetchDeliverableAreaByLatLong()
    {
        $dataInput = $this->request->getJSON(true);

        $latitude = $dataInput['latitude'];
        $longitude = $dataInput['longitude'];

        // Load the DeliverableArea model
        $deliverableAreaModel = new DeliverableAreaModel();
        $sellerModel = new SellerModel();
        $productModel = new ProductModel();

        // Fetch all deliverable areas
        $deliverableAreas = $deliverableAreaModel->where('is_delete', 0)->findAll();

        $foundArea = null;
        $cityId = 0;
        $deliverableAreaId = 0;

        // Loop through each deliverable area to check if the point is inside the polygon
        $productExist = false;
        foreach ($deliverableAreas as $area) {
            $boundaryPoints = json_decode($area['boundry_points'], true);

            if ($this->pointInPolygon($latitude, $longitude, $boundaryPoints)) {
                $foundArea = $area;
                $cityId = $area['city_id'];
                $deliverableAreaId = $area['id'];

                $findSellers = $sellerModel->select('COUNT(id) as total_sellers')
                    ->where('deliverable_area_id', $area['id'])
                    ->where('is_delete', 0)
                    ->where('status', 1)
                    ->first();

                if ($findSellers && $findSellers['total_sellers'] > 0) {
                    // Get list of seller IDs
                    $findSellersForProduct = $sellerModel->select('id')
                        ->where('deliverable_area_id', $area['id'])
                        ->findAll();

                    $sellerIds = array_column($findSellersForProduct, 'id');

                    if (!empty($sellerIds)) {
                        $guestId = $dataInput['guest_id'] ?? null;

                        $userModel = new UserModel();
                        $cartsModel = new CartsModel();
                        $authHeader = $this->request->getHeaderLine('Authorization');
                        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
                            $payload = $this->authorizedToken();
                            if ($payload instanceof ResponseInterface) {
                                return $payload;
                            }
                            if (isset($payload['email'])) {
                                $user = $userModel
                                    ->where('is_active', 1)
                                    ->where('is_email_verified', 1)
                                    ->where('is_delete', 0)
                                    ->where('email', $payload['email'])
                                    ->first();
                            } elseif (isset($payload['mobile'])) {
                                $user = $userModel
                                    ->where('is_active', 1)
                                    ->where('is_delete', 0)
                                    ->where('mobile', $payload['mobile'])
                                    ->first();
                            }

                            if ($user) {
                                $cartsModel->where('user_id', $user['id'])->delete();
                            }
                        } else {
                            $cartsModel->where('guest_id', $guestId)->delete();
                        }

                        // Count products available under these sellers
                        $ProductCount = $productModel->select('COUNT(id) as total_product')
                            ->whereIn('seller_id', $sellerIds)
                            ->first();

                        if ($ProductCount && $ProductCount['total_product'] > 0) {
                            $productExist = true;
                            break; // Stop the loop if products exist
                        }
                    }
                }
            }
        }

        if ($foundArea && $productExist) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Location is within a deliverable area.',
                'city_id' => $cityId,
                'deliverable_area_id' => $deliverableAreaId
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'We are not serviceable at your location.',
                'city_id' => 0,
                'deliverable_area_id' => 0
            ]);
        }
    }

    public function getBestSellerCategories()
    {
        if ($this->settings['frontend_category_section'] == 0) {
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => [],
            ]);
        }

        $categoryModel = new CategoryModel();
        $productModel = new ProductModel();

        // Fetch bestseller categories
        $categories = $categoryModel->where('is_bestseller_category', 1)->findAll();

        $result = [];

        foreach ($categories as $category) {
            // Fetch the first 4 product images for this category
            $products = $productModel->select('main_img')
                ->where('category_id', $category['id'])
                ->limit(4)
                ->find();

            // Get total product count in this category
            $totalProducts = $productModel->where('category_id', $category['id'])->countAllResults();

            // Format product images with base_url
            $productImages = [];
            foreach ($products as $product) {
                $productImages[] = base_url($product['main_img']);
            }

            // Append category info to result array
            $result[] = [
                'category_id'   => $category['id'],
                'category_name' => $category['category_name'],
                'images'        => $productImages,
                'total_count'   => $totalProducts,
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $result,
        ]);
    }

    public function fetchAllCategories()
    {
        $categoryModel = new CategoryModel();

        if ($this->settings['frontend_category_section'] == 0) {
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => [],
            ]);
        }

        // Fetch all categories ordered by `row_order` in ascending order
        $categories = $categoryModel->orderBy('row_order', 'ASC')->findAll();

        // Append base_url to category_img
        foreach ($categories as &$category) {
            $category['category_img'] = base_url($category['category_img']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $categories,
        ]);
    }

    public function fetchSubCategoriesByCategoryId()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input

        $categoryModel = new CategoryModel();
        $category = $categoryModel->where('id', $dataInput['category_id'])->first();

        $subcategoryModel = new SubcategoryModel();
        $subcategories = $subcategoryModel->where('category_id', $dataInput['category_id'])
            ->orderBy('row_order', 'ASC')
            ->findAll();

        // Append base_url to subcategory_img
        foreach ($subcategories as &$subcategory) {
            $subcategory['subcategory_img'] = base_url($subcategory['img']);
        }

        // ✅ Add "All" category as the first item using category image
        $allSubcategory = [
            "id" => 0,
            "category_id" => $dataInput['category_id'],
            "row_order" => 0,
            "name" => "All",
            "slug" => "all",
            "subcategory_img" => base_url($category['category_img']) // Use category image as subcategory_img
        ];

        // Prepend "All" to subcategories array
        array_unshift($subcategories, $allSubcategory);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $subcategories,
            'category' => $category
        ]);
    }

    public function fetchProductDetailsById()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input

        $userModel = new UserModel();
        $cartsModel = new CartsModel();
        $authHeader = $this->request->getHeaderLine('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $payload = $this->authorizedToken();
            if ($payload instanceof ResponseInterface) {
                return $payload;
            }
            if (isset($payload['email'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_email_verified', 1)
                    ->where('is_delete', 0)
                    ->where('email', $payload['email'])
                    ->first();
            } elseif (isset($payload['mobile'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->where('mobile', $payload['mobile'])
                    ->first();
            }

            // If user wasn't found, set default id
            if (empty($user)) {
                $user['id'] = 0;
            }
        } else {
            $user['id'] = 0;
        }

        $guestId = $dataInput['guest_id'] ?? null;

        $userId = $user['id'] ?? 0;
        $identifier = $userId ?: $guestId;

        $productModel = new ProductModel();
        $productImagesModel = new ProductImagesModel();
        $productRatingsModel = new ProductRatingsModel();
        $productVariantsModel = new ProductVariantsModel();
        $sellerModel = new SellerModel();
        $categoryModel = new CategoryModel();
        $subcategoryModel = new SubcategoryModel();
        $brandModel = new BrandModel();
        $taxModel = new TaxModel();
        $deliverableAreaModel = new DeliverableAreaModel();

        // Fetch product details
        $product = $productModel->where('id', $dataInput['product_id'])->first();

        // Fetch brand name
        $brand = $brandModel->select('brand, image')->where('id', $product['brand_id'])->first();
        $product['brand'] = $brand ? $brand['brand'] : null;
        $product['brand_image'] = $brand ? base_url($brand['image']) : null;

        // Fetch category name
        $category = $categoryModel->select('category_name, id')->where('id', $product['category_id'])->first();
        $product['category_name'] = $category ? $category['category_name'] : null;

        // Fetch subcategory name
        $subcategory = $subcategoryModel->select('name, id')->where('id', $product['subcategory_id'])->first();
        $product['subcategory_name'] = $subcategory ? $subcategory['name'] : null;

        // Fetch seller name
        $seller = $sellerModel->select('name, id, latitude, longitude')->where('id', $product['seller_id'])->where('is_delete', 0)->first();
        $product['seller_name'] = $seller ? $seller['name'] : null;

        // Fetch tax percentage
        $tax = $taxModel->select('percentage')
            ->where('id', $product['tax_id'])
            ->where('is_active', 1)
            ->where('is_delete', 0)
            ->first();
        $product['tax_percentage'] = $tax ? $tax['percentage'] : 0;

        // Fetch product images (main_img + other images)
        $productImages = $productImagesModel->select('image')
            ->where('product_id', $product['id'])
            ->findAll();

        $product['images'] = array_map(fn($img) => base_url($img['image']), $productImages);
        array_unshift($product['images'], base_url($product['main_img'])); // Add main image at the start

        // Fetch product variants
        $variants = $productVariantsModel->where('product_id', $product['id'])->where('is_delete', 0)->findAll();
        foreach ($variants as &$variant) {
            if ($variant['discounted_price'] > 0) {
                $variant['discount_percentage'] = round((($variant['price'] - $variant['discounted_price']) / $variant['price']) * 100, 2);
            } else {
                $variant['discount_percentage'] = 0;
            }

            if ($identifier) {
                $cartItem = $cartsModel->where($userId ? 'user_id' : 'guest_id', $identifier)
                    ->where('product_id', $dataInput['product_id'])
                    ->where('product_variant_id', $variant['id'])
                    ->first();

                if ($cartItem) {
                    $variant['cart_quantity'] = $cartItem['quantity'];
                } else {
                    $variant['cart_quantity'] = 0;
                }
            }
        }


        $perKmTime = $deliverableAreaModel->where('is_delete', 0)->where('id', $dataInput['deliverable_area_id'])->first();

        $geoUtils = new GeoUtils();
        $findTime = $geoUtils->travelDistanceTime($dataInput['latitude'], $dataInput['longitude'], $seller['latitude'], $seller['longitude'], $perKmTime['time_to_travel']);
        $product['proxyDeliveryTime'] = $perKmTime['base_delivery_time'] + $findTime['estimated_delivery_time_min'];

        $product['variants'] = $variants;

        // Fetch product ratings (average and count)
        $ratings = $productRatingsModel->select('AVG(rate) as avg_rating, COUNT(id) as total_ratings')
            ->where('product_id', $product['id'])
            ->where('is_approved_to_show', 1)
            ->first();

        $product['avg_rating'] = $ratings ? round($ratings['avg_rating'], 1) : 0;
        $product['total_ratings'] = $ratings ? $ratings['total_ratings'] : 0;

        //similar products (same subcategory)
        $sellers = $sellerModel->where('city_id', $dataInput['city_id'])
            ->where('status', 1)
            ->where('is_delete', 0)
            ->findAll();

        // Extract seller IDs
        $sellerIds = array_column($sellers, 'id');
        $similarProducts = $productModel->where('is_delete', 0)
            ->where('status', 1)
            ->whereIn('seller_id', $sellerIds) // Use whereIn for multiple sellers
            ->where('subcategory_id', $subcategory['id'])
            ->findAll();
        // Append product variants
        foreach ($similarProducts as &$similarProduct) {
            $similarProduct['main_img'] = base_url($similarProduct['main_img']);

            // Fetch product variants
            $variants = $productVariantsModel
                ->where('product_id', $similarProduct['id'])
                ->where('is_delete', 0)
                ->findAll();

            // Loop through each variant to calculate discount
            foreach ($variants as &$variant) {
                if ($variant['discounted_price'] == 0 || $variant['price'] == 0) {
                    $variant['discount_percentage'] = 0;
                } else {
                    $variant['discount_percentage'] = round((($variant['price'] - $variant['discounted_price']) / $variant['price']) * 100);
                }
            }

            // Assign updated variants back to product
            $similarProduct['variants'] = $variants;
        }

        //categorywise products
        $categoryProducts = $productModel->where('is_delete', 0)
            ->where('status', 1)
            ->whereIn('seller_id', $sellerIds) // Use whereIn for multiple sellers
            ->where('category_id', $category['id'])
            ->findAll();
        // Append product variants
        foreach ($categoryProducts as &$categoryProduct) {
            $categoryProduct['main_img'] = base_url($categoryProduct['main_img']);

            // Fetch product variants
            $variants = $productVariantsModel
                ->where('product_id', $categoryProduct['id'])
                ->where('is_delete', 0)
                ->findAll();

            // Loop through each variant to calculate discount
            foreach ($variants as &$variant) {
                if ($variant['discounted_price'] == 0 || $variant['price'] == 0) {
                    $variant['discount_percentage'] = 0;
                } else {
                    $variant['discount_percentage'] = round((($variant['price'] - $variant['discounted_price']) / $variant['price']) * 100);
                }
            }

            // Assign updated variants back to product
            $categoryProduct['variants'] = $variants;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $product,
            'similarProducts' => $similarProducts,
            'categoryProducts' => $categoryProducts
        ]);
    }

    public function fetchProductBySubcategoryId()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input
        $userModel = new UserModel();
        $cartsModel = new CartsModel();
        $authHeader = $this->request->getHeaderLine('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $payload = $this->authorizedToken();
            if ($payload instanceof ResponseInterface) {
                return $payload;
            }
            if (isset($payload['email'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_email_verified', 1)
                    ->where('is_delete', 0)
                    ->where('email', $payload['email'])
                    ->first();
            } elseif (isset($payload['mobile'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->where('mobile', $payload['mobile'])
                    ->first();
            }

            // If user wasn't found, set default id
            if (empty($user)) {
                $user['id'] = 0;
            }
        } else {
            $user['id'] = 0;
        }
        $guestId = $dataInput['guest_id'] ?? null;

        // Validate guest ID for non-logged-in users
        if (!$user['id'] && empty($guestId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Guest ID is required for non-logged-in users.']);
        }



        $userId = $user['id'] ?? 0;
        $identifier = $userId ?: $guestId;
        $sellerModel = new SellerModel();
        $productModel = new ProductModel();
        $productVariantsModel = new ProductVariantsModel();

        $sellers = $sellerModel->where('city_id', $dataInput['city_id'])
            ->where('status', 1)
            ->where('is_delete', 0)
            ->findAll();

        // Extract seller IDs
        $sellerIds = array_column($sellers, 'id');

        // Fetch products for the given subcategory and sellers
        if ($dataInput['subcategory_id'] == 0) {
            $products = $productModel->where('is_delete', 0)
                ->where('status', 1)
                ->whereIn('seller_id', $sellerIds) // Use whereIn for multiple sellers
                ->where('category_id', $dataInput['category_id'])
                ->limit(10, 0)
                ->findAll();
        } else {
            $products = $productModel->where('is_delete', 0)
                ->where('status', 1)
                ->whereIn('seller_id', $sellerIds) // Use whereIn for multiple sellers
                ->where('subcategory_id', $dataInput['subcategory_id'])
                ->limit(10, 0)
                ->findAll();
        }

        // Append product variants
        $finalProducts = [];

        foreach ($products as $product) {
            $product['main_img'] = base_url($product['main_img']);

            // Fetch product variants
            $variants = $productVariantsModel
                ->where('product_id', $product['id'])
                ->where('is_delete', 0)
                ->findAll();

            // Skip product if no variants
            if (empty($variants)) {
                continue;
            }

            // Process variants
            foreach ($variants as &$variant) {
                if (empty($variant['discounted_price']) || empty($variant['price'])) {
                    $variant['discount_percentage'] = 0;
                } else {
                    $variant['discount_percentage'] = round((($variant['price'] - $variant['discounted_price']) / $variant['price']) * 100);
                }

                if ($identifier) {
                    $cartItem = $cartsModel->where($userId ? 'user_id' : 'guest_id', $identifier)
                        ->where('product_id', $product['id'])
                        ->where('product_variant_id', $variant['id'])
                        ->first();

                    $variant['cart_quantity'] = $cartItem['quantity'] ?? 0;
                } else {
                    $variant['cart_quantity'] = 0;
                }
            }

            $product['variants'] = $variants;

            // Add the valid product to final list
            $finalProducts[] = $product;
        }



        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $finalProducts
        ]);
    }

    public function fetchSimilarProductsByProductId()
    {
        $dataInput = $this->request->getJSON(true);

        $userModel = new UserModel();
        $cartsModel = new CartsModel();
        $authHeader = $this->request->getHeaderLine('Authorization');

        $user = ['id' => 0];

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $payload = $this->authorizedToken();
            if ($payload instanceof ResponseInterface) {
                return $payload;
            }

            if (isset($payload['email'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_email_verified', 1)
                    ->where('is_delete', 0)
                    ->where('email', $payload['email'])
                    ->first();
            } elseif (isset($payload['mobile'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->where('mobile', $payload['mobile'])
                    ->first();
            }

            if (empty($user)) {
                $user = ['id' => 0];
            }
        }

        $guestId = $dataInput['guest_id'] ?? null;
        $userId = $user['id'] ?? 0;
        $identifier = $userId ?: $guestId;

        $productModel = new ProductModel();
        $productVariantsModel = new ProductVariantsModel();
        $sellerModel = new SellerModel();
        $subcategoryModel = new SubcategoryModel();

        // Fetch product details
        $product = $productModel->where('id', $dataInput['product_id'])->first();

        if (!$product) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Product not found.'
            ]);
        }

        $subcategory = $subcategoryModel->select('name, id')->where('id', $product['subcategory_id'])->first();
        $product['subcategory_name'] = $subcategory ? $subcategory['name'] : null;

        // Fetch valid sellers in the same city
        $sellers = $sellerModel->where('city_id', $dataInput['city_id'])
            ->where('status', 1)
            ->where('is_delete', 0)
            ->findAll();

        $sellerIds = array_column($sellers, 'id');

        // Get similar products in the same subcategory
        $similarProducts = $productModel->where('is_delete', 0)
            ->where('status', 1)
            ->whereIn('seller_id', $sellerIds)
            ->where('subcategory_id', $subcategory['id'])
            ->findAll();

        $validProducts = [];

        foreach ($similarProducts as $similarProduct) {
            $variants = $productVariantsModel
                ->where('product_id', $similarProduct['id'])
                ->where('is_delete', 0)
                ->findAll();

            if (empty($variants)) {
                continue; // Skip product if no variants
            }

            foreach ($variants as &$variant) {
                $variant['discount_percentage'] = ($variant['price'] > 0 && $variant['discounted_price'] > 0)
                    ? round((($variant['price'] - $variant['discounted_price']) / $variant['price']) * 100)
                    : 0;

                $variant['cart_quantity'] = 0;

                if ($identifier) {
                    $cartItem = $cartsModel->where($userId ? 'user_id' : 'guest_id', $identifier)
                        ->where('product_variant_id', $variant['id'])
                        ->first();

                    if ($cartItem) {
                        $variant['cart_quantity'] = (int) $cartItem['quantity'];
                    }
                }
            }

            $similarProduct['main_img'] = base_url($similarProduct['main_img']);
            $similarProduct['variants'] = $variants;

            $validProducts[] = $similarProduct;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $validProducts
        ]);
    }
    public function fetchCategoryProductsByProductId()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input

        $userModel = new UserModel();
        $cartsModel = new CartsModel();
        $productModel = new ProductModel();
        $productVariantsModel = new ProductVariantsModel();
        $sellerModel = new SellerModel();
        $subcategoryModel = new SubcategoryModel();
        $categoryModel = new CategoryModel();

        $user = ['id' => 0];
        $authHeader = $this->request->getHeaderLine('Authorization');

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $payload = $this->authorizedToken();
            if ($payload instanceof ResponseInterface) {
                return $payload;
            }

            if (isset($payload['email'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_email_verified', 1)
                    ->where('is_delete', 0)
                    ->where('email', $payload['email'])
                    ->first();
            } elseif (isset($payload['mobile'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->where('mobile', $payload['mobile'])
                    ->first();
            }

            if (empty($user)) {
                $user = ['id' => 0];
            }
        }

        $guestId = $dataInput['guest_id'] ?? null;
        $userId = $user['id'] ?? 0;
        $identifier = $userId ?: $guestId;

        // Fetch product details
        $product = $productModel->where('id', $dataInput['product_id'])->first();

        if (!$product) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Product not found.'
            ]);
        }

        $subcategory = $subcategoryModel
            ->select('name, id')
            ->where('id', $product['subcategory_id'])
            ->first();

        $product['subcategory_name'] = $subcategory['name'] ?? null;

        $category = $categoryModel
            ->select('category_name, id')
            ->where('id', $product['category_id'])
            ->first();

        if (!$category) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Category not found.'
            ]);
        }

        // Fetch valid sellers in the same city
        $sellers = $sellerModel
            ->where('city_id', $dataInput['city_id'])
            ->where('status', 1)
            ->where('is_delete', 0)
            ->findAll();

        $sellerIds = array_column($sellers, 'id');

        // Get category-wise products
        $categoryProducts = $productModel
            ->where('is_delete', 0)
            ->where('status', 1)
            ->whereIn('seller_id', $sellerIds)
            ->where('category_id', $category['id'])
            ->findAll();

        $validProducts = [];

        foreach ($categoryProducts as $categoryProduct) {
            $variants = $productVariantsModel
                ->where('product_id', $categoryProduct['id'])
                ->where('is_delete', 0)
                ->findAll();

            if (empty($variants)) {
                continue; // Skip product if it has no variants
            }

            foreach ($variants as &$variant) {
                $variant['discount_percentage'] = ($variant['price'] > 0 && $variant['discounted_price'] > 0)
                    ? round((($variant['price'] - $variant['discounted_price']) / $variant['price']) * 100)
                    : 0;

                $variant['cart_quantity'] = 0;

                if ($identifier) {
                    $cartItem = $cartsModel->where($userId ? 'user_id' : 'guest_id', $identifier)
                        ->where('product_variant_id', $variant['id'])
                        ->first();

                    if ($cartItem) {
                        $variant['cart_quantity'] = (int) $cartItem['quantity'];
                    }
                }
            }

            $categoryProduct['main_img'] = base_url($categoryProduct['main_img']);
            $categoryProduct['variants'] = $variants;

            $validProducts[] = $categoryProduct;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $validProducts
        ]);
    }

    public function fetchAllNearbySeller()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input

        if ($this->settings['frontend_seller_section'] == 0) {
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => [],
            ]);
        }

        $sellerModel = new SellerModel();
        $productModel = new ProductModel();
        $productVariantsModel = new ProductVariantsModel();

        $geoUtils = new GeoUtils();

        $sellers = $sellerModel->where('city_id', $dataInput['city_id'])
            ->where('deliverable_area_id', $dataInput['deliverable_area_id'])
            ->where('status', 1)
            ->where('is_delete', 0)
            ->findAll();

        foreach ($sellers as &$seller) {
            // Get all product IDs for this seller
            $productIds = $productModel->where('seller_id', $seller['id'])
                ->select('id')
                ->findAll();

            // Extract product IDs into an array
            $productIdsArray = array_column($productIds, 'id');

            // Find the smallest product price from product_variants
            if (!empty($productIdsArray)) {
                $smallestPrice = $productVariantsModel->whereIn('product_id', $productIdsArray)
                    ->where('is_delete', 0)
                    ->selectMin('price')
                    ->first();
                $seller['smallest_price'] = $smallestPrice ? $smallestPrice['price'] : null;
            } else {
                $seller['smallest_price'] = null; // No products found
            }

            // Add base URL to logo
            $seller['logo'] = base_url($seller['logo']);

            $distance = $geoUtils->haversineDistance(
                $seller['latitude'],
                $seller['longitude'],
                $dataInput['latitude'],
                $dataInput['longitude']
            );

            $seller['distance'] = round($distance, 2);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $sellers,
        ]);
    }

    public function fetchAllBrand()
    {
        $brandModel = new BrandModel();

        if ($this->settings['frontend_brand_section'] == 0) {
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => [],
            ]);
        }

        $brands = $brandModel->orderBy('row_order', 'ace')
            ->findAll();

        foreach ($brands as &$brand) {
            $brand['logo'] = base_url($brand['image']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $brands,
        ]);
    }

    public function fetchDealoftheProducts()
    {
        $dataInput = $this->request->getJSON(true);

        if ($this->settings['frontend_deal_of_the_day_section'] == 0) {
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => [],
            ]);
        }

        $userModel = new UserModel();
        $cartsModel = new CartsModel();
        $authHeader = $this->request->getHeaderLine('Authorization');

        $user = ['id' => 0];

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $payload = $this->authorizedToken();
            if ($payload instanceof ResponseInterface) {
                return $payload;
            }

            if (isset($payload['email'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_email_verified', 1)
                    ->where('is_delete', 0)
                    ->where('email', $payload['email'])
                    ->first();
            } elseif (isset($payload['mobile'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->where('mobile', $payload['mobile'])
                    ->first();
            }

            if (empty($user)) {
                $user = ['id' => 0];
            }
        }

        $guestId = $dataInput['guest_id'] ?? null;

        if (!$user['id'] && empty($guestId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Guest ID is required for non-logged-in users.',
                'data' => []
            ]);
        }

        $userId = $user['id'];
        $identifier = $userId ?: $guestId;

        $sellerModel = new SellerModel();
        $productModel = new ProductModel();
        $productVariantsModel = new ProductVariantsModel();

        $sellers = $sellerModel->where('city_id', $dataInput['city_id'] ?? 0)
            ->where('status', 1)
            ->where('is_delete', 0)
            ->findAll();

        $sellerIds = array_column($sellers, 'id');

        if (empty($sellerIds)) {
            return $this->response->setJSON([
                'status' => 'success',
                'data' => [],
            ]);
        }

        $products = $productModel->where('is_delete', 0)
            ->where('status', 1)
            ->whereIn('seller_id', $sellerIds)
            ->where('deal_of_the_day', 1)
            ->findAll();

        $finalProducts = [];

        foreach ($products as $product) {
            $variants = $productVariantsModel
                ->where('product_id', $product['id'])
                ->where('is_delete', 0)
                ->findAll();

            // Filter out invalid variants (not arrays or missing price keys)
            $validVariants = [];
            foreach ($variants as $variant) {
                if (!is_array($variant) || !isset($variant['id'])) {
                    continue;
                }
                if (!isset($variant['price']) ) {
                    continue;
                }

                $variant['discount_percentage'] = 0;

                if (
                    isset($variant['price']) && $variant['price'] > 0 &&
                    isset($variant['discounted_price']) &&
                    $variant['discounted_price'] > 0 &&
                    $variant['discounted_price'] < $variant['price']
                ) {
                    $variant['discount_percentage'] = round(
                        (($variant['price'] - $variant['discounted_price']) / $variant['price']) * 100
                    );
                }

                $variant['cart_quantity'] = 0;

                if ($identifier) {
                    $cartItem = $cartsModel
                        ->where($userId ? 'user_id' : 'guest_id', $identifier)
                        ->where('product_id', $product['id'])
                        ->where('product_variant_id', $variant['id'])
                        ->first();

                    if ($cartItem) {
                        $variant['cart_quantity'] = (int) $cartItem['quantity'];
                    }
                }

                $validVariants[] = $variant;
            }

            // Only include product if valid variants exist
            if (count($validVariants) > 0) {
                $product['main_img'] = base_url($product['main_img']);
                $product['variants'] = $validVariants;
                $finalProducts[] = $product;
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $finalProducts
        ]);
    }




    public function headerBanner()
    {
        $bannerModel = new BannerModel();
        $categoryModel = new CategoryModel();
        $banners = $bannerModel->where('status', 0)->findAll();

        $bannerImages = [];
        $baseUrl = base_url(); // Get base URL

        foreach ($banners as $row) {
            $imageURL = $baseUrl . $row['banner_img'];
            $category_details = $categoryModel->where('id', $row['category_id'])->first();

            $bannerImages[] = [
                "category_id" => $row['category_id'],
                "title" => $category_details['category_name'] ?? "",
                "image" => $imageURL
            ];
        }

        return $this->respond(
            $bannerImages
        );
    }

    public function dealOfTheDayBanner()
    {
        $bannerModel = new BannerModel();
        $categoryModel = new CategoryModel();

        $banners = $bannerModel->where('status', 1)->findAll();

        $bannerImages = [];
        $baseUrl = base_url(); // Get base URL

        foreach ($banners as $row) {
            $imageURL = $baseUrl . $row['banner_img'];
            $category_details = $categoryModel->where('id', $row['category_id'])->first();

            $bannerImages[] = [
                "category_id" => $row['category_id'],
                "title" => $category_details['category_name'] ?? "",
                "image" => $imageURL
            ];
        }

        return $this->respond(
            $bannerImages
        );
    }

    public function homeSectionBanner()
    {
        $bannerModel = new BannerModel();
        $categoryModel = new CategoryModel();

        $banners = $bannerModel->where('status', 2)->findAll();

        $bannerImages = [];
        $baseUrl = base_url(); // Get base URL

        foreach ($banners as $row) {
            $imageURL = $baseUrl . $row['banner_img'];
            $category_details = $categoryModel->where('id', $row['category_id'])->first();

            $bannerImages[] = [
                "category_id" => $row['category_id'],
                "title" => $category_details['category_name'] ?? "",
                "image" => $imageURL
            ];
        }

        return $this->respond(
            $bannerImages
        );
    }

    public function footerBanner()
    {
        $bannerModel = new BannerModel();
        $categoryModel = new CategoryModel();

        $banners = $bannerModel->where('status', 3)->findAll();

        $bannerImages = [];
        $baseUrl = base_url(); // Get base URL

        foreach ($banners as $row) {
            $imageURL = $baseUrl . $row['banner_img'];
            $category_details = $categoryModel->where('id', $row['category_id'])->first();

            $bannerImages[] = [
                "category_id" => $row['category_id'],
                "title" => $category_details['category_name'] ?? "",
                "image" => $imageURL
            ];
        }

        return $this->respond(
            $bannerImages
        );
    }

    public function fetchSerachProducts()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input
        $userModel = new UserModel();
        $cartsModel = new CartsModel();
        $authHeader = $this->request->getHeaderLine('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $payload = $this->authorizedToken();
            if ($payload instanceof ResponseInterface) {
                return $payload;
            }
            if (isset($payload['email'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_email_verified', 1)
                    ->where('is_delete', 0)
                    ->where('email', $payload['email'])
                    ->first();
            } elseif (isset($payload['mobile'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->where('mobile', $payload['mobile'])
                    ->first();
            }

            // If user wasn't found, set default id
            if (empty($user)) {
                $user['id'] = 0;
            }
        } else {
            $user['id'] = 0;
        }

        $dataInput = $this->request->getJSON(true);
        $guestId = $dataInput['guest_id'] ?? null;

        // Validate guest ID for non-logged-in users
        if (!$user['id'] && empty($guestId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Guest ID is required for non-logged-in users.']);
        }





        $userId = $user['id'] ?? 0;
        $identifier = $userId ?: $guestId;
        // Load models
        $tagsModel = new TagsModel();
        $productTagModel = new ProductTagModel();
        $sellerModel = new SellerModel();
        $productModel = new ProductModel();
        $productVariantsModel = new ProductVariantsModel();

        // Fetch matching tags based on search query
        $tags = $tagsModel->like('name', $dataInput['searchQry'])->findAll();
        $tagIds = array_column($tags, 'id');

        // Ensure we don't run an invalid whereIn() if $tagIds is empty
        $productTags = !empty($tagIds) ? $productTagModel->whereIn('tag_id', $tagIds)->findAll() : [];
        $productIds = array_column($productTags, 'product_id');

        // Fetch sellers based on city ID
        $sellers = $sellerModel->where('city_id', $dataInput['city_id'])
            ->where('status', 1)
            ->where('is_delete', 0)
            ->findAll();

        $sellerIds = array_column($sellers, 'id');

        // Fetch products that match the criteria
        $productQuery  = $productModel
            ->where('is_delete', 0)
            ->where('status', 1);

        // Ensure $sellerIds is not empty before using whereIn()
        if (!empty($sellerIds)) {
            $productQuery->whereIn('seller_id', $sellerIds);
        }

        $searchTerm = $dataInput['searchQry'];

        $productQuery->groupStart()
            ->like('product_name', $searchTerm);

        // Ensure $productIds is not empty before using whereIn()
        if (!empty($productIds)) {
            $productQuery->orWhereIn('id', $productIds);
        }

        $productQuery->groupEnd();


        $products = $productQuery->findAll();

        // Append product variants & calculate discounts
        foreach ($products as &$product) {
            $product['main_img'] = base_url($product['main_img']);

            // Fetch variants
            $variants = $productVariantsModel
                ->where('product_id', $product['id'])
                ->where('is_delete', 0)
                ->findAll();

            foreach ($variants as &$variant) {
                $variant['discount_percentage'] = ($variant['discounted_price'] == 0 || $variant['price'] == 0)
                    ? 0
                    : round((($variant['price'] - $variant['discounted_price']) / $variant['price']) * 100);
                if ($identifier) {
                    $cartItem = $cartsModel->where($userId ? 'user_id' : 'guest_id', $identifier)
                        ->where('product_id', $product['id'])
                        ->where('product_variant_id', $variant['id'])
                        ->first();

                    if ($cartItem) {
                        $variant['cart_quantity'] = $cartItem['quantity'];
                    } else {
                        $variant['cart_quantity'] = 0;
                    }
                }
            }

            $product['variants'] = $variants;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $products
        ]);
    }

    public function fetchOrderList()
    {

        $userModel = new UserModel();
        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }

        $orderModel = new OrderModel();
        $orderProductModel = new OrderProductModel();

        $orders = $orderModel->select(
            ' orders.order_id as my_order_id, orders.id as order_id,  orders.order_date, orders.status, order_status_lists.status as order_status, order_status_lists.text_color, order_status_lists.bg_color, orders.tax, orders.additional_charge, orders.used_wallet_amount, orders.delivery_charge, orders.coupon_amount'
        )
            ->join('order_status_lists', 'order_status_lists.id = orders.status', 'left')
            ->where('orders.user_id', $user['id'])
            ->groupBy('orders.id')
            ->orderBy('orders.id', 'desc')
            ->findAll();

        $output = [];
        foreach ($orders as $order) {
            $selectSubtotal = $orderProductModel->select('SUM(CASE 
            WHEN order_products.discounted_price = 0 THEN order_products.price * order_products.quantity 
            ELSE order_products.discounted_price * order_products.quantity 
            END) as subtotal')
                ->join('order_return_request', 'order_return_request.order_products_id = order_products.id AND order_return_request.status IN (2, 4)', 'left')
                ->where('order_return_request.id IS NULL')
                ->where('order_products.order_id', $order['order_id'])
                ->first();

            $amount = $selectSubtotal['subtotal'] + $order['tax'] + $order['additional_charge']  - $order['used_wallet_amount'] + $order['delivery_charge'] - $order['coupon_amount'];

            $output[] = [
                'order_id' => $order['order_id'],
                'my_order_id' => $order['my_order_id'],
                'order_date' => date('d M Y, h.iA', strtotime($order['order_date'])),
                'payment' => number_format($amount, 2),
                'status' => $order['order_status'],
                'text_color' => $order['text_color'],
                'bg_color' => $order['bg_color'],
            ];
        }

        return $this->respond([
            'status' => 200,
            'result' => 'true',
            'message' => 'Order history found',
            'data' => $output
        ]);
    }

    public function fetchAddressList()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input

        $userModel = new UserModel();
        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }

        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }

        $addressModel = new AddressModel();
        if ($dataInput['deliverable_area_id']) {
            $addressList = $addressModel->where('user_id', $user['id'])->where('is_delete', 0)->where('deliverable_area_id', $dataInput['deliverable_area_id'])->findAll();
        } else {
            $addressList = $addressModel->where('user_id', $user['id'])->where('is_delete', 0)->findAll();
        }

        $output = [];
        foreach ($addressList as $address) {

            $output[] = [
                "id" => $address['id'],
                "name" => $address['user_name'],
                "phone" => $address['user_mobile'],
                "address_type" => $address['address_type'],
                "addressLines" => [
                    $address['flat'] . ", " . $address['address'],
                    $address['area'] . ", " . $address['city'],
                    $address['state'] . ", " . $address['pincode'],
                ],
                "bgColor" => $address['status'] == 1 ? 'bg-[#FFF4F1]' : 'bg-[#F7F7F7]',
                "borderColor" => $address['status'] == 1 ? 'border-red-400' : 'bg-[#F7F7F7]',
                "is_active" => $address['status']
            ];
        }

        return $this->respond([
            'status' => 200,
            'result' => 'true',
            'message' => 'Address list found',
            'data' => $output
        ]);
    }

    public function fetchCouponList()
    {

        $userModel = new UserModel();
        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }

        $couponModel = new CouponModel();
        $usedCouponModel = new UsedCouponModel();

        // Fetch used coupon IDs by the user
        $usedCoupons = $usedCouponModel->where('user_id', $user['id'])->findAll();
        $usedCouponIds = array_column($usedCoupons, 'coupon_id');

        // Set the timezone
        date_default_timezone_set($this->timeZone['timezone']);

        // Base query: Fetch coupons that are not deleted, are active, and have a valid date
        $couponModel->where('is_delete', 0)
            ->where('status', 1)
            ->where('date >=', date("Y-m-d"));

        // Include coupons that belong to the current user or all users
        $couponModel->groupStart()
            ->where('user_id', $user['id']) // Coupons for the current user
            ->orWhere('user_id', 0) // Coupons for all users
            ->groupEnd();

        // Exclude coupons that have already been used by the user, unless they are multi-time usable
        if (!empty($usedCouponIds)) {
            $couponModel->groupStart()
                ->whereNotIn('id', $usedCouponIds) // Exclude used coupons
                ->orWhere('is_multitimes', 1) // Include multi-time usable coupons
                ->groupEnd();
        }

        // Execute the query
        $couponList = $couponModel->findAll();

        $output = [];
        foreach ($couponList as $coupon) {
            $output[] = [
                "coupon_id" => $coupon['id'],
                "coupon_type" => $coupon['coupon_type'], // 1 = percentage, 2 = value	
                "title" => $coupon['coupon_title'],
                "code" => $coupon['coupon_code'],
                "image" => base_url() . $coupon['coupon_img'],
                "value" => $coupon['value'],
                "min_order_amount" => $coupon['min_order_amount'],
                "description" => $coupon['description'],
                "is_multitimes" => (int) $coupon['is_multitimes'],
                "user_id" => $coupon['user_id'],
            ];
        }

        return $this->respond([
            'status' => 200,
            'result' => 'true',
            'message' => 'Valid coupon list found',
            'data' => $output
        ]);
    }

    public function fetchWalletHistory()
    {

        $userModel = new UserModel();
        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }

        $walletModel = new WalletModel();
        $walletList = $walletModel->where('user_id', $user['id'])->findAll();

        $output = [];
        foreach ($walletList as $wallet) {

            $output[] = [
                "date" => date('d-m-Y', strtotime($wallet['date'])),
                "amount" => number_format($wallet['amount'], 2),
                "status" => $wallet['flag'],
                "color" => $wallet['flag'] == 'debit' ? 'text-red-600' : 'text-green-600'

            ];
        }
        $totalCredit = $walletModel
            ->select('SUM(CASE WHEN flag = "credit" OR flag = "top_up" THEN amount ELSE 0 END) as total_credit')
            ->where('user_id', $user['id'])
            ->first();

        $totalDebit = $walletModel
            ->select('SUM(CASE WHEN flag = "debit"  THEN amount ELSE 0 END) as total_debit')
            ->where('user_id', $user['id'])
            ->first();
        $currentAmount = $walletModel
            ->select('closing_amount')
            ->where('user_id', $user['id'])
            ->orderBy('id', 'DESC')
            ->first();

        return $this->respond([
            'status' => 200,
            'result' => 'true',
            'message' => 'Wallet transaction found',
            'data' => $output,
            'totalCredit' => $totalCredit['total_credit'] ?? 0,
            'totalDebit' => $totalDebit['total_debit'] ?? 0,
            'currentAmount' =>  $currentAmount['closing_amount'] ?? 0
        ]);
    }

    public function fetchProductsByBrandId()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input

        $sellerModel = new SellerModel();
        $productModel = new ProductModel();
        $productVariantsModel = new ProductVariantsModel();

        $sellers = $sellerModel->where('city_id', $dataInput['city_id'])
            ->where('status', 1)
            ->where('is_delete', 0)
            ->findAll();

        // Extract seller IDs
        $sellerIds = array_column($sellers, 'id');

        $products = $productModel->where('is_delete', 0)
            ->where('status', 1)
            ->whereIn('seller_id', $sellerIds) // Use whereIn for multiple sellers
            ->where('brand_id', $dataInput['brand_id'])
            ->findAll();

        // Append product variants
        foreach ($products as &$product) {
            $product['main_img'] = base_url($product['main_img']);

            // Fetch product variants
            $variants = $productVariantsModel
                ->where('product_id', $product['id'])
                ->where('is_delete', 0)
                ->findAll();

            // Loop through each variant to calculate discount
            foreach ($variants as &$variant) {
                if ($variant['discounted_price'] == 0 || $variant['price'] == 0) {
                    $variant['discount_percentage'] = 0;
                } else {
                    $variant['discount_percentage'] = round((($variant['price'] - $variant['discounted_price']) / $variant['price']) * 100);
                }
            }

            // Assign updated variants back to product
            $product['variants'] = $variants;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $products
        ]);
    }

    public function fetchProductsBySellerId()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input

        $productModel = new ProductModel();
        $productVariantsModel = new ProductVariantsModel();



        $products = $productModel->where('is_delete', 0)
            ->where('status', 1)
            ->where('seller_id', $dataInput['seller_id'])
            ->findAll();

        // Append product variants
        foreach ($products as &$product) {
            $product['main_img'] = base_url($product['main_img']);

            // Fetch product variants
            $variants = $productVariantsModel
                ->where('product_id', $product['id'])
                ->where('is_delete', 0)
                ->findAll();

            // Loop through each variant to calculate discount
            foreach ($variants as &$variant) {
                if ($variant['discounted_price'] == 0 || $variant['price'] == 0) {
                    $variant['discount_percentage'] = 0;
                } else {
                    $variant['discount_percentage'] = round((($variant['price'] - $variant['discounted_price']) / $variant['price']) * 100);
                }
            }

            // Assign updated variants back to product
            $product['variants'] = $variants;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $products
        ]);
    }

    public function insertAddress()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input
        $geoUtils = new GeoUtils();
        $userModel = new UserModel();
        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }

        // Fetch the city details
        $cityModel = new CityModel();
        $city = $cityModel->where('name', $dataInput['city'])
            ->where('is_delete', 0)
            ->first();

        if (!$city) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'City not found']);
        }

        // Fetch deliverable areas
        $deliverableAreaModel = new DeliverableAreaModel();
        $areas = $deliverableAreaModel->where('is_delete', 0)->where('city_id', $city['id'])->findAll();

        // Calculate cart totals

        foreach ($areas as $area) {
            $polygon = json_decode($area['boundary_points_web'], true);

            // Convert boundary points to a usable array
            $polygonPoints = array_map(fn($point) => [$point['lat'], $point['lng']], $polygon);

            // Check if the provided lat/lng is inside the polygon
            if ($geoUtils->pointInPolygon($dataInput['latitude'], $dataInput['longitude'], $polygonPoints)) {
                // Update existing addresses for the user to status = 0
                $addressModel = new AddressModel();
                $addressModel->where('user_id', $user['id'])->set(['status' => 0])->update();

                // Prepare the new address dataInput
                $addressData = [
                    'user_id' => $user['id'],
                    'city_id' => $city['id'],
                    'address' => $dataInput['address'],
                    'area' => $dataInput['area'],
                    'city' => $dataInput['city'],
                    'state' => $dataInput['state'],
                    'pincode' => $dataInput['pincode'],
                    'status' => 1,
                    'latitude' => $dataInput['latitude'],
                    'longitude' => $dataInput['longitude'],
                    'map_address' => $dataInput['map_address'],
                    'is_delete' => 0,
                    'deliverable_area_id' => $area['id'],
                    'address_type' => $dataInput['address_type'],
                    'flat' => $dataInput['flat'],
                    'floor' => $dataInput['floor'],
                    'user_name' => $dataInput['name'],
                    'user_mobile' => $dataInput['mobile']
                ];

                // Insert the new address
                if ($addressModel->insert($addressData)) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Address saved successfully',
                    ]);
                } else {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save address. Please try again later.']);
                }
            }
        }

        // If no area matches
        return $this->response->setJSON(['status' => 'error', 'message' => 'Address is not in a deliverable area']);
    }

    public function deleteAddress()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input
        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        $userModel = new UserModel();
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }


        $addressModel = new AddressModel();
        $deleteAddress = $addressModel->where('user_id', $user['id'])->where('id', $dataInput['address_id'])->set(['is_delete' => 1])->update();
        if ($deleteAddress) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Address deleted successfully']);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Address delete failed']);
    }

    public function activeAddress()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input
        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        $userModel = new UserModel();
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }


        $addressModel = new AddressModel();
        $addressModel->where('user_id', $user['id'])->set(['status' => 0])->update();
        $activeAddress = $addressModel->where('user_id', $user['id'])->where('id', $dataInput['address_id'])->set(['status' => 1])->update();
        if ($activeAddress) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Address active successfully']);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Address activation failed']);
    }

    public function fetchProfileDetails()
    {
        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        $userModel = new UserModel();
        $user = null;
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }

        $default_img = base_url() . $this->settings['logo'];
        $img = $default_img;

        // If user logged in via Google, use the direct image URL from Google
        if ($user['login_type'] == 'google') {
            $img = $user['img'];
        }
        // If login is normal or mobile, check if image is set
        elseif (in_array($user['login_type'], ['normal', 'mobile'])) {
            if (!empty($user['img'])) {
                // Check if it's already a full URL
                if (filter_var($user['img'], FILTER_VALIDATE_URL)) {
                    $img = $user['img'];
                } else {
                    $img = base_url() . $user['img'];
                }
            }
        }

        $output[] = [
            "name" => $user['name'],
            "mobile" => $user['mobile'],
            "email" => $user['email'],
            "is_email_verified" => $user['is_email_verified'],
            "is_mobile_verified" => $user['is_mobile_verified'],
            "country_code" => $user['country_code'],
            "img" => $img,
            "ref_code" => $user['ref_code'],
            "created_at" => $user['created_at'],
            "login_type" => $user['login_type'],
        ];
        return $this->response->setJSON(['status' => 'success', 'data' => $output]);
    }

    public function updateProfileDetails()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input
        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        $userModel = new UserModel();
        $user = null;

        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }

        $profileData = [
            'name' => $dataInput['name'],
        ];


        if ($user['is_email_verified'] == 0) {
            $existingUser = $userModel->where('email', $dataInput['email'])->first();

            if ($existingUser) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'This Email already Used'
                ]);
            } else {
                $profileData = [
                    'email' => $dataInput['email'],
                ];
            }
        }

        if ($user['is_mobile_verified'] == 0) {
            $existingUser = $userModel->where('mobile', $dataInput['mobile'])->first();

            if ($existingUser) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'This Mobile already Used'
                ]);
            } else {
                $profileData = [
                    'mobile' => $dataInput['mobile'],
                ];
            }
        }

        $updateUser = $userModel->where('id', $user['id'])->set($profileData)->update();

        if ($updateUser) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Profile updated successfully']);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'Profile update failed']);
    }

    public function fetchPrivacyPolicy()
    {
        $settings = $this->settings;
        return $this->response->setJSON(['status' => 'success', 'data' => $settings['customer_app_privacy_policy']]);
    }

    public function fetchAboutUs()
    {
        $settings = $this->settings;
        return $this->response->setJSON(['status' => 'success', 'data' => $settings['customer_app_about']]);
    }

    public function fetchContactUs()
    {
        $settings = $this->settings;

        // Decode social links JSON
        $socialLinks = json_decode($settings['social_link'], true);

        // Filter only active social links
        $filteredSocialLinks = array_filter($socialLinks, function ($link) {
            return isset($link['status']) && $link['status'] === 'on';
        });

        $output = [
            "business_name" => $settings['business_name'],
            "logo" => base_url($settings['logo']),
            "phone" => $settings['phone'],
            "email" => $settings['email'],
            "social_link" => array_values($filteredSocialLinks), // Reindex array
        ];

        return $this->response->setJSON(['status' => 'success', 'data' => $output]);
    }


    public function fetchTermsAndCondition()
    {
        $settings = $this->settings;
        return $this->response->setJSON(['status' => 'success', 'data' => $settings['customer_app_terms_policy']]);
    }

    public function fetchRefundPolicy()
    {
        $settings = $this->settings;
        return $this->response->setJSON(['status' => 'success', 'data' => $settings['customer_app_refund_policy']]);
    }

    public function fetchSorting()
    {
        $productSortTypeModel = new ProductSortTypeModel();
        return $this->response->setJSON(['status' => 'success', 'data' => $productSortTypeModel->findAll()]);
    }

    public function fetchOrderDetails()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input
        $orderProductModel = new OrderProductModel();
        $productModel = new ProductModel();
        $orderReturnRequestModel = new OrderReturnRequestModel();

        $userModel = new UserModel();
        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }
        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }



        $orderModel = new OrderModel();

        $data['orderDetails'] = $orderModel->select(
            'orders.id as order_id, orders.user_id, orders.address_id, orders.subtotal, orders.tax, orders.used_wallet_amount, orders.additional_charge, orders.delivery_method,
            orders.delivery_charge, orders.coupon_amount, orders.order_date, 
            COALESCE(DATE_FORMAT(orders.delivery_date, "%Y-%m-%d"), "") as delivery_date, 
            orders.timeslot, orders.delivery_boy_id, orders.transaction_id, orders.status, user.name as user_name, orders.order_delivery_otp,
            user.mobile as user_mobile, user.email as user_email, address.latitude, address.longitude, address.address, address.area, address.city, address.city_id, address.state, address.pincode, 
            delivery_boy.name as delivery_boy_name, delivery_boy.mobile as delivery_boy_mobile, 
            order_status_lists.status as order_status, order_status_lists.id as status_id, order_status_lists.color as order_status_color, 
            payment_method.img as payment_method_img, payment_method.title as payment_method_title'
        )
            ->join('delivery_boy', 'delivery_boy.id = orders.delivery_boy_id', 'left')
            ->join('order_status_lists', 'order_status_lists.id = orders.status', 'left')
            ->join('user', 'user.id = orders.user_id', 'left')
            ->join('address', 'address.id = orders.address_id', 'left')
            ->join('payment_method', 'payment_method.id = orders.payment_method_id', 'left')
            ->where('orders.id', $dataInput['order_id'])
            ->where('orders.user_id', $user['id'])
            ->first();


        $orderProductModel = new OrderProductModel();


        $data['orderProducts'] = $orderProductModel->select(
            'order_products.product_name, 
            order_products.product_variant_name, 
            order_products.quantity, 
            order_products.price, 
            order_products.tax_percentage, 
            order_products.tax_amount, 
            order_products.id, 
            order_products.product_id, 
            order_products.discounted_price, 
            seller.store_name, 
            CONCAT("' . base_url() . '", product.main_img) AS main_img,
            IF(order_products.discounted_price = 0, order_products.price, order_products.discounted_price) AS final_price'
        )
            ->join('seller', 'seller.id = order_products.seller_id', 'left')
            ->join('product', 'product.id = order_products.product_id', 'left')
            ->where('order_products.order_id', $dataInput['order_id'])
            ->where('order_products.user_id', $user['id'])

            ->findAll();

        $data['is_order_cancelleble'] = 0;
        $orderStatusesModel = new OrderStatusesModel();
        $orderStatusListModel = new OrderStatusListsModel();

        // Fetch all status details from the order_status_lists table
        $statusesList = $orderStatusListModel->whereIn('id', [2, 3, 4, 6])->findAll();

        // Fetch statuses for the given order ID
        $orderStatuses = $orderStatusesModel->where('orders_id', $dataInput['order_id'])->findAll();

        // Map statuses for easier use in the view
        $mappedStatuses = [];
        foreach ($orderStatuses as $status) {
            $mappedStatuses[$status['status']] = [
                'created_at' => $status['created_at'],
                'id' => $status['id']
            ];
        }
        $data['orderStages'] = [];
        foreach ($statusesList as $status) {
            $data['orderStages'][] = [
                'id' => $orderStatuses['id'] ?? null,
                'name' => $status['status'],
                'color' => $status['color'],
                'text_color' => $status['text_color'],
                'bg_color' => $status['bg_color'],
                'completed' => isset($mappedStatuses[$status['id']]) ? true : false,
                'created_at' => $mappedStatuses[$status['id']]['created_at'] ?? null,
            ];
        }
        $order_cancelled_till = $this->settings['order_cancelled_till'];
        if ($data['orderDetails']['status_id'] <= $order_cancelled_till) {
            $data['is_order_cancelleble'] = 1;
        }


        $subtotal = $orderProductModel->select('SUM(CASE 
        WHEN order_products.discounted_price = 0 THEN order_products.price * order_products.quantity 
        ELSE order_products.discounted_price * order_products.quantity 
        END) as subtotal')
            ->join('order_return_request', 'order_return_request.order_products_id = order_products.id AND order_return_request.status IN (2, 4, 5)', 'left')
            ->where('order_return_request.id IS NULL') // Exclude returned items
            ->where('order_products.order_id', $dataInput['order_id']) // No return request
            ->where('order_products.user_id', $user['id']) // No return request
            ->first();
        $data['orderDetails']['subtotal'] = $subtotal['subtotal'];

        foreach ($data['orderProducts'] as &$orderProduct) {
            $product = $productModel->select('main_img, is_returnable, return_days, id')->find($orderProduct['product_id']);
            $orderProduct['main_img'] = base_url($product['main_img']) ?? null;
            $orderProduct['is_returnable'] = 0;
            $orderProduct['differenceInDays'] = 0;

            $existingRequest = $orderReturnRequestModel
                ->where('order_id', $dataInput['order_id'])
                ->where('order_products_id', $orderProduct['id'])
                ->first();

            // Convert dates to timestamps
            $orderDeliveryDate = strtotime($data['orderDetails']['delivery_date']);
            $currentDate = strtotime(date('Y-m-d'));

            // Calculate difference in days (allowing negative values)
            $differenceInSeconds = $currentDate - $orderDeliveryDate;
            $differenceInDays = floor($differenceInSeconds / (60 * 60 * 24)); // Convert seconds to days

            // Check returnable conditions
            if ($product['is_returnable'] && $differenceInDays <= $product['return_days']) {
                $orderProduct['is_returnable'] = 1;
                $orderProduct['differenceInDays'] = $differenceInDays;
                $orderProduct['order_retuning_status'] = null;

                if (isset($existingRequest['id'])) {
                    $orderProduct['order_retuning_status'] = $existingRequest['status'];
                    $orderProduct['order_retuning_reason'] = $existingRequest['reason'];
                    $orderProduct['order_retuning_remark'] = $existingRequest['remark'];
                    $orderProduct['order_retuning_created_at'] = $existingRequest['created_at'];
                    $orderProduct['order_retuning_updated_at'] = $existingRequest['updated_at'];
                }
            }
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $data]);
    }

    public function fetchFAQ()
    {
        $faqsModel = new FaqsModel();
        return $this->response->setJSON(['status' => 'success', 'data' => $faqsModel->getAllFaqs()]);
    }

    public function fetchProductsByFilters()
    {
        $dataInput = $this->request->getJSON(true);

        $userModel = new UserModel();
        $cartsModel = new CartsModel();
        $productModel = new ProductModel();
        $productVariantsModel = new ProductVariantsModel();
        $sellerModel = new SellerModel();
        $categoryModel = new CategoryModel();
        $brandModel = new BrandModel();

        $authHeader = $this->request->getHeaderLine('Authorization');
        $user = ['id' => 0];

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $payload = $this->authorizedToken();
            if ($payload instanceof ResponseInterface) return $payload;

            if (isset($payload['email'])) {
                $user = $userModel->where(['email' => $payload['email'], 'is_active' => 1, 'is_email_verified' => 1, 'is_delete' => 0])->first();
            } elseif (isset($payload['mobile'])) {
                $user = $userModel->where(['mobile' => $payload['mobile'], 'is_active' => 1, 'is_delete' => 0])->first();
            }

            if (empty($user)) {
                $user['id'] = 0;
            }
        }

        $guestId = $dataInput['guest_id'] ?? null;
        $userId = $user['id'] ?? 0;
        $identifier = $userId ?: $guestId;

        $settings = $this->settings;
        $country = $this->country;

        $categories = $dataInput['categories'] ?? [];
        $brands = $dataInput['brands'] ?? [];
        $sellers_array = $dataInput['sellers'] ?? [];

        // Pagination parameters
        $page = $dataInput['page'] ?? 1;
        $limit = $dataInput['limit'] ?? 10;
        $offset = ($page - 1) * $limit;

        // Get sellers based on city and filters
        $sellers = empty($sellers_array)
            ? $sellerModel->where('city_id', $dataInput['city_id'])->findAll()
            : $sellerModel->where('city_id', $dataInput['city_id'])->whereIn('id', $sellers_array)->findAll();

        $sellerIds = array_column($sellers, 'id');

        // Filters
        $categoryIds = !empty($categories)
            ? array_column($categoryModel->whereIn('id', $categories)->findAll(), 'id')
            : [];

        $brandIds = !empty($brands)
            ? array_column($brandModel->whereIn('id', $brands)->findAll(), 'id')
            : [];

        $productQuery = $productModel
            ->where('is_delete', 0)
            ->where('status', 1)
            ->whereIn('seller_id', $sellerIds);

        if (!empty($categoryIds)) $productQuery->whereIn('category_id', $categoryIds);
        if (!empty($brandIds)) $productQuery->whereIn('brand_id', $brandIds);

        $totalProducts = $productQuery->countAllResults(false);

        $rawProducts = $productQuery->limit($limit, $offset)->findAll();

        $products = [];

        foreach ($rawProducts as $product) {
            $variants = $productVariantsModel
                ->where('product_id', $product['id'])
                ->where('is_delete', 0)
                ->findAll();

            if (empty($variants)) continue; // Skip product if no variants

            foreach ($variants as &$variant) {
                $variant['discount_percentage'] = ($variant['discounted_price'] > 0)
                    ? round((($variant['price'] - $variant['discounted_price']) / $variant['price']) * 100)
                    : 0;

                $variant['cart_quantity'] = 0;

                if ($identifier) {
                    $cartItem = $cartsModel
                        ->where($userId ? 'user_id' : 'guest_id', $identifier)
                        ->where('product_id', $product['id'])
                        ->where('product_variant_id', $variant['id'])
                        ->first();

                    $variant['cart_quantity'] = $cartItem['quantity'] ?? 0;
                }
            }

            $product['main_img'] = base_url($product['main_img']);
            $product['variants'] = $variants;

            $products[] = $product;
        }

        // Sorting
        switch ($dataInput['productSort'] ?? 1) {
            case 2: // Price Low to High
                usort($products, function ($a, $b) {
                    $aPrice = $a['variants'][0]['discounted_price'] ?: $a['variants'][0]['price'];
                    $bPrice = $b['variants'][0]['discounted_price'] ?: $b['variants'][0]['price'];
                    return $aPrice <=> $bPrice;
                });
                break;
            case 3: // Price High to Low
                usort($products, function ($a, $b) {
                    $aPrice = $a['variants'][0]['discounted_price'] ?: $a['variants'][0]['price'];
                    $bPrice = $b['variants'][0]['discounted_price'] ?: $b['variants'][0]['price'];
                    return $bPrice <=> $aPrice;
                });
                break;
            case 4: // Discount High to Low
                usort($products, function ($a, $b) {
                    return $b['variants'][0]['discount_percentage'] <=> $a['variants'][0]['discount_percentage'];
                });
                break;
            case 5: // Name ASC
                usort($products, fn($a, $b) => strcmp($a['product_name'], $b['product_name']));
                break;
            case 6: // Popular
                $products = array_filter($products, fn($p) => $p['popular'] == 1);
                break;
            case 7: // Deal of the day
                $products = array_filter($products, fn($p) => $p['deal_of_the_day'] == 1);
                break;
            default:
                // No extra sorting needed
                break;
        }

        // Calculate pagination info
        $totalPages = ceil($totalProducts / $limit);
        $hasNextPage = $page < $totalPages;
        $hasPreviousPage = $page > 1;

        return $this->response->setJSON([
            'status' => 'success',
            'data' => array_values($products),
            'base_url' => base_url(),
            'currency_symbol' => $country['currency_symbol'],
            'currency_symbol_position' => $settings['currency_symbol_position'],
            'pagination' => [
                'total_pages' => $totalPages,
                'has_next_page' => $hasNextPage,
                'has_previous_page' => $hasPreviousPage,
            ],
        ]);
    }

    public function fetchHomeSectionByCityId()
    {
        $dataInput = $this->request->getJSON(true);

        $userModel = new UserModel();
        $cartsModel = new CartsModel();
        $authHeader = $this->request->getHeaderLine('Authorization');

        $user = ['id' => 0];

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $payload = $this->authorizedToken();
            if ($payload instanceof ResponseInterface) {
                return $payload;
            }

            if (isset($payload['email'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_email_verified', 1)
                    ->where('is_delete', 0)
                    ->where('email', $payload['email'])
                    ->first();
            } elseif (isset($payload['mobile'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->where('mobile', $payload['mobile'])
                    ->first();
            }

            if (empty($user)) {
                $user = ['id' => 0];
            }
        }

        $guestId = $dataInput['guest_id'] ?? null;
        $userId = $user['id'];
        $identifier = $userId ?: $guestId;

        if (!isset($dataInput['city_id'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'City ID is required'
            ]);
        }

        $cityId = $dataInput['city_id'];

        $sellerModel = new SellerModel();
        $productModel = new ProductModel();
        $productVariantsModel = new ProductVariantsModel();
        $homeSectionModel = new HomeSectionModel();

        $sellers = $sellerModel->where('city_id', $cityId)
            ->where('status', 1)
            ->where('is_delete', 0)
            ->findAll();

        if (empty($sellers)) {
            return $this->response->setJSON([
                'status' => 'success',
                'data' => []
            ]);
        }

        $sellerIds = array_column($sellers, 'id');
        $homeSections = $homeSectionModel->where('is_active', 1)->findAll();

        $output = [];

        foreach ($homeSections as $homeSection) {
            $sectionData = [
                'home_section_title' => $homeSection['title'],
                'home_section_product' => []
            ];

            $productQuery = $productModel->where('is_delete', 0)
                ->where('status', 1)
                ->whereIn('seller_id', $sellerIds)
                ->where('category_id', $homeSection['category_id'])
                ->limit(6);

            if ($homeSection['subcategory_id'] != 0) {
                $productQuery->where('subcategory_id', $homeSection['subcategory_id']);
            }

            $products = $productQuery->findAll();

            if (empty($products)) {
                continue;
            }

            $validProducts = [];

            foreach ($products as $product) {
                $variants = $productVariantsModel
                    ->where('product_id', $product['id'])
                    ->where('is_delete', 0)
                    ->findAll();

                if (empty($variants)) {
                    continue; // skip product with no variants
                }

                foreach ($variants as &$variant) {
                    $variant['discount_percentage'] = ($variant['price'] > 0 && $variant['discounted_price'] > 0)
                        ? round((($variant['price'] - $variant['discounted_price']) / $variant['price']) * 100)
                        : 0;

                    $variant['cart_quantity'] = 0;

                    if ($identifier) {
                        $cartItem = $cartsModel->where($userId ? 'user_id' : 'guest_id', $identifier)
                            ->where('product_id', $product['id'])
                            ->where('product_variant_id', $variant['id'])
                            ->first();

                        if ($cartItem) {
                            $variant['cart_quantity'] = (int) $cartItem['quantity'];
                        }
                    }
                }

                // Append valid product
                $product['main_img'] = base_url($product['main_img']);
                $product['variants'] = $variants;
                $validProducts[] = $product;
            }

            if (!empty($validProducts)) {
                $sectionData['home_section_product'] = $validProducts;
                $output[] = $sectionData;
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $output
        ]);
    }
    public function fetchHighlightsByCityId()
    {
        // Get JSON input
        $dataInput = $this->request->getJSON(true);
        if (!isset($dataInput['city_id'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'City ID is required'
            ]);
        }

        $cityId = $dataInput['city_id'];



        $sellerModel = new SellerModel();
        $HighlightsModel = new HighlightsModel();

        // Fetch active sellers in the specified city
        $sellers = $sellerModel->where('city_id', $cityId)
            ->where('status', 1)
            ->where('is_delete', 0)
            ->findAll();

        if (empty($sellers)) {
            return $this->response->setJSON([
                'status' => 'success',
                'data' => []
            ]);
        }

        // Extract seller IDs
        $sellerIds = array_column($sellers, 'id');
        $highlights = $HighlightsModel->where('is_active', 1)->whereIn('seller_id', $sellerIds)->findAll();

        $output = [];
        foreach ($highlights as $highlight) {
            $highlights_row = [
                'title' => $highlight['title'],
                'description' => $highlight['description'],
                'video' => $highlight['video'],
                'image' => base_url() . $highlight['image'],
                'seller_id' => $highlight['seller_id'],
            ];


            $output[] = $highlights_row;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $output
        ]);
    }
    public function deleteUserAccount()
    {
        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        $userModel = new UserModel();
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }


        $deleteUser = $userModel->where('id', $user['id'])->set(['is_active' => 0, 'is_delete' => 1])->update();
        if ($deleteUser) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'User deleted successfully']);
        }
        return $this->response->setJSON(['status' => 'error', 'message' => 'User delete failed']);
    }
    public function fetchProductVarientByProductId()
    {

        $dataInput = $this->request->getJSON(true);

        $userModel = new UserModel();
        $cartsModel = new CartsModel();
        $authHeader = $this->request->getHeaderLine('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $payload = $this->authorizedToken();
            if ($payload instanceof ResponseInterface) {
                return $payload;
            }
            if (isset($payload['email'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_email_verified', 1)
                    ->where('is_delete', 0)
                    ->where('email', $payload['email'])
                    ->first();
            } elseif (isset($payload['mobile'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->where('mobile', $payload['mobile'])
                    ->first();
            }

            // If user wasn't found, set default id
            if (empty($user)) {
                $user['id'] = 0;
            }
        } else {
            $user['id'] = 0;
        }

        $guestId = $dataInput['guest_id'] ?? null;

        $userId = $user['id'] ?? 0;
        $identifier = $userId ?: $guestId;

        if (!isset($dataInput['product_id'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Product ID is required'
            ]);
        }

        $productId = $dataInput['product_id'];

        $productVariantsModel = new ProductVariantsModel();

        $variants = $productVariantsModel
            ->where('product_id', $productId)
            ->where('is_delete', 0)
            ->findAll();

        // Loop through each variant to calculate discount
        foreach ($variants as &$variant) {
            if ($variant['discounted_price'] == 0 || $variant['price'] == 0) {
                $variant['discount_percentage'] = 0;
            } else {
                $variant['discount_percentage'] = round((($variant['price'] - $variant['discounted_price']) / $variant['price']) * 100);
            }

            if ($identifier) {
                $cartItem = $cartsModel->where($userId ? 'user_id' : 'guest_id', $identifier)
                    ->where('product_id', $productId)
                    ->where('product_variant_id', $variant['id'])
                    ->first();

                if ($cartItem) {
                    $variant['cart_quantity'] = $cartItem['quantity'];
                } else {
                    $variant['cart_quantity'] = 0;
                }
            }
        }
        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $variants
        ]);
    }

    public function addToCart()
    {
        $dataInput = $this->request->getJSON(true);
        $userModel = new UserModel();
        $authHeader = $this->request->getHeaderLine('Authorization');
        if ($authHeader || str_starts_with($authHeader, 'Bearer ')) {
            $payload = $this->authorizedToken();
            if ($payload instanceof ResponseInterface) {
                return $payload;
            }
            if (isset($payload['email'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_email_verified', 1)
                    ->where('is_delete', 0)
                    ->where('email', $payload['email'])
                    ->first();
            } elseif (isset($payload['mobile'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->where('mobile', $payload['mobile'])
                    ->first();
            }
        } else {
            $user['id'] = null;
        }

        $guestId = $dataInput['guest_id'] ?? null;

        // Validate guest ID for non-logged-in users
        if (!$user['id'] && empty($guestId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Guest ID is required for non-logged-in users.']);
        }


        date_default_timezone_set($this->timeZone['timezone']);


        $userId = $user['id'] ?? 0;
        $identifier = $userId ?: $guestId;

        // Fetch product and variant details
        $productModel = new ProductModel();
        $variantModel = new ProductVariantsModel();

        $product = $productModel->select('id, total_allowed_quantity, slug, seller_id')->find($dataInput['product_id']);
        $variant = $variantModel->select('id, product_id, stock, is_unlimited_stock, discounted_price')
            ->where('id', $dataInput['variant_id'])
            ->first();

        if (!$product || !$variant || $variant['product_id'] != $product['id']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product or variant not found.']);
        }

        $totalAllowedQty = $product['total_allowed_quantity'];
        $availableStock = $variant['is_unlimited_stock'] ? PHP_INT_MAX : $variant['stock'];

        $cartsModel = new CartsModel();

        // Fetch existing cart item
        if ($this->settings['seller_only_one_seller_cart'] == 1) {
            $existingCartItem = $cartsModel
                ->groupStart()
                ->where($userId ? 'user_id' : 'guest_id', $identifier)
                ->groupEnd()
                ->where('product_id', $dataInput['product_id'])
                ->where('product_variant_id', $dataInput['variant_id'])
                ->where('seller_id', $product['seller_id'])
                ->first();
        } else {
            $existingCartItem = $cartsModel
                ->groupStart()
                ->where($userId ? 'user_id' : 'guest_id', $identifier)
                ->groupEnd()
                ->where('product_id', $dataInput['product_id'])
                ->where('product_variant_id', $dataInput['variant_id'])
                ->first();
        }


        $newQuantity = $existingCartItem ? $existingCartItem['quantity'] + 1 : 1;

        // Validate quantity limits
        if ($totalAllowedQty > 0 && $newQuantity > $totalAllowedQty) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'You cannot add more of this item.']);
        }

        if ($variant['is_unlimited_stock'] == 0 && $newQuantity > $availableStock) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Insufficient stock for this item.']);
        }

        // Add or update cart item
        if ($existingCartItem) {
            $cartsModel->update($existingCartItem['id'], [
                'quantity' => $newQuantity,
                'user_id' => $userId ?: $existingCartItem['user_id'],
            ]);
        } else {
            $cartsModel->insert([
                'user_id' => $userId,
                'guest_id' => $guestId,
                'product_id' => $dataInput['product_id'],
                'product_variant_id' => $dataInput['variant_id'],
                'quantity' => $newQuantity,
                'save_for_later' => 0,
                'seller_id' => $product['seller_id'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Calculate cart details
        if ($this->settings['seller_only_one_seller_cart'] == 1) {
            $cartItems = $cartsModel->where($userId > 0 ? 'user_id' : 'guest_id', $identifier)
                // ->where('seller_id', $product['seller_id'])
                ->findAll();
        } else {
            $cartItems = $cartsModel->where($userId > 0 ? 'user_id' : 'guest_id', $identifier)->findAll();
        }

        $subTotal = 0;
        $discountedPricesaving = 0;

        foreach ($cartItems as $cartItem) {
            $variant = $variantModel->select('discounted_price, price')
                ->where('id', $cartItem['product_variant_id'])
                ->where('is_delete', 0)
                ->first();

            if ($variant) {
                $itemPrice = $variant['price'];
                $discountedPrice = $variant['discounted_price'] > 0 ? $variant['discounted_price'] : $itemPrice;

                $subTotal += $cartItem['quantity'] * $discountedPrice;
                $discountedPricesaving += $cartItem['quantity'] * ($itemPrice - $discountedPrice);
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Item added to cart successfully.',
            'slug' => $product['slug'],
            'quantity' => $newQuantity,
            'itemCount' => count($cartItems),
            'subtotal' => $subTotal,
            'discountedPricesaving' => $discountedPricesaving
        ]);
    }

    public function removeFromCart()
    {
        $dataInput = $this->request->getJSON(true);
        $userModel = new UserModel();
        $authHeader = $this->request->getHeaderLine('Authorization');
        if ($authHeader || str_starts_with($authHeader, 'Bearer ')) {
            $payload = $this->authorizedToken();
            if ($payload instanceof ResponseInterface) {
                return $payload;
            }
            if (isset($payload['email'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_email_verified', 1)
                    ->where('is_delete', 0)
                    ->where('email', $payload['email'])
                    ->first();
            } elseif (isset($payload['mobile'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->where('mobile', $payload['mobile'])
                    ->first();
            }
        } else {
            $user['id'] = null;
        }
        $guestId = $dataInput['guest_id'] ?? null;

        // Validate guest ID for non-logged-in users
        if (!$user['id'] && empty($guestId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Guest ID is required for non-logged-in users.']);
        }


        date_default_timezone_set($this->timeZone['timezone']);


        $userId = $user['id'] ?? 0;
        $identifier = $userId ?: $guestId;
        date_default_timezone_set($this->timeZone['timezone']);

        $productModel = new ProductModel();
        $product = $productModel->select('id, total_allowed_quantity, slug, seller_id')->find($dataInput['product_id']);

        if (!$product) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product not found.']);
        }

        $cartsModel = new CartsModel();
        $variantModel = new ProductVariantsModel();

        // Handle cart logic based on user state
        if ($userId) {
            // Check for existing cart item by guest ID and update it to the logged-in user ID
            if ($this->settings['seller_only_one_seller_cart'] == 1) {
                $guestCartItem = $cartsModel->where($userId ? 'user_id' : 'guest_id', $identifier)
                    ->where('product_id', $dataInput['product_id'])
                    ->where('product_variant_id', $dataInput['variant_id'])
                    ->where('seller_id', $product['seller_id'])
                    ->first();
            } else {
                $guestCartItem = $cartsModel->where($userId ? 'user_id' : 'guest_id', $identifier)
                    ->where('product_id', $dataInput['product_id'])
                    ->where('product_variant_id', $dataInput['variant_id'])
                    ->first();
            }


            if ($guestCartItem) {
                // Update guest cart item to associate with logged-in user
                $cartsModel->update($guestCartItem['id'], ['user_id' => $userId]);
            }

            // Check for existing cart item for logged-in user
            if ($this->settings['seller_only_one_seller_cart'] == 1) {
                $cartItem = $cartsModel->where('user_id', $userId)
                    ->where('product_id', $dataInput['product_id'])
                    ->where('product_variant_id', $dataInput['variant_id'])
                    ->where('seller_id', $product['seller_id'])
                    ->first();
            } else {
                $cartItem = $cartsModel->where('user_id', $userId)
                    ->where('product_id', $dataInput['product_id'])
                    ->where('product_variant_id', $dataInput['variant_id'])
                    ->first();
            }
        } else {
            // Handle guest cart items
            if ($this->settings['seller_only_one_seller_cart'] == 1) {
                $cartItem = $cartsModel->where($userId ? 'user_id' : 'guest_id', $identifier)
                    ->where('product_id', $dataInput['product_id'])
                    ->where('product_variant_id', $dataInput['variant_id'])
                    ->where('seller_id', $product['seller_id'])
                    ->first();
            } else {
                $cartItem = $cartsModel->where($userId ? 'user_id' : 'guest_id', $identifier)
                    ->where('product_id', $dataInput['product_id'])
                    ->where('product_variant_id', $dataInput['variant_id'])
                    ->first();
            }
        }

        if (!$cartItem) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Item not found in the cart.']);
        }

        // Calculate the new quantity
        $newQuantity = $cartItem['quantity'] - 1;

        // Update the quantity or remove the item
        if ($newQuantity > 0) {
            $cartsModel->update($cartItem['id'], ['quantity' => $newQuantity]);
        } else {
            $cartsModel->delete($cartItem['id']);
        }

        // Re-fetch all items in the cart to recalculate totals
        if ($this->settings['seller_only_one_seller_cart'] == 1) {
            $cartItems = $cartsModel->where($userId ? 'user_id' : 'guest_id', $userId ? $userId : $guestId)->where('seller_id', $product['seller_id'])->findAll();
        } else {
            $cartItems = $cartsModel->where($userId ? 'user_id' : 'guest_id', $userId ? $userId : $guestId)->findAll();
        }

        $subTotal = 0;
        $discountedPricesaving = 0;

        // Recalculate subtotal and discounted price saving
        foreach ($cartItems as $cartItemx) {
            $variant = $variantModel->select('discounted_price, price')
                ->where('id', $cartItemx['product_variant_id'])
                ->where('is_delete', 0)
                ->first();

            if ($variant) {
                $itemPrice = $variant['price'];
                $discountedPrice = $variant['discounted_price'] > 0 ? $variant['discounted_price'] : $itemPrice;

                // Accumulate totals
                $subTotal += $cartItemx['quantity'] * $discountedPrice;
                $discountedPricesaving += $cartItemx['quantity'] * ($itemPrice - $discountedPrice);
            }
        }

        // Get the updated cart item count
        $cartItemCount = count($cartItems);

        // Return the updated response
        return $this->response->setJSON([
            'status' => 'success',
            'message' => $newQuantity > 0 ? 'Quantity updated successfully.' : 'Item removed from the cart.',
            'slug' => $product['slug'],
            'quantity' => $newQuantity,
            'itemCount' => $cartItemCount,
            'discountedPricesaving' => $discountedPricesaving,
            'subtotal' => $subTotal,
        ]);
    }
    public function fetchSelectedVarientDetails()
    {
        $dataInput = $this->request->getJSON(true);

        $userModel = new UserModel();
        $cartsModel = new CartsModel();
        $authHeader = $this->request->getHeaderLine('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $payload = $this->authorizedToken();
            if ($payload instanceof ResponseInterface) {
                return $payload;
            }
            if (isset($payload['email'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_email_verified', 1)
                    ->where('is_delete', 0)
                    ->where('email', $payload['email'])
                    ->first();
            } elseif (isset($payload['mobile'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->where('mobile', $payload['mobile'])
                    ->first();
            }

            // If user wasn't found, set default id
            if (empty($user)) {
                $user['id'] = 0;
            }
        } else {
            $user['id'] = 0;
        }

        $guestId = $dataInput['guest_id'] ?? null;

        $userId = $user['id'] ?? 0;
        $identifier = $userId ?: $guestId;

        if (!isset($dataInput['product_varient_id'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Product Varient ID is required'
            ]);
        }

        $productVarientId = $dataInput['product_varient_id'];

        $productVariantsModel = new ProductVariantsModel();

        $variant = $productVariantsModel
            ->where('id', $productVarientId)
            ->where('is_delete', 0)
            ->first();
        // Loop through each variant to calculate discount

        if ($identifier) {
            $cartItem = $cartsModel->where($userId ? 'user_id' : 'guest_id', $identifier)
                ->where('product_variant_id', $variant['id'])
                ->first();

            if ($cartItem) {
                $variant['cart_quantity'] = $cartItem['quantity'];
            } else {
                $variant['cart_quantity'] = 0;
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $variant
        ]);
    }

    public function fetchCartList()
    {
        $dataInput = $this->request->getJSON(true);
        $userModel = new UserModel();
        $user = null;
        $authHeader = $this->request->getHeaderLine('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $payload = $this->authorizedToken();
            if ($payload instanceof ResponseInterface) {
                return $payload;
            }
            if (isset($payload['email'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_email_verified', 1)
                    ->where('is_delete', 0)
                    ->where('email', $payload['email'])
                    ->first();
            } elseif (isset($payload['mobile'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->where('mobile', $payload['mobile'])
                    ->first();
            }

            // If user wasn't found, set default id
            if (empty($user)) {
                $user['id'] = 0;
            }
        } else {
            $user['id'] = 0;
        }
        $cartsModel = new CartsModel();
        $productModel = new ProductModel();
        $variantModel = new ProductVariantsModel();
        if ($user['id'] != 0) {
            if ($this->settings['seller_only_one_seller_cart'] == 1) {
                $cartItems = $cartsModel
                    ->groupStart()
                    ->where('user_id', $user['id'])
                    ->groupEnd()
                    ->where('seller_id', $dataInput['seller_id'])
                    ->findAll();
            } else {
                $cartItems = $cartsModel
                    ->groupStart()
                    ->where('user_id', $user['id'])
                    ->groupEnd()
                    ->findAll();
            }
        } else {
            if ($this->settings['seller_only_one_seller_cart'] == 1) {
                $cartItems = $cartsModel
                    ->groupStart()
                    ->where('guest_id', $dataInput['guest_id'])
                    ->groupEnd()
                    ->where('seller_id', $dataInput['seller_id'])
                    ->findAll();
            } else {
                $cartItems = $cartsModel
                    ->groupStart()
                    ->where('guest_id', $dataInput['guest_id'])
                    ->groupEnd()
                    ->findAll();
            }
        }

        $subTotal = 0;
        $discountedPricesaving = 0;

        foreach ($cartItems as &$cartItem) {
            $variant = $variantModel->select('title, discounted_price, price')
                ->where('id', $cartItem['product_variant_id'])
                ->where('is_delete', 0)
                ->first();
            $product = $productModel->select('product_name, main_img')
                ->where('id', $cartItem['product_id'])
                ->where('is_delete', 0)
                ->first();

            $cartItem['product_name'] = $product['product_name'];
            $cartItem['image'] = base_url() . $product['main_img'];
            $cartItem['weight'] = $variant['title'];

            if ($variant) {
                $itemPrice = $variant['price'];
                $discountedPrice = $variant['discounted_price'] > 0 ? $variant['discounted_price'] : $itemPrice;
                $cartItem['price'] = $variant['discounted_price'] > 0 ? $variant['discounted_price'] : $itemPrice;

                $subTotal += $cartItem['quantity'] * $discountedPrice;
                $discountedPricesaving += $cartItem['quantity'] * ($itemPrice - $discountedPrice);
            }
        }
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $cartItems,
            'itemCount' => count($cartItems),
            'subtotal' => $subTotal,
            'discountedPricesaving' => $discountedPricesaving
        ]);
    }

    public function fetchDeliveryMethods()
    {
        $settings = $this->settings;

        // Filter methods where status is 1
        if ($settings['seller_only_one_seller_cart'] == '0') {
            $methods = [
                json_decode($settings['home_delivery_status'], true),
                json_decode($settings['schedule_delivery_status'], true),
            ];
        } else {
            $methods = [
                json_decode($settings['home_delivery_status'], true),
                json_decode($settings['schedule_delivery_status'], true),
                json_decode($settings['takeaway_status'], true)
            ];
        }

        $filteredMethods = array_filter($methods, function ($method) {
            return is_array($method) && isset($method['status']) && $method['status'] == 1;
        });

        foreach ($filteredMethods as &$filteredMethod) {
            $filteredMethod['image'] = base_url() . $filteredMethod['image'];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => array_values($filteredMethods) // Reset array keys
        ]);
    }

    public function fetchPaymentMethods()
    {
        $paymentMethodModel = new PaymentMethodModel();
        $paymentMethods = $paymentMethodModel->select('id,img, title, description, api_key, screen_name')->where('status', 1)->findAll();

        foreach ($paymentMethods as &$paymentMethod) {
            $paymentMethod['img'] = base_url() . $paymentMethod['img'];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $paymentMethods // Reset array keys
        ]);
    }

    public function isItemInCart()
    {
        $dataInput = $this->request->getJSON(true);
        $user = null;
        $userModel = new UserModel();
        $productModel = new ProductModel();
        $cartsModel = new CartsModel();
        $authHeader = $this->request->getHeaderLine('Authorization');

        // Authenticate User
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $payload = $this->authorizedToken();
            if ($payload instanceof ResponseInterface) {
                return $payload;
            }
            if (isset($payload['email'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_email_verified', 1)
                    ->where('is_delete', 0)
                    ->where('email', $payload['email'])
                    ->first();
            } elseif (isset($payload['mobile'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->where('mobile', $payload['mobile'])
                    ->first();
            }
        } else {
            $user = ['id' => 0]; // Ensure `$user['id']` is always set
        }

        $guestId = $dataInput['guest_id'] ?? null;
        $userId = $user['id'] ?? 0;
        $identifier = $userId ?: $guestId;

        $data = [];

        if ($identifier) {
            // Fetch cart items for the user or guest
            $cartItems = $cartsModel->select('product_id')
                ->where($userId ? 'user_id' : 'guest_id', $identifier)
                ->orderBy('id', 'desc')
                ->findAll(3); // Use `findAll()` instead of `find()`

            foreach ($cartItems as $cartItem) {
                $product = $productModel->select('main_img')
                    ->where('id', $cartItem['product_id'])
                    ->first();

                if ($product) {
                    $data[] = base_url() . $product['main_img']; // Store images in an array
                }
            }
        }
        $cartCount = $cartsModel->select('count(id) as item_count')
            ->where($userId ? 'user_id' : 'guest_id', $identifier)
            ->first();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $data, // Returns an array of images
            'cartCount' => $cartCount['item_count']
        ]);
    }
    public function sellersCart()
    {
        $dataInput = $this->request->getJSON(true);

        $userModel = new UserModel();
        $sellerModel = new SellerModel();
        $cartsModel = new CartsModel();
        $authHeader = $this->request->getHeaderLine('Authorization');

        // Authenticate User
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $payload = $this->authorizedToken();
            if ($payload instanceof ResponseInterface) {
                return $payload;
            }
            if (isset($payload['email'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_email_verified', 1)
                    ->where('is_delete', 0)
                    ->where('email', $payload['email'])
                    ->first();
            } elseif (isset($payload['mobile'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->where('mobile', $payload['mobile'])
                    ->first();
            }
        } else {
            $user = ['id' => 0]; // Ensure `$user['id']` is always set
        }

        $guestId = $dataInput['guest_id'] ?? null;
        $userId = $user['id'] ?? 0;

        $identifier = $userId ?: $guestId;

        $data = [];

        if ($identifier) {
            $cartItems = $cartsModel->distinct()
                ->select('seller_id')
                ->where($userId ? 'user_id' : 'guest_id', $identifier)
                ->findAll();

            $sellerIds = array_column($cartItems, 'seller_id'); // Extract seller IDs

            if (!empty($sellerIds)) {
                // Fetch seller details based on unique seller IDs
                $sellers = $sellerModel->select('id, name, store_name, logo')
                    ->where('is_delete', 0)
                    ->whereIn('id', $sellerIds)
                    ->findAll();

                // Add base_url to store_logo
                foreach ($sellers as &$seller) {
                    $cartCount = $cartsModel->select('count(id) as item_count')
                        ->where($userId ? 'user_id' : 'guest_id', $identifier)
                        ->where('seller_id', $seller['id'])
                        ->first();

                    $seller['logo'] = base_url($seller['logo']);
                    $seller['totalItems'] = $cartCount['item_count'];
                }

                $data = $sellers; // Assign seller data
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    public function fetchDeliveryDate()
    {
        $days = [];

        // Loop through to create dates for the next 8 days
        for ($i = 0; $i < 9; $i++) {
            $date = new \DateTime();
            $date->modify("+$i day");

            $days[] = [
                'day' => $i === 0 ? 'Today' : $date->format('D'), // "Today" for the current day, else day name
                'date' => $date->format('M j'), // Month and day format
                'formattedDate' => $date->format('Y-m-d'), // Month and day format
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $days // Reset array keys
        ]);
    }

    public function fetchDeliveryTimeslot()
    {
        $dataInput = $this->request->getJSON(true);
        date_default_timezone_set($this->timeZone['timezone']);

        // Validate input
        if (empty($dataInput['date'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Date is required']);
        }

        // Selected date
        $selectedDate = $dataInput['date']; // Format: yyyy-mm-dd

        // Get the current date and time
        $currentDate = date('Y-m-d');
        $currentTime = date('H.i'); // Current time in 24-hour format (e.g., 13.30)

        // Initialize the model
        $timeslotModel = new TimeslotModel();

        // Fetch all time slots
        $timeSlots = $timeslotModel->select('id, mintime, maxtime')->findAll();

        // Filter slots based on the selected date
        $filteredSlots = array_filter($timeSlots, function ($slot) use ($selectedDate, $currentDate, $currentTime) {
            // If the selected date is today, filter based on the current time
            if ($selectedDate === $currentDate) {
                // If the slot's maxtime is less than or equal to the current time, exclude it
                if (floatval($slot['maxtime']) <= floatval($currentTime)) {
                    return false;
                }
            }

            // If the selected date is a future date, show all slots
            return true;
        });

        $formattedSlots = array_map(function ($slot) {
            return [
                'id' => $slot['id'],
                'mintime' => date('g:i A', strtotime($slot['mintime'])), // Convert to AM/PM format
                'maxtime' => date('g:i A', strtotime($slot['maxtime']))  // Convert to AM/PM format
            ];
        }, array_values($filteredSlots));

        if (!empty($formattedSlots)) {
            return $this->response->setJSON(['status' => 'success', 'data' => $formattedSlots]);
        }


        if (!empty($filteredSlots)) {
            return $this->response->setJSON(['status' => 'success', 'data' => array_values($filteredSlots)]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'No time slots available for the selected date']);
    }

    public function fetchOrderSummary()
    {
        $cartSummery = new CartSummery();
        $dataInput = $this->request->getJSON(true);
        $userModel = new UserModel();
        $variantModel = new ProductVariantsModel();
        $taxModel = new TaxModel();
        $productModel = new ProductModel();
        $walletModel = new WalletModel();

        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }
        $cartsModel = new CartsModel();
        if ($this->settings['seller_only_one_seller_cart'] == 1) {
            $cartItems = $cartsModel->select('id, product_id, product_variant_id, quantity')
                ->groupStart()
                ->where('user_id', $user['id'])
                ->groupEnd()
                ->where('seller_id', $dataInput['seller_id'])
                ->findAll();
        } else {
            $cartItems = $cartsModel->select('id, product_id, product_variant_id, quantity')
                ->groupStart()
                ->where('user_id', $user['id'])
                ->groupEnd()
                ->findAll();
        }
        $subTotal = 0;
        $discountedPricesaving = 0;
        $taxTotal = 0;
        $wallet = $walletModel->select('closing_amount')->where('user_id', $user['id'])->orderBy('id', 'desc')->first();
        foreach ($cartItems as &$cartItem) {
            $product = $productModel
                ->select('tax_id')
                ->where('id', $cartItem['product_id'])
                ->where('is_delete', 0)
                ->first();

            $variant = $variantModel->select('discounted_price, price')
                ->where('id', $cartItem['product_variant_id'])
                ->where('is_delete', 0)
                ->first();

            if ($variant) {
                $itemPrice = $variant['price'];
                $discountedPrice = $variant['discounted_price'] > 0 ? $variant['discounted_price'] : $itemPrice;
                $cartItem['price'] = $variant['discounted_price'] > 0 ? $variant['discounted_price'] : $itemPrice;

                $subTotal += $cartItem['quantity'] * $discountedPrice;
                $discountedPricesaving += $cartItem['quantity'] * ($itemPrice - $discountedPrice);

                if (isset($product['tax_id']) && $product['tax_id'] > 0) {
                    $tax = $taxModel->where('id', $product['tax_id'])->first();
                    if ($tax) {
                        $taxTotal += ($cartItem['quantity'] * (int)$discountedPrice) * $tax['percentage'] / 100;
                    }
                }
            }
        }
        $additional_charge_name = null;
        $additional_charge = 0;

        if ($this->settings['additional_charge_status'] == "1") {
            $additional_charge_name = $this->settings['additional_charge_name'];
            $additional_charge = $this->settings['additional_charge'];
        }
        $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $cartItems,
            'itemCount' => count($cartItems),
            'subtotal' => $subTotal,
            'discountedPricesaving' => $discountedPricesaving,
            'additional_charge_name' => $additional_charge_name,
            'additional_charge' => $additional_charge,
            'deliveryCharge' => $deliveryDetails['deliveryCharge'],
            'tax' => $taxTotal,
            'wallet' => $wallet['closing_amount'] ?? 0
        ]);
    }

    // public function placeCODOrder()
    // {
    //     helper('firebase_helper');
    //     $cartSummery = new CartSummery();
    //     $dataInput = $this->request->getJSON(true);

    //     $userModel = new UserModel();
    //     $variantModel = new ProductVariantsModel();
    //     $taxModel = new TaxModel();
    //     $productModel = new ProductModel();
    //     $walletModel = new WalletModel();
    //     $addressModel = new AddressModel();
    //     $orderModel = new OrderModel();
    //     $seller_id = $dataInput['seller_id'];
    //     $selectedDeliveryMethod = $dataInput['selectedDeliveryMethod'];
    //     $selectedPaymentMethod = $dataInput['selectedPaymentMethod'];
    //     $cartItems = $dataInput['cartitem'];
    //     $coupon = $dataInput['coupon'];
    //     $usedWalletAmount = $dataInput['usedWalletAmount'];
    //     $remainingWalletAmount = $dataInput['remainingWalletAmount'];
    //     $cartsModel = new CartsModel();
    //     $dataForNotification = [
    //         'screen' => 'Notification',
    //     ];
    //     $deviceTokenModel = new DeviceTokenModel();
    //     $payload = $this->authorizedToken();
    //     if ($payload instanceof ResponseInterface) {
    //         return $payload;
    //     }
    //     if (isset($payload['email'])) {
    //         $user = $userModel
    //             ->where('is_active', 1)
    //             ->where('is_email_verified', 1)
    //             ->where('is_delete', 0)
    //             ->where('email', $payload['email'])
    //             ->first();
    //     } elseif (isset($payload['mobile'])) {
    //         $user = $userModel
    //             ->where('is_active', 1)
    //             ->where('is_mobile_verified', 1)
    //             ->where('is_delete', 0)
    //             ->where('mobile', $payload['mobile'])
    //             ->first();
    //     }

    //     if (!$user) {
    //         return $this->respond([
    //             'status' => 404,
    //             'result' => 'false',
    //             'message' => 'User not found'
    //         ]);
    //     }
    //     $address = $addressModel->where('user_id', $user['id'])
    //         ->where('is_delete', 0)
    //         ->where('status', 1)
    //         ->first();

    //     if (!$address) {
    //         return $this->response->setJSON([
    //             'status' => 'error',
    //             'message' => 'Enter Delivery Address Details.'
    //         ]);
    //     }

    //     if (isset($seller_id) && $seller_id !== null) {
    //         // $seller_id is set and not null
    //         list($subTotal, $taxTotal) = $cartSummery->calculateCartTotals($user['id'], $seller_id);
    //     } else {
    //         // $seller_id is either not set or is null
    //         list($subTotal, $taxTotal) = $cartSummery->calculateCartTotals($user['id'], 0);
    //     }

    //     $deliveryDetails = $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
    //     $deliveryCharge = $deliveryDetails['deliveryCharge'];

    //     $coupon_amount = 0;
    //     $coupon_id = 0;
    //     if (isset($coupon['coupon_id']) && $coupon !== null && (int)$coupon['coupon_id'] > 0) {
    //         list($coupon_amount, $coupon_id) = $cartSummery->calculateCouponAmount($coupon, $subTotal, $user['id']);
    //     }

    //     $additional_charge_status = $this->settings['additional_charge_status'];
    //     $additional_charge = 0;

    //     if ($additional_charge_status == 1) {
    //         $additional_charge = (float)$this->settings['additional_charge'];
    //     }




    //     $year = date('Y'); // Get the current year
    //     $randomNumber = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT); // Generate a 6-digit random number
    //     $order_id = '#' . $year . $randomNumber;

    //     $order_delivery_otp = str_pad(mt_rand(0000, 9999), 4, '0', STR_PAD_LEFT); // Generate a 4-digit random number



    //     $transaction_id = "cod_" . $randomNumber;

    //     if ($selectedDeliveryMethod == 'scheduledDelivery') {
    //         $delivery_date = $dataInput['selectedDeliveryDate'];
    //         $timeslot = $dataInput['selectedDeliveryTime'];
    //     } else {
    //         $delivery_date = null;
    //         $timeslot = null;
    //     }

    //     if (isset($selectedPaymentMethod)) {
    //         $paymentMethode = 1;
    //     } else {
    //         $paymentMethode = 0;
    //     }

    //     $remainingAmount = $this->settings['minimum_order_amount'] - ($subTotal + $taxTotal);

    //     if ($remainingAmount > 0) {
    //         return $this->response->setJSON([
    //             'status' => 'error',
    //             'message' => 'You need to add ' . $this->country['currency_symbol'] . $remainingAmount . ' more to place your order. Please add more items to proceed.'
    //         ]);
    //     }

    //     $orderData = [
    //         'order_id' => $order_id,
    //         'user_id' => $user['id'],
    //         'address_id' => $address['id'],
    //         'payment_method_id' => $paymentMethode,
    //         'coupon_id' => $coupon_id,
    //         'delivery_date' => $delivery_date,
    //         'timeslot' => $timeslot,
    //         'order_date' => date('Y-m-d H:i:s'),
    //         'status' => 2, // received
    //         'delivery_boy_id' => 0,
    //         'transaction_id' => $transaction_id,
    //         'order_delivery_otp' => $order_delivery_otp,
    //         'subtotal' => $subTotal,
    //         'tax' => $taxTotal,
    //         'used_wallet_amount' => $usedWalletAmount,
    //         'delivery_charge' => $deliveryCharge,
    //         'coupon_amount' => $coupon_amount,
    //         'created_at' => date('Y-m-d H:i:s'),
    //         'additional_charge' => $additional_charge,
    //         'delivery_method' => $selectedDeliveryMethod
    //     ];


    //     if ($orderModel->insert($orderData)) {

    //         $orderId = $orderModel->insertID();


    //         $orderProductModel = new OrderProductModel();


    //         $subTotal = 0;
    //         $taxTotal = 0;
    //         $sellerIds = [];
    //         foreach ($cartItems as $cartItem) {
    //             // Fetch product and variant details
    //             $product = $productModel
    //                 ->select('id, product_name, tax_id, seller_id')
    //                 ->where('id', $cartItem['product_id'])
    //                 ->where('is_delete', 0)
    //                 ->first();

    //             $variant = $variantModel
    //                 ->select('id, title as product_variant_name, price, discounted_price')
    //                 ->where('id', $cartItem['product_variant_id'])
    //                 ->where('is_delete', 0)
    //                 ->first();

    //             $variantModel->where('is_unlimited_stock', 0)
    //                 ->where('id', $cartItem['product_variant_id'])
    //                 ->set('stock', 'stock - ' . (int)$cartItem['quantity'], false)
    //                 ->update();

    //             if ($product && $variant) {
    //                 $price = (float) ($variant['discounted_price'] ?: $variant['price']);
    //                 $quantity = (int) $cartItem['quantity'];
    //                 $lineTotal = $price * $quantity;
    //                 $subTotal += $lineTotal;

    //                 // Calculate tax if applicable
    //                 $taxAmount = 0;
    //                 $taxPercentage = 0;
    //                 if ($product['tax_id']) {
    //                     $tax = $taxModel->select('percentage')->where('id', $product['tax_id'])->first();
    //                     if ($tax) {
    //                         $taxPercentage = (float) $tax['percentage'];
    //                         $taxAmount = ($price * $taxPercentage / 100) * $quantity;
    //                         $taxTotal += $taxAmount;
    //                     }
    //                 }

    //                 // Prepare data for insertion into order_products table
    //                 $orderProductData = [
    //                     'user_id' => $user['id'],
    //                     'order_id' => $orderId,
    //                     'product_id' => $product['id'],
    //                     'product_variant_id' => $variant['id'],
    //                     'product_name' => $product['product_name'],
    //                     'product_variant_name' => $variant['product_variant_name'],
    //                     'quantity' => $quantity,
    //                     'price' => $variant['price'],
    //                     'discounted_price' => $variant['discounted_price'],
    //                     'tax_amount' => $taxAmount,
    //                     'tax_percentage' => $taxPercentage,
    //                     'discount' => $variant['price'] - $variant['discounted_price'],
    //                     'seller_id' => $product['seller_id'],
    //                 ];

    //                 // Insert into order_products table
    //                 $orderProductModel->insert($orderProductData);
    //                 if (!in_array($product['seller_id'], $sellerIds)) {
    //                     $sellerIds[] = $product['seller_id'];
    //                 }
    //             }
    //         }

    //         // Clear the cart after placing the order
    //         if ($this->settings['seller_only_one_seller_cart']) {
    //             $cartsModel->where('user_id', $user['id'])->where('seller_id', $seller_id)->delete();
    //         } else {
    //             $cartsModel->where('user_id', $user['id'])->delete();
    //         }


    //         if (isset($coupon['coupon_id'])) {

    //             $usedCouponModel = new UsedCouponModel();

    //             $coupon_id = $coupon['coupon_id'];

    //             $usedCouponData = [
    //                 'coupon_id' => $coupon_id,
    //                 'user_id' => $user['id'],
    //                 'date' => date('Y-m-d H:i:s'),
    //                 'order_id' => $orderId
    //             ];

    //             $couponAmountUpdateOrder = ['coupon_amount' => $coupon['value']];
    //             $orderModel->set($couponAmountUpdateOrder)->where('id', $orderId)->update();
    //             $usedCouponModel->insert($usedCouponData);
    //         }

    //         if ($usedWalletAmount > 0) {
    //             $walletModel = new WalletModel();

    //             // Fetch the last closing_amount for the user
    //             $lastWalletEntry = $walletModel
    //                 ->select('closing_amount')
    //                 ->where('user_id', $user['id'])
    //                 ->orderBy('id', 'DESC') // Assuming `id` is auto-incremented
    //                 ->first();

    //             $closingAmount = $lastWalletEntry ? (float) $lastWalletEntry['closing_amount'] - $usedWalletAmount : $remainingWalletAmount;

    //             // Prepare wallet data for insertion
    //             $walletData = [
    //                 'user_id' => $user['id'],
    //                 'ref_user_id' => 0, // Reference user ID if applicable
    //                 'amount' => $usedWalletAmount,
    //                 'closing_amount' => $closingAmount,
    //                 'flag' => 'debit',
    //                 'remark' => 'Used in Order Id: ' . $orderId,
    //                 'date' => date('Y-m-d H:i:s'),
    //             ];

    //             // Insert into wallet table
    //             $walletModel->insert($walletData);

    //             $userModel->set('wallet', $closingAmount)->where('id', $user['id'])->update();
    //         }

    //         $orderStatusesModel = new OrderStatusesModel();
    //         $orderStatusesData = [
    //             'orders_id' => $orderId,
    //             'status' => 2,
    //             'created_by' => $user['id'],
    //             'user_type' => 'Customer',
    //             'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
    //         ];
    //         $orderStatusesModel->insert($orderStatusesData);
    //         if ($this->settings['notification_order_pending_status'] == 1) {
    //             $userTokens = $deviceTokenModel->where('user_type', 2)->where('user_id', $user['id'])->orderBy('id', 'desc')->findAll(3);
    //             foreach ($userTokens as $userToken) {
    //                 if (isset($userToken['app_key'])) {
    //                     $template = $this->settings['notification_order_pending_message'];
    //                     $placeholders = [
    //                         '{userName}' => $user['name'] ?? '',
    //                         '{orderId}' => $dataInput['order_id'] ?? '',
    //                     ];

    //                     $finalMessage = str_replace(array_keys($placeholders), array_values($placeholders), $template);

    //                     sendFirebaseNotification($userToken['app_key'], 'Order placed successfully', $finalMessage, $dataForNotification);
    //                 }
    //             }
    //         }


    //         foreach ($sellerIds as $sellerId) {
    //             $userTokens = $deviceTokenModel->where('user_type', 4)->where('user_id', $sellerId)->orderBy('id', 'desc')->findAll(2);
    //             foreach ($userTokens as $userToken) {
    //                 if (isset($userToken['app_key'])) {
    //                     sendFirebaseNotification($userToken['app_key'], 'New Order arrived', 'Check now', $dataForNotification);
    //                 }
    //             }
    //         }
    //         $userTokens = $deviceTokenModel->where('user_type', 1)->orderBy('id', 'desc')->findAll();
    //         foreach ($userTokens as $userToken) {
    //             if (isset($userToken['app_key'])) {

    //                 sendFirebaseNotification($userToken['app_key'], 'New order arrived ', 'Check now', $dataForNotification);
    //             }
    //         }


    //         return $this->response->setJSON(['status' => 'success', 'message' => 'Order Placed Successfully', 'order_id' => $orderId, 'base_url' => base_url()]);
    //     } else {
    //         return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Placed Order. Please try again later.']);
    //     }
    // }

    public function placeCODOrder()
    {
        helper('firebase_helper');
        date_default_timezone_set($this->timeZone['timezone']); // Set the timezone
        $cartSummery = new CartSummery();
        $dataInput = $this->request->getJSON(true);

        // Initialize models
        $userModel = new UserModel();
        $variantModel = new ProductVariantsModel();
        $taxModel = new TaxModel();
        $productModel = new ProductModel();
        $walletModel = new WalletModel();
        $addressModel = new AddressModel();
        $orderModel = new OrderModel();
        $cartsModel = new CartsModel();
        $deviceTokenModel = new DeviceTokenModel();

        // Extract input data
        $seller_id = $dataInput['seller_id'];
        $selectedDeliveryMethod = $dataInput['selectedDeliveryMethod'];
        $selectedPaymentMethod = $dataInput['selectedPaymentMethod'];
        $cartItems = $dataInput['cartitem'];
        $coupon = $dataInput['coupon'];
        $usedWalletAmount = $dataInput['usedWalletAmount'];
        $remainingWalletAmount = $dataInput['remainingWalletAmount'];

        $dataForNotification = ['screen' => 'Notification'];

        // Validate user token
        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }

        // Get user
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }

        // Get address
        $address = $addressModel->where('user_id', $user['id'])
            ->where('is_delete', 0)
            ->where('status', 1)
            ->first();

        if (!$address) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Enter Delivery Address Details.'
            ]);
        }

        // Calculate totals
        if (isset($seller_id) && $seller_id !== null) {
            list($subTotal, $taxTotal) = $cartSummery->calculateCartTotals($user['id'], $seller_id);
        } else {
            list($subTotal, $taxTotal) = $cartSummery->calculateCartTotals($user['id'], 0);
        }

        $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
        $deliveryCharge = $deliveryDetails['deliveryCharge'];

        // Calculate coupon
        $coupon_amount = 0;
        $coupon_id = 0;
        if (isset($coupon['coupon_id']) && $coupon !== null && (int)$coupon['coupon_id'] > 0) { 
            list($coupon_amount, $coupon_id) = $cartSummery->calculateCouponAmount($coupon, $subTotal, $user['id']);
        }

        // Calculate additional charges
        $additional_charge_status = $this->settings['additional_charge_status'];
        $additional_charge = ($additional_charge_status == 1) ? (float)$this->settings['additional_charge'] : 0;
        foreach ($cartItems as $cartItemForOrderId) {
            // Fetch product details for the first item only
            $productForOrderId = $productModel
                ->select('seller_id')
                ->where('id', $cartItemForOrderId['product_id'])
                ->where('is_delete', 0)
                ->first();

            if ($productForOrderId) {
                $sellerIdForOrderId = str_pad($productForOrderId['seller_id'], 3, '0', STR_PAD_LEFT);
            }

            break; // Exit loop after first iteration
        }
        $datefororderid = date('ymd');

        $base_order_id = 'ORD-' . $datefororderid . '-' . $sellerIdForOrderId . '-';
        // Generate order details
        $randomNumber = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        // $order_id = 'ORD-' . $datefororderid . '-' . $randomNumber;
        $order_delivery_otp = str_pad(mt_rand(0000, 9999), 4, '0', STR_PAD_LEFT);
        $transaction_id = "cod_" . $randomNumber;
        $currentDateTime = date('Y-m-d H:i:s');

        // Set delivery details
        if ($selectedDeliveryMethod == 'scheduledDelivery') {
            $delivery_date = $dataInput['selectedDeliveryDate'];
            $timeslot = $dataInput['selectedDeliveryTime'];
        } else {
            $delivery_date = null;
            $timeslot = null;
        }

        $paymentMethode = isset($selectedPaymentMethod) ? 1 : 0;

        // Check minimum order amount
        $remainingAmount = $this->settings['minimum_order_amount'] - ($subTotal + $taxTotal);
        if ($remainingAmount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You need to add ' . $this->country['currency_symbol'] . $remainingAmount . ' more to place your order. Please add more items to proceed.'
            ]);
        }

        // Prepare order data
        $orderData = [
            'user_id' => $user['id'],
            'address_id' => $address['id'],
            'payment_method_id' => $paymentMethode,
            'coupon_id' => $coupon_id,
            'delivery_date' => $delivery_date,
            'timeslot' => $timeslot,
            'order_date' => $currentDateTime,
            'status' => 2, // received 
            'delivery_boy_id' => 0,
            'transaction_id' => $transaction_id,
            'order_delivery_otp' => $order_delivery_otp,
            'subtotal' => $subTotal,
            'tax' => $taxTotal,
            'used_wallet_amount' => $usedWalletAmount,
            'delivery_charge' => $deliveryCharge,
            'coupon_amount' => $coupon_amount,
            'created_at' => $currentDateTime,
            'additional_charge' => $additional_charge,
            'delivery_method' => $selectedDeliveryMethod
        ];

        // Insert order
        if (!$orderModel->insert($orderData)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Place Order. Please try again later.']);
        }

        $orderId = $orderModel->insertID();
        $order_id = $base_order_id . str_pad($orderId, 4, '0', STR_PAD_LEFT);
        $orderModel->update($orderId, ['order_id' => $order_id]);
        $orderProductModel = new OrderProductModel();
        $sellerIds = [];

        // Process cart items in batch
        $orderProductsData = [];
        $stockUpdates = [];

        foreach ($cartItems as $cartItem) {
            // Fetch product and variant details
            $product = $productModel
                ->select('id, product_name, tax_id, seller_id')
                ->where('id', $cartItem['product_id'])
                ->where('is_delete', 0)
                ->first();

            $variant = $variantModel
                ->select('id, title as product_variant_name, price, discounted_price')
                ->where('id', $cartItem['product_variant_id'])
                ->where('is_delete', 0)
                ->first();

            if ($product && $variant) {
                // Collect stock updates for batch processing
                $stockUpdates[] = [
                    'id' => $cartItem['product_variant_id'],
                    'quantity' => (int)$cartItem['quantity']
                ];

                $price = (float) ($variant['discounted_price'] ?: $variant['price']);
                $quantity = (int) $cartItem['quantity'];

                // Calculate tax if applicable
                $taxAmount = 0;
                $taxPercentage = 0;
                if ($product['tax_id']) {
                    $tax = $taxModel->select('percentage')->where('id', $product['tax_id'])->first();
                    if ($tax) {
                        $taxPercentage = (float) $tax['percentage'];
                        $taxAmount = ($price * $taxPercentage / 100) * $quantity;
                    }
                }

                // Prepare data for batch insertion
                $orderProductsData[] = [
                    'user_id' => $user['id'],
                    'order_id' => $orderId,
                    'product_id' => $product['id'],
                    'product_variant_id' => $variant['id'],
                    'product_name' => $product['product_name'],
                    'product_variant_name' => $variant['product_variant_name'],
                    'quantity' => $quantity,
                    'price' => $variant['price'],
                    'discounted_price' => $variant['discounted_price'],
                    'tax_amount' => $taxAmount,
                    'tax_percentage' => $taxPercentage,
                    'discount' => $variant['price'] - $variant['discounted_price'],
                    'seller_id' => $product['seller_id'],
                ];

                // Collect unique seller IDs
                if (!in_array($product['seller_id'], $sellerIds)) {
                    $sellerIds[] = $product['seller_id'];
                }
            }
        }

        // Batch insert order products
        if (!empty($orderProductsData)) {
            $orderProductModel->insertBatch($orderProductsData);
        }

        // Batch update stock
        foreach ($stockUpdates as $stockUpdate) {
            $variantModel->where('is_unlimited_stock', 0)
                ->where('id', $stockUpdate['id'])
                ->set('stock', 'stock - ' . $stockUpdate['quantity'], false)
                ->update();
        }

        // Clear cart
        if ($this->settings['seller_only_one_seller_cart']) {
            $cartsModel->where('user_id', $user['id'])->where('seller_id', $seller_id)->delete();
        } else {
            $cartsModel->where('user_id', $user['id'])->delete();
        }

        // Handle coupon
        if (isset($coupon['coupon_id'])) {
            $usedCouponModel = new UsedCouponModel();
            $usedCouponData = [
                'coupon_id' => $coupon['coupon_id'],
                'user_id' => $user['id'],
                'date' => $currentDateTime,
                'order_id' => $orderId
            ];
            // $orderModel->set(['coupon_amount' => $coupon['value']])->where('id', $orderId)->update();
            $usedCouponModel->insert($usedCouponData);
        }

        // Handle wallet
        if ($usedWalletAmount > 0) {
            $lastWalletEntry = $walletModel
                ->select('closing_amount')
                ->where('user_id', $user['id'])
                ->orderBy('id', 'DESC')
                ->first();

            $closingAmount = $lastWalletEntry ? (float) $lastWalletEntry['closing_amount'] - $usedWalletAmount : $remainingWalletAmount;

            $walletData = [
                'user_id' => $user['id'],
                'ref_user_id' => 0,
                'amount' => $usedWalletAmount,
                'closing_amount' => $closingAmount,
                'flag' => 'debit',
                'remark' => 'Used in Order Id: ' . $orderId,
                'date' => $currentDateTime,
            ];

            $walletModel->insert($walletData);
            $userModel->set('wallet', $closingAmount)->where('id', $user['id'])->update();
        }

        // Insert order status
        $orderStatusesModel = new OrderStatusesModel();
        $orderStatusesData = [
            'orders_id' => $orderId,
            'status' => 2,
            'created_by' => $user['id'],
            'user_type' => 'Customer',
            'created_at' => $currentDateTime,
        ];
        $orderStatusesModel->insert($orderStatusesData);

        // OPTIMIZED NOTIFICATION SYSTEM - Collect all tokens first, then send in batches
        $allNotifications = [];

        // Customer notification
        if ($this->settings['notification_order_pending_status'] == 1) {
            $userTokens = $deviceTokenModel->where('user_type', 2)->where('user_id', $user['id'])->orderBy('id', 'desc')->findAll(3);
            $template = $this->settings['notification_order_pending_message'];
            $placeholders = [
                '{userName}' => $user['name'] ?? '',
                '{orderId}' => $order_id ?? '',
            ];
            $finalMessage = str_replace(array_keys($placeholders), array_values($placeholders), $template);

            foreach ($userTokens as $userToken) {
                if (isset($userToken['app_key'])) {
                    $allNotifications[] = [
                        'token' => $userToken['app_key'],
                        'title' => 'Order placed successfully',
                        'message' => $finalMessage,
                        'data' => $dataForNotification
                    ];
                }
            }
        }

        // Seller notifications
        if (!empty($sellerIds)) {

            $builder = $deviceTokenModel->builder();

            // Subquery to get max ID (latest) per user_id for admin users
            $subQuery = $builder->select('MAX(id) as id')
                ->whereIn('user_id', $sellerIds)
                ->where('user_type', 4)
                ->groupBy('user_id')
                ->getCompiledSelect();

            // Use subquery to get the full device token records
            $sellerTokens = $deviceTokenModel
                ->where("id IN ($subQuery)", null, false)
                ->orderBy('id', 'desc')
                ->findAll();

            foreach ($sellerTokens as $sellerToken) {
                if (isset($sellerToken['app_key'])) {
                    $allNotifications[] = [
                        'token' => $sellerToken['app_key'],
                        'title' => 'New Order arrived',
                        'message' => 'Check now',
                        'data' => $dataForNotification
                    ];
                }
            }
        }

        // Admin notifications
        $builder = $deviceTokenModel->builder();

        // Subquery to get max ID (latest) per user_id for admin users
        $subQuery = $builder->select('MAX(id) as id')
            ->where('user_type', 1)
            ->groupBy('user_id')
            ->getCompiledSelect();

        // Use subquery to get the full device token records
        $adminTokens = $deviceTokenModel
            ->where("id IN ($subQuery)", null, false)
            ->orderBy('id', 'desc')
            ->findAll();

        // Admin notifications
        foreach ($adminTokens as $adminToken) {
            if (isset($adminToken['app_key'])) {
                $allNotifications[] = [
                    'token' => $adminToken['app_key'],
                    'title' => 'New order arrived',
                    'message' => 'Check now',
                    'data' => $dataForNotification
                ];
            }
        }

        // Send notifications asynchronously in background (non-blocking)
        if (!empty($allNotifications)) {
            // Process notifications in chunks to avoid memory issues
            $chunks = array_chunk($allNotifications, 10);
            foreach ($chunks as $chunk) {
                foreach ($chunk as $notification) {
                    // Use @ to suppress any FCM errors that might slow down response
                    @sendFirebaseNotification(
                        $notification['token'],
                        $notification['title'],
                        $notification['message'],
                        $notification['data']
                    );
                }
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Order Placed Successfully',
            'order_id' => $orderId,
            'base_url' => base_url()
        ]);
    }

    public function createRazorpayOrder()
    {
        $cartSummery = new CartSummery();
        date_default_timezone_set($this->timeZone['timezone']); // Set the timezone
        $dataInput = $this->request->getJSON(true);
        $userModel = new UserModel();
        $variantModel = new ProductVariantsModel();
        $taxModel = new TaxModel();
        $productModel = new ProductModel();
        $walletModel = new WalletModel();
        $addressModel = new AddressModel();
        $orderModel = new OrderModel();
        $seller_id = $dataInput['seller_id'];
        $selectedDeliveryMethod = $dataInput['selectedDeliveryMethod'];
        $selectedPaymentMethod = $dataInput['selectedPaymentMethod'];
        $cartItems = $dataInput['cartitem'];
        $coupon = $dataInput['coupon'];
        $usedWalletAmount = $dataInput['usedWalletAmount'];
        $remainingWalletAmount = $dataInput['remainingWalletAmount'];
        $cartsModel = new CartsModel();

        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }
        $address = $addressModel->where('user_id', $user['id'])
            ->where('is_delete', 0)
            ->where('status', 1)
            ->first();

        if (!$address) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Enter Delivery Address Details.'
            ]);
        }

        if (isset($seller_id) && $seller_id !== null) {
            // $seller_id is set and not null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], $seller_id);
        } else {
            // $seller_id is either not set or is null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], 0);
        }

        $deliveryDetails = $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
        $deliveryCharge = $deliveryDetails['deliveryCharge'];

        $coupon_amount = 0;
        $coupon_id = 0;
        if (isset($coupon['coupon_id']) && $coupon !== null && (int)$coupon['coupon_id'] > 0) {
            list($coupon_amount, $coupon_id) = $cartSummery->calculateCouponAmount($coupon, $subTotal, $user['id']);
        }

        $additional_charge_status = $this->settings['additional_charge_status'];
        $additional_charge = 0;

        if ($additional_charge_status == 1) {
            $additional_charge = (float)$this->settings['additional_charge'];
        }
        foreach ($cartItems as $cartItemForOrderId) {
            // Fetch product details for the first item only
            $productForOrderId = $productModel
                ->select('seller_id')
                ->where('id', $cartItemForOrderId['product_id'])
                ->where('is_delete', 0)
                ->first();

            if ($productForOrderId) {
                $sellerIdForOrderId = str_pad($productForOrderId['seller_id'], 3, '0', STR_PAD_LEFT);
            }

            break; // Exit loop after first iteration
        }
        $datefororderid = date('ymd');

        $base_order_id = 'ORD-' . $datefororderid . '-' . $sellerIdForOrderId . '-';

        if ($selectedDeliveryMethod == 'scheduledDelivery') {
            $delivery_date = $dataInput['selectedDeliveryDate'];
            $timeslot = $dataInput['selectedDeliveryTime'];
        } else {
            $delivery_date = null;
            $timeslot = null;
        }

        $paymentMethodModel = new PaymentMethodModel();

        $paymentMethods =  $paymentMethodModel->where('status', 1)->where('id', $selectedPaymentMethod)->first();
        $razorpayApiKey = $paymentMethods['api_key'];
        $razorpayApiSecret = $paymentMethods['secret_key'];

        $paymentAmount = (round($subTotal + $taxTotal + $deliveryCharge + $additional_charge - $coupon_amount - $usedWalletAmount, 2)) * 100;
        $api = new Api($razorpayApiKey, $razorpayApiSecret);

        // Order data
        $orderData = [
            'receipt'         => 'RZP_' . time(),
            'amount'          => (int)$paymentAmount,
            'currency'        => $this->country['currency_shortcut'],
        ];

        // Create order
        $order = $api->order->create($orderData);

        $reflection = new ReflectionClass($order);
        $property = $reflection->getProperty('attributes');
        $property->setAccessible(true);
        $orderAttributes = $property->getValue($order);

        if (isset($selectedPaymentMethod)) {
            $paymentMethode = $selectedPaymentMethod;
        } else {
            $paymentMethode = 0;
        }

        $remainingAmount = $this->settings['minimum_order_amount'] - ($subTotal + $taxTotal);

        if ($remainingAmount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You need to add ' . $this->country['currency_symbol'] . $remainingAmount . ' more to place your order. Please add more items to proceed.'
            ]);
        }
        $order_delivery_otp = str_pad(mt_rand(0000, 9999), 4, '0', STR_PAD_LEFT); // Generate a 4-digit random number

        $orderData = [
            // 'order_id' => $orderAttributes['id'],
            'user_id' => $user['id'],
            'address_id' => $address['id'],
            'payment_method_id' => $paymentMethode,
            'coupon_id' => $coupon_id,
            'delivery_date' => $delivery_date,
            'timeslot' => $timeslot,
            'order_date' => date('Y-m-d H:i:s'),
            'status' => 1, // payment pending
            'delivery_boy_id' => 0,
            'subtotal' => $subTotal,
            'order_delivery_otp' => $order_delivery_otp,
            'tax' => $taxTotal,
            'used_wallet_amount' => $usedWalletAmount,
            'delivery_charge' => $deliveryCharge,
            'coupon_amount' => $coupon_amount,
            'created_at' => date('Y-m-d H:i:s'),
            'additional_charge' => $additional_charge,
            'delivery_method' => $selectedDeliveryMethod
        ];


        if ($orderModel->insert($orderData)) {

            $orderId = $orderModel->insertID();
            $order_id = $base_order_id . str_pad($orderId, 4, '0', STR_PAD_LEFT);
            $orderModel->update($orderId, ['order_id' => $order_id]);

            $orderProductModel = new OrderProductModel();


            $subTotal = 0;
            $taxTotal = 0;

            foreach ($cartItems as $cartItem) {
                // Fetch product and variant details
                $product = $productModel
                    ->select('id, product_name, tax_id, seller_id')
                    ->where('id', $cartItem['product_id'])
                    ->where('is_delete', 0)
                    ->first();

                $variant = $variantModel
                    ->select('id, title as product_variant_name, price, discounted_price')
                    ->where('id', $cartItem['product_variant_id'])
                    ->where('is_delete', 0)
                    ->first();

                $variantModel->where('is_unlimited_stock', 0)
                    ->where('id', $cartItem['product_variant_id'])
                    ->set('stock', 'stock - ' . (int)$cartItem['quantity'], false)
                    ->update();

                if ($product && $variant) {
                    $price = (float) ($variant['discounted_price'] ?: $variant['price']);
                    $quantity = (int) $cartItem['quantity'];
                    $lineTotal = $price * $quantity;
                    $subTotal += $lineTotal;

                    // Calculate tax if applicable
                    $taxAmount = 0;
                    $taxPercentage = 0;
                    if ($product['tax_id']) {
                        $tax = $taxModel->select('percentage')->where('id', $product['tax_id'])->first();
                        if ($tax) {
                            $taxPercentage = (float) $tax['percentage'];
                            $taxAmount = ($price * $taxPercentage / 100) * $quantity;
                            $taxTotal += $taxAmount;
                        }
                    }

                    // Prepare data for insertion into order_products table
                    $orderProductData = [
                        'user_id' => $user['id'],
                        'order_id' => $orderId,
                        'product_id' => $product['id'],
                        'product_variant_id' => $variant['id'],
                        'product_name' => $product['product_name'],
                        'product_variant_name' => $variant['product_variant_name'],
                        'quantity' => $quantity,
                        'price' => $variant['price'],
                        'discounted_price' => $variant['discounted_price'],
                        'tax_amount' => $taxAmount,
                        'tax_percentage' => $taxPercentage,
                        'discount' => $variant['price'] - $variant['discounted_price'],
                        'seller_id' => $product['seller_id'],
                    ];

                    // Insert into order_products table
                    $orderProductModel->insert($orderProductData);
                }
            }

            // Clear the cart after placing the order
            // if ($this->settings['seller_only_one_seller_cart']) {
            //     $cartsModel->where('user_id', $user['id'])->where('seller_id', $seller_id)->delete();
            // } else {
            //     $cartsModel->where('user_id', $user['id'])->delete();
            // }


            if (isset($coupon['coupon_id'])) {

                $usedCouponModel = new UsedCouponModel();

                $coupon_id = $coupon['coupon_id'];

                $usedCouponData = [
                    'coupon_id' => $coupon_id,
                    'user_id' => $user['id'],
                    'date' => date('Y-m-d H:i:s'),
                    'order_id' => $orderId
                ];

                // $couponAmountUpdateOrder = ['coupon_amount' => $coupon['value']];
                // $orderModel->set($couponAmountUpdateOrder)->where('id', $orderId)->update();
                $usedCouponModel->insert($usedCouponData);
            }

            if ($usedWalletAmount > 0) {
                $walletModel = new WalletModel();

                // Fetch the last closing_amount for the user
                $lastWalletEntry = $walletModel
                    ->select('closing_amount')
                    ->where('user_id', $user['id'])
                    ->orderBy('id', 'DESC') // Assuming `id` is auto-incremented
                    ->first();

                $closingAmount = $lastWalletEntry ? (float) $lastWalletEntry['closing_amount'] - $usedWalletAmount : $remainingWalletAmount;

                // Prepare wallet data for insertion
                $walletData = [
                    'user_id' => $user['id'],
                    'ref_user_id' => 0, // Reference user ID if applicable
                    'amount' => $usedWalletAmount,
                    'closing_amount' => $closingAmount,
                    'flag' => 'debit',
                    'remark' => 'Used in Order Id: ' . $orderId,
                    'date' => date('Y-m-d H:i:s'),
                ];

                // Insert into wallet table
                $walletModel->insert($walletData);

                $userModel->set('wallet', $closingAmount)->where('id', $user['id'])->update();
            }

            $orderStatusesModel = new OrderStatusesModel();
            $orderStatusesData = [
                'orders_id' => $orderId,
                'status' => 1,
                'created_by' => $user['id'],
                'user_type' => 'Customer',
                'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
            ];
            $orderStatusesModel->insert($orderStatusesData);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Order created, Payment Pending', 'order_id' => $orderId, 'razorpay_api_key' => $razorpayApiKey, 'razorpay_order_id' => $orderAttributes['id'], 'amount' => round($paymentAmount, 2)]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Placed Order. Please try again later.']);
        }
    }

    public function verifyRazorpayPayment()
    {
        $dataInput = $this->request->getJSON(true);
        helper('firebase_helper');
        $userModel = new UserModel();
        $orderModel = new OrderModel();
        $cartsModel = new CartsModel();
        $dataForNotification = [
            'screen' => 'Notification',
        ];
        $deviceTokenModel = new DeviceTokenModel();
        $sellerIds = [];
        $orderProductModel = new OrderProductModel();


        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }

        $razorpayPaymentId = $dataInput['razorpay_payment_id'];
        $razorpayOrderId   = $dataInput['razorpay_order_id'];
        $razorpaySignature = $dataInput['razorpay_signature'];
        $paymentMethodModel = new PaymentMethodModel();

        $paymentMethods =  $paymentMethodModel->where('status', 1)->where('id', 2)->first();
        $razorpayApiSecret = $paymentMethods['secret_key'];
        $generatedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $razorpayApiSecret);

        try {
            $generatedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $razorpayApiSecret);

            if (hash_equals($generatedSignature, $razorpaySignature)) {

                $orderModel = new OrderModel();
                $orderModel->where('id', $dataInput['order_id'])
                    ->where('user_id', $user['id'])
                    ->set('transaction_id', $razorpayPaymentId)
                    ->set('payment_json', json_encode($dataInput))
                    ->set('status', 2)
                    ->update();

                $cartsModel = new CartsModel();
                $userModel = new UserModel();
                // Clear the cart after placing the order
                if ($this->settings['seller_only_one_seller_cart']) {
                    $cartsModel->where('user_id', $user['id'])->where('seller_id', $dataInput['seller_id'])->delete();
                } else {
                    $cartsModel->where('user_id', $user['id'])->delete();
                }

                $orderStatusesModel = new OrderStatusesModel();
                $data = [
                    'orders_id' => $dataInput['order_id'],
                    'status' => 2,
                    'created_by' => $user['id'],
                    'user_type' => 'Customer',
                    'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
                ];
                $orderStatusesModel->insert($data);
                $findOrderedProductsInfo = $orderProductModel->select('seller_id')->where('order_id', $dataInput['order_id'])->findAll();

                foreach ($findOrderedProductsInfo as $findOrderedProductInfo) {
                    if (!in_array($findOrderedProductInfo['seller_id'], $sellerIds)) {
                        $sellerIds[] = $findOrderedProductInfo['seller_id'];
                    }
                    if ($this->settings['seller_only_one_seller_cart']) {
                        $cartsModel->where('user_id', $user['id'])->where('seller_id', $findOrderedProductInfo['seller_id'])->delete();
                    }
                }
                if (!$this->settings['seller_only_one_seller_cart'])  {
                    $cartsModel->where('user_id', $user['id'])->delete();
                }
                // OPTIMIZED NOTIFICATION SYSTEM - Collect all tokens first, then send in batches
                $allNotifications = [];

                // Customer notification
                if ($this->settings['notification_order_pending_status'] == 1) {
                    $userTokens = $deviceTokenModel->where('user_type', 2)->where('user_id', $user['id'])->orderBy('id', 'desc')->findAll(1);
                    $template = $this->settings['notification_order_pending_message'];
                    $placeholders = [
                        '{userName}' => $user['name'] ?? '',
                        '{orderId}' => $order_id ?? '',
                    ];
                    $finalMessage = str_replace(array_keys($placeholders), array_values($placeholders), $template);

                    foreach ($userTokens as $userToken) {
                        if (isset($userToken['app_key'])) {
                            $allNotifications[] = [
                                'token' => $userToken['app_key'],
                                'title' => 'Order placed successfully',
                                'message' => $finalMessage,
                                'data' => $dataForNotification
                            ];
                        }
                    }
                }

                // Seller notifications
                if (!empty($sellerIds)) {

                    $builder = $deviceTokenModel->builder();

                    // Subquery to get max ID (latest) per user_id for admin users
                    $subQuery = $builder->select('MAX(id) as id')
                        ->whereIn('user_id', $sellerIds)
                        ->where('user_type', 4)
                        ->groupBy('user_id')
                        ->getCompiledSelect();

                    // Use subquery to get the full device token records
                    $sellerTokens = $deviceTokenModel
                        ->where("id IN ($subQuery)", null, false)
                        ->orderBy('id', 'desc')
                        ->findAll();

                    foreach ($sellerTokens as $sellerToken) {
                        if (isset($sellerToken['app_key'])) {
                            $allNotifications[] = [
                                'token' => $sellerToken['app_key'],
                                'title' => 'New Order arrived',
                                'message' => 'Check now',
                                'data' => $dataForNotification
                            ];
                        }
                    }
                }

                // Admin notifications
                $builder = $deviceTokenModel->builder();

                // Subquery to get max ID (latest) per user_id for admin users
                $subQuery = $builder->select('MAX(id) as id')
                    ->where('user_type', 1)
                    ->groupBy('user_id')
                    ->getCompiledSelect();

                // Use subquery to get the full device token records
                $adminTokens = $deviceTokenModel
                    ->where("id IN ($subQuery)", null, false)
                    ->orderBy('id', 'desc')
                    ->findAll();

                foreach ($adminTokens as $adminToken) {
                    if (isset($adminToken['app_key'])) {
                        $allNotifications[] = [
                            'token' => $adminToken['app_key'],
                            'title' => 'New order arrived',
                            'message' => 'Check now',
                            'data' => $dataForNotification
                        ];
                    }
                }

                // Send notifications asynchronously in background (non-blocking)
                if (!empty($allNotifications)) {
                    // Process notifications in chunks to avoid memory issues
                    $chunks = array_chunk($allNotifications, 10);
                    foreach ($chunks as $chunk) {
                        foreach ($chunk as $notification) {
                            // Use @ to suppress any FCM errors that might slow down response
                            @sendFirebaseNotification(
                                $notification['token'],
                                $notification['title'],
                                $notification['message'],
                                $notification['data']
                            );
                        }
                    }
                }

                return $this->response->setJSON(['status' => 'success', 'message' => 'Order Placed Successfully, Payment verified successfully.', 'base_url' => base_url()]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Payment Signature not Verified']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function createPaypalOrder()
    {
        $cartSummery = new CartSummery();

        $dataInput = $this->request->getJSON(true);
        $userModel = new UserModel();
        $variantModel = new ProductVariantsModel();
        $taxModel = new TaxModel();
        $productModel = new ProductModel();
        $walletModel = new WalletModel();
        $addressModel = new AddressModel();
        $orderModel = new OrderModel();
        $seller_id = $dataInput['seller_id'];
        $selectedDeliveryMethod = $dataInput['selectedDeliveryMethod'];
        $selectedPaymentMethod = $dataInput['selectedPaymentMethod'];
        $cartItems = $dataInput['cartitem'];
        $coupon = $dataInput['coupon'];
        $usedWalletAmount = $dataInput['usedWalletAmount'];
        $remainingWalletAmount = $dataInput['remainingWalletAmount'];
        $cartsModel = new CartsModel();

        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }
        $address = $addressModel->where('user_id', $user['id'])
            ->where('is_delete', 0)
            ->where('status', 1)
            ->first();

        if (!$address) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Enter Delivery Address Details.'
            ]);
        }

        if (isset($seller_id) && $seller_id !== null) {
            // $seller_id is set and not null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], $seller_id);
        } else {
            // $seller_id is either not set or is null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], 0);
        }

        $deliveryDetails = $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
        $deliveryCharge = $deliveryDetails['deliveryCharge'];

        $coupon_amount = 0;
        $coupon_id = 0;
        if (isset($coupon['coupon_id']) && $coupon !== null && (int)$coupon['coupon_id'] > 0) {
            list($coupon_amount, $coupon_id) = $cartSummery->calculateCouponAmount($coupon, $subTotal, $user['id']);
        }

        $additional_charge_status = $this->settings['additional_charge_status'];
        $additional_charge = 0;

        if ($additional_charge_status == 1) {
            $additional_charge = (float)$this->settings['additional_charge'];
        }
        foreach ($cartItems as $cartItemForOrderId) {
            // Fetch product details for the first item only
            $productForOrderId = $productModel
                ->select('seller_id')
                ->where('id', $cartItemForOrderId['product_id'])
                ->where('is_delete', 0)
                ->first();

            if ($productForOrderId) {
                $sellerIdForOrderId = str_pad($productForOrderId['seller_id'], 3, '0', STR_PAD_LEFT);
            }

            break; // Exit loop after first iteration
        }
        $datefororderid = date('ymd');

        $base_order_id = 'ORD-' . $datefororderid . '-' . $sellerIdForOrderId . '-';


        if ($selectedDeliveryMethod == 'scheduledDelivery') {
            $delivery_date = $dataInput['selectedDeliveryDate'];
            $timeslot = $dataInput['selectedDeliveryTime'];
        } else {
            $delivery_date = null;
            $timeslot = null;
        }

        $paymentMethodModel = new PaymentMethodModel();

        $paymentMethods =  $paymentMethodModel->where('status', 1)->where('id', $selectedPaymentMethod)->first();
        $paypalApiKey = $paymentMethods['api_key'];
        $paypalApiSecret = $paymentMethods['secret_key'];

        $paymentAmount = (round($subTotal + $taxTotal + $deliveryCharge + $additional_charge - $coupon_amount - $usedWalletAmount, 2)) * 100;
        $url = 'https://api-m.paypal.com/v2/checkout/orders';
        $headers = [
            'Authorization: Basic ' . base64_encode($paypalApiKey . ':' . $paypalApiSecret),
            'Content-Type: application/json',
        ];

        if (isset($selectedPaymentMethod)) {
            $paymentMethode = $selectedPaymentMethod;
        } else {
            $paymentMethode = 0;
        }

        $items = [];

        foreach ($cartItems as $cartItem) {
            $product = $productModel->select('product_name')->where('id', $cartItem['product_id'])->where('is_delete', 0)->first();
            $variant = $variantModel->select('price, discounted_price')->where('id', $cartItem['product_variant_id'])->where('is_delete', 0)->first();

            $basePrice = $variant['discounted_price'] > 0 ? $variant['discounted_price'] : $variant['price'];
            $finalPrice = number_format($basePrice, 2, '.', '');

            $items[] = [
                'name' => $product['product_name'],
                'unit_amount' => [
                    'currency_code' => $this->country['currency_shortcut'],
                    'value' => $finalPrice,
                ],
                'quantity' => $cartItem['quantity'],
            ];
        }

        $purchaseData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $this->country['currency_shortcut'],
                        'value' => number_format($paymentAmount / 100, 2, '.', ''),
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => $this->country['currency_shortcut'],
                                'value' => str_replace(',', '', number_format($subTotal, 2)),
                            ],
                            'shipping' => [
                                'currency_code' => $this->country['currency_shortcut'],
                                'value' => number_format($deliveryCharge + $additional_charge, 2),
                            ],
                            'discount' => [
                                'currency_code' => $this->country['currency_shortcut'],
                                'value' => number_format($coupon_amount + $usedWalletAmount, 2),
                            ],
                            'tax_total' => [
                                'currency_code' => $this->country['currency_shortcut'],
                                'value' => number_format($taxTotal, 2),
                            ]
                        ],
                    ],
                    'items' => $items,
                ],
            ],
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($purchaseData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);


        $remainingAmount = $this->settings['minimum_order_amount'] - ($subTotal + $taxTotal);

        if ($remainingAmount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You need to add ' . $this->country['currency_symbol'] . $remainingAmount . ' more to place your order. Please add more items to proceed.'
            ]);
        }


        if ($httpCode === 201) {
            $order = json_decode($response, true);
            $order_delivery_otp = str_pad(mt_rand(0000, 9999), 4, '0', STR_PAD_LEFT); // Generate a 4-digit random number

            $orderData = [
                // 'order_id' => $order['id'],
                'user_id' => $user['id'],
                'address_id' => $address['id'],
                'payment_method_id' => $paymentMethode,
                'coupon_id' => $coupon_id,
                'delivery_date' => $delivery_date,
                'timeslot' => $timeslot,
                'order_date' => date('Y-m-d H:i:s'),
                'status' => 1, // payment pending
                'delivery_boy_id' => 0,
                'order_delivery_otp' => $order_delivery_otp,
                'subtotal' => $subTotal,
                'tax' => $taxTotal,
                'used_wallet_amount' => $usedWalletAmount,
                'delivery_charge' => $deliveryCharge,
                'coupon_amount' => $coupon_amount,
                'created_at' => date('Y-m-d H:i:s'),
                'additional_charge' => $additional_charge,
                'delivery_method' => $selectedDeliveryMethod
            ];


            if ($orderModel->insert($orderData)) {

                $orderId = $orderModel->insertID();

                $order_id = $base_order_id . str_pad($orderId, 4, '0', STR_PAD_LEFT);
                $orderModel->update($orderId, ['order_id' => $order_id]);
                $orderProductModel = new OrderProductModel();


                $subTotal = 0;
                $taxTotal = 0;

                foreach ($cartItems as $cartItem) {
                    // Fetch product and variant details
                    $product = $productModel
                        ->select('id, product_name, tax_id, seller_id')
                        ->where('id', $cartItem['product_id'])
                        ->where('is_delete', 0)
                        ->first();

                    $variant = $variantModel
                        ->select('id, title as product_variant_name, price, discounted_price')
                        ->where('id', $cartItem['product_variant_id'])
                        ->where('is_delete', 0)
                        ->first();

                    $variantModel->where('is_unlimited_stock', 0)
                        ->where('id', $cartItem['product_variant_id'])
                        ->set('stock', 'stock - ' . (int)$cartItem['quantity'], false)
                        ->update();

                    if ($product && $variant) {
                        $price = (float) ($variant['discounted_price'] ?: $variant['price']);
                        $quantity = (int) $cartItem['quantity'];
                        $lineTotal = $price * $quantity;
                        $subTotal += $lineTotal;

                        // Calculate tax if applicable
                        $taxAmount = 0;
                        $taxPercentage = 0;
                        if ($product['tax_id']) {
                            $tax = $taxModel->select('percentage')->where('id', $product['tax_id'])->first();
                            if ($tax) {
                                $taxPercentage = (float) $tax['percentage'];
                                $taxAmount = ($price * $taxPercentage / 100) * $quantity;
                                $taxTotal += $taxAmount;
                            }
                        }

                        // Prepare data for insertion into order_products table
                        $orderProductData = [
                            'user_id' => $user['id'],
                            'order_id' => $orderId,
                            'product_id' => $product['id'],
                            'product_variant_id' => $variant['id'],
                            'product_name' => $product['product_name'],
                            'product_variant_name' => $variant['product_variant_name'],
                            'quantity' => $quantity,
                            'price' => $variant['price'],
                            'discounted_price' => $variant['discounted_price'],
                            'tax_amount' => $taxAmount,
                            'tax_percentage' => $taxPercentage,
                            'discount' => $variant['price'] - $variant['discounted_price'],
                            'seller_id' => $product['seller_id'],
                        ];

                        // Insert into order_products table
                        $orderProductModel->insert($orderProductData);
                    }
                }

                // Clear the cart after placing the order
                if ($this->settings['seller_only_one_seller_cart']) {
                    $cartsModel->where('user_id', $user['id'])->where('seller_id', $seller_id)->delete();
                } else {
                    $cartsModel->where('user_id', $user['id'])->delete();
                }


                if (isset($coupon['coupon_id'])) {

                    $usedCouponModel = new UsedCouponModel();

                    $coupon_id = $coupon['coupon_id'];

                    $usedCouponData = [
                        'coupon_id' => $coupon_id,
                        'user_id' => $user['id'],
                        'date' => date('Y-m-d H:i:s'),
                        'order_id' => $orderId
                    ];

                    // $couponAmountUpdateOrder = ['coupon_amount' => $coupon['value']];
                    // $orderModel->set($couponAmountUpdateOrder)->where('id', $orderId)->update();
                    $usedCouponModel->insert($usedCouponData);
                }

                if ($usedWalletAmount > 0) {
                    $walletModel = new WalletModel();

                    // Fetch the last closing_amount for the user
                    $lastWalletEntry = $walletModel
                        ->select('closing_amount')
                        ->where('user_id', $user['id'])
                        ->orderBy('id', 'DESC') // Assuming `id` is auto-incremented
                        ->first();

                    $closingAmount = $lastWalletEntry ? (float) $lastWalletEntry['closing_amount'] - $usedWalletAmount : $remainingWalletAmount;

                    // Prepare wallet data for insertion
                    $walletData = [
                        'user_id' => $user['id'],
                        'ref_user_id' => 0, // Reference user ID if applicable
                        'amount' => $usedWalletAmount,
                        'closing_amount' => $closingAmount,
                        'flag' => 'debit',
                        'remark' => 'Used in Order Id: ' . $orderId,
                        'date' => date('Y-m-d H:i:s'),
                    ];

                    // Insert into wallet table
                    $walletModel->insert($walletData);

                    $userModel->set('wallet', $closingAmount)->where('id', $user['id'])->update();
                }

                $orderStatusesModel = new OrderStatusesModel();
                $orderStatusesData = [
                    'orders_id' => $orderId,
                    'status' => 1,
                    'created_by' => $user['id'],
                    'user_type' => 'Customer',
                    'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
                ];
                $orderStatusesModel->insert($orderStatusesData);


                return $this->response->setJSON(['status' => 'success', 'message' => 'Order created, Payment Pending', 'paypal_order_id' => $order['id'], 'order_id' => $orderId, 'amount' => round($paymentAmount, 2), 'purchaseData' => $purchaseData, 'paypal_api_key' => $paypalApiKey, 'paypal_secret_key' => $paypalApiSecret, 'paypal_response' => json_decode($response, true)]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Placed Order. Please try again later.']);
            }
        } else {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Unable to create PayPal order.',
                'details' => json_decode($response, true),
                'curl_error' => $curlError,
                'purchaseData' => $purchaseData
            ]);
        }
    }

    public function capturePaypalOrder()
    {
        helper('firebase_helper');
        $dataInput = $this->request->getJSON(true);
        $userModel = new UserModel();
        $orderModel = new OrderModel();
        $cartsModel = new CartsModel();
        $dataForNotification = [
            'screen' => 'Notification',
        ];
        $deviceTokenModel = new DeviceTokenModel();
        $sellerIds = [];
        $orderProductModel = new OrderProductModel();


        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }
        $paypal_order_id = $dataInput['paypal_order_id'];
        $paymentMethodModel = new PaymentMethodModel();

        $paymentMethods =  $paymentMethodModel->where('status', 1)->where('id', 3)->first();
        $paypalApiKey = $paymentMethods['api_key'];
        $paypalApiSecret = $paymentMethods['secret_key'];


        $tokenUrl = "https://api-m.paypal.com/v1/oauth2/token";
        $tokenCh = curl_init($tokenUrl);
        curl_setopt($tokenCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($tokenCh, CURLOPT_USERPWD, $paypalApiKey . ":" . $paypalApiSecret);
        curl_setopt($tokenCh, CURLOPT_POST, true);
        curl_setopt($tokenCh, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($tokenCh, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

        $tokenResponse = curl_exec($tokenCh);
        curl_close($tokenCh);

        $tokenData = json_decode($tokenResponse, true);
        $accessToken = $tokenData['access_token'];


        $url = "https://api-m.paypal.com/v2/checkout/orders/$paypal_order_id";

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Use HTTPGET instead of the non-standard GET option
        curl_setopt($ch, CURLOPT_HTTPGET, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $captureData = json_decode($response, true);

            $orderModel = new OrderModel();
            $orderModel->where('order_id', $paypal_order_id)
                ->set('status', 2)
                ->set('payment_json', json_encode($captureData))
                ->update();

            $order = $orderModel->where('order_id', $paypal_order_id)->first();

            $cartsModel = new CartsModel();
            $userModel = new UserModel();
            // Clear the cart after placing the order
            if ($this->settings['seller_only_one_seller_cart']) {
                $cartsModel->where('user_id', $user['id'])->where('seller_id', $dataInput['seller_id'])->delete();
            } else {
                $cartsModel->where('user_id', $user['id'])->delete();
            }

            $orderStatusesModel = new OrderStatusesModel();
            $data = [
                'orders_id' => $order['id'],
                'status' => 2,
                'created_by' => $user['id'],
                'user_type' => 'Customer',
                'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
            ];
            $orderStatusesModel->insert($data);
            $findOrderedProductsInfo = $orderProductModel->select('seller_id')->where('order_id', $dataInput['order_id'])->findAll();

            foreach ($findOrderedProductsInfo as $findOrderedProductInfo) {
                if (!in_array($findOrderedProductInfo['seller_id'], $sellerIds)) {
                    $sellerIds[] = $findOrderedProductInfo['seller_id'];
                }
            }
            // OPTIMIZED NOTIFICATION SYSTEM - Collect all tokens first, then send in batches
            $allNotifications = [];

            // Customer notification
            if ($this->settings['notification_order_pending_status'] == 1) {
                $userTokens = $deviceTokenModel->where('user_type', 2)->where('user_id', $user['id'])->orderBy('id', 'desc')->findAll(1);
                $template = $this->settings['notification_order_pending_message'];
                $placeholders = [
                    '{userName}' => $user['name'] ?? '',
                    '{orderId}' => $order_id ?? '',
                ];
                $finalMessage = str_replace(array_keys($placeholders), array_values($placeholders), $template);

                foreach ($userTokens as $userToken) {
                    if (isset($userToken['app_key'])) {
                        $allNotifications[] = [
                            'token' => $userToken['app_key'],
                            'title' => 'Order placed successfully',
                            'message' => $finalMessage,
                            'data' => $dataForNotification
                        ];
                    }
                }
            }

            // Seller notifications
            if (!empty($sellerIds)) {

                $builder = $deviceTokenModel->builder();

                // Subquery to get max ID (latest) per user_id for admin users
                $subQuery = $builder->select('MAX(id) as id')
                    ->whereIn('user_id', $sellerIds)
                    ->where('user_type', 4)
                    ->groupBy('user_id')
                    ->getCompiledSelect();

                // Use subquery to get the full device token records
                $sellerTokens = $deviceTokenModel
                    ->where("id IN ($subQuery)", null, false)
                    ->orderBy('id', 'desc')
                    ->findAll();

                foreach ($sellerTokens as $sellerToken) {
                    if (isset($sellerToken['app_key'])) {
                        $allNotifications[] = [
                            'token' => $sellerToken['app_key'],
                            'title' => 'New Order arrived',
                            'message' => 'Check now',
                            'data' => $dataForNotification
                        ];
                    }
                }
            }

            // Admin notifications
            $builder = $deviceTokenModel->builder();

            // Subquery to get max ID (latest) per user_id for admin users
            $subQuery = $builder->select('MAX(id) as id')
                ->where('user_type', 1)
                ->groupBy('user_id')
                ->getCompiledSelect();

            // Use subquery to get the full device token records
            $adminTokens = $deviceTokenModel
                ->where("id IN ($subQuery)", null, false)
                ->orderBy('id', 'desc')
                ->findAll();

            foreach ($adminTokens as $adminToken) {
                if (isset($adminToken['app_key'])) {
                    $allNotifications[] = [
                        'token' => $adminToken['app_key'],
                        'title' => 'New order arrived',
                        'message' => 'Check now',
                        'data' => $dataForNotification
                    ];
                }
            }

            // Send notifications asynchronously in background (non-blocking)
            if (!empty($allNotifications)) {
                // Process notifications in chunks to avoid memory issues
                $chunks = array_chunk($allNotifications, 10);
                foreach ($chunks as $chunk) {
                    foreach ($chunk as $notification) {
                        // Use @ to suppress any FCM errors that might slow down response
                        @sendFirebaseNotification(
                            $notification['token'],
                            $notification['title'],
                            $notification['message'],
                            $notification['data']
                        );
                    }
                }
            }

            return $this->response->setJSON(['status' => 'success', 'message' => 'Order Placed Successfully, Payment verified successfully.', 'base_url' => base_url(), 'order_id' => $order['id']]);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Unable to capture PayPal payment.', 'response' => $response, 'httpCode' => $httpCode]);
        }
    }

    public function createPaystackOrder()
    {
        $cartSummery = new CartSummery();
        helper('firebase_helper');
        $dataInput = $this->request->getJSON(true);
        $userModel = new UserModel();
        $variantModel = new ProductVariantsModel();
        $taxModel = new TaxModel();
        $productModel = new ProductModel();
        $walletModel = new WalletModel();
        $addressModel = new AddressModel();
        $orderModel = new OrderModel();
        $seller_id = $dataInput['seller_id'];
        $selectedDeliveryMethod = $dataInput['selectedDeliveryMethod'];
        $selectedPaymentMethod = $dataInput['selectedPaymentMethod'];
        $cartItems = $dataInput['cartitem'];
        $coupon = $dataInput['coupon'];
        $usedWalletAmount = $dataInput['usedWalletAmount'];
        $remainingWalletAmount = $dataInput['remainingWalletAmount'];
        $cartsModel = new CartsModel();


        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }
        $address = $addressModel->where('user_id', $user['id'])
            ->where('is_delete', 0)
            ->where('status', 1)
            ->first();

        if (!$address) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Enter Delivery Address Details.'
            ]);
        }

        if (isset($seller_id) && $seller_id !== null) {
            // $seller_id is set and not null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], $seller_id);
        } else {
            // $seller_id is either not set or is null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], 0);
        }

        $deliveryDetails = $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
        $deliveryCharge = $deliveryDetails['deliveryCharge'];

        $coupon_amount = 0;
        $coupon_id = 0;
        if (isset($coupon['coupon_id']) && $coupon !== null && (int)$coupon['coupon_id'] > 0) {
            list($coupon_amount, $coupon_id) = $cartSummery->calculateCouponAmount($coupon, $subTotal, $user['id']);
        }

        $additional_charge_status = $this->settings['additional_charge_status'];
        $additional_charge = 0;

        if ($additional_charge_status == 1) {
            $additional_charge = (float)$this->settings['additional_charge'];
        }

        foreach ($cartItems as $cartItemForOrderId) {
            // Fetch product details for the first item only
            $productForOrderId = $productModel
                ->select('seller_id')
                ->where('id', $cartItemForOrderId['product_id'])
                ->where('is_delete', 0)
                ->first();

            if ($productForOrderId) {
                $sellerIdForOrderId = str_pad($productForOrderId['seller_id'], 3, '0', STR_PAD_LEFT);
            }

            break; // Exit loop after first iteration
        }
        $datefororderid = date('ymd');

        $base_order_id = 'ORD-' . $datefororderid . '-' . $sellerIdForOrderId . '-';

        if ($selectedDeliveryMethod == 'scheduledDelivery') {
            $delivery_date = $dataInput['selectedDeliveryDate'];
            $timeslot = $dataInput['selectedDeliveryTime'];
        } else {
            $delivery_date = null;
            $timeslot = null;
        }


        $paymentAmount = (round($subTotal + $taxTotal + $deliveryCharge + $additional_charge - $coupon_amount - $usedWalletAmount, 2));


        if (isset($selectedPaymentMethod)) {
            $paymentMethode = $selectedPaymentMethod;
        } else {
            $paymentMethode = 0;
        }
        $paymentMethodModel = new PaymentMethodModel();

        $paymentMethods =  $paymentMethodModel->where('status', 1)->where('id', $selectedPaymentMethod)->first();
        $paystack_public_key = $paymentMethods['api_key'];

        $remainingAmount = $this->settings['minimum_order_amount'] - ($subTotal + $taxTotal);

        if ($remainingAmount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You need to add ' . $this->country['currency_symbol'] . $remainingAmount . ' more to place your order. Please add more items to proceed.'
            ]);
        }
        $order_delivery_otp = str_pad(mt_rand(0000, 9999), 4, '0', STR_PAD_LEFT); // Generate a 4-digit random number

        $orderData = [
            'user_id' => $user['id'],
            'address_id' => $address['id'],
            'payment_method_id' => $paymentMethode,
            'coupon_id' => $coupon_id,
            'delivery_date' => $delivery_date,
            'timeslot' => $timeslot,
            'order_date' => date('Y-m-d H:i:s'),
            'status' => 1, // payment pending
            'delivery_boy_id' => 0,
            'subtotal' => $subTotal,
            'order_delivery_otp' => $order_delivery_otp,
            'tax' => $taxTotal,
            'used_wallet_amount' => $usedWalletAmount,
            'delivery_charge' => $deliveryCharge,
            'coupon_amount' => $coupon_amount,
            'created_at' => date('Y-m-d H:i:s'),
            'additional_charge' => $additional_charge,
            'delivery_method' => $selectedDeliveryMethod
        ];


        if ($orderModel->insert($orderData)) {

            $orderId = $orderModel->insertID();
            $order_id = $base_order_id . str_pad($orderId, 4, '0', STR_PAD_LEFT);
            $orderModel->update($orderId, ['order_id' => $order_id]);

            $orderProductModel = new OrderProductModel();


            $subTotal = 0;
            $taxTotal = 0;

            foreach ($cartItems as $cartItem) {
                // Fetch product and variant details
                $product = $productModel
                    ->select('id, product_name, tax_id, seller_id')
                    ->where('id', $cartItem['product_id'])
                    ->where('is_delete', 0)
                    ->first();

                $variant = $variantModel
                    ->select('id, title as product_variant_name, price, discounted_price')
                    ->where('id', $cartItem['product_variant_id'])
                    ->where('is_delete', 0)
                    ->first();

                $variantModel->where('is_unlimited_stock', 0)
                    ->where('id', $cartItem['product_variant_id'])
                    ->set('stock', 'stock - ' . (int)$cartItem['quantity'], false)
                    ->update();

                if ($product && $variant) {
                    $price = (float) ($variant['discounted_price'] ?: $variant['price']);
                    $quantity = (int) $cartItem['quantity'];
                    $lineTotal = $price * $quantity;
                    $subTotal += $lineTotal;

                    // Calculate tax if applicable
                    $taxAmount = 0;
                    $taxPercentage = 0;
                    if ($product['tax_id']) {
                        $tax = $taxModel->select('percentage')->where('id', $product['tax_id'])->first();
                        if ($tax) {
                            $taxPercentage = (float) $tax['percentage'];
                            $taxAmount = ($price * $taxPercentage / 100) * $quantity;
                            $taxTotal += $taxAmount;
                        }
                    }

                    // Prepare data for insertion into order_products table
                    $orderProductData = [
                        'user_id' => $user['id'],
                        'order_id' => $orderId,
                        'product_id' => $product['id'],
                        'product_variant_id' => $variant['id'],
                        'product_name' => $product['product_name'],
                        'product_variant_name' => $variant['product_variant_name'],
                        'quantity' => $quantity,
                        'price' => $variant['price'],
                        'discounted_price' => $variant['discounted_price'],
                        'tax_amount' => $taxAmount,
                        'tax_percentage' => $taxPercentage,
                        'discount' => $variant['price'] - $variant['discounted_price'],
                        'seller_id' => $product['seller_id'],
                    ];

                    // Insert into order_products table
                    $orderProductModel->insert($orderProductData);
                }
            }

            // Clear the cart after placing the order
            if ($this->settings['seller_only_one_seller_cart']) {
                $cartsModel->where('user_id', $user['id'])->where('seller_id', $seller_id)->delete();
            } else {
                $cartsModel->where('user_id', $user['id'])->delete();
            }


            if (isset($coupon['coupon_id'])) {

                $usedCouponModel = new UsedCouponModel();

                $coupon_id = $coupon['coupon_id'];

                $usedCouponData = [
                    'coupon_id' => $coupon_id,
                    'user_id' => $user['id'],
                    'date' => date('Y-m-d H:i:s'),
                    'order_id' => $orderId
                ];

                // $couponAmountUpdateOrder = ['coupon_amount' => $coupon['value']];
                // $orderModel->set($couponAmountUpdateOrder)->where('id', $orderId)->update();
                $usedCouponModel->insert($usedCouponData);
            }

            if ($usedWalletAmount > 0) {
                $walletModel = new WalletModel();

                // Fetch the last closing_amount for the user
                $lastWalletEntry = $walletModel
                    ->select('closing_amount')
                    ->where('user_id', $user['id'])
                    ->orderBy('id', 'DESC') // Assuming `id` is auto-incremented
                    ->first();

                $closingAmount = $lastWalletEntry ? (float) $lastWalletEntry['closing_amount'] - $usedWalletAmount : $remainingWalletAmount;

                // Prepare wallet data for insertion
                $walletData = [
                    'user_id' => $user['id'],
                    'ref_user_id' => 0, // Reference user ID if applicable
                    'amount' => $usedWalletAmount,
                    'closing_amount' => $closingAmount,
                    'flag' => 'debit',
                    'remark' => 'Used in Order Id: ' . $orderId,
                    'date' => date('Y-m-d H:i:s'),
                ];

                // Insert into wallet table
                $walletModel->insert($walletData);

                $userModel->set('wallet', $closingAmount)->where('id', $user['id'])->update();
            }

            $orderStatusesModel = new OrderStatusesModel();
            $orderStatusesData = [
                'orders_id' => $orderId,
                'status' => 1,
                'created_by' => $user['id'],
                'user_type' => 'Customer',
                'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
            ];
            $orderStatusesModel->insert($orderStatusesData);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Order created, Payment Pending', 'order_id' => $orderId, 'amount' => round($paymentAmount, 2), 'paystack_public_key' => $paystack_public_key]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Placed Order. Please try again later.']);
        }
    }

    public function verifyPaystackOrder()
    {
        helper('firebase_helper');
        $dataInput = $this->request->getJSON(true);
        $userModel = new UserModel();
        $orderModel = new OrderModel();
        $cartsModel = new CartsModel();
        $dataForNotification = [
            'screen' => 'Notification',
        ];
        $deviceTokenModel = new DeviceTokenModel();
        $sellerIds = [];
        $orderProductModel = new OrderProductModel();


        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }
        $reference = $dataInput['reference'];
        $transaction = $dataInput['transaction'];
        $amount = $dataInput['amount'];
        $order_id = $dataInput['order_id'];

        $order = $orderModel->where('id', $order_id)->first();

        $additional_charge_status = $this->settings['additional_charge_status'];
        $additional_charge = 0;

        if ($additional_charge_status == 1) {
            $additional_charge = (float)$this->settings['additional_charge'];
        }

        $total = $order['subtotal'] + $order['tax'] + $additional_charge + $order['delivery_charge'] - $order['coupon_amount'] - $order['used_wallet_amount'];

        if (round($amount, 2) == round($total, 2)) {
            $orderModel->where('id', $order_id)
                ->set('transaction_id', $transaction)
                ->set('order_id', $reference)
                ->set('status', 2)
                ->set('payment_json', json_encode($dataInput))
                ->update();
            // Clear the cart after placing the order
            if ($this->settings['seller_only_one_seller_cart']) {
                $cartsModel->where('user_id', $user['id'])->where('seller_id', $dataInput['seller_id'])->delete();
            } else {
                $cartsModel->where('user_id', $user['id'])->delete();
            }

            $orderStatusesModel = new OrderStatusesModel();
            $data = [
                'orders_id' => $order_id,
                'status' => 2,
                'created_by' => $user['id'],
                'user_type' => 'Customer',
                'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
            ];
            $orderStatusesModel->insert($data);
            $findOrderedProductsInfo = $orderProductModel->select('seller_id')->where('order_id', $dataInput['order_id'])->findAll();

            foreach ($findOrderedProductsInfo as $findOrderedProductInfo) {
                if (!in_array($findOrderedProductInfo['seller_id'], $sellerIds)) {
                    $sellerIds[] = $findOrderedProductInfo['seller_id'];
                }
            }
            // OPTIMIZED NOTIFICATION SYSTEM - Collect all tokens first, then send in batches
            $allNotifications = [];

            // Customer notification
            if ($this->settings['notification_order_pending_status'] == 1) {
                $userTokens = $deviceTokenModel->where('user_type', 2)->where('user_id', $user['id'])->orderBy('id', 'desc')->findAll(1);
                $template = $this->settings['notification_order_pending_message'];
                $placeholders = [
                    '{userName}' => $user['name'] ?? '',
                    '{orderId}' => $order_id ?? '',
                ];
                $finalMessage = str_replace(array_keys($placeholders), array_values($placeholders), $template);

                foreach ($userTokens as $userToken) {
                    if (isset($userToken['app_key'])) {
                        $allNotifications[] = [
                            'token' => $userToken['app_key'],
                            'title' => 'Order placed successfully',
                            'message' => $finalMessage,
                            'data' => $dataForNotification
                        ];
                    }
                }
            }

            // Seller notifications
            if (!empty($sellerIds)) {

                $builder = $deviceTokenModel->builder();

                // Subquery to get max ID (latest) per user_id for admin users
                $subQuery = $builder->select('MAX(id) as id')
                    ->whereIn('user_id', $sellerIds)
                    ->where('user_type', 4)
                    ->groupBy('user_id')
                    ->getCompiledSelect();

                // Use subquery to get the full device token records
                $sellerTokens = $deviceTokenModel
                    ->where("id IN ($subQuery)", null, false)
                    ->orderBy('id', 'desc')
                    ->findAll();

                foreach ($sellerTokens as $sellerToken) {
                    if (isset($sellerToken['app_key'])) {
                        $allNotifications[] = [
                            'token' => $sellerToken['app_key'],
                            'title' => 'New Order arrived',
                            'message' => 'Check now',
                            'data' => $dataForNotification
                        ];
                    }
                }
            }

            // Admin notifications
            $builder = $deviceTokenModel->builder();

            // Subquery to get max ID (latest) per user_id for admin users
            $subQuery = $builder->select('MAX(id) as id')
                ->where('user_type', 1)
                ->groupBy('user_id')
                ->getCompiledSelect();

            // Use subquery to get the full device token records
            $adminTokens = $deviceTokenModel
                ->where("id IN ($subQuery)", null, false)
                ->orderBy('id', 'desc')
                ->findAll();

            foreach ($adminTokens as $adminToken) {
                if (isset($adminToken['app_key'])) {
                    $allNotifications[] = [
                        'token' => $adminToken['app_key'],
                        'title' => 'New order arrived',
                        'message' => 'Check now',
                        'data' => $dataForNotification
                    ];
                }
            }

            // Send notifications asynchronously in background (non-blocking)
            if (!empty($allNotifications)) {
                // Process notifications in chunks to avoid memory issues
                $chunks = array_chunk($allNotifications, 10);
                foreach ($chunks as $chunk) {
                    foreach ($chunk as $notification) {
                        // Use @ to suppress any FCM errors that might slow down response
                        @sendFirebaseNotification(
                            $notification['token'],
                            $notification['title'],
                            $notification['message'],
                            $notification['data']
                        );
                    }
                }
            }
            return $this->response->setJSON(['status' => 'success', 'message' => 'Order Placed Successfully, Payment verified successfully.', 'base_url' => base_url()]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Placed Order. Please try again later.']);
        }
    }

    public function createCashFreeOrder()
    {
        $cartSummery = new CartSummery();

        $dataInput = $this->request->getJSON(true);
        $userModel = new UserModel();
        $variantModel = new ProductVariantsModel();
        $taxModel = new TaxModel();
        $productModel = new ProductModel();
        $walletModel = new WalletModel();
        $addressModel = new AddressModel();
        $orderModel = new OrderModel();
        $seller_id = $dataInput['seller_id'];
        $selectedDeliveryMethod = $dataInput['selectedDeliveryMethod'];
        $selectedPaymentMethod = $dataInput['selectedPaymentMethod'];
        $cartItems = $dataInput['cartitem'];
        $coupon = $dataInput['coupon'];
        $usedWalletAmount = $dataInput['usedWalletAmount'];
        $remainingWalletAmount = $dataInput['remainingWalletAmount'];
        $cartsModel = new CartsModel();


        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }
        $address = $addressModel->where('user_id', $user['id'])
            ->where('is_delete', 0)
            ->where('status', 1)
            ->first();

        if (!$address) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Enter Delivery Address Details.'
            ]);
        }

        if (isset($seller_id) && $seller_id !== null) {
            // $seller_id is set and not null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], $seller_id);
        } else {
            // $seller_id is either not set or is null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], 0);
        }

        $deliveryDetails = $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
        $deliveryCharge = $deliveryDetails['deliveryCharge'];

        $coupon_amount = 0;
        $coupon_id = 0;
        if (isset($coupon['coupon_id']) && $coupon !== null && (int)$coupon['coupon_id'] > 0) {
            list($coupon_amount, $coupon_id) = $cartSummery->calculateCouponAmount($coupon, $subTotal, $user['id']);
        }

        $additional_charge_status = $this->settings['additional_charge_status'];
        $additional_charge = 0;

        if ($additional_charge_status == 1) {
            $additional_charge = (float)$this->settings['additional_charge'];
        }

        foreach ($cartItems as $cartItemForOrderId) {
            // Fetch product details for the first item only
            $productForOrderId = $productModel
                ->select('seller_id')
                ->where('id', $cartItemForOrderId['product_id'])
                ->where('is_delete', 0)
                ->first();

            if ($productForOrderId) {
                $sellerIdForOrderId = str_pad($productForOrderId['seller_id'], 3, '0', STR_PAD_LEFT);
            }

            break; // Exit loop after first iteration
        }
        $datefororderid = date('ymd');

        $base_order_id = 'ORD-' . $datefororderid . '-' . $sellerIdForOrderId . '-';
        if ($selectedDeliveryMethod == 'scheduledDelivery') {
            $delivery_date = $dataInput['selectedDeliveryDate'];
            $timeslot = $dataInput['selectedDeliveryTime'];
        } else {
            $delivery_date = null;
            $timeslot = null;
        }


        $paymentAmount = (round($subTotal + $taxTotal + $deliveryCharge + $additional_charge - $coupon_amount - $usedWalletAmount, 2));


        if (isset($selectedPaymentMethod)) {
            $paymentMethode = $selectedPaymentMethod;
        } else {
            $paymentMethode = 0;
        }

        $remainingAmount = $this->settings['minimum_order_amount'] - ($subTotal + $taxTotal);

        if ($remainingAmount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You need to add ' . $this->country['currency_symbol'] . $remainingAmount . ' more to place your order. Please add more items to proceed.'
            ]);
        }

        $paymentMethodModel = new PaymentMethodModel();

        $paymentMethods =  $paymentMethodModel->where('status', 1)->where('id', $selectedPaymentMethod)->first();
        $cashFreeApiKey = $paymentMethods['api_key'];
        $cashFreeApiSecret = $paymentMethods['secret_key'];


        Cashfree::$XClientId = $cashFreeApiKey;
        Cashfree::$XClientSecret = $cashFreeApiSecret;
        Cashfree::$XEnvironment = Cashfree::$PRODUCTION; // Use Cashfree::$PRODUCTION for production

        $cashfree = new \Cashfree\Cashfree();
        $x_api_version = "2022-09-01";
        $create_orders_request = new \Cashfree\Model\CreateOrderRequest();

        $create_orders_request->setOrderAmount($paymentAmount);
        $create_orders_request->setOrderCurrency($this->country['currency_shortcut']);

        $customer_details = new \Cashfree\Model\CustomerDetails();
        $customerId = preg_replace('/[^a-zA-Z0-9_-]/', '', $user['name']) . "_" . $user['id'];
        $customer_details->setCustomerId($customerId);

        $customer_details->setCustomerPhone($user['mobile']);
        $create_orders_request->setCustomerDetails($customer_details);


        try {
            $result = $cashfree->PGCreateOrder($x_api_version, $create_orders_request);
            $order_delivery_otp = str_pad(mt_rand(0000, 9999), 4, '0', STR_PAD_LEFT); // Generate a 4-digit random number

            $orderData = [
                // 'order_id' => $result[0]['order_id'],
                'user_id' => $user['id'],
                'address_id' => $address['id'],
                'payment_method_id' => $paymentMethode,
                'coupon_id' => $coupon_id,
                'delivery_date' => $delivery_date,
                'timeslot' => $timeslot,
                'order_date' => date('Y-m-d H:i:s'),
                'status' => 1, //payment pending
                'delivery_boy_id' => 0,
                'subtotal' => $subTotal,
                'order_delivery_otp' => $order_delivery_otp,
                'tax' => $taxTotal,
                'used_wallet_amount' => $usedWalletAmount,
                'delivery_charge' => $deliveryCharge,
                'coupon_amount' => $coupon_amount,
                'created_at' => date('Y-m-d H:i:s'),
                'additional_charge' => $additional_charge,
                'delivery_method' => $selectedDeliveryMethod
            ];

            $orderModel = new OrderModel();

            if ($orderModel->insert($orderData)) {

                $orderId = $orderModel->insertID();
                $order_id = $base_order_id . str_pad($orderId, 4, '0', STR_PAD_LEFT);
                $orderModel->update($orderId, ['order_id' => $order_id]);
                $cartsModel = new CartsModel();
                $productModel = new ProductModel();
                $variantModel = new ProductVariantsModel();
                $taxModel = new TaxModel();
                $orderProductModel = new OrderProductModel();

                // Fetch all cart items for the current user
                if ($this->settings['seller_only_one_seller_cart']) {
                    $cartItems = $cartsModel->where('user_id', $user['id'])->where('seller_id', $seller_id)->findAll();
                } else {
                    $cartItems = $cartsModel->where('user_id', $user['id'])->findAll();
                }

                $subTotal = 0;
                $taxTotal = 0;

                foreach ($cartItems as $cartItem) {
                    // Fetch product and variant details
                    $product = $productModel
                        ->select('id, product_name, tax_id, seller_id')
                        ->where('id', $cartItem['product_id'])
                        ->where('is_delete', 0)
                        ->first();

                    $variant = $variantModel
                        ->select('id, title as product_variant_name, price, discounted_price')
                        ->where('id', $cartItem['product_variant_id'])
                        ->where('is_delete', 0)
                        ->first();

                    $variantModel->where('is_unlimited_stock', 0)
                        ->where('id', $cartItem['product_variant_id'])
                        ->set('stock', 'stock - ' . (int)$cartItem['quantity'], false)
                        ->update();

                    if ($product && $variant) {
                        $price = (float) ($variant['discounted_price'] ?: $variant['price']);
                        $quantity = (int) $cartItem['quantity'];
                        $lineTotal = $price * $quantity;
                        $subTotal += $lineTotal;

                        // Calculate tax if applicable
                        $taxAmount = 0;
                        $taxPercentage = 0;
                        if ($product['tax_id']) {
                            $tax = $taxModel->select('percentage')->where('id', $product['tax_id'])->first();
                            if ($tax) {
                                $taxPercentage = (float) $tax['percentage'];
                                $taxAmount = ($price * $taxPercentage / 100) * $quantity;
                                $taxTotal += $taxAmount;
                            }
                        }

                        // Prepare data for insertion into order_products table
                        $orderProductData = [
                            'user_id' => $user['id'],
                            'order_id' => $orderId,
                            'product_id' => $product['id'],
                            'product_variant_id' => $variant['id'],
                            'product_name' => $product['product_name'],
                            'product_variant_name' => $variant['product_variant_name'],
                            'quantity' => $quantity,
                            'price' => $variant['price'],
                            'discounted_price' => $variant['discounted_price'],
                            'tax_amount' => $taxAmount,
                            'tax_percentage' => $taxPercentage,
                            'discount' => $variant['price'] - $variant['discounted_price'],
                            'seller_id' => $product['seller_id'],
                        ];

                        // Insert into order_products table
                        $orderProductModel->insert($orderProductData);
                    }
                }
                if ($this->settings['seller_only_one_seller_cart']) {
                    $cartsModel->where('user_id', $user['id'])->where('seller_id', $dataInput['seller_id'])->delete();
                } else {
                    $cartsModel->where('user_id', $user['id'])->delete();
                }
                if (isset($coupon['coupon_id'])) {

                    $usedCouponModel = new UsedCouponModel();

                    $coupon_id = $coupon['coupon_id'];

                    $usedCouponData = [
                        'coupon_id' => $coupon_id,
                        'user_id' => $user['id'],
                        'date' => date('Y-m-d H:i:s'),
                        'order_id' => $orderId
                    ];

                    // $couponAmountUpdateOrder = ['coupon_amount' => $coupon['value']];
                    // $orderModel->set($couponAmountUpdateOrder)->where('id', $orderId)->update();
                    $usedCouponModel->insert($usedCouponData);
                }

                if ($usedWalletAmount > 0) {
                    $walletModel = new WalletModel();

                    $lastWalletEntry = $walletModel
                        ->select('closing_amount')
                        ->where('user_id', $user['id'])
                        ->orderBy('id', 'DESC') // Assuming `id` is auto-incremented
                        ->first();

                    $closingAmount = $lastWalletEntry ? (float) $lastWalletEntry['closing_amount'] - $usedWalletAmount : $remainingWalletAmount;

                    // Prepare wallet data for insertion
                    $walletData = [
                        'user_id' => $user['id'],
                        'ref_user_id' => 0, // Reference user ID if applicable
                        'amount' => $usedWalletAmount,
                        'closing_amount' => $closingAmount,
                        'flag' => 'debit',
                        'remark' => 'Used in Order Id: ' . $orderId,
                        'date' => date('Y-m-d H:i:s'),
                    ];

                    // Insert into wallet table
                    $walletModel->insert($walletData);

                    $userModel->set('wallet', $closingAmount)->where('id', $user['id'])->update();
                }


                $orderStatusesModel = new OrderStatusesModel();
                $orderStatusesData = [
                    'orders_id' => $orderId,
                    'status' => 1,
                    'created_by' => $user['id'],
                    'user_type' => 'Customer',
                    'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
                ];
                $orderStatusesModel->insert($orderStatusesData);

                return $this->response->setJSON(['status' => 'success', 'message' => 'Order created, Payment Pending', 'order_id' => $orderId, 'cashfree_order_id' => $result[0]['order_id'], 'amount' => $paymentAmount, 'payment_session_id' => $result[0]['payment_session_id']]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Placed Order. Please try again later.']);
            }
        } catch (Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function confirmCashFreeOrder()
    {
        helper('firebase_helper');
        $dataInput = $this->request->getJSON(true);
        $userModel = new UserModel();
        $orderModel = new OrderModel();
        $dataForNotification = [
            'screen' => 'Notification',
        ];
        $deviceTokenModel = new DeviceTokenModel();
        $sellerIds = [];
        $orderProductModel = new OrderProductModel();


        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }

        $paymentMethodModel = new PaymentMethodModel();

        $paymentMethods =  $paymentMethodModel->where('status', 1)->where('id', 5)->first();
        $cashFreeApiKey = $paymentMethods['api_key'];
        $cashFreeApiSecret = $paymentMethods['secret_key'];


        Cashfree::$XClientId = $cashFreeApiKey;
        Cashfree::$XClientSecret = $cashFreeApiSecret;
        Cashfree::$XEnvironment = Cashfree::$PRODUCTION;

        $x_api_version = "2023-08-01";
        $cashfree = new \Cashfree\Cashfree();


        try {
            $response = $cashfree->PGFetchOrder($x_api_version, $dataInput['cashfree_order_id']);
            $amount = $dataInput['amount'];
            $order_id = $dataInput['order_id'];

            $order = $orderModel->where('id', $order_id)->first();

            $additional_charge_status = $this->settings['additional_charge_status'];
            $additional_charge = 0;

            if ($additional_charge_status == 1) {
                $additional_charge = (float)$this->settings['additional_charge'];
            }



            $total = $order['subtotal'] + $order['tax'] + $additional_charge + $order['delivery_charge'] - $order['coupon_amount'] - $order['used_wallet_amount'];

            if (round($amount, 2) == round($total, 2)) {
                $orderModel->where('id', $order_id)
                    ->set('transaction_id', $dataInput['payment_session_id'])
                    ->set('status', 2)
                    ->set('payment_json', json_encode($response))
                    ->update();
                $orderStatusesModel = new OrderStatusesModel();
                $data = [
                    'orders_id' => $order_id,
                    'status' => 2,
                    'created_by' => $user['id'],
                    'user_type' => 'Customer',
                    'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
                ];
                $orderStatusesModel->insert($data);
                $findOrderedProductsInfo = $orderProductModel->select('seller_id')->where('order_id', $dataInput['order_id'])->findAll();

                foreach ($findOrderedProductsInfo as $findOrderedProductInfo) {
                    if (!in_array($findOrderedProductInfo['seller_id'], $sellerIds)) {
                        $sellerIds[] = $findOrderedProductInfo['seller_id'];
                    }
                }
                // OPTIMIZED NOTIFICATION SYSTEM - Collect all tokens first, then send in batches
                $allNotifications = [];

                // Customer notification
                if ($this->settings['notification_order_pending_status'] == 1) {
                    $userTokens = $deviceTokenModel->where('user_type', 2)->where('user_id', $user['id'])->orderBy('id', 'desc')->findAll(1);
                    $template = $this->settings['notification_order_pending_message'];
                    $placeholders = [
                        '{userName}' => $user['name'] ?? '',
                        '{orderId}' => $order_id ?? '',
                    ];
                    $finalMessage = str_replace(array_keys($placeholders), array_values($placeholders), $template);

                    foreach ($userTokens as $userToken) {
                        if (isset($userToken['app_key'])) {
                            $allNotifications[] = [
                                'token' => $userToken['app_key'],
                                'title' => 'Order placed successfully',
                                'message' => $finalMessage,
                                'data' => $dataForNotification
                            ];
                        }
                    }
                }

                // Seller notifications
                if (!empty($sellerIds)) {

                    $builder = $deviceTokenModel->builder();

                    // Subquery to get max ID (latest) per user_id for admin users
                    $subQuery = $builder->select('MAX(id) as id')
                        ->whereIn('user_id', $sellerIds)
                        ->where('user_type', 4)
                        ->groupBy('user_id')
                        ->getCompiledSelect();

                    // Use subquery to get the full device token records
                    $sellerTokens = $deviceTokenModel
                        ->where("id IN ($subQuery)", null, false)
                        ->orderBy('id', 'desc')
                        ->findAll();

                    foreach ($sellerTokens as $sellerToken) {
                        if (isset($sellerToken['app_key'])) {
                            $allNotifications[] = [
                                'token' => $sellerToken['app_key'],
                                'title' => 'New Order arrived',
                                'message' => 'Check now',
                                'data' => $dataForNotification
                            ];
                        }
                    }
                }

                // Admin notifications
                $builder = $deviceTokenModel->builder();

                // Subquery to get max ID (latest) per user_id for admin users
                $subQuery = $builder->select('MAX(id) as id')
                    ->where('user_type', 1)
                    ->groupBy('user_id')
                    ->getCompiledSelect();

                // Use subquery to get the full device token records
                $adminTokens = $deviceTokenModel
                    ->where("id IN ($subQuery)", null, false)
                    ->orderBy('id', 'desc')
                    ->findAll();

                foreach ($adminTokens as $adminToken) {
                    if (isset($adminToken['app_key'])) {
                        $allNotifications[] = [
                            'token' => $adminToken['app_key'],
                            'title' => 'New order arrived',
                            'message' => 'Check now',
                            'data' => $dataForNotification
                        ];
                    }
                }

                // Send notifications asynchronously in background (non-blocking)
                if (!empty($allNotifications)) {
                    // Process notifications in chunks to avoid memory issues
                    $chunks = array_chunk($allNotifications, 10);
                    foreach ($chunks as $chunk) {
                        foreach ($chunk as $notification) {
                            // Use @ to suppress any FCM errors that might slow down response
                            @sendFirebaseNotification(
                                $notification['token'],
                                $notification['title'],
                                $notification['message'],
                                $notification['data']
                            );
                        }
                    }
                }
                return $this->response->setJSON(['status' => 'success', 'message' => 'Order Placed Successfully, Payment verified successfully.', 'base_url' => base_url()]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Placed Order. Please try again later.']);
            }
        } catch (Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Placed Order. Please try again later.']);
        }
    }

    public function cancelOrder()
    {
        helper('firebase_helper');
        $dataInput = $this->request->getJSON(true);
        $userModel = new UserModel();
        $orderModel = new OrderModel();
        $dataForNotification = [
            'screen' => 'Notification',
        ];
        $deviceTokenModel = new DeviceTokenModel();
        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }

        $order_cancelled_till = $this->settings['order_cancelled_till'];

        $orderModel = new OrderModel();
        $order = $orderModel->find($dataInput['order_id']);

        if (!$order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found.']);
        }



        // Check if order can be cancelled
        if ($order['status'] <= $order_cancelled_till) {
            // Update order status and note
            $updateData = [
                'note' => $dataInput['note'],
                'status' => 7
            ];

            $orderModel->update($dataInput['order_id'], $updateData);

            // Insert into order statuses
            $orderStatusesModel = new OrderStatusesModel();
            $orderStatusesData = [
                'orders_id'  => $dataInput['order_id'],
                'status'     => 7,
                'created_by' => $user['id'],
                'user_type'  => 'Customer',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $orderStatusesModel->insert($orderStatusesData);

            if ($order['payment_method_id'] != 1) {
                $grandTotal = $order['subtotal'] + $order['tax'] + $order['delivery_charge'] + $order['additional_charge'] + $order['used_wallet_amount'];

                $walletModel = new WalletModel();
                $wallet = $walletModel->where('user_id', $user['id'])
                    ->orderBy('id', 'DESC')
                    ->first();

                $totalWalletAmount = $wallet['closing_amount'] + $grandTotal;

                $walletData  = [
                    'user_id' => $user['id'],
                    'amount' => $wallet['closing_amount'],
                    'closing_amount' => $totalWalletAmount,
                    'flag' => 'credit',
                    'remark' => 'Cancelled Order amount added, Order Id : ' . $order['id'],
                    'date' => date('Y-m-d')
                ];

                $userModel->update($user['id'], ['wallet' => $totalWalletAmount]);

                $walletModel->insert($walletData);
                $OrderProductModel = new OrderProductModel();
                $sellerIds = $OrderProductModel->select('seller_id')
                    ->where('order_id', $order['id'])
                    ->groupBy('seller_id')
                    ->findAll();
                // OPTIMIZED NOTIFICATION SYSTEM - Collect all tokens first, then send in batches
                $allNotifications = [];

                // Customer notification
                if ($this->settings['notification_order_cancelled_status'] == 1) {
                    $userTokens = $deviceTokenModel->where('user_type', 2)->where('user_id', $user['id'])->orderBy('id', 'desc')->findAll(1);
                    $template = $this->settings['notification_order_cancelled_message'];
                    $placeholders = [
                        '{userName}' => $user['name'] ?? '',
                        '{orderId}' => $order_id ?? '',
                    ];
                    $finalMessage = str_replace(array_keys($placeholders), array_values($placeholders), $template);

                    foreach ($userTokens as $userToken) {
                        if (isset($userToken['app_key'])) {
                            $allNotifications[] = [
                                'token' => $userToken['app_key'],
                                'title' => 'Order cancelled successfully',
                                'message' => $finalMessage,
                                'data' => $dataForNotification
                            ];
                        }
                    }
                }

                // Seller notifications
                if (!empty($sellerIds)) {

                    $builder = $deviceTokenModel->builder();

                    // Subquery to get max ID (latest) per user_id for admin users
                    $subQuery = $builder->select('MAX(id) as id')
                        ->whereIn('user_id', $sellerIds)
                        ->where('user_type', 4)
                        ->groupBy('user_id')
                        ->getCompiledSelect();

                    // Use subquery to get the full device token records
                    $sellerTokens = $deviceTokenModel
                        ->where("id IN ($subQuery)", null, false)
                        ->orderBy('id', 'desc')
                        ->findAll();

                    foreach ($sellerTokens as $sellerToken) {
                        if (isset($sellerToken['app_key'])) {
                            $allNotifications[] = [
                                'token' => $sellerToken['app_key'],
                                'title' => 'order cancelled by user',
                                'message' => 'Check now',
                                'data' => $dataForNotification
                            ];
                        }
                    }
                }

                // Admin notifications
                $builder = $deviceTokenModel->builder();

                // Subquery to get max ID (latest) per user_id for admin users
                $subQuery = $builder->select('MAX(id) as id')
                    ->where('user_type', 1)
                    ->groupBy('user_id')
                    ->getCompiledSelect();

                // Use subquery to get the full device token records
                $adminTokens = $deviceTokenModel
                    ->where("id IN ($subQuery)", null, false)
                    ->orderBy('id', 'desc')
                    ->findAll();

                foreach ($adminTokens as $adminToken) {
                    if (isset($adminToken['app_key'])) {
                        $allNotifications[] = [
                            'token' => $adminToken['app_key'],
                            'title' => 'order cancelled by user',
                            'message' => 'Check now',
                            'data' => $dataForNotification
                        ];
                    }
                }

                // Send notifications asynchronously in background (non-blocking)
                if (!empty($allNotifications)) {
                    // Process notifications in chunks to avoid memory issues
                    $chunks = array_chunk($allNotifications, 10);
                    foreach ($chunks as $chunk) {
                        foreach ($chunk as $notification) {
                            // Use @ to suppress any FCM errors that might slow down response
                            @sendFirebaseNotification(
                                $notification['token'],
                                $notification['title'],
                                $notification['message'],
                                $notification['data']
                            );
                        }
                    }
                }

                return $this->response->setJSON(['status' => 'success', 'message' => 'Order cancelled successfully. Amount return to Your wallet']);
            } else {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Order cancelled successfully']);
            }
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to cancel order. Order cannot be cancelled at this stage.']);
        }
    }
    public function downloadInvoice()
    {
        $dataInput = $this->request->getJSON(true);
        $userModel = new UserModel();
        $orderModel = new OrderModel();

        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }

        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        $order_id = $dataInput['order_id'];


        $data['address'] = $this->settings['address'];
        $data['call'] = $this->settings['phone'];
        $data['mail'] = $this->settings['email'];
        $data['website'] = $this->settings['website'];

        $orderModel = new OrderModel();
        $data['orderDetails'] = $orderModel->select(
            'orders.id as order_id, orders.order_id as user_order_id, orders.order_id as my_order_id, orders.user_id, orders.address_id, orders.subtotal, orders.tax, orders.used_wallet_amount, 
                    orders.delivery_charge, orders.coupon_amount,  orders.order_date, orders.delivery_date, orders.additional_charge, 
                    orders.timeslot, orders.delivery_boy_id, orders.transaction_id, orders.status, user.name as user_name, 
                    user.mobile as user_mobile, user.email as user_email, address.address, address.area, address.city, address.state, address.pincode, 
                    delivery_boy.name as delivery_boy_name, delivery_boy.mobile as delivery_boy_mobile, 
                    order_status_lists.status as order_status, order_status_lists.color as order_status_color, payment_method.img as payment_method_img, payment_method.title as payment_method_title'
        )
            ->join('delivery_boy', 'delivery_boy.id = orders.delivery_boy_id', 'left')
            ->join('order_status_lists', 'order_status_lists.id = orders.status', 'left')
            ->join('user', 'user.id = orders.user_id', 'left')
            ->join('address', 'address.id = orders.address_id', 'left')
            ->join('payment_method', 'payment_method.id = orders.payment_method_id', 'left')
            ->where('orders.id', $order_id)
            ->first();


        $orderProductModel = new OrderProductModel();

        $data['orderProducts'] = $orderProductModel->select(
            'order_products.product_name, 
            order_products.product_variant_name, 
            order_products.quantity, 
            order_products.price,  
            order_products.tax_percentage, 
            order_products.tax_amount, 
            order_products.product_id, 
            order_products.discounted_price, 
            seller.store_name'
        )
            ->join('seller', 'seller.id = order_products.seller_id', 'left')
            ->join(
                'order_return_request',
                'order_return_request.order_id = order_products.order_id 
                AND order_return_request.status = 4',
                'left'
            )
            ->where('order_products.order_id', $order_id)
            ->where('order_return_request.id IS NULL') // Ensure no matching order_return_request
            ->findAll();

        $data['returnedProducts'] = $orderProductModel->select(
            'order_products.product_name, 
            order_products.product_variant_name, 
            order_products.quantity, 
            order_products.price,  
            order_products.tax_percentage, 
            order_products.tax_amount, 
            order_products.product_id, 
            order_products.discounted_price, 
            seller.store_name'
        )
            ->join('seller', 'seller.id = order_products.seller_id', 'left')
            ->join(
                'order_return_request',
                'order_return_request.order_id = order_products.order_id',
                'left'
            )
            ->where('order_products.order_id', $order_id)
            ->where('order_return_request.status', 4)
            ->findAll();



        $cssPath = FCPATH . 'assets/dist/css/adminlte.min.css'; // Path to your external CSS file
        $css = file_get_contents($cssPath);

        // Load the view into a variable and include inline styles
        $html = view('website/order/invoice',  $data);
        $html = "
                    <html>
                        <head>
                        <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
                            <style>{$css} </style>
                            <style>
                                body {
                                    font-family: 'DejaVu Sans', sans-serif;
                                    font-size: 12px !important;
                                }
                                 h1 {
                                    font-size: 16px;
                                }
                                p {
                                    margin: 5px 0;
                                }
                                    .table td,{
                                    padding-block:10px }
                            </style>
                        </head>
                        <body class='text-sm'>
                            {$html}
                        </body>
                    </html>
                ";

        // Initialize Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfOutput = $dompdf->output();

        return $this->response->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="invoice_' . $order_id . '.pdf"')
            ->setBody($pdfOutput);
    }

    public function returningItemRequest()
    {
        helper('firebase_helper');
        date_default_timezone_set($this->timeZone['timezone']);
        $dataInput = $this->request->getJSON(true);
        $userModel = new UserModel();
        $orderModel = new OrderModel();
        $dataForNotification = [
            'screen' => 'Notification',
        ];
        $deviceTokenModel = new DeviceTokenModel();
        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }


        $orderModel = new OrderModel();
        $order = $orderModel->find($dataInput['order_id']);

        $orderProductModel = new OrderProductModel();
        $orderProduct = $orderProductModel->select('product_id, seller_id')->find($dataInput['order_product_id']);


        $productModel = new ProductModel();
        $product = $productModel->select('is_returnable, return_days, id')->find($orderProduct['product_id']);

        $orderProduct['is_returnable'] = 0;
        $orderProduct['differenceInDays'] = 0;

        // Convert dates to timestamps
        $orderDeliveryDate = strtotime($order['delivery_date']);
        $currentDate = strtotime(date('Y-m-d'));

        // Calculate difference in days (allowing negative values)
        $differenceInSeconds = $currentDate - $orderDeliveryDate;
        $differenceInDays = floor($differenceInSeconds / (60 * 60 * 24)); // Convert seconds to days

        // Check returnable conditions
        if ($product['is_returnable'] && $differenceInDays <= $product['return_days']) {

            $orderReturnRequestModel = new OrderReturnRequestModel();

            $existingRequest = $orderReturnRequestModel
                ->where('order_id', $dataInput['order_id'])
                ->where('order_products_id', $dataInput['order_product_id'])
                ->first();

            if ($existingRequest) {
                // If the request already exists, return an error response
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Already Returning Item Request sent.',
                ]);
            }

            $orderReturnRequestData = [
                'order_id' => $dataInput['order_id'],
                'order_products_id' => $dataInput['order_product_id'],
                'reason' => $dataInput['note'],
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $orderReturnRequestModel->insert($orderReturnRequestData);

            // OPTIMIZED NOTIFICATION SYSTEM - Collect all tokens first, then send in batches
            $allNotifications = [];

            // Customer notification
            if ($this->settings['notification_order_item_return_request_pending_status'] == 1) {
                $userTokens = $deviceTokenModel->where('user_type', 2)->where('user_id', $user['id'])->orderBy('id', 'desc')->findAll(1);
                $template = $this->settings['notification_order_item_return_request_pending_message'];
                $placeholders = [
                    '{userName}' => $user['name'] ?? '',
                    '{orderId}' => $order['order_id'] ?? '',
                ];
                $finalMessage = str_replace(array_keys($placeholders), array_values($placeholders), $template);

                foreach ($userTokens as $userToken) {
                    if (isset($userToken['app_key'])) {
                        $allNotifications[] = [
                            'token' => $userToken['app_key'],
                            'title' => 'Item requested return sccessfully',
                            'message' => $finalMessage,
                            'data' => $dataForNotification
                        ];
                    }
                }
            }

            // Seller notifications
            if (!empty($sellerIds)) {

                $builder = $deviceTokenModel->builder();

                // Subquery to get max ID (latest) per user_id for admin users
                $subQuery = $builder->select('MAX(id) as id')
                    ->whereIn('user_id', $sellerIds)
                    ->where('user_type', 4)
                    ->groupBy('user_id')
                    ->getCompiledSelect();

                // Use subquery to get the full device token records
                $sellerTokens = $deviceTokenModel
                    ->where("id IN ($subQuery)", null, false)
                    ->orderBy('id', 'desc')
                    ->findAll();

                foreach ($sellerTokens as $sellerToken) {
                    if (isset($sellerToken['app_key'])) {
                        $allNotifications[] = [
                            'token' => $sellerToken['app_key'],
                            'title' => 'Item return requested by user',
                            'message' => 'Check now',
                            'data' => $dataForNotification
                        ];
                    }
                }
            }

            // Admin notifications
            $builder = $deviceTokenModel->builder();

            // Subquery to get max ID (latest) per user_id for admin users
            $subQuery = $builder->select('MAX(id) as id')
                ->where('user_type', 1)
                ->groupBy('user_id')
                ->getCompiledSelect();

            // Use subquery to get the full device token records
            $adminTokens = $deviceTokenModel
                ->where("id IN ($subQuery)", null, false)
                ->orderBy('id', 'desc')
                ->findAll();

            foreach ($adminTokens as $adminToken) {
                if (isset($adminToken['app_key'])) {
                    $allNotifications[] = [
                        'token' => $adminToken['app_key'],
                        'title' => 'Item return requested by user',
                        'message' => 'Check now',
                        'data' => $dataForNotification
                    ];
                }
            }

            // Send notifications asynchronously in background (non-blocking)
            if (!empty($allNotifications)) {
                // Process notifications in chunks to avoid memory issues
                $chunks = array_chunk($allNotifications, 10);
                foreach ($chunks as $chunk) {
                    foreach ($chunk as $notification) {
                        // Use @ to suppress any FCM errors that might slow down response
                        @sendFirebaseNotification(
                            $notification['token'],
                            $notification['title'],
                            $notification['message'],
                            $notification['data']
                        );
                    }
                }
            }
            return $this->response->setJSON(['status' => 'success', 'message' => 'Sending Returning Item Request successfully']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to sending Returning Item Request.']);
        }
    }

    public function trackingOrder()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input
        $userModel = new UserModel();
        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }


        $orderStatusesModel = new OrderStatusesModel();
        $orderStatusListModel = new OrderStatusListsModel();

        // Fetch all status details from the order_status_lists table
        $statusesList = $orderStatusListModel->whereIn('id', [2, 3, 4, 6])->findAll();

        // Fetch statuses for the given order ID
        $orderStatuses = $orderStatusesModel->where('orders_id', $dataInput['order_id'])->findAll();

        // Map statuses for easier use in the view
        $mappedStatuses = [];
        foreach ($orderStatuses as $status) {
            $mappedStatuses[$status['status']] = [
                'created_at' => $status['created_at'],
                'id' => $status['id']
            ];
        }
        $data['orderStages'] = [];
        foreach ($statusesList as $status) {
            $data['orderStages'][] = [
                'id' => $mappedStatuses[$status['id']]['id'] ?? null,
                'name' => $status['status'],
                'color' => $status['color'],
                'text_color' => $status['text_color'],
                'bg_color' => $status['bg_color'],
                'completed' => isset($mappedStatuses[$status['id']]) ? true : false,
                'created_at' => $mappedStatuses[$status['id']]['created_at'] ?? null,
            ];
        }


        return $this->response->setJSON(['status' => 'success', 'data' => $data]);
    }

    public function calculateProxyDeliveryTime()
    {
        $dataInput = $this->request->getJSON(true); // Parse JSON input

        $latitude = $dataInput['latitude'];
        $longitude = $dataInput['longitude'];
        $deliverable_area_id = $dataInput['deliverable_area_id'];
        $deliverableAreaModel = new DeliverableAreaModel();
        $sellerModel = new SellerModel();


        $findFirstSeller = $sellerModel->select('id, latitude, longitude')
            ->where('deliverable_area_id', $deliverable_area_id)
            ->where('is_delete', 0)
            ->where('status', 1)
            ->first();
        $perKmTime = $deliverableAreaModel->where('is_delete', 0)->where('id', $deliverable_area_id)->first();

        $geoUtils = new GeoUtils();
        $findTime = $geoUtils->travelDistanceTime($latitude, $longitude, $findFirstSeller['latitude'], $findFirstSeller['longitude'], $perKmTime['time_to_travel']);
        if ($findTime) {
            return $this->response->setJSON([
                'status' => 'success',
                'delivery_time' => $perKmTime['base_delivery_time'] + $findTime['estimated_delivery_time_min'],
                'distance_km' => $findTime['distance_km']
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'delivery_time' => null,
                'distance_km' => null
            ]);
        }
    }

    public function fetchNotificationList()
    {
        $userModel = new UserModel();
        $notificationModel = new NotificationModel();

        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }

        $notificationList = $notificationModel->groupStart()->where('user_id', $user['id'])->orWhere('user_id', 0)->groupEnd()->orderBy('id', 'desc')->findAll();

        return $this->response->setJSON(['status' => 'success', 'data' => $notificationList]);
    }

    public function uploadProfilePic()
    {
        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        $userModel = new UserModel();
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }

        $file = $this->request->getFile('file');

        if (!$file || !$file->isValid()) {
            return $this->respond([
                'status' => 500,
                'result' => 'false',
                'message' => $file ? $file->getErrorString() : 'No file uploaded'
            ]);
        }

        $uploadPath = FCPATH . 'uploads/profile/'; // Publicly accessible directory
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true); // Ensure directory exists
        }

        $newName = $file->getRandomName();
        if (!$file->move($uploadPath, $newName)) {
            return $this->respond([
                'status' => 500,
                'result' => 'false',
                'message' => 'File move failed'
            ]);
        }

        $data = [
            'img' => base_url("uploads/profile/$newName"),
        ];

        $updateUser = $userModel->where('id', $user['id'])->set($data)->update();
        if ($updateUser) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Profile image successfully uploaded']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Profile image update failed']);
    }

    public function fetchLiveDeliveryTracking()
    {
        try {
            $dataInput = $this->request->getJSON(true);

            // Validate input
            if (!isset($dataInput['order_id'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Order ID is required'
                ])->setStatusCode(400);
            }

            $payload = $this->authorizedToken();
            if ($payload instanceof ResponseInterface) {
                return $payload;
            }
            $userModel = new UserModel();
            $user = null;

            if (isset($payload['email'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_email_verified', 1)
                    ->where('is_delete', 0)
                    ->where('email', $payload['email'])
                    ->first();
            } elseif (isset($payload['mobile'])) {
                $user = $userModel
                    ->where('is_active', 1)
                    ->where('is_mobile_verified', 1)
                    ->where('is_delete', 0)
                    ->where('mobile', $payload['mobile'])
                    ->first();
            }

            if (!$user) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User not found or not authorized'
                ])->setStatusCode(401);
            }

            $orderModel = new OrderModel();
            $order = $orderModel->where('user_id', $user['id'])
                ->where('id', $dataInput['order_id'])
                ->first();

            if (!$order) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Order not found or does not belong to user'
                ])->setStatusCode(404);
            }

            $deliveryTrackingModel = new DeliveryTrackingModel();
            $deliveryTracking = $deliveryTrackingModel->where('order_id', $dataInput['order_id'])->first();

            if (!$deliveryTracking) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Delivery Boy Hasn`t start Delivery'
                ])->setStatusCode(404);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Live Tracking started',
                'liveTracking' => $deliveryTracking
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'An error occurred while fetching tracking information'
            ])->setStatusCode(500);
        }
    }


    private function twilio($smsSetting, $otp, $mobile, $is_first_time)
    {
        // Validate required parameters
        if (empty($smsSetting) || empty($otp) || empty($mobile)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Missing required parameters for SMS'
            ]);
        }

        // Format phone number to E.164 format if needed
        $mobile = $this->country['country_code'] . $mobile;

        // Parse SMS settings
        $settings = json_decode($smsSetting, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid SMS gateway configuration'
            ]);
        }

        // Extract Twilio credentials with validation
        $accountSid = $settings['accountSid'] ?? null;
        $authToken = $settings['authToken'] ?? null;
        $twilioNumber = $settings['twilioNumber'] ?? null;
        $messageTemplate = $settings['message'] ?? 'Your OTP is #OTP#';

        // Validate required Twilio credentials
        if (empty($accountSid) || empty($authToken) || empty($twilioNumber)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Missing Twilio configuration'
            ]);
        }

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

        // Check for curl execution errors
        if ($error) {
            curl_close($ch);
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to connect to Twilio API: ' . $error
            ]);
        }

        curl_close($ch);

        // Process the response
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid JSON response from Twilio API'
            ]);
        }

        // Check for Twilio API errors
        if ($httpCode !== 201 || isset($result['error_code'])) {
            $errorMsg = $result['message'] ?? 'Unknown error';
            $errorCode = $result['code'] ?? $result['error_code'] ?? 'unknown';

            // Log the error for debugging
            log_message('error', "Twilio API Error: {$errorMsg} (Code: {$errorCode})");

            return $this->response->setJSON([
                'status' => 'error',
                'message' => "Failed to send SMS: {$errorMsg}"
            ]);
        }

        // Success response
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'OTP sent to registered Mobile Number.',
            'is_first_time' => $is_first_time
        ]);
    }
    private function nexmo($smsSetting, $otp, $mobile, $is_first_time)
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
            'message' => 'OTP sent to registered Mobile Number.',
            'is_first_time' => $is_first_time
        ]);
    }
    private function twoFactor($smsSetting, $otp, $mobile, $is_first_time)
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
            'message' => 'OTP sent to registered Mobile Number.',
            'is_first_time' => $is_first_time
        ]);
    }
    private function msg91($smsSetting, $otp, $mobile, $is_first_time)
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
            'message' => 'OTP sent to registered Mobile Number.',
            'is_first_time' => $is_first_time
        ]);
    }
    private function fast2Sms($smsSetting, $otp, $mobile, $is_first_time)
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
            'message' => 'OTP sent to registered Mobile Number.',
            'is_first_time' => $is_first_time
        ]);
    }

    public function fetchLanguageList()
    {

        $languageModel = new LanguageModel();

        $languageList = $languageModel->where('is_active', 1)->findAll();

        return $this->response->setJSON(['status' => 'success', 'data' => $languageList]);
    }
    
    public function paymentFailedUpdate()
    {
        $dataInput = $this->request->getJSON(true);
        helper('firebase_helper');
        $userModel = new UserModel();
        $orderModel = new OrderModel();
        $cartsModel = new CartsModel();



        $payload = $this->authorizedToken();
        if ($payload instanceof ResponseInterface) {
            return $payload;
        }
        if (isset($payload['email'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_email_verified', 1)
                ->where('is_delete', 0)
                ->where('email', $payload['email'])
                ->first();
        } elseif (isset($payload['mobile'])) {
            $user = $userModel
                ->where('is_active', 1)
                ->where('is_mobile_verified', 1)
                ->where('is_delete', 0)
                ->where('mobile', $payload['mobile'])
                ->first();
        }

        if (!$user) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'User not found'
            ]);
        }

        $orderModel->delete($dataInput['order_id']);
        $orderProductModel = new OrderProductModel();
        $orderProductModel->where('order_id', $dataInput['order_id'])->delete();
        
        return $this->response->setJSON(['status' => 'success', 'message' => 'Order status failed updated']);
    }
    
     public function fetchGroupCategories()
    {
        $categoryGroupModel = new CategoryGroupModel();
        $categoryModel = new CategoryModel();

        if ($this->settings['frontend_category_section'] == 0) {
            return $this->response->setJSON(['status' => 'success', 'data' => []]);
        }

        // Get all category groups
        $categoryGroups = $categoryGroupModel->findAll();
        $filteredGroups = [];

        // For each group, get its categories
        foreach ($categoryGroups as $group) {
            $categories = $categoryModel->where('category_group_id', $group['id'])
                ->orderBy('row_order', 'ASC')
                ->findAll();

            // Skip group if no categories found
            if (empty($categories)) {
                continue;
            }

            // Append base_url to category_img for each category
            foreach ($categories as &$category) {
                $category['category_img'] = base_url($category['category_img']);
            }

            $group['categories'] = $categories;
            $filteredGroups[] = $group;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $filteredGroups,
        ]);
    }

public function fetchHeaderCategories()
    {
        $headerCategoryModel = new HeaderCategoryModel();

        if ($this->settings['frontend_category_section'] == 0) {
            return $this->response->setJSON(['status' => 'success', 'data' => []]);
        }

        // Get header categories with category details
        $headerCategories = $headerCategoryModel
            ->select('header_category.id, header_category.title, header_category.icon, header_category.icon_library, header_category.category_id, category.category_name as category')
            ->join('category', 'category.id = header_category.category_id', 'left')
            ->findAll();

        // Prepare the result array
        $result = [];

        // Add "All" as the first item
        $result[] = [
            'id' => 0,
            'title' => 'All',
            'icon' => 'accessibility',
            'active' => true,
            'category_id' => 0,
            'category' => '',
            'icon_library' =>  0 
        ];

        // Add other categories
        foreach ($headerCategories as $index => $headerCategory) {
            $result[] = [
                'id' => $headerCategory['id'],
                'title' => $headerCategory['title'],
                'icon' => $headerCategory['icon'] ?: 'accessibility', // Use default icon if null
                'active' => false, // All others are inactive by default
                'category_id' => (int)$headerCategory['category_id'],
                'category' => $headerCategory['category'] ?: '', // Use empty string if null
                'icon_library' => $headerCategory['icon_library'] ?: 0 // Use empty string if null
            ];
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $result]);
    }        $headerCategoryModel = new HeaderCategoryModel();

        if ($this->settings['frontend_category_section'] == 0) {
            return $this->response->setJSON(['status' => 'success', 'data' => []]);
        }

        // Get header categories with category details
        $headerCategories = $headerCategoryModel
            ->select('header_category.id, header_category.title, header_category.icon, header_category.category_id, category.category_name as category')
            ->join('category', 'category.id = header_category.category_id', 'left')
            ->findAll();

        // Prepare the result array
        $result = [];

        // Add "All" as the first item
        $result[] = [
            'id' => 0,
            'title' => 'All',
            'icon' => 'accessibility',
            'active' => true,
            'category_id' => 0,
            'category' => ''
        ];

        // Add other categories
        foreach ($headerCategories as $index => $headerCategory) {
            $result[] = [
                'id' => $headerCategory['id'],
                'title' => $headerCategory['title'],
                'icon' => $headerCategory['icon'] ?: 'accessibility', // Use default icon if null
                'active' => false, // All others are inactive by default
                'category_id' => (int)$headerCategory['category_id'],
                'category' => $headerCategory['category'] ?: '' // Use empty string if null
            ];
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $result]);
    }
}
