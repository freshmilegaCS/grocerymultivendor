<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>App Setting | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

    <?= $this->include('template/style') ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css" />
    <link rel="stylesheet" href="<?= base_url('/assets/plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>
    <style>
        .bootstrap-switch {
            border-radius: 20px !important;
        }

        .bootstrap-switch .bootstrap-switch-handle-off.bootstrap-switch-success,
        .bootstrap-switch .bootstrap-switch-handle-on.bootstrap-switch-success {
            color: #fff;
            background: #005555;
        }

        .toggle-switch:not(.form-group) {
            margin-bottom: 0;
        }

        .toggle-switch {
            font-weight: 500;
        }

        .toggle-switch {
            border-color: #e7eaf3 !important;
        }

        .toggle-switch {
            position: relative;
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 0.85rem;
            text-transform: capitalize;
        }

        .toggle-switch {
            position: relative;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-align: center;
            align-items: center;
            cursor: pointer;
        }

        input[type="checkbox"],
        input[type="radio"] {
            box-sizing: border-box;
            padding: 0;
        }

        .toggle-switch-input {
            position: absolute;
            z-index: -1;
            opacity: 0;
        }

        .form--check .form-check-input[type="radio"]:checked,
        .toggle-switch-input:checked+.toggle-switch-label {
            background-color: #14b19e;
        }

        .toggle-switch-input:checked+.toggle-switch-label {
            background-color: #00868f;
        }

        .switch--custom-label .toggle-switch-label {
            width: 44px;
            height: 26px;
            margin: 0;
        }

        .toggle-switch-label {
            position: relative;
            display: block;
            width: 3rem;
            height: 2rem;
            background-color: #e7eaf3;
            background-clip: content-box;
            border: 0.125rem solid transparent;
            border-radius: 6.1875rem;
            transition: 0.3s;
        }

        .switch--custom-label .toggle-switch-input:checked+.toggle-switch-label .toggle-switch-indicator {
            transform: translate3d(18px, 50%, 0);
        }

        .toggle-switch-input:checked+.toggle-switch-label .toggle-switch-indicator {
            -webkit-transform: translate3d(1.025rem, 50%, 0);
            transform: translate3d(1.025rem, 50%, 0);
        }

        .switch--custom-label .toggle-switch-indicator {
            width: 18px;
            height: 18px;
        }

        .toggle-switch-indicator {
            position: absolute;
            left: 0.125rem;
            bottom: 50%;
            width: 1.5rem;
            height: 1.5rem;
            background-color: #fff;
            -webkit-transform: initial;
            transform: initial;
            box-shadow: 0 3px 6px 0 rgba(140, 152, 164, 0.25);
            border-radius: 50%;
            -webkit-transform: translate3d(0, 50%, 0);
            transform: translate3d(0, 50%, 0);
            transition: 0.3s;
        }
    </style>
</head>

