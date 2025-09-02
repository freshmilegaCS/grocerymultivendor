<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notification | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid"> 
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Notification</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Notification</li>
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
                            <div class="card card-<?php echo $settings['primary_color'] ?>">
                                <div class="card-header">
                                    <h3 class="card-title">Send Notification</h3>
                                </div>
                                <!-- /.card-header -->
                                <form class="form" method="post" enctype="multipart/form-data" autocomplete="off">
                                    <input type="hidden" id="user" name="user" />
                                    <div class="card-body">

                                        <div class="form-group">
                                            <label for="cname">Select User Type</label>
                                            <select name="user_type" id="test" onchange="showDiv()" class="form-control " required>
                                                <option value="0">For All User</option>
                                                <option value="1">For Single User</option>
                                            </select>
                                        </div>

                                        <div id="single" class="form-group" style="display:none">
                                            <label>Select User Name </label>
                                            <input type="text" id="searchKeyword" class="form-control " name="text">
                                            <ul id="searchResult" class="list-group" style="position: absolute;z-index: 9; width: 95%;box-shadow: 0px 5px 10px grey;">
                                            </ul>
                                        </div>

                                        <div class="form-group">
                                            <label for="exampleInputBorder">Title <span class="text-danger">*</span></label>
                                            <input required="true" type="text" class="form-control " name="title" id="title" placeholder="Enter Title">
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputBorder">Description</label>
                                            <input type="text" class="form-control " name="description" id="description" placeholder="Enter Description">
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <input type="button" name="send_notification" id="send_notification" class="btn btn-primary" value="Send Notification" onclick="addNotification()">
                                    </div>
                                </form>
                                <!-- /.card-body -->
                            </div>
                        </div>
                        <!-- ./col -->

                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">View Notification</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                <div class=" d-flex justify-content-between">
                                        <div class="d-flex  ">
                                            <div class="mr-2">
                                                <label for="">Show System Generated</label>
                                                <select id="is_system_generated" class=" form-control form-control-sm primary-bprder filter-product">
                                                        <option value="0" >No</option>
                                                        <option value="1" >Yes</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center ">
                                            <div class="mx-2">

                                                <select id="custom-length-change" class=" form-control  primary-bprder">
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                    <option value="-1">All</option>
                                                </select>
                                            </div>
                                            <!-- Export Dropdown Button -->
                                            <div class="btn-group mx-2">

                                                <a href="#!" class="btn btn-primary btn-sm  dropdown-toggle " data-toggle="dropdown" aria-expanded="false">
                                                    <i class="fi fi-tr-file-export"></i> Export
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
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
                                    </div>
                                    <table id="view_notification" class="table table-bordered table-hover">
                                        <thead>
                                            <tr> 
                                                <th>Sr No</th>
                                                <th>Users</th>
                                                <th>Title</th>
                                                <th>Description</th>
                                                <th>Date</th>
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
    <script src="<?= base_url('/assets/page-script/notification.js') ?>"></script>
</body>

</html>