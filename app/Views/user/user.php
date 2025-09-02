<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User List | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
                            <h1 class="m-0">User List</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">User List</li>
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
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">View Users</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <table id="view_user" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Sr No</th>
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>registration Date</th>
                                                <th>Status</th>
                                                <th>Ref Code</th>
                                                <th>Wallet Amt</th>
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
            <div id="addWalletModal" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg d_data">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Wallet Fund Or Return fund to <span id="wallet_user_name" style="text-transform: capitalize;" class="text-primary"></span> </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span id="" aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="user_id" id="user_id">
                            <label for="walletAmount">Amount <span class="text-danger">*</span></label>
                            <input type="text" name="walletAmount" id="walletAmount" class="form-control" placeholder="Enter Amount" />

                            <label for="flag" class="mt-3">Flag <span class="text-danger">*</span></label>
                            <select class="form-control" id="flag" name="flag">
                                <option value="">Select</option>
                                <option value="top_up">Top Up</option>
                                <option value="fund_return">Fund Return</option>
                                <option value="debit">Debit (Deduct amount from wallet)</option>
                            </select>

                            <label for="remark" class="mt-3">remark <span class="text-danger">*</span></label>
                            <input type="text" name="remark" id="remark" class="form-control" placeholder="Enter Remark" />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="addWalletModal()">Add Fund</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal" id="walletHistoryModel" aria-modal="true" role="dialog">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Wallet History</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <table id="user_wallet_list" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Amount</th>
                                                <th>Flag</th>
                                                <th>Remark</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default btn-xs" data-dismiss="modal">Ok</button>
                        </div>
                    </div>
                </div>
            </div>

        </div> 

        <!-- /.content-wrapper -->
        <?= $this->include('template/footer') ?>

        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->

    <?= $this->include('template/script') ?>
    <script src="<?= base_url('/assets/page-script/users.js') ?>"></script>

</body>

</html>