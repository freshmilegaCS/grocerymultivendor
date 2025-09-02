<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delivery Boy | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

    <?= $this->include('template/style') ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>
</head>

<body class="sidebar-mini control-sidebar-slide-open text-sm  layout-fixed <?php echo  $settings['thememode'] == 'Light' ? '' : 'dark-mode'; ?> layout-navbar-fixed text-sm" id="body">
    <div class="wrapper">


        <?= $this->include('template/header') ?>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <?= $this->include('template/sidebar') ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-<?= $settings['primary_color'] ?>">
                                <div class="card-header">
                                    <h3 class="card-title">Add Delivery Boy</h3>
                                </div>
                                <!-- /.card-header -->
                                <form class="form" id="deliveryBoyForm" enctype="multipart/form-data">

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-md-4">
                                                <label for="name">Name <span class="text-danger">*</span></label>
                                                <input type="text" id="name" class="form-control " name="name" placeholder="Name" required>
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="mobile">Mobile <span class="text-danger">*</span></label>
                                                <input type="number" id="mobile" class="form-control " placeholder="Mobile Number" name="mobile" required min="6000000000" max="9999999999" onchange="onMobileChange()">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="dob">Date of Birth </label>
                                                <input type="date" id="dob" class="form-control " name="dob">
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="password">Password <span class="text-danger">*</span></label>
                                                <input autocomplete="new-password" type="password" placeholder="Password" id="password" class="form-control " name="password" required>
                                            </div>

                                            <div class="form-group col-md-8">
                                                <label for="address">Address </label>
                                                <textarea type="text" id="address" class="form-control " name="address" placeholder="Address"></textarea>
                                            </div>


                                            <div class="form-group col-md-6">
                                                <label for="driving_license">Driving License <span class="text-danger">*</span></label>
                                                <div class="dropzone custom-dropzone" id="driving_license">
                                                    <div class="dropzone-driving_license">
                                                        <div class="icon"><i class="fi fi-br-upload"></i></div>
                                                        <p>Upload Driving License </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="driving_license">National Identity Card <span class="text-danger">*</span></label>
                                                <div class="dropzone custom-dropzone" id="national_identity_card">
                                                    <div class="dropzone-national_identity_card">
                                                        <div class="icon"><i class="fi fi-br-upload"></i></div>
                                                        <p>Upload National Identity Card </p>
                                                    </div>
                                                </div>
                                            </div>



                                            <div class="form-group col-md-4">
                                                <label for="bank_account_number">Bank Account Number</label>
                                                <input type="text" id="bank_account_number" class="form-control " name="bank_account_number" placeholder="Bank Account Number">
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="bank_name">Bank Name</label>
                                                <input type="text" id="bank_name" class="form-control " name="bank_name" placeholder="Bank Name">
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="account_name">Account Name</label>
                                                <input type="text" id="account_name" class="form-control " name="account_name" placeholder="Account Name">
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="ifsc_code">IFSC Code</label>
                                                <input type="text" id="ifsc_code" class="form-control " name="ifsc_code" placeholder="IFSC Code">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="city_id">City <span class="text-danger">*</span> </label>
                                                <select class="form-control " name="city_id" id="city_id">
                                                    <option>Select City</option>
                                                    <?php foreach ($city as $key => $val) { ?>
                                                        <option value="<?php echo $val['id'] ?>"><?php echo $val['name'] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="pincode">Pincode</label>
                                                <input type="number" id="pincode" class="form-control " name="pincode" placeholder="Pincode" min="0">
                                            </div>

                                            <div class="form-group col-md-12">
                                                <label for="other_payment_information">Other Payment Information</label>
                                                <textarea id="other_payment_information" class="form-control " name="other_payment_information" placeholder="Other Payment Information"></textarea>
                                            </div>


                                            <div class="form-group col-md-4">
                                                <label for="bonus_type">Bonus Type <span class="text-danger">*</span></label>
                                                <select id="bonus_type" class="form-control " name="bonus_type" required>
                                                    <option value="0">Fixed or Salaried</option>
                                                    <?php
                                                    if ($settings['delivery_boy_bonus_setting'] == 1) { ?>
                                                        <option value="1">Commission</option>
                                                    <?php }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php
                                        if ($settings['delivery_boy_bonus_setting'] == 1) { ?>
                                            <div class="row" id="commission_div" style="display: none;">
                                                <div class="form-group col-md-4">
                                                    <label for="bonus_percentage">Bonus Percentage <span class="text-danger">*</span></label>
                                                    <input type="number" id="bonus_percentage" class="form-control " name="bonus_percentage" placeholder="Bonus Percentage" min="0">
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label for="bonus_min_amount">Bonus Min Amount </label>
                                                    <input type="number" id="bonus_min_amount" class="form-control " name="bonus_min_amount" placeholder="Bonus Min Amount" min="0">
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label for="bonus_max_amount">Bonus Max Amount</label>
                                                    <input type="number" id="bonus_max_amount" class="form-control " name="bonus_max_amount" placeholder="Bonus Max Amount" min="0">
                                                </div>
                                            </div>
                                        <?php }
                                        ?>

                                    </div>
                                    <div class="card-footer">
                                        <button type="submit" name="submitBtn" id="submitBtn" class="btn btn-primary">
                                            Add Delivery Boy
                                        </button>
                                    </div>
                                </form>
                                <!-- /.card-body -->
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
    <script src="<?= base_url('/assets/page-script/delivery_boy_add.js') ?>"></script>
</body>

</html>