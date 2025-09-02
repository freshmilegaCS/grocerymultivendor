<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Banner | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
                            <h1 class="m-0">Banner</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Banner</li>
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
                        <div class="col-md-4">
                            <div class="card card-<?php echo $settings['primary_color']; ?>">
                                <div class="card-header">
                                    <h3 class="card-title">Add Banner</h3>
                                </div>
                                <!-- /.card-header -->
                                <form id="addBannerForm" method="post" enctype="multipart/form-data">
                                    <div class="card-body">

                                        <div class="form-group">
                                            <label for="cname">Select Banner Type <span class="text-danger">*</span></label>
                                            <select class="form-control " name="banner_type" id="banner_type">
                                                <option value="">Select Banner Type</option>
                                                <option value="0" >Header </option>
                                                <option value="1" >Deal of the day</option>
                                                <option value="2" >Home section </option>
                                                <option value="3" >Footer</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="cname">Select Category</label>
                                            <select class="form-control " name="category_id" id="category_id">
                                                <option value="" >Select Category</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?= esc($category['id']); ?>"><?= esc($category['category_name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="cname">Banner Image</label>
                                            <input accept=".jpeg, .png, .jpg, .webp" type="file" id="banner_img" class="form-control " placeholder="Choose Banner Image" name="banner_img" onchange="convertImage(event)">
                                        </div>
                                        <div class="form-group">
                                            <img src="" id="banner_img_webp" style="width:100%">
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button type="button" name="sub_product" class="btn btn-primary" onclick="addBanner()">
                                            Add Banner
                                        </button>
                                    </div>

                                </form>
                                <!-- /.card-body -->
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">View Banner</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <table id="view_banner" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Sr No</th>
                                                <th>Category Name</th>
                                                <th>Banner Image</th>
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
    <script src="<?= base_url('/assets/page-script/banner.js') ?>"></script>

</body>

</html>