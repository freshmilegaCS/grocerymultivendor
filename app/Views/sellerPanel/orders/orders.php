<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orders List | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

    <?= $this->include('sellerPanel/template/style') ?>
    <link rel="stylesheet" href="<?= base_url('/assets/plugins/daterangepicker/daterangepicker.css') ?>">

</head>

<body class="sidebar-mini control-sidebar-slide-open text-sm  layout-fixed <?php echo  $settings['thememode'] == 'Light' ? '' : 'dark-mode'; ?> layout-navbar-fixed text-sm" id="body">
    <div class="wrapper">


        <?= $this->include('sellerPanel/template/header') ?>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <?= $this->include('sellerPanel/template/sidebar') ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Orders List</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Orders List</li>
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
                        <!-- ./col -->
                        <div class="col-md-12">
                            <div class="card card-<?= $settings['primary_color'] ?>">
                                <div class="card-header">
                                    <h3 class="card-title">View Order List</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class=" d-flex justify-content-between">
                                        <div class="d-flex  ">
                                            <form action="">
                                                <div class="mr-2">
                                                    <label>From - To Order Date </label>

                                                    <div class="input-group primary-bprder">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">
                                                                <i class="fi fi-tr-calendar-days"></i>
                                                            </span>
                                                        </div>
                                                        <input type="text" class="form-control  primary-bprder  filter-product" id="order_date">
                                                        <div class="input-group-append">
                                                            <button type="reset" class=" btn btn-dark">
                                                                Clear
                                                            </button>
                                                        </div>
                                                    </div>
                                            </form>
                                        </div>
                                        <div class="mx-2">
                                            <label for="">Status</label>
                                            <select id="status" class=" form-control  primary-bprder filter-product">
                                                <option value="" <?php echo isset($_GET['status']) ? 'selected ' : '' ?>>All Status</option>
                                                <?php foreach ($orderStatusLists as $orderStatusList):
                                                ?>
                                                    <option value="<?= esc($orderStatusList['id']);
                                                                    ?>" <?php echo  isset($_GET['status']) && $_GET['status'] == $orderStatusList['id'] ? 'selected' : '' ?>><?= esc($orderStatusList['status']);
                                                                                                                                                                                ?></option>
                                                <?php endforeach;
                                                ?>
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
                                </div>
                                <table id="view_order" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>O. Id</th>
                                            <?php if ($sellerInfo['view_customer_details'] == 1) { ?>
                                                <th>Customer Details</th>
                                                <th>Address</th>
                                            <?php } ?>

                                            <th>D. Date</th>
                                            <th>O. Date</th>
                                            <th>Status</th>
                                            <!--<th>Delvery Boy Assign Status</th>-->
                                            <th>Amount</th>
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


    </div>

    <!-- /.content-wrapper -->
    <?= $this->include('sellerPanel/template/footer') ?>

    <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->

    <?= $this->include('sellerPanel/template/script') ?>
    <script src="<?= base_url('/assets/page-script/sellerPanel/orders.js') ?>"></script>
    <script src="<?= base_url('/assets/plugins/moment/moment.min.js') ?>"></script>
    <script src="<?= base_url('/assets/plugins/inputmask/jquery.inputmask.min.js') ?>"></script>

    <script src="<?= base_url('/assets/plugins/daterangepicker/daterangepicker.js') ?>"></script>


</body>

</html>