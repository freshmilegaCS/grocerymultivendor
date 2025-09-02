<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Store Setting | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
                            <h1 class="m-0">Store Setting</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Store Setting</li>
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
                                            <a class="list-group-item list-group-item-action <?php echo (!isset($_GET['setting']) || $_GET['setting'] == 'store') ? 'active ' : '' ?>" onclick="changeURL('/admin/store-setting?setting=store')" id="store-tab" data-toggle="pill" href="#store" role="tab" aria-controls="store" aria-selected="true">
                                                Store Setting
                                            </a>
                                            <a class="list-group-item list-group-item-action <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'country' ? 'active' : '' ?>" onclick="changeURL('/admin/store-setting?setting=country')" id="country-tab" data-toggle="pill" href="#country" role="tab" aria-controls="country" aria-selected="false">
                                                Country Setting
                                            </a>
                                            <a class="list-group-item list-group-item-action <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'order' ? 'active' : '' ?>" onclick="changeURL('/admin/store-setting?setting=order')" id="order-tab" data-toggle="pill" href="#order" role="tab" aria-controls="order" aria-selected="false">
                                                Order Setting
                                            </a>
                                            <a class="list-group-item list-group-item-action <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'delivery-boy' ? 'active' : '' ?>" onclick="changeURL('/admin/store-setting?setting=delivery-boy')" id="delivery-boy-tab" data-toggle="pill" href="#delivery-boy" role="tab" aria-controls="delivery-boy" aria-selected="false">
                                                Delivery Boy Setting
                                            </a>
                                            <a class="list-group-item list-group-item-action <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'seller' ? 'active' : '' ?>" onclick="changeURL('/admin/store-setting?setting=seller')" id="seller-tab" data-toggle="pill" href="#seller" role="tab" aria-controls="seller" aria-selected="false">
                                                Seller Setting
                                            </a>
                                            <a class="list-group-item list-group-item-action <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'app-setting' ? 'active' : '' ?>" onclick="changeURL('/admin/store-setting?setting=app-setting')" id="app-setting-tab" data-toggle="pill" href="#app-setting" role="tab" aria-controls="app-setting" aria-selected="false">
                                                App Setting
                                            </a>
                                            <a class="list-group-item list-group-item-action <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'frontend-landing' ? 'active' : '' ?>" onclick="changeURL('/admin/store-setting?setting=frontend-landing')" id="frontend-landing-tab" data-toggle="pill" href="#frontend-landing" role="tab" aria-controls="frontend-landing" aria-selected="false">
                                                Frontend Landing Page
                                            </a>
                                           
                                        </div>
                                    </div>
                                    <div class="col-md-10" style=" box-shadow: 0 0 10px rgb(0 0 0 / 13%), 0px 0px 10px rgb(0 0 0 / 0%); border-radius:5px;">
                                        <!-- Tab Content -->
                                        <div class="tab-content" id="settingsTabContent">
                                            <div class="tab-pane fade <?php echo (!isset($_GET['setting']) || $_GET['setting'] == 'store') ? 'active show' : '' ?>" id="store" role="tabpanel" aria-labelledby="store-tab">
                                                <h5>Store Setting</h5>
                                                <form id="storeSettingForm" enctype="multipart/form-data">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Title</label>
                                                                <input type="text" id="business_name" class="form-control " name="business_name" value="<?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?>" required placeholder="Enter Title">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Email</label>
                                                                <input type="email" id="email" class="form-control " name="email" value="<?= isset($settings['email']) ? esc($settings['email']) : '' ?>" required placeholder="Enter Email">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Phone</label>
                                                                <input type="text" id="phone" class="form-control " name="phone" value="<?= isset($settings['phone']) ? esc($settings['phone']) : '' ?>" required placeholder="Enter Phone">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Store Address</label>
                                                                <textarea class="form-control " name="address" id="address" rows="3" required placeholder="Enter Store Address"><?php echo json_decode($settings['address'])->address; ?></textarea>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Latitude</label>
                                                                        <input type="text" id="latitude" class="form-control " name="latitude" value="<?php echo json_decode($settings['address'])->latitude; ?>" required placeholder="Enter Latitude">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Longitude</label>
                                                                        <input type="text" id="longitude" class="form-control " name="longitude" value="<?php echo json_decode($settings['address'])->longitude; ?>" required placeholder="Enter Longitude">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="exampleInputBorder">Logo (1:1 ratio)</label>
                                                                        <div class="dropzone custom-dropzone" id="images-dropzone">
                                                                            <div class="dropzone-clickable-area">
                                                                                <div class="icon"><i class="fi fi-br-upload"></i></div>
                                                                                <p>Upload Logo</p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="exampleInputBorder">Old Logo </label>
                                                                        <br>
                                                                        <img src="<?php echo  base_url($settings['logo']) ?>" style="width: 100px;" alt="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Select Store Location</label>
                                                                <div class="form-group">
                                                                    <input type="text" autocomplete="false" id="pac-input" class="custom-form-control" placeholder="Search City">
                                                                </div>
                                                                <div id="map"></div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'country' ? 'active show' : '' ?>" id="country" role="tabpanel" aria-labelledby="country-tab">
                                                <h5>Country Setting</h5>
                                                <form id="countryForm">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="country">Country <span class="text-danger text-xs">*</span></label>
                                                                <select id="country" name="country" required="" class="form-control ">
                                                                    <?php
                                                                    foreach ($country as $key => $val) {
                                                                    ?>
                                                                        <option value="<?php echo $val['id'] ?>" <?php if ($val['is_active'] == 1) {
                                                                                                                        echo 'selected';
                                                                                                                    } ?>><?php echo $val['country_name'] . " (Dial Code: " . $val['country_code'] . ", Currency: " . $val['currency'] . ")" ?></option>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group ">
                                                                <label for="timezone">Timezone <span class="text-danger text-xs">*</span></label>
                                                                <select id="timezone" name="timezone" required="" class="form-control ">
                                                                    <?php
                                                                    foreach ($timezone as $key => $val) {
                                                                    ?>
                                                                        <option value="<?php echo $val['id'] ?>" <?php if ($val['is_active'] == 1) {
                                                                                                                        echo 'selected';
                                                                                                                    } ?>><?php echo $val['timezone'] . " - GMT: " . $val['gmt'] ?></option>
                                                                    <?php
                                                                    }
                                                                    ?>

                                                                </select>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'order' ? 'active show' : '' ?>" id="order" role="tabpanel" aria-labelledby="order-tab">
                                                <h5>Order Setting </h5>
                                                <form id="orderForm">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="order_delivery_verification">Order Delivery Verification <span class="text-danger text-xs">*(It will show 4 digit pin in user app to deliver order)</span></label>
                                                                <input type="checkbox" <?= isset($settings['order_delivery_verification']) && $settings['order_delivery_verification'] == '1' ? 'checked' : ''; ?> name="order_delivery_verification" id="order_delivery_verification"
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="live_tracking">Order Live Tracking</label>
                                                                <br>
                                                                <input type="checkbox" <?= isset($settings['live_tracking']) && $settings['live_tracking'] == '1' ? 'checked' : ''; ?> name="live_tracking" id="live_tracking"
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group ">
                                                                <label for="minimum_order_amount">Minimum Order Amount <span class="text-danger text-xs">*</span></label>
                                                                <input type="number" value="<?= isset($settings['minimum_order_amount']) ? esc($settings['minimum_order_amount']) : '' ?>" id="minimum_order_amount" name="minimum_order_amount" required="" class="form-control ">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <h5 class="mt-4">Additional Charge Control</h5>
                                                    <div class="row  ">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="additional_charge_status">Additional Charge</label>
                                                                <br>
                                                                <input type="checkbox" <?= isset($settings['additional_charge_status']) && $settings['additional_charge_status'] == '1' ? 'checked' : ''; ?> name="additional_charge_status" id="additional_charge_status"
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group ">
                                                                <label for="additional_charge_name">Additional Charge Name <span class="text-danger text-xs">*</span></label>
                                                                <input type="text" value="<?= isset($settings['additional_charge_name']) ? esc($settings['additional_charge_name']) : '' ?>" id="additional_charge_name" name="additional_charge_name" required="" class="form-control ">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group ">
                                                                <label for="additional_charge">Additional Charge Amount <span class="text-danger text-xs">*</span></label>
                                                                <input type="number" value="<?= isset($settings['additional_charge']) ? esc($settings['additional_charge']) : '' ?>" id="additional_charge" name="additional_charge" required="" class="form-control ">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <h5 class="mt-4">Delivery Method Control</h5>
                                                    <div class="row">
                                                        <input type="hidden" id="home_delivery_status_id" name="home_delivery_status_id" value="<?= esc(json_decode($settings['home_delivery_status'])->id); ?>">
                                                        <input type="hidden" id="home_delivery_status_image" name="home_delivery_status_image" value="<?= esc(json_decode($settings['home_delivery_status'])->image); ?>">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="home_delivery_status_title">Home Delivery Title</label>
                                                                <br>
                                                                <input type="text" class="form-control" placeholder="Title" name="home_delivery_status_title" id="home_delivery_status_title" value="<?= esc(json_decode($settings['home_delivery_status'])->title); ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="home_delivery_status_description">Home Delivery Description</label>
                                                                <br>
                                                                <input type="text" class="form-control" placeholder="Description" name="home_delivery_status_description" id="home_delivery_status_description" value="<?= esc(json_decode($settings['home_delivery_status'])->description); ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="home_delivery_status_status">Home Delivery Status</label>
                                                                <br>
                                                                <input type="checkbox" name="home_delivery_status_status" id="home_delivery_status_status" <?= isset(json_decode($settings['home_delivery_status'])->status) && json_decode($settings['home_delivery_status'])->status == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <input type="hidden" id="schedule_delivery_status_id" name="schedule_delivery_status_id" value="<?= esc(json_decode($settings['schedule_delivery_status'])->id); ?>">
                                                        <input type="hidden" id="schedule_delivery_status_image" name="schedule_delivery_status_image" value="<?= esc(json_decode($settings['schedule_delivery_status'])->image); ?>">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="schedule_delivery_status_title">Schedule Delivery Title</label>
                                                                <br>
                                                                <input type="text" class="form-control" placeholder="Title" name="schedule_delivery_status_title" id="schedule_delivery_status_title" value="<?= esc(json_decode($settings['schedule_delivery_status'])->title); ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="schedule_delivery_status_description">Schedule Delivery Description</label>
                                                                <br>
                                                                <input type="text" class="form-control" placeholder="Description" name="schedule_delivery_status_description" id="schedule_delivery_status_description" value="<?= esc(json_decode($settings['schedule_delivery_status'])->description); ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="schedule_delivery_status_status">Schedule Delivery Status</label>
                                                                <br>
                                                                <input type="checkbox" name="schedule_delivery_status_status" id="schedule_delivery_status_status" <?= isset(json_decode($settings['schedule_delivery_status'])->status) && json_decode($settings['schedule_delivery_status'])->status == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <input type="hidden" id="takeaway_status_id" name="takeaway_status_id" value="<?= esc(json_decode($settings['takeaway_status'])->id); ?>">
                                                        <input type="hidden" id="takeaway_status_image" name="takeaway_status_image" value="<?= esc(json_decode($settings['takeaway_status'])->image); ?>">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="takeaway_status_title">Self Pickup Title</label>
                                                                <br>
                                                                <input type="text" class="form-control" placeholder="Title" name="takeaway_status_title" id="takeaway_status_title" value="<?= esc(json_decode($settings['takeaway_status'])->title); ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="takeaway_status_description">Self Pickup Description</label>
                                                                <br>
                                                                <input type="text" class="form-control" placeholder="Description" name="takeaway_status_description" id="takeaway_status_description" value="<?= esc(json_decode($settings['takeaway_status'])->description); ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="takeaway_status_status">Self Pickup Status</label>
                                                                <br>
                                                                <input type="checkbox" name="takeaway_status_status" id="takeaway_status_status" <?= isset(json_decode($settings['takeaway_status'])->status) && json_decode($settings['takeaway_status'])->status == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </form>
                                            </div>

                                            <div class="tab-pane fade <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'delivery-boy' ? 'active show' : '' ?>" id="delivery-boy" role="tabpanel" aria-labelledby="delivery-boy-tab">
                                                <h5>Delivery Boy Setting</h5>
                                                <form id="deliveryBoyForm">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="delivery_boy_show_earning_in_app">Show Earning In App <span class="text-danger text-xs">*(With this feature Deliverymen can see their earnings on a specific order while accepting it.)</span></label>
                                                                <input type="checkbox" name="delivery_boy_show_earning_in_app" id="delivery_boy_show_earning_in_app" <?= isset($settings['delivery_boy_show_earning_in_app']) && $settings['delivery_boy_show_earning_in_app'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="delivery_boy_bonus_setting">Bonus Settings <span class="text-danger text-xs">*</span></label>
                                                                <br>
                                                                <input type="checkbox" name="delivery_boy_bonus_setting" id="delivery_boy_bonus_setting" <?= isset($settings['delivery_boy_bonus_setting']) && $settings['delivery_boy_bonus_setting'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="delivery_boy_cash_in_hand">Delivery Boy Cash In Hand <span class="text-danger text-xs">*</span></label>
                                                                <br>
                                                                <input type="checkbox" name="delivery_boy_cash_in_hand" id="delivery_boy_cash_in_hand" <?= isset($settings['delivery_boy_cash_in_hand']) && $settings['delivery_boy_cash_in_hand'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group ">
                                                                <label for="delivery_boy_maximum_cash_in_hand">Delivery Man Maximum Cash in Hand ($)<span class="text-danger text-xs">*</span></label>
                                                                <input type="number" value="<?= isset($settings['delivery_boy_maximum_cash_in_hand']) ? esc($settings['delivery_boy_maximum_cash_in_hand']) : '' ?>" id="delivery_boy_maximum_cash_in_hand" name="delivery_boy_maximum_cash_in_hand" required="" class="form-control ">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Update</button>

                                                </form>
                                            </div>
                                            <div class="tab-pane fade <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'app-setting' ? 'active show' : '' ?>" id="app-setting" role="tabpanel" aria-labelledby="app-setting-tab">
                                                <form id="appSettingForm">
                                                    <h5>App Setting</h5>
                                                    <h5 class=" mt-4"> Customer App Control</h5>
                                                    <div class="row">
                                                        <div class="col-md-6">

                                                            <div class="form-group">
                                                                <label for="app_minimum_version_android" class="form-label">
                                                                    Minimum User App Version (Android)

                                                                </label>
                                                                <input id="app_minimum_version_android" type="text" value="<?= isset($settings['app_minimum_version_android']) ? esc($settings['app_minimum_version_android']) : '' ?>" placeholder="App minimum version" class="form-control" name="app_minimum_version_android">
                                                            </div>
                                                            <div class="form-group ">
                                                                <label for="app_url_android" class="form-label">
                                                                    Download URL for User App (Android)

                                                                </label>
                                                                <input id="app_url_android" type="text" value="<?= isset($settings['app_url_android']) ? esc($settings['app_url_android']) : '' ?>" placeholder="App url" class="form-control" name="app_url_android">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">


                                                            <div class="form-group">
                                                                <label for="app_minimum_version_ios" class="form-label">Minimum User App Version (Ios)

                                                                </label>
                                                                <input id="app_minimum_version_ios" value="<?= isset($settings['app_minimum_version_ios']) ? esc($settings['app_minimum_version_ios']) : '' ?>" type="text" placeholder="App minimum version" class="form-control" name="app_minimum_version_ios">
                                                            </div>
                                                            <div class="form-group ">
                                                                <label for="app_url_ios" class="form-label">
                                                                    Download URL for User App (Ios)

                                                                </label>
                                                                <input id="app_url_ios" type="text" value="<?= isset($settings['app_url_ios']) ? esc($settings['app_url_ios']) : '' ?>" placeholder="App url" class="form-control" name="app_url_ios">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <h5 class=" mt-4"> Delivery Boy App Control</h5>

                                                    <div class="row">
                                                        <div class="col-md-6">

                                                            <div class="form-group">
                                                                <label for="app_minimum_version_android_delivery_boy" class="form-label text-capitalize">Minimum Delivery Boy App Version (Android)

                                                                </label>
                                                                <input id="app_minimum_version_android_delivery_boy" type="text" value="<?= isset($settings['app_minimum_version_android_delivery_boy']) ? esc($settings['app_minimum_version_android_delivery_boy']) : '' ?>" placeholder="App minimum version" class="form-control " name="app_minimum_version_android_delivery_boy" min="0">
                                                            </div>
                                                            <div class="form-group ">
                                                                <label for="app_url_android_delivery_boy" class="form-label text-capitalize">
                                                                    Download URL for Delivery Boy App (Android)
                                                                    <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="Users will download the latest store app using this URL.">

                                                                    </span>
                                                                </label>
                                                                <input id="app_url_android_delivery_boy" type="text" value="<?= isset($settings['app_url_android_delivery_boy']) ? esc($settings['app_url_android_delivery_boy']) : '' ?>" placeholder="Download Url" class="form-control " name="app_url_android_delivery_boy">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">

                                                            <div class="form-group">
                                                                <label for="app_minimum_version_ios_delivery_boy" class="form-label text-capitalize">Minimum Delivery Boy App Version (Ios)

                                                                </label>
                                                                <input id="app_minimum_version_ios_delivery_boy" type="text" value="<?= isset($settings['app_minimum_version_ios_delivery_boy']) ? esc($settings['app_minimum_version_ios_delivery_boy']) : '' ?>" placeholder="App minimum version" class="form-control " name="app_minimum_version_ios_delivery_boy" min="0">
                                                            </div>
                                                            <div class="form-group ">
                                                                <label for="app_url_ios_delivery_boy" class="form-label text-capitalize">
                                                                    Download URL for Delivery Boy App (Ios)
                                                                    <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="Users will download the latest store app version using this URL.">

                                                                    </span>
                                                                </label>
                                                                <input id="app_url_ios_delivery_boy" type="text" value="<?= isset($settings['app_url_ios_delivery_boy']) ? esc($settings['app_url_ios_delivery_boy']) : '' ?>" placeholder="Download Url" class="form-control " name="app_url_ios_delivery_boy">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'frontend-landing' ? 'active show' : '' ?>" id="frontend-landing" role="tabpanel" aria-labelledby="frontend-landing-tab">
                                                <h5>Frontend Landing Page</h5>
                                                <form id="frontendSettingForm">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="frontend_category_section">Display Category Section in Home </label>
                                                                <br>
                                                                <input type="checkbox" name="frontend_category_section" id="frontend_category_section" <?= isset($settings['frontend_category_section']) && $settings['frontend_category_section'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="frontend_brand_section">Display Brand Section in Home </label>
                                                                <br>
                                                                <input type="checkbox" name="frontend_brand_section" id="frontend_brand_section" <?= isset($settings['frontend_brand_section']) && $settings['frontend_brand_section'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="frontend_seller_section">Display Seller Section in Home</label>
                                                                <br>
                                                                <input type="checkbox" name="frontend_seller_section" id="frontend_seller_section" <?= isset($settings['frontend_seller_section']) && $settings['frontend_seller_section'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="frontend_popular_section">Display Popular Section in Home</label>
                                                                <br>
                                                                <input type="checkbox" name="frontend_popular_section" id="frontend_popular_section" <?= isset($settings['frontend_popular_section']) && $settings['frontend_popular_section'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="frontend_deal_of_the_day_section">Display Deal Of The Day Section in Home</label>
                                                                <br>
                                                                <input type="checkbox" name="frontend_deal_of_the_day_section" id="frontend_deal_of_the_day_section" <?= isset($settings['frontend_deal_of_the_day_section']) && $settings['frontend_deal_of_the_day_section'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="qr_code_search_status">QR Code Search (For App)</label>
                                                                <br>
                                                                <input type="checkbox" name="qr_code_search_status" id="qr_code_search_status" <?= isset($settings['qr_code_search_status']) && $settings['qr_code_search_status'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="voice_search_status">Voice Search (For App)</label>
                                                                <br>
                                                                <input type="checkbox" name="voice_search_status" id="voice_search_status" <?= isset($settings['voice_search_status']) && $settings['voice_search_status'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="frontend_category_row_show">Display Category Row</label>
                                                                <br>
                                                                <input type="number" class="form-control " name="frontend_category_row_show" id="frontend_category_row_show" value="<?=  $settings['frontend_category_row_show'] ?>"
                                                                    >
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade <?php echo  isset($_GET['setting']) && $_GET['setting'] == 'seller' ? 'active show' : '' ?>" id="seller" role="tabpanel" aria-labelledby="seller-tab">
                                                <h5>Seller Setting</h5>
                                                <form id="sellerSettingForm">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="seller_can_cancel_order">Can a Store Cancel Order <span class="text-danger text-xs">*</span></label>
                                                                <br>
                                                                <input type="checkbox" name="seller_can_cancel_order" id="seller_can_cancel_order" <?= isset($settings['seller_can_cancel_order']) && $settings['seller_can_cancel_order'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="seller_can_complete_order">Can a Store Complete Order <span class="text-danger text-xs">*</span></label>
                                                                <br>
                                                                <input type="checkbox" name="seller_can_complete_order" id="seller_can_complete_order" <?= isset($settings['seller_can_complete_order']) && $settings['seller_can_complete_order'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="seller_only_one_seller_cart">One Seller Cart<span class="text-danger text-xs">*</span></label>
                                                                <br>
                                                                <input type="checkbox" name="seller_only_one_seller_cart" id="seller_only_one_seller_cart" <?= isset($settings['seller_only_one_seller_cart']) && $settings['seller_only_one_seller_cart'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="seller_approval_product">Need Approval for Publishing Products <span class="text-danger text-xs">*</span></label>
                                                                <br>
                                                                <input type="checkbox" name="seller_approval_product" id="seller_approval_product" <?= isset($settings['seller_approval_product']) && $settings['seller_approval_product'] == '1' ? 'checked' : ''; ?>
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success">
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
    <script src="<?= base_url('/assets/page-script/store_setting.js') ?>"></script>
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