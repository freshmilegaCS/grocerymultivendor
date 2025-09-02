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
                                    <h3 class="card-title">Edit Delivery Boy</h3>
                                </div>
                                <!-- /.card-header -->
                                <form class="form" id="deliveryBoyForm" enctype="multipart/form-data">
                                    <input type="hidden" name="editid" id="editid" value="<?= $deliveryBoy['id'] ?>">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-md-4">
                                                <label for="name">Name</label>
                                                <input type="text" id="name" class="form-control " name="name" placeholder="Name" required value="<?= $deliveryBoy['name'] ?>">
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="mobile">Mobile</label>
                                                <input type="number" id="mobile" class="form-control " placeholder="Mobile Number" name="mobile" required min="6000000000" max="9999999999" onchange="onMobileChange()" value="<?= $deliveryBoy['mobile'] ?>">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="dob">Date of Birth</label>
                                                <input type="date" id="dob" class="form-control " name="dob" value="<?= $deliveryBoy['dob'] ?>">
                                            </div>

                                            <div class="form-group col-md-12">
                                                <label for="address">Address</label>
                                                <textarea type="text" id="address" class="form-control " name="address" placeholder="Address" required><?= $deliveryBoy['address'] ?></textarea>
                                            </div>


                                            <div class="form-group col-md-6">
                                                <label for="driving_license">Driving License <a href="<?= base_url() . $deliveryBoy['driving_license'] ?>" target="_blank" class="btn btn-primary btn-sm">View Old Document</a></label>
                                                <div class="dropzone custom-dropzone" id="driving_license">
                                                    <div class="dropzone-driving_license">
                                                        <div class="icon"><i class="fi fi-br-upload"></i></div>
                                                        <p>Upload Driving License </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="driving_license">National Identity Card <a href="<?= base_url() . $deliveryBoy['national_identity_card'] ?>" target="_blank" class="btn btn-primary btn-sm">View Old Document</a></label>
                                                <div class="dropzone custom-dropzone" id="national_identity_card">
                                                    <div class="dropzone-national_identity_card">
                                                        <div class="icon"><i class="fi fi-br-upload"></i></div>
                                                        <p>Upload National Identity Card </p>
                                                    </div>
                                                </div>
                                            </div>



                                            <div class="form-group col-md-4">
                                                <label for="bank_account_number">Bank Account Number</label>
                                                <input type="text" id="bank_account_number" class="form-control " name="bank_account_number" placeholder="Bank Account Number" value="<?= $deliveryBoy['bank_account_number'] ?>">
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="bank_name">Bank Name</label>
                                                <input type="text" id="bank_name" class="form-control " name="bank_name" placeholder="Bank Name" value="<?= $deliveryBoy['bank_name'] ?>">
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="account_name">Account Name</label>
                                                <input type="text" id="account_name" class="form-control " name="account_name" placeholder="Account Name" value="<?= $deliveryBoy['account_name'] ?>">
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="ifsc_code">IFSC Code</label>
                                                <input type="text" id="ifsc_code" class="form-control " name="ifsc_code" placeholder="IFSC Code" value="<?= $deliveryBoy['ifsc_code'] ?>">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="city_id">City <span class="text-danger">*</span> </label>
                                                <select class="form-control " name="city_id" id="city_id">
                                                    <option>Select City</option>
                                                    <?php foreach ($city as $key => $val) { ?>
                                                        <option value="<?php echo $val['id'] ?>" <?php echo $val['id'] == $deliveryBoy['city_id'] ? "selected" : "" ?>><?php echo $val['name'] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="pincode">Pincode</label>
                                                <input type="number" id="pincode" class="form-control " name="pincode" placeholder="Pincode" min="0" value="<?= $deliveryBoy['pincode'] ?>">
                                            </div>

                                            <div class="form-group col-md-12">
                                                <label for="other_payment_information">Other Payment Information</label>
                                                <textarea id="other_payment_information" class="form-control " name="other_payment_information" placeholder="Other Payment Information"><?= $deliveryBoy['other_payment_information'] ?></textarea>
                                            </div>


                                            <div class="form-group col-md-4">
                                                <label for="bonus_type">Bonus Type</label>
                                                <select id="bonus_type" class="form-control " name="bonus_type" required>
                                                    <option value="">Select Bonus Type</option>
                                                    <option value="0" <?php echo $deliveryBoy['bonus_type'] == 0 ? "selected" : "" ?>>Fixed or Salaried</option>
                                                    <?php
                                                    if ($settings['delivery_boy_bonus_setting'] == 1) { ?>
                                                        <option value="1" <?php echo $deliveryBoy['bonus_type'] == 1 ? "selected" : "" ?>>Commission</option>
                                                    <?php }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php
                                        if ($settings['delivery_boy_bonus_setting'] == 1) { ?>
                                            <div class="row" id="commission_div" <?php echo $deliveryBoy['bonus_type'] == 1 ? "" : "style='display: none;'" ?>>
                                                <div class="form-group col-md-4">
                                                    <label for="bonus_percentage">Bonus Percentage</label>
                                                    <input type="number" id="bonus_percentage" class="form-control " name="bonus_percentage" placeholder="Bonus Percentage" step="0.01" min="0" value="<?= $deliveryBoy['bonus_percentage'] ?>">
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label for="bonus_min_amount">Bonus Min Amount</label>
                                                    <input type="number" id="bonus_min_amount" class="form-control " name="bonus_min_amount" placeholder="Bonus Min Amount" step="0.01" min="0" value="<?= $deliveryBoy['bonus_min_amount'] ?>">
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label for="bonus_max_amount">Bonus Max Amount</label>
                                                    <input type="number" id="bonus_max_amount" class="form-control " name="bonus_max_amount" placeholder="Bonus Max Amount" step="0.01" min="0" value="<?= $deliveryBoy['bonus_max_amount'] ?>">
                                                </div>
                                            </div>
                                        <?php }
                                        ?>
                                    </div>
                                    <div class="card-footer">
                                        <button type="submit" name="submitBtn" id="submitBtn" class="btn btn-primary">
                                            Edit Delivery Boy
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
    <script src="<?= base_url('/assets/page-script/delivery_boy_edit.js') ?>"></script>
</body>

</html>