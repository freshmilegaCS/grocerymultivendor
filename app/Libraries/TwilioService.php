<?php

namespace App\Libraries;

use Twilio\Rest\Client;

class TwilioService
{
    protected $client;
    protected $fromNumber;
    protected $indiaServiceSid;
    protected $indiaSenderId;
    protected $indiaTemplateId;

    public function __construct()
    {
        // Load configuration from .env file
        $accountSid = 'ACa10fbdfa744a89b97ffb3cc606c5a9f2';
        $authToken = '37cc6fe915b1e19dd92750329723ced2';
        $this->fromNumber = '+18777804236';

        // India-specific DLT configuration
        $this->indiaServiceSid = getenv('TWILIO_INDIA_SERVICE_SID');
        $this->indiaSenderId = getenv('TWILIO_INDIA_SENDER_ID');
        $this->indiaTemplateId = getenv('TWILIO_INDIA_TEMPLATE_ID');

        // Initialize Twilio client
        $this->client = new Client($accountSid, $authToken);
    }

    /**
     * Send SMS message with proper handling for different countries
     * 
     * @param string $to Recipient phone number
     * @param string $message Message content
     * @return object Twilio message object
     */
    public function sendSMS($to, $message)
    {
        try {
            // Check if the number is an Indian number (starts with +91)
            if (strpos($to, '+91') === 0) {
                return $this->sendIndiaOTP($to, $message);
            } else {
                $result = $this->client->messages->create(
                    $to,
                    [
                        'from' => $this->fromNumber,
                        'body' => $message
                    ]
                );

                return $result;
            }
        } catch (\Exception $e) {
            log_message('error', 'Twilio SMS Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send OTP message to Indian numbers (compliant with DLT regulations)
     * 
     * @param string $to Recipient phone number (must be Indian format +91...)
     * @param string $message Message content with OTP
     * @return object Twilio message object
     */
    protected function sendIndiaOTP($to, $message)
    {
        if (empty($this->indiaServiceSid) || empty($this->indiaSenderId) || empty($this->indiaTemplateId)) {
            throw new \Exception('India DLT configuration is missing in .env file');
        }

        try {
            $result = $this->client->messages->create(
                $to,
                [
                    'messagingServiceSid' => $this->indiaServiceSid,
                    'body' => $message,
                    'contentSid' => $this->indiaTemplateId,
                    'contentVariables' => json_encode([
                        'otp' => $this->extractOTP($message)
                    ])
                ]
            );

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Twilio India SMS Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Extract OTP from message text
     * This is used to populate the template variables for Indian SMS
     * 
     * @param string $message The message containing the OTP
     * @return string The extracted OTP
     */
    protected function extractOTP($message)
    {
        // Simple regex to extract digits that look like an OTP
        preg_match('/\b(\d{4,6})\b/', $message, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }

        // If no OTP found, return the whole message
        // You might want to improve this logic based on your specific OTP format
        return $message;
    }

    /**
     * Make a phone call
     * 
     * @param string $to Recipient phone number
     * @param string $twimlUrl URL to TwiML instructions or TwiML string
     * @param array $options Additional options
     * @return object Twilio call object
     */
    public function makeCall($to, $twimlUrl, $options = [])
    {
        try {
            $callOptions = array_merge([
                'from' => $this->fromNumber,
                'twiml' => $twimlUrl
            ], $options);

            $call = $this->client->calls->create($to, $callOptions);

            return $call;
        } catch (\Exception $e) {
            log_message('error', 'Twilio Call Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get message details
     * 
     * @param string $messageSid The SID of the message
     * @return object Message details
     */
    public function getMessageDetails($messageSid)
    {
        try {
            return $this->client->messages($messageSid)->fetch();
        } catch (\Exception $e) {
            log_message('error', 'Twilio Message Details Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
