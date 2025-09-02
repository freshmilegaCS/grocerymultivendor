<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AI Insight Report | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
                            <h1 class="m-0">AI Insight Report</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">AI Insight Report</li>
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
                        <div class="col-md-5">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Refresh AI Insight Report</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <form id="refresh-report" class="row">
                                        <div class="form-group col-md-6">
                                            <label for="">Selling Report Start Date</label>
                                            <input type="date" name="from_date" id="from_date" class="form-control">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="">Selling Report End Date</label>
                                            <input type="date" name="to_date" id="to_date" class="form-control">
                                        </div>
                                        <div class="form-group col-md-12">

                                            <button type="submit" id="refresh-report-btn" class="btn btn-primary">Refresh Report</button>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.card-body -->
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">View AI Insight Report For <span class="report-date"></span></h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class="row">
                                        <div class=" col-6">
                                            <div class="info-box bg-lightblue-light">
                                                <span class="info-box-icon bg-lightblue "><i class="fi fi-tr-member-list"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Orders</span>
                                                    <span class="info-box-number" id="totalOrders">-</span>
                                                </div>

                                            </div>
                                        </div>
                                        <!-- ./col -->
                                        <div class=" col-6">
                                            <div class="info-box bg-warning-light">
                                                <span class="info-box-icon bg-warning"><i class="fi fi-tr-rectangle-list"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Revenue</span>
                                                    <span class="info-box-number" id="totalRevenue">-</span>
                                                </div>

                                            </div>
                                        </div>
                                        <!-- ./col -->
                                        <div class=" col-6">
                                            <div class="info-box bg-maroon-light">
                                                <span class="info-box-icon bg-maroon"><i class="fi fi-tr-rectangle-list"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Discounts</span>
                                                    <span class="info-box-number" id="totalDiscounts">-</span>
                                                </div>

                                            </div>
                                        </div>
                                        <!-- ./col -->
                                        <div class=" col-6">
                                            <div class="info-box bg-orange-light">
                                                <span class="info-box-icon bg-orange"><i class="fi fi-tr-box-open"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Refunds</span>
                                                    <span class="info-box-number" id="totalRefunds"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.card-body -->
                            </div>
                        </div>
                        <div class="col-md-7">
                            <!-- Top Selling Products -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">🔥 Top Selling Products For <span class="report-date"></span></h3>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-end align-items-center mb-3">
                                        <div class="mr-2">
                                            <select id="custom-length-change" class=" form-control form-control-sm primary-bprder">
                                                <option value="6">6</option>
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
                                    <table class="table table-bordered " id="top_selling_product_table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Total Sold</th>
                                            </tr>
                                        </thead>
                                        <tbody id="topProducts">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">💡 AI Business Insights For <span class="report-date"></span></h3>
                                </div>
                                <div class="card-body">
                                    <p id="aiInsights">Loading insights...</p>
                                </div>
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
    <script src="<?= base_url('/assets/page-script/ai_insight_report.js') ?>"></script>

</body>

</html>