<?php

namespace App\Controllers;

use App\Models\SettingsModel;

class ServiceWorkerController extends BaseController
{
    public function firebaseMessagingSW()
    {
        // Set the content type to JavaScript
        $this->response->setHeader('Content-Type', 'application/javascript');
        $settingModel = new SettingsModel();
        $settings = $settingModel->getSettings();
        // Dynamic Firebase configuration

        // Generate the service worker script
        $script = "
            importScripts('https://www.gstatic.com/firebasejs/8.6.1/firebase-app.js');
            importScripts('https://www.gstatic.com/firebasejs/8.6.1/firebase-messaging.js');

            // Firebase configuration
            const firebaseConfig = " . $settings['fcm_credentials'] . ";

            // Initialize Firebase
            firebase.initializeApp(firebaseConfig);

            // Initialize Firebase Messaging
            const messaging = firebase.messaging();

            messaging.onBackgroundMessage(function(payload) {
                console.log('[firebase-messaging-sw.js] Received background message ', payload);

                const notificationTitle = payload.notification.title;
                const notificationOptions = {
                    body: payload.notification.body,
                    icon: payload.notification.icon
                };

                return self.registration.showNotification(notificationTitle, notificationOptions);
            });
        ";

        return $this->response->setBody($script);
    }
}
