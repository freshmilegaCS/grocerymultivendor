<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Coupon | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

    <?= $this->include('template/style') ?>

</head>

<body class="sidebar-mini control-sidebar-slide-open text-sm  layout-fixed <?php echo  $settings['thememode'] == 'Light' ? '' : 'dark-mode'; ?> layout-navbar-fixed text-sm" id="body">
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
                            <h1 class="m-0">Coupon</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Coupon</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->

            <section class="content">
                <div class="container-fluid">
                    <!-- Small boxes (Stat box) -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-<?php echo $settings['primary_color']; ?>">
                                <div class="card-header">
                                    <h3 class="card-title">Add Coupon</h3>
                                </div>
                                <!-- .card-header -->
                                <form class="form" method="post" enctype="multipart/form-data">
                                    <input type="hidden" id="user_id" name="user_id" />
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 col-lg-4">
                                                <div class="form-group">
                                                    <label>Select User Type <span class="text-danger">*</span></label>
                                                    <select name="user_type" id="user_type" onchange="showDiv()" class="form-control " required>
                                                        <option value="">Select User Type</option>
                                                        <option value="0">For All User</option>
                                                        <option value="1">For Single User</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div id="single" class="col-md-4 col-lg-4" style="display:none">
                                                <div id="single" class="form-group">
                                                    <label>Select User Name <span class="text-danger">*</span></label>

                                                    <input type="text" id="searchKeyword" class="form-control " name="searchKeyword">
                                                    <ul id="searchResult" class="list-group" style="position: absolute;z-index: 9; width: 95%;    box-shadow: 0px 5px 10px grey;">
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-lg-4">
                                                <div class="form-group">
                                                    <label>Number of Times <span class="text-danger">*</span></label>
                                                    <select name="n_use" id="n_use" class="form-control " required>
                                                        <option value="0">Single Time Valid</option>
                                                        <option value="1">Multiple Time Valid</option>

                                                    </select>
                                                </div>
                                            </div>


                                            <div class="col-md-4 col-lg-4">

                                                <div class="form-group">
                                                    <label>Coupon Image <span class="text-danger">*</span></label>
                                                    <input accept=".jpeg, .png, .jpg, .webp" type="file" id="coupon_img" class="form-control " placeholder="Choose  Image" name="coupon_img" onchange="convertImage(event)" required>
                                                    <img src="" id="coupon_img_webp" style="display:none">
                                                </div>
                                            </div>

                                            <div class="col-md-4 col-lg-4">

                                                <div class="form-group">
                                                    <label>Coupon Expiry Date <span class="text-danger">*</span></label>
                                                    <input type="date" name="exp_date" class="form-control " id="exp_date" required>
                                                </div>
                                            </div>



                                            <div class="col-md-4 col-lg-4">
                                                <div class="form-group">

                                                    <label>Coupon Code <span class="text-danger">*</span></label>
                                                    <div class="row">
                                                        <div class="col-md-10 col-lg-10 col-sm-10">
                                                            <input type="text" id="coupon_code" class="form-control " maxlength="8" name="coupon_code" required oninput="this.value = this.value.toUpperCase()">
                                                        </div>

                                                        <div class="col-md-2 col-lg-2 col-sm-2">
                                                            <button type="button" class="btn btn-success btn-sm" onclick="makeid(8)"><i class="fi fi-ts-rotate-reverse" style="font-size: 20px;" aria-hidden="true"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>




                                            <div class="col-md-3 col-lg-3">
                                                <div class="form-group">
                                                    <label>Coupon title <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control " name="coupon_title" id="coupon_title" required>
                                                </div>
                                            </div>



                                            <div class="col-md-3 col-lg-3">
                                                <div class="form-group">
                                                    <label>Coupon Status <span class="text-danger">*</span></label>
                                                    <select name="coupon_status" id="coupon_status" class="form-control " required>
                                                        <option value="">Select Coupon Status</option>
                                                        <option value="1">Publish</option>
                                                        <option value="0">Unpublish</option>

                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-3 col-lg-3">

                                                <div class="form-group">
                                                    <label>Coupon Min Order Amount <span class="text-danger">*</span></label>
                                                    <input type="number" id="min_amt" min="1" class="form-control " name="min_amt" step="1" onkeypress="return event.charCode >= 48 && event.charCode <= 57" required>
                                                </div>
                                            </div>

                                            <div class="col-md-3 col-lg-3">
                                                <div class="form-group">
                                                    <label>Coupon Value <span class="text-danger">*</span></label>
                                                    <input type="number" id="coupon_value" min="1" class="form-control " name="coupon_value" step="1" onkeypress="return event.charCode >= 48 && event.charCode <= 57" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-lg-3">
                                                <div class="form-group">
                                                    <label>Coupon Type <span class="text-danger">*</span></label>
                                                   <select name="coupon_type" id="coupon_type" class="form-control " required>
                                                        <option value="1">Percentage</option>
                                                        <option value="2">Value</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-9 col-lg-9">
                                                <div class="form-group">
                                                    <label>Coupon Description <span class="text-danger">*</span></label>
                                                    <textarea class="form-control " rows="2" id="description" name="description" style="resize: none;"></textarea>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button type="button" name="add_coupon" onclick="addCoupon()" class="btn btn-primary">
                                            Add Coupon
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">View Coupon</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <table id="view_coupon" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                            <tr>
                                                <th>Sr No.</th>
                                                <th>Coupon Title</th>
                                                <th>Coupon Code</th>
                                                <th>Coupon Discount?</th>
                                                <th>Coupon Type</th>
                                                <th>Discount Type</th>
                                                <th>Coupon Expiry Date</th>
                                                <th>Coupn Status </th>
                                                <th>Coupon Order Min Value</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
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
    <script src="<?= base_url('/assets/page-script/coupon.js') ?>"></script>

</body>

</html>