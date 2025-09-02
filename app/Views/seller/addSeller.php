<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Seller | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

    <?= $this->include('template/style') ?>
</head>

<body class="sidebar-mini control-sidebar-slide-open text-sm sidebar-mini-xs sidebar-mini-md layout-fixed <?php echo $settings['thememode'] == 'Light' ? '' : 'dark-mode' ?> layout-navbar-fixed text-sm" id="body">
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
                            <form method="post" id="sellerForm" enctype="multipart/form-data">
                                <input type="hidden" name="map_address" id="map_address" >
                                <div class="card card-<?php echo $settings['primary_color'] ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Seller Info</h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name"> Seller Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control " name="name" id="name" placeholder="Enter  Seller Name">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="email">Email <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control " name="email" id="email" placeholder="Enter email">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="password">Password <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control " name="password" id="password" placeholder="Enter Password">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="mobile">Mobile <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control " name="mobile" id="mobile" placeholder="Enter Mobile">
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                                <div class="card card-<?php echo $settings['primary_color'] ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Store Info</h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="store_name"> Store Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control " name="store_name" id="store_name" placeholder="Enter Store Name">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="category">Select Category <span class="text-danger">*</span></label>
                                                    <div class="select2-olive">
                                                        <select class="form-control " multiple="multiple" name="category[]" data-dropdown-css-class="select2-olive" id="category">
                                                            <?php foreach ($categories as $key => $val) { ?>
                                                                <option value="<?php echo $val['id'] ?>"><?php echo $val['category_name'] ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="store_address"> Address </label>
                                                    <input type="text" class="form-control " name="store_address" id="store_address" placeholder="Enter  Address">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="pan_number">Pan Card </label>
                                                    <input type="text" class="form-control " name="pan_number" id="pan_number" placeholder="Enter PAN">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="tax_name">Tax Name/ GST Name </label>
                                                    <input type="text" class="form-control " name="tax_name" id="tax_name" placeholder="Enter Tax Name">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="tax_number">Tax Number/ GST Number </label>
                                                    <input type="text" class="form-control " name="tax_number" id="tax_number" placeholder="Enter Tax Number">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card card-<?php echo $settings['primary_color'] ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Store Location Info</h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="city_id">City <span class="text-danger">*</span> </label>
                                                    <select class="form-control " name="city_id" id="city_id">
                                                        <option>Select City</option>
                                                        <?php foreach ($city as $key => $val) { ?>
                                                            <option value="<?php echo $val['id'] ?>"><?php echo $val['name'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="deliverable_area_id">Serviceable Area <span class="text-danger">*</span> </label>
                                                    <select class="form-control " name="deliverable_area_id" id="deliverable_area_id">
                                                        <option value="">Select Serviceable Area</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="pan_number">Search Location</label>
                                                    <input type="text" class="form-control " id="pac-input" placeholder="Search Location">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="latitude">Latitude </label>
                                                    <input type="text" class="form-control " readonly name="latitude" id="latitude" placeholder="Enter Latitude">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="longitude">Longitude</label>
                                                    <input type="text" class="form-control " readonly name="longitude" id="longitude" placeholder="Enter Longitude">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div id="map"></div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card card-<?php echo $settings['primary_color'] ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Payment Details</h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="account_name">Account Name</label>
                                                    <input type="text" class="form-control " name="account_name" id="account_name" placeholder="Enter Account Name">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="bank_name">Bank Name</label>
                                                    <input type="text" class="form-control " name="bank_name" id="bank_name" placeholder="Enter  Bank Name">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="branch">Branch </label>
                                                    <input type="text" class="form-control " name="branch" id="branch" placeholder="Enter Branch">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="account_number">Account Number </label>
                                                    <input type="text" class="form-control " name="account_number" id="account_number" placeholder="Enter Account Number">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="bank_ifsc_code">IFSC </label>
                                                    <input type="text" class="form-control " name="bank_ifsc_code" id="bank_ifsc_code" placeholder="Enter IFSC">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="card card-<?php echo $settings['primary_color'] ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Document Section</h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="">Profile  <span class="text-danger">*</span></label>
                                                    <input type="file" class="form-control" required name="logo" id="logo" accept="image/png, image/jpg, image/jpeg">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="national_id_proof">Id Proof </label>
                                                    <input type="file" class="form-control " name="national_id_proof" id="national_id_proof" placeholder="Enter Id Proof">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="address_proof">Address Proof</label>
                                                    <input type="file" class="form-control" name="address_proof" id="address_proof" placeholder="Enter Address Proof">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="card card-<?php echo $settings['primary_color'] ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Other Info</h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">

                                        <div class="row">

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="require_products_approval">Require Product's Approval? <span class="text-danger">*</span></label>
                                                    <select class="form-control " name="require_products_approval" id="require_products_approval">
                                                        <option value="0">No</option>
                                                        <option value="1">Yes</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="view_customer_details">View Customer's Details? <span class="text-danger">*</span></label>
                                                    <select class="form-control " name="view_customer_details" id="view_customer_details">
                                                        <option value="0">No</option>
                                                        <option value="1">Yes</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="commission">Commission % <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control " name="commission" id="commission" placeholder="Enter Commission">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="card-footer">
                                        <input type="submit" class="btn btn-primary" name="add_franchaise" id="add_franchaise" value="Add Seller">
                                    </div>
                                </div>
                            </form>
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
    <script src="<?= base_url('/assets/page-script/seller.js') ?>"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $settings['map_api_key'] ?>&callback=initAutocomplete&libraries=places&v=weekly" defer></script>
</body>

</html> 