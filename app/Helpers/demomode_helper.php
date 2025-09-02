<?php

use App\Models\SettingsModel;


if (!function_exists('demoModeStatus')) {
    function demoModeStatus()
    {
        $settingModel = new SettingsModel();
        $settings =  $settingModel->getSettings();


        if ($settings['demo_mode'] == 1) {
            return true;
        } else {
            return false;
        }
    }
}