<body class="sidebar-mini control-sidebar-slide-open text-sm  layout-fixed  <?php echo  $settings['thememode'] == 'Light' ? '' : 'dark-mode'; ?> layout-navbar-fixed text-sm" id="body">
    <div class="wrapper">


        <?= $this->include('template/header') ?>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <?= $this->include('template/sidebar') ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">App Setting</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">App Setting</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12 card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2 pr-3">
                                        <div class="list-group" id="settingsTabs" role="tablist">

                                            <a class="list-group-item list-group-item-action <?php echo (!isset($_GET['setting']) || (isset($_GET['setting']) && $_GET['setting'] === 'mail')) == 'mail' ? 'active' : '' ?>" onclick="changeURL('/admin/setting?setting=mail')" id="mail-tab" data-toggle="pill" href="#mail" role="tab" aria-controls="mail" aria-selected="true">
                                                Mail Setting
                                            </a>
                                            <a class="list-group-item list-group-item-action <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'google-map-api' ? 'active' : '' ?>" onclick="changeURL('/admin/setting?setting=google-map-api')" id="google-map-api-tab" data-toggle="pill" href="#google-map-api" role="tab" aria-controls="google-map-api" aria-selected="false">
                                                Google API / Recaptcha
                                            </a>
                                            <a class="list-group-item list-group-item-action <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'firebase-setting-api' ? 'active' : '' ?>" onclick="changeURL('/admin/setting?setting=firebase-setting-api')" id="firebase-setting-api-tab" data-toggle="pill" href="#firebase-setting-api" role="tab" aria-controls="firebase-setting-api" aria-selected="false">
                                                Firebase Setting
                                            </a>
                                            <a class="list-group-item list-group-item-action <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'notification' ? 'active' : '' ?>" onclick="changeURL('/admin/setting?setting=notification')" id="notification-tab" data-toggle="pill" href="#notification" role="tab" aria-controls="notification" aria-selected="false">
                                                Notification
                                            </a>
                                            <a class="list-group-item list-group-item-action <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'login' ? 'active' : '' ?>" onclick="changeURL('/admin/setting?setting=login')" id="login-tab" data-toggle="pill" href="#login" role="tab" aria-controls="login" aria-selected="false">
                                                Login Setting
                                            </a>
                                            <a class="list-group-item list-group-item-action <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'social-links' ? 'active' : '' ?>" onclick="changeURL('/admin/setting?setting=social-links')" id="social-links-tab" data-toggle="pill" href="#social-links" role="tab" aria-controls="social-links" aria-selected="false">
                                                Social Links
                                            </a>
                                            <a class="list-group-item list-group-item-action <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'external-api-setting' ? 'active' : '' ?>" onclick="changeURL('/admin/setting?setting=external-api-setting')" id="external-api-setting-tab" data-toggle="pill" href="#external-api-setting" role="tab" aria-controls="external-api-setting" aria-selected="false">
                                                3rd Party API
                                            </a>
                                            <a class="list-group-item list-group-item-action <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'language' ? 'active' : '' ?>" onclick="changeURL('/admin/setting?setting=language')" id="language-tab" data-toggle="pill" href="#language" role="tab" aria-controls="language" aria-selected="false">
                                                Language Setting
                                            </a>
                                            <a class="list-group-item list-group-item-action <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'app-main-header' ? 'active' : '' ?>" onclick="changeURL('/admin/setting?setting=app-main-header')" id="app-main-header-tab" data-toggle="pill" href="#app-main-header" role="tab" aria-controls="app-main-header" aria-selected="false">
                                                App Main Header Setting
                                            </a>
                                            <a class="list-group-item list-group-item-action <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'other' ? 'active' : '' ?>" onclick="changeURL('/admin/setting?setting=other')" id="other-tab" data-toggle="pill" href="#other" role="tab" aria-controls="other" aria-selected="false">
                                                Other Setting
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-10" style=" box-shadow: 0 0 10px rgb(0 0 0 / 13%), 0px 0px 10px rgb(0 0 0 / 0%); border-radius:5px;">
                                        <!-- Tab Content -->
                                        <div class="tab-content" id="settingsTabContent">
                                            <div class="tab-pane fade <?php echo (!isset($_GET['setting']) || (isset($_GET['setting']) && $_GET['setting'] === 'mail')) ? 'active show' : '';  ?>" id="mail" role="tabpanel" aria-labelledby="mail-tab">
                                                <h5>Mail Setting</h5>
                                                <form id="mailSettingForm">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Mailer Name</label>
                                                                <input type="text" value="<?= isset($settings['mail_config']) ? esc(json_decode($settings['mail_config'])->name) : '' ?>" id="name" placeholder="Mailer Name" value="" class="form-control" name="name">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Host</label>
                                                                <input type="text" value="<?= isset($settings['mail_config']) ? esc(json_decode($settings['mail_config'])->host) : '' ?>" id="host" placeholder="Host" value="" class="form-control" name="host">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Email Id/ Username</label>
                                                                <input type="text" value="<?= isset($settings['mail_config']) ? esc(json_decode($settings['mail_config'])->username) : '' ?>" placeholder="Email Id/ Username" id="username" value="" class="form-control" name="username" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Password</label>
                                                                <input type="text" value="<?= isset($settings['mail_config']) ? esc(json_decode($settings['mail_config'])->password) : '' ?>" id="password" placeholder="Password" value="" class="form-control" name="password" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="port">Port </label>
                                                                <select name="port" class="form-control" required>
                                                                    <option value="">Select Port</option>
                                                                    <option value="465" <?php if (json_decode($settings['mail_config'])->port == '465') {
                                                                                            echo 'selected';
                                                                                        } ?>>465 </option>
                                                                    <option value="587" <?php if (json_decode($settings['mail_config'])->port == '587') {
                                                                                            echo 'selected';
                                                                                        } ?>>587</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="encryption">Encryption </label>
                                                                <select name="encryption" class="form-control" required>
                                                                    <option value="">Select Encryption</option>
                                                                    <option value="ssl" <?php if (json_decode($settings['mail_config'])->encryption == 'ssl') {
                                                                                            echo 'selected';
                                                                                        } ?>>SSL </option>
                                                                    <option value="tsl" <?php if (json_decode($settings['mail_config'])->encryption == 'tsl') {
                                                                                            echo 'selected';
                                                                                        } ?>>TSL</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </form>
                                                <div class="row mt-5">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Enter mail id for testing smtp connection</label>
                                                            <input type="email" value="" placeholder="Enter mail id for testing smtp connection" id="test_mail_id" class="form-control" name="test_mail_id" required>
                                                        </div>
                                                    </div>

                                                </div>
                                                <button type="button" class="btn btn-danger-light" onclick="testMail()">Test Mail</button>

                                            </div>
                                            <div class="tab-pane fade <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'google-map-api' ? 'active show' : '' ?>" id="google-map-api" role="tabpanel" aria-labelledby="google-map-api-tab">
                                                <h5>Google MAP API</h5>
                                                <form id="googleMapSettingForm">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Google Map API Key</label>
                                                                <input type="text" value="<?= isset($settings['map_api_key']) ? esc($settings['map_api_key']) : '' ?>" id="map_api_key" placeholder="Map API Key" value="" class="form-control" name="map_api_key">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Google Speech To Text API Key</label>
                                                                <input type="text" value="<?= isset($settings['google_speech_api']) ? esc($settings['google_speech_api']) : '' ?>" id="google_speech_api" placeholder="Google Speech To Text API Key" value="" class="form-control" name="google_speech_api">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <h5 class="mt-4">Google ReCAPTCHA Control (V3)</h5>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="google_recaptcha_status">Google ReCAPTCHA Status</label>
                                                                <br>
                                                                <input type="checkbox" name="google_recaptcha_status" id="google_recaptcha_status" <?= isset($settings['google_recaptcha_status']) && $settings['google_recaptcha_status'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Site Key</label>
                                                                <input type="text" value="<?= isset($settings['google_recaptcha_site_key']) ? esc($settings['google_recaptcha_site_key']) : '' ?>" id="google_recaptcha_site_key" placeholder="Site Key" value="" class="form-control" name="google_recaptcha_site_key">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Secret Key</label>
                                                                <input type="text" value="<?= isset($settings['google_recaptcha_secret_key']) ? esc($settings['google_recaptcha_secret_key']) : '' ?>" id="google_recaptcha_secret_key" placeholder="Secret Key" value="" class="form-control" name="google_recaptcha_secret_key">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'login' ? 'active show' : '' ?>" id="login" role="tabpanel" aria-labelledby="login-tab">
                                                <h5>Login Setting</h5>
                                                <form id="loginSettingForm">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="direct_login">Direct Email Login <span class="text-danger text-xs">*</span></label>
                                                                <br>
                                                                <input type="checkbox" name="direct_login" id="direct_login" <?= isset($settings['direct_login']) && $settings['direct_login'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="phone_login">Phone Login (using OTP) <span class="text-danger text-xs">*</span></label>
                                                                <br>
                                                                <input type="checkbox" name="phone_login" id="phone_login" <?= isset($settings['phone_login']) && $settings['phone_login'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="google_login_status">Google Login <span class="text-danger text-xs">*</span></label>
                                                                <br>
                                                                <?php
                                                                $socialLogin = isset($settings['social_login']) ? json_decode($settings['social_login'], true) : null;
                                                                ?>

                                                                <input type="checkbox" name="google_login_status" id="google_login_status" <?= isset($socialLogin[0]['status']) && $socialLogin[0]['status'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="apple_login_status">Apple Login (For iOS Devices) <span class="text-danger text-xs">*</span></label>
                                                                <br>
                                                                <?php
                                                                $socialLogin = isset($settings['social_login']) ? json_decode($settings['social_login'], true) : null;
                                                                ?>

                                                                <input type="checkbox" name="apple_login_status" id="apple_login_status" <?= isset($socialLogin[1]['status']) && $socialLogin[1]['status'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <h5 class="mt-4 google-login"> Google Login Control</h5>
                                                    <div class="row google-login">
                                                        <div class="col-md-6">
                                                            <div class="form-group ">
                                                                <label for="google_login_client_id" class="form-label">
                                                                    Google Login Client Id
                                                                </label>
                                                                <?php

                                                                // Check if data is valid and fetch the property
                                                                $googleClientSecret = '';
                                                                if ($socialLogin && isset($socialLogin[0]['client_secret'])) {
                                                                    $googleClientSecret = $socialLogin[0]['client_secret'];
                                                                    $googleClientId = $socialLogin[0]['client_id'];
                                                                    $googleLoginMedium = $socialLogin[0]['login_medium'];
                                                                }
                                                                ?>
                                                                <input type="hidden" id="google_login_medium" name="google_login_medium" value="<?= esc($googleLoginMedium) ?>">
                                                                <input id="google_login_client_id" type="text" value="<?= esc($googleClientId) ?>" placeholder="Google Login Client Id" class="form-control" name="google_login_client_id">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group ">
                                                                <label for="google_login_client_secret" class="form-label">
                                                                    Google Login Client Secret
                                                                </label>
                                                                <input id="google_login_client_secret" type="text" value="<?= esc($googleClientSecret) ?>" placeholder="Google Login Client Secret" class="form-control" name="google_login_client_secret">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'notification' ? 'active show' : '' ?>" id="notification" role="tabpanel" aria-labelledby="notification-tab">
                                                <h5>Push Notification Templates</h5>
                                                <form id="notificationSettingForm">
                                                    <div class="row my-4">
                                                        <?php
                                                        $notification_fields = [
                                                            'order_pending',
                                                            'order_received',
                                                            'order_processed',
                                                            'order_shipped',
                                                            'order_out_for_delivery',
                                                            'order_delivered',
                                                            'order_cancelled',
                                                            'order_delivery_boy_assign',
                                                            'order_item_return_request_pending',
                                                            'order_item_return_request_approved',
                                                            'order_item_return_request_reject',
                                                            'order_item_return_request_return_to_deliveryboy',
                                                            'order_item_return_request_return_to_seller',
                                                            'order_update_delivery_date',
                                                        ];

                                                        foreach ($notification_fields as $field) {
                                                            $status_key = "notification_{$field}_status";
                                                            $message_key = "notification_{$field}_message";

                                                            $label = ucwords(str_replace("_", " ", $field)); // e.g., Order Pending
                                                        ?>
                                                            <div class="col-md-2 mt-2">
                                                                <div class="form-group">
                                                                    <label for="<?= $status_key ?>"><?= $label ?> Status <span class="text-danger text-xs">*</span></label>
                                                                    <br>
                                                                    <input type="checkbox"
                                                                        name="<?= $status_key ?>"
                                                                        id="<?= $status_key ?>"
                                                                        <?= isset($settings[$status_key]) && $settings[$status_key] == '1' ? 'checked' : ''; ?>
                                                                        data-bootstrap-switch
                                                                        data-off-color="danger"
                                                                        class='system-users-switch bootstrap-switch-id-<?= $status_key ?>'
                                                                        data-on-color="success">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 mt-2">
                                                                <div class="form-group">
                                                                    <label for="<?= $message_key ?>"><?= $label ?> Message <span class="text-danger text-xs">*</span></label>
                                                                    <textarea
                                                                        class="form-control"
                                                                        name="<?= $message_key ?>"
                                                                        id="<?= $message_key ?>"
                                                                        rows="2"
                                                                        required
                                                                        placeholder="Enter Message"><?= isset($settings[$message_key]) ? $settings[$message_key] : '' ?></textarea>
                                                                </div>
                                                            </div>
                                                        <?php } ?>

                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'social-links' ? 'active show' : '' ?>" id="social-links" role="tabpanel" aria-labelledby="social-links-tab">
                                                <h5>Social Link Setting</h5>
                                                <form id="socialLinkForm">
                                                    <?php
                                                    $socialLink = isset($settings['social_link']) ? json_decode($settings['social_link'], true) : null;
                                                    foreach ($socialLink as $item) {

                                                        $name = $item['name'];
                                                        $status = $item['status'];
                                                        $link = $item['link'];
                                                        $icon = $item['icon'];
                                                        $name_lower = strtolower(str_replace(' ', '_', $name)); // Normalize name for IDs
                                                    ?>
                                                        <h5 class=" mt-4"><?php echo $name; ?></h5>
                                                        <div class="row">

                                                            <div class="col-md-4">
                                                                <div class="form-group ">
                                                                    <label for="<?php echo $name_lower; ?>_link" class="form-label">
                                                                        Link <span class="text-danger text-xs">*</span>
                                                                    </label>
                                                                    <input id="<?php echo $name_lower; ?>_link" type="text" placeholder="Link" class="form-control" name="<?php echo $name_lower; ?>_link" required value="<?php echo $link; ?>">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group ">
                                                                    <label for="<?php echo $name_lower; ?>_icon" class="form-label">
                                                                        Icon <span class="text-danger text-xs">*</span>
                                                                    </label>
                                                                    <input id="<?php echo $name_lower; ?>_icon" type="text" placeholder="Icon" class="form-control" name="<?php echo $name_lower; ?>_icon" required value="<?php echo $icon; ?>">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="<?php echo $name_lower; ?>_status"><?php echo $name; ?> Status <span class="text-danger text-xs">*</span></label>
                                                                    <br>
                                                                    <input type="checkbox" name="<?php echo $name_lower; ?>_status" id="<?php echo $name_lower; ?>_status" <?= $status == '1' ? 'checked' : ''; ?>
                                                                        data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                                </div>
                                                            </div>
                                                        </div>

                                                    <?php } ?>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'firebase-setting-api' ? 'active show' : '' ?>" id="firebase-setting-api" role="tabpanel" aria-labelledby="firebase-setting-api-tab">
                                                <h5>Firebase Setting</h5>
                                                <form id="firebaseSettingForm">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group ">
                                                                <label for="firebase_admin_json_file_content" class="form-label">
                                                                    Json Service File Content
                                                                </label>
                                                                <textarea id="firebase_admin_json_file_content" placeholder="Json Service File Content" class="form-control" rows="15" name="firebase_admin_json_file_content"><?= isset($settings['firebase_admin_json_file_content']) ? esc($settings['firebase_admin_json_file_content']) : '' ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group ">
                                                                <label for="apiKey" class="form-label">
                                                                    API Key
                                                                </label>
                                                                <input id="apiKey" type="text" value="<?= isset($settings['fcm_credentials']) ? esc(json_decode($settings['fcm_credentials'])->apiKey) : '' ?>" placeholder="API Key" class="form-control" name="apiKey">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group ">
                                                                <label for="authDomain" class="form-label">
                                                                    Auth Domain
                                                                </label>
                                                                <input id="authDomain" type="text" value="<?= isset($settings['fcm_credentials']) ? esc(json_decode($settings['fcm_credentials'])->authDomain) : '' ?>" placeholder="Auth Domain" class="form-control" name="authDomain">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group ">
                                                                <label for="storageBucket" class="form-label">
                                                                    Storage Bucket
                                                                </label>
                                                                <input id="storageBucket" type="text" value="<?= isset($settings['fcm_credentials']) ? esc(json_decode($settings['fcm_credentials'])->storageBucket) : '' ?>" placeholder="Storage Bucket" class="form-control" name="storageBucket">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group ">
                                                                <label for="messagingSenderId" class="form-label">
                                                                    Messaging Sender Id
                                                                </label>
                                                                <input id="messagingSenderId" type="text" value="<?= isset($settings['fcm_credentials']) ? esc(json_decode($settings['fcm_credentials'])->messagingSenderId) : '' ?>" placeholder="Messaging Sender Id" class="form-control" name="messagingSenderId">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group ">
                                                                <label for="projectId" class="form-label">
                                                                    Project Id
                                                                </label>
                                                                <input id="projectId" type="text" value="<?= isset($settings['fcm_credentials']) ? esc(json_decode($settings['fcm_credentials'])->projectId) : '' ?>" placeholder="Project Id" class="form-control" name="projectId">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group ">
                                                                <label for="appId" class="form-label">
                                                                    App Id
                                                                </label>
                                                                <input id="appId" type="text" value="<?= isset($settings['fcm_credentials']) ? esc(json_decode($settings['fcm_credentials'])->appId) : '' ?>" placeholder="App Id" class="form-control" name="appId">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group ">
                                                                <label for="measurementId" class="form-label">
                                                                    Measurement Id
                                                                </label>
                                                                <input id="measurementId" type="text" value="<?= isset($settings['fcm_credentials']) ? esc(json_decode($settings['fcm_credentials'])->measurementId) : '' ?>" placeholder="Measurement Id" class="form-control" name="measurementId">
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Update</button>

                                                </form>
                                            </div>
                                            <div class="tab-pane fade <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'external-api-setting' ? 'active show' : '' ?>" id="external-api-setting" role="tabpanel" aria-labelledby="external-api-setting-tab">
                                                <h5>3rd Party API Setting</h5>
                                                <form id="chatgptSettingForm">
                                                    <h5 class="mt-4 google-login">ChatGPT API</h5>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="chatgpt_status">Enable Chatgpt <span class="text-danger text-xs">*</span></label>
                                                                <br>
                                                                <input type="checkbox" name="chatgpt_status" id="chatgpt_status" <?= isset($settings['chatgpt_status']) && $settings['chatgpt_status'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group ">
                                                                <label for="chatgpt_api_key" class="form-label">
                                                                    Chatgpt API Key
                                                                </label>
                                                                <input id="chatgpt_api_key" type="text" value="<?= esc($settings['chatgpt_api_key']) ?>" placeholder="Chatgpt API Key" class="form-control" name="chatgpt_api_key">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <h5 class="mt-4 google-login">Live Chat TWAK API</h5>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="twak_live_chat_status">Enable TWAK live chat in website <span class="text-danger text-xs">*</span></label>
                                                                <br>
                                                                <input type="checkbox" name="twak_live_chat_status" id="twak_live_chat_status" <?= isset($settings['twak_live_chat_status']) && $settings['twak_live_chat_status'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group ">
                                                                <label for="twak_live_chat_widget_code" class="form-label">
                                                                    Twak Widget Code
                                                                </label>
                                                                <textarea id="twak_live_chat_widget_code" placeholder="Twak Widget Code" class="form-control" name="twak_live_chat_widget_code" rows="4"><?= esc($settings['twak_live_chat_widget_code']) ?></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'other' ? 'active show' : '' ?>" id="other" role="tabpanel" aria-labelledby="other-tab">
                                                <h5>Other Setting</h5>
                                                <form id="otherSettingForm">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group ">
                                                                <label for="currency_symbol_position">Currency Symbol Position <span class="text-danger text-xs">*</span></label>
                                                                <select id="currency_symbol_position" name="currency_symbol_position" required="" class="form-control ">
                                                                    <option value="right" <?php if ($settings['currency_symbol_position'] == 'right') {
                                                                                                echo 'selected';
                                                                                            } ?>>Right ($)</option>
                                                                    <option value="left" <?php if ($settings['currency_symbol_position'] == 'left') {
                                                                                                echo 'selected';
                                                                                            } ?>>($) Left</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group ">
                                                                <label for="website_loading_text">Website Loading Text <span class="text-danger text-xs">*</span></label>
                                                                <input id="website_loading_text" type="text" value="<?= esc($settings['website_loading_text']) ?>" placeholder="Website Loading Text" class="form-control" name="website_loading_text">
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group ">
                                                                <label for="cookies_text">Website Cookie Text <span class="text-danger text-xs">*</span></label>
                                                                <textarea class="form-control " name="cookies_text" id="cookies_text" rows="3" required placeholder="Enter Website Cookie Text"><?= isset($settings['cookies_text']) ? esc($settings['cookies_text']) : '' ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group ">
                                                                <label for="footer_text">Website Footer Text <span class="text-danger text-xs">*</span></label>
                                                                <textarea class="form-control " name="footer_text" id="footer_text" rows="3" required placeholder="Enter Website Footer Text"><?= isset($settings['footer_text']) ? esc($settings['footer_text']) : '' ?></textarea>

                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group ">
                                                                <label for="short_description">Website Short Description <span class="text-danger text-xs">*</span></label>
                                                                <textarea class="form-control " name="short_description" id="short_description" rows="3" required placeholder="Enter Website Short Description"><?= isset($settings['short_description']) ? esc($settings['short_description']) : '' ?></textarea>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <h5 class="mt-4">Refer & Earn Control</h5>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="refer_and_earn_status">Enable Refer & Earn <span class="text-danger text-xs">*</span></label>
                                                                <br>
                                                                <input type="checkbox" name="refer_and_earn_status" id="refer_and_earn_status" <?= isset($settings['refer_and_earn_status']) && $settings['refer_and_earn_status'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                                <!-- <label class="switch--custom-label toggle-switch  align-items-center" for="refer_and_earn_status"> Enable Refer & Earn 
                                                                    <input type="checkbox" data-custom-switch class="status toggle-switch-input add-required-attribute  dynamic-checkbox-toggle" name="refer_and_earn_status" id="refer_and_earn_status" <?= isset($settings['refer_and_earn_status']) && $settings['refer_and_earn_status'] == '1' ? 'checked' : ''; ?>>
                                                                    <span class="toggle-switch-label">
                                                                        <span class="toggle-switch-indicator"></span>
                                                                    </span>
                                                                </label>
                                                                <input type="hidden" name="refer_and_earn_status_hidden" value="0"> -->

                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group ">
                                                                <label for="referer_earning">Referer Earning (The one who is referring) <span class="text-danger text-xs">*</span></label>
                                                                <input id="referer_earning" type="text" value="<?= esc($settings['referer_earning']) ?>" placeholder="Referer Earning" class="form-control" name="referer_earning">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group ">
                                                                <label for="refered_earning">Referred Earning (The one who is refered) <span class="text-danger text-xs">*</span></label>
                                                                <input id="refered_earning" type="text" value="<?= esc($settings['refered_earning']) ?>" placeholder="Referred Earning" class="form-control" name="refered_earning">

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'language' ? 'active show' : '' ?>" id="language" role="tabpanel" aria-labelledby="language-tab">
                                                <h5>Language Setting (Limited to Website / Customer App / DeliveryBoy App)</h5>
                                                <form id="languageSettingForm">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="user_can_select_language">User Can Change Language<span class="text-danger text-xs">*</span></label>
                                                                <br>
                                                                <input type="checkbox" name="user_can_select_language" id="user_can_select_language" <?= isset($settings['user_can_select_language']) && $settings['user_can_select_language'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                                <!-- <label class="switch--custom-label toggle-switch  align-items-center" for="user_can_select_language"> Enable Refer & Earn 
                                                                    <input type="checkbox" data-custom-switch class="status toggle-switch-input add-required-attribute  dynamic-checkbox-toggle" name="user_can_select_language" id="user_can_select_language" <?= isset($settings['user_can_select_language']) && $settings['user_can_select_language'] == '1' ? 'checked' : ''; ?>>
                                                                    <span class="toggle-switch-label">
                                                                        <span class="toggle-switch-indicator"></span>
                                                                    </span>
                                                                </label>
                                                                <input type="hidden" name="user_can_select_language_hidden" value="0"> -->

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <table id="language_table" class="table table-bordered table-hover w-100">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Id</th>
                                                                        <th>Language</th>
                                                                        <th>Is Default</th>
                                                                        <th>Is RTL</th>
                                                                        <th>Is Active</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade <?= isset($_GET['setting']) && $_GET['setting'] == 'app-main-header' ? 'active show' : '' ?>" id="app-main-header" role="tabpanel" aria-labelledby="app-main-header-tab">
                                                <h5>App Main Header Setting</h5>
                                                <form id="appMainHeaderForm" method="POST" enctype="multipart/form-data">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Main Header Banner</label><br>
                                                                <input type="checkbox" name="main_header_banner" id="main_header_banner" <?= isset($settings['main_header_banner']) && $settings['main_header_banner'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Main Header Banner Overlay Text Color</label>
                                                                <input type="color" value="<?= isset($settings['main_header_banner_overlay_text_color']) ? esc($settings['main_header_banner_overlay_text_color']) : '#000000' ?>" id="main_header_banner_overlay_text_color" name="main_header_banner_overlay_text_color" class="form-control">

                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Main Header Banner Image</label>
                                                                <div class="dropzone custom-dropzone" id="images-dropzone">
                                                                    <div class="dropzone-clickable-area">
                                                                        <div class="icon"><i class="fi fi-br-upload"></i></div>
                                                                        <p>Upload Main Header Banner Image</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Old Image</label>
                                                                <br>
                                                                <img src="<?php echo  base_url($settings['main_header_banner_img']) ?>" style="width: 100px;" alt="">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- /.content -->
        </div>

        <!-- /.content-wrapper -->
        <?= $this->include('template/footer') ?>

        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->

    <?= $this->include('template/script') ?>
    <script src="<?= base_url('/assets/plugins/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= isset($settings['map_api_key']) ? esc($settings['map_api_key']) : '' ?>&callback=initAutocomplete&libraries=places&v=weekly" defer></script>
    <script src="<?= base_url('/assets/page-script/setting.js') ?>"></script>
    <script>
        function testMail() {
            Swal.fire({
                title: "Confirm?",
                text: "Make sure mail setting is update done successfully!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, setting updated",
            }).then((result) => {
                if (result.isConfirmed) {
                    var test_mail_id = $("#test_mail_id").val();
                    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    if (!emailRegex.test(test_mail_id)) {
                        toastr.error("Please enter a valid email address", "Admin says");
                        return;
                    }
                    $.ajax({
                        url: "/admin/setting/mail/test",
                        type: "POST",
                        data: {
                            test_mail_id
                        },
                        dataType: "json",
                        beforeSend: function() {
                            toastr.info('Sending test mail', "Admin says");

                        },
                        success: function(response) {
                            if (response.success == true) {
                                toastr.success(response.message, "Admin says");
                            } else {
                                toastr.error(response.message, "Admin says");
                            }
                        },
                        error: function(e) {
                            toastr.error("Error while testing mail", "Admin says");
                        },
                    });
                }
            });
        }

    </script>
</body>

</html>