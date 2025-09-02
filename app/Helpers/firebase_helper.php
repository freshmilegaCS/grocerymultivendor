<?php

use Google\Client;
use App\Models\SettingsModel;

if (!function_exists('sendFirebaseNotification')) {
    function sendFirebaseNotification($token, $title, $body, $data = [])
{
    $SettingsModel = new SettingsModel();
    $serviceAccountSetting = $SettingsModel->getSettings();

    if (empty($serviceAccountSetting['firebase_admin_json_file_content']) || empty($serviceAccountSetting['fcm_credentials'])) {
        // Skip if settings are missing
        return ['skipped' => 'Firebase credentials not found, notification skipped.'];
    }

    $firebaseProjectID = json_decode($serviceAccountSetting['fcm_credentials'], true);
    $serviceAccountJson = json_decode($serviceAccountSetting['firebase_admin_json_file_content'], true);

    if (empty($firebaseProjectID['projectId']) || empty($serviceAccountJson)) {
        // Skip if credentials are invalid
        return ['skipped' => 'Invalid Firebase credentials, notification skipped.'];
    }

    $url = 'https://fcm.googleapis.com/v1/projects/' . $firebaseProjectID['projectId'] . '/messages:send';

    try {
        $client = new Google\Client();
        $client->setAuthConfig($serviceAccountJson);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $tokenData = $client->fetchAccessTokenWithAssertion();

        if (isset($tokenData['error'])) {
            return ['skipped' => 'Could not fetch access token, notification skipped.', 'details' => $tokenData];
        }

        $accessToken = $tokenData['access_token'];

        $message = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => $data
            ]
        ];

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($message),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return ['error' => $error];
        }

        return json_decode($response, true);

    } catch (\DomainException $e) {
        // Skip on credential errors
        return ['skipped' => 'DomainException: ' . $e->getMessage()];
    } catch (\Exception $e) {
        return ['skipped' => 'Exception: ' . $e->getMessage()];
    }
}

}
