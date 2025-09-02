<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delivery Boy Cash Collection List | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

    <?= $this->include('template/style') ?>
    <link rel="stylesheet" href="<?= base_url('/assets/plugins/daterangepicker/daterangepicker.css') ?>">

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
                                    <h3 class="card-title">Delivery Boy Cash Collection List </h3><span style="float: right;"><button type="button" class="btn btn-default" data-toggle="modal" data-target="#modal-default" fdprocessedid="vegodg"><i class="fi fi-bs-plus"></i> Add Cash Collection</button></span>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class=" d-flex justify-content-between">
                                        <div class="d-flex  ">
                                            <form action="">
                                                <div class="mr-2">
                                                    <label>From - To Date </label>

                                                    <div class="input-group primary-bprder">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">
                                                                <i class="fi fi-tr-calendar-days"></i>
                                                            </span>
                                                        </div>
                                                        <input type="text" class="form-control  primary-bprder  filter-product" id="txn_date">
                                                        <div class="input-group-append">
                                                            <button type="reset" class=" btn btn-dark">
                                                                Clear
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            <div class="mx-2">
                                                <label for="">Filter by Delivery Boy</label>
                                                <select id="delivery_boy" class=" form-control  primary-bprder filter-product">
                                                    <option value="">All Delivery Boy</option>
                                                    <?php foreach ($deliveryBoys as $delivery_boy):
                                                    ?>
                                                        <option value="<?= esc($delivery_boy['id']);
                                                                        ?>"><?= esc($delivery_boy['name']);
                                                                        ?></option>
                                                    <?php endforeach;
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="mx-2">
                                                <label for="">Filter by Method</label>
                                                <select id="method" class=" form-control primary-bprder filter-product">
                                                    <option value="">All</option>
                                                    <option value="credit">Credit</option>
                                                    <option value="debit">Debit</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center ">
                                            <div class="mx-2">
                                                <label for="">Per Page</label>

                                                <select id="custom-length-change" class=" form-control primary-bprder">
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

                                                <input type="search" id="custom-search" class="form-control primary-bprder" placeholder="Search:" aria-controls="example">
                                            </div>
                                        </div>
                                    </div>

                                    <table id="view_cash_collection" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Name</th>
                                                <th>O.&nbsp;Id</th>
                                                <th>Total</th>
                                                <th>Amount</th>
                                                <th>Remark</th>
                                                <th>Date Time</th>
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
            <div class="modal fade" id="modal-default" style="display: none;" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Cash Collection Modal</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <form id="cashCollectionForm">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group"><label>Delivery Boy</label>
                                            <select class="form-control " name="delivery_boy_id" id="delivery_boy_id">
                                                <option>Select Delivery Boy</option>
                                                <?php foreach ($deliveryBoys as $deliveryBoy) { ?>
                                                    <option value="<?php echo $deliveryBoy['id'] ?>"><?php echo $deliveryBoy['name'] ?> (<?= $country['currency_symbol'] . " " . $deliveryBoy['cash_collection_amount'] ?>)</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="amount">Transfer Amount</label>
                                            <input type="number" name="amount" id="amount" required="required" placeholder="Enter Transfer Amount" step="0.01" class="form-control">
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

        </div>

        <!-- /.content-wrapper -->
        <?= $this->include('template/footer') ?>

        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->

    <?= $this->include('template/script') ?>
    <script src="<?= base_url('/assets/plugins/moment/moment.min.js') ?>"></script>
    <script src="<?= base_url('/assets/plugins/inputmask/jquery.inputmask.min.js') ?>"></script>

    <script src="<?= base_url('/assets/plugins/daterangepicker/daterangepicker.js') ?>"></script>
    <script src="<?= base_url('/assets/page-script/delivery_boy_cash_collection.js') ?>"></script>



</body>

</html>