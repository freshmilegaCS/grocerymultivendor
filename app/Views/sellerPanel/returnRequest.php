<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Return Request | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
                            <h1 class="m-0">Return Request</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Return Request</li>
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
                                    <h3 class="card-title">View Return Request</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class=" d-flex justify-content-between">
                                        <div class="d-flex  ">
                                            <div class="mr-2">
                                                <label>From - To Date </label>
                                                <div class="input-group primary-border">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="fi fi-tr-calendar-days"></i>
                                                        </span>
                                                    </div>
                                                    <input type="text" class="form-control  primary-border  filter-product" id="report_date">
                                                    <div class="input-group-append">
                                                        <button type="reset" class=" btn btn-dark">
                                                            Clear
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mx-2">
                                                <label for="">Filter by Status</label>
                                                <select id="status" class=" form-control  primary-border filter-product">
                                                    <option value="">All Status</option>
                                                    <option value="1">Pending</option>
                                                    <option value="2">Approved</option>
                                                    <option value="3">Rejected</option>
                                                    <option value="4">Returned To DeliveryBoy</option>
                                                    <option value="5">Returned Successfully</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center ">
                                            <div class="mx-2">
                                                <label for="">Per Page</label>

                                                <select id="custom-length-change" class=" form-control primary-border">
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                </select>
                                            </div>
                                            <!-- Export Dropdown Button -->
                                            <div class="mx-2" style="display: grid;">
                                                <label for="">Export</label>
                                                <div class="btn-group ">

                                                    <a href="#!" class="btn btn-primary   dropdown-toggle " data-toggle="dropdown" aria-expanded="false">
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
                                            </div>
                                            <div>
                                                <label for="">Search</label>
                                                <input type="search" id="custom-search" class="form-control primary-border" placeholder="Search:" aria-controls="example">
                                            </div>
                                        </div>
                                    </div>
                                    <table id="view_return_request_list" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order Item Id</th>
                                                <?php if ($sellerInfo['view_customer_details'] == 1) { ?>
                                                    <th>User</th>
                                                <?php } ?>
                                                <th>Product</th>
                                                <th>Variant</th>
                                                <th>Price</th>
                                                <th>Disc Price</th>
                                                <th>Quantity</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>

                                    </table>
                                </div>

                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="modal-default" style="display: none;" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Update Return Request</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <form id="updateRequestForm">
                                <input type="hidden" id="request_id" name="request_id">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="status">Status</label><br>
                                                <select class="form-control" id="status" name="status">
                                                    <option value="1">Pending</option>
                                                    <option value="2">Approved</option>
                                                    <option value="3">Rejected</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group"><label>Delivery Boy</label>
                                                <select class="form-control " name="delivery_boy_id" id="delivery_boy_id">
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">

                                            <div class="form-group">
                                                <label for="reason">Reason (By Customer)</label>
                                                <textarea name="reason" id="reason" rows="3" readonly placeholder="Enter Reason." class="form-control"></textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-12">

                                            <div class="form-group">
                                                <label for="remark">Remark</label>
                                                <textarea name="remark" id="remark" rows="3" placeholder="Enter Remark." class="form-control"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>

                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
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
    <script src="<?= base_url('/assets/page-script/sellerPanel/return_request.js') ?>"></script>
    <script src="<?= base_url('/assets/plugins/moment/moment.min.js') ?>"></script>
    <script src="<?= base_url('/assets/plugins/inputmask/jquery.inputmask.min.js') ?>"></script>

    <script src="<?= base_url('/assets/plugins/daterangepicker/daterangepicker.js') ?>"></script>

</body>

</html>