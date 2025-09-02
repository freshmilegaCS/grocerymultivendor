<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HighLight | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">HighLight</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Dashboard</li>
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
                                    <h3 class="card-title">Add HighLight</h3>
                                </div>
                                <!-- /.card-header -->
                                <form method="post" id="highlightForm" enctype="multipart/form-data">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group  col-md-4">
                                                <label>Select Seller <span class="text-danger">*</span></label>
                                                <div class="select2-olive">
                                                    <select id="seller_id" name="seller_id" class="form-control select2 select2-olive" data-dropdown-css-class="select2-olive" ata-placeholder="Select Seller" required>
                                                        <option value="" selected="" disabled="">Select Seller</option>
                                                        <?php foreach ($sellers as $seller): ?>
                                                            <option value="<?= esc($seller['id']); ?>"><?= esc($seller['store_name']); ?></option>
                                                        <?php endforeach; ?>

                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group  col-md-4">
                                                <label for="exampleInputBorder">Title</label>
                                                <input type="text" class="form-control " name="title" id="title" placeholder="Enter Title">
                                            </div>
                                            <div class="form-group  col-md-4">
                                                <label for="exampleInputBorder">Description</label>
                                                <input type="text" class="form-control " name="description" id="description" placeholder="Enter Description">
                                            </div>
                                            <div class="form-group  col-md-4">
                                                <label>Select Type <span class="text-danger">*</span></label>
                                                <select id="media_type" name="media_type" class="form-control " required>
                                                    <option value="" selected="" disabled="">Select Type</option>
                                                    <option value="image">Image</option>
                                                    <option value="video">Video</option>
                                                </select>
                                            </div>

                                            <div class="form-group  col-md-4 d-none" id="video-div">
                                                <label for="exampleInputBorder">Youtube Video Link</label>
                                                <input type="text" class="form-control " name="video" id="video" placeholder="Enter Youtube Video Link">
                                            </div>
                                            <div class="form-group  col-md-12 d-none" id="image-div">
                                                <label for="exampleInputBorder">Image</label>
                                                <div class="dropzone custom-dropzone" id="highlights-image">
                                                    <div class="dropzone-clickable-area">
                                                        <div class="icon"><i class="fi fi-br-upload"></i></div>
                                                        <p>Upload Main Image </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button type="button" name="add_cat" id="add_cat" class="btn btn-primary" onclick="addHighlights()">
                                            Add HighLight
                                        </button>
                                    </div>

                                </form>
                                <!-- /.card-body -->
                            </div>
                        </div>
                        <!-- ./col -->

                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">View HighLight</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class="d-flex justify-content-end align-items-center mb-3">
                                        <div class="mr-2">
                                            <select id="custom-length-change" class=" form-control form-control-sm primary-bprder">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                        </div>
                                        <!-- Export Dropdown Button -->
                                        <div class="btn-group mr-2">
                                            <a href="#!" class="btn btn-primary btn-sm  dropdown-toggle " data-toggle="dropdown" aria-expanded="false">
                                                <i class="fi fi-tr-file-export"></i> Export
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                <!-- Placeholder for DataTable export buttons -->
                                                <li><a class="dropdown-item dt-export-copy" href="#">Copy</a></li>
                                                <li><a class="dropdown-item dt-export-csv" href="#">CSV</a></li>
                                                <li><a class="dropdown-item dt-export-excel" href="#">Excel</a></li>
                                                <li><a class="dropdown-item dt-export-pdf" href="#">PDF</a></li>
                                                <li><a class="dropdown-item dt-export-print" href="#">Print</a></li>
                                            </ul>
                                        </div>

                                        <!-- Search box aligned next to Export button -->
                                        <div>
                                            <input type="search" id="custom-search" class="form-control form-control-sm primary-bprder" placeholder="Search:" aria-controls="example">
                                        </div>
                                    </div>

                                    <table id="view_highlights" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Title</th>
                                                <th>Description</th>
                                                <th>Media</th>
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
    <script src="<?= base_url('/assets/page-script/highlights.js') ?>"></script>


</body>

</html>