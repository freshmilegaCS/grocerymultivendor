<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

    <?= $this->include('sellerPanel/template/style') ?>

</head>

<body class="sidebar-mini accent control-sidebar-slide-open text-sm  layout-fixed <?php echo  $settings['thememode'] == 'Light' ? '' : 'dark-mode'; ?> layout-navbar-fixed text-sm" id="body">
    <div class="wrapper">


        <?= $this->include('sellerPanel/template/header') ?>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <?= $this->include('sellerPanel/template/sidebar') ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">


            <!-- Main content -->

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3 col-6">
                                    <div class="info-box bg-lightblue-light">
                                        <span class="info-box-icon bg-lightblue "><i class="fi fi-tr-member-list"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total User</span>
                                            <span class="info-box-number"><?= $totalUsers ?></span>
                                        </div>

                                    </div>
                                </div>
                                <!-- ./col -->
                                <div class="col-md-3 col-6">
                                    <div class="info-box bg-warning-light">
                                        <span class="info-box-icon bg-warning"><i class="fi fi-tr-rectangle-list"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Category</span>
                                            <span class="info-box-number"><?= $totalCategories ?></span>
                                        </div>

                                    </div>
                                </div>
                                <!-- ./col -->
                                <div class="col-md-3 col-6">
                                    <div class="info-box bg-maroon-light">
                                        <span class="info-box-icon bg-maroon"><i class="fi fi-tr-rectangle-list"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Subcategory</span>
                                            <span class="info-box-number"><?= $totalSubcategories ?></span>
                                        </div>

                                    </div>
                                </div>
                                <!-- ./col -->
                                <div class="col-md-3 col-6">
                                    <div class="info-box bg-orange-light">
                                        <span class="info-box-icon bg-orange"><i class="fi fi-tr-box-open"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Product</span>
                                            <span class="info-box-number"><?= $totalProducts ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="info-box bg-navy-light">
                                        <span class="info-box-icon bg-navy"><i class="fi fi-tr-shipping-fast"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Orders</span>
                                            <span class="info-box-number"><?= $totalOrders ?></span>
                                        </div>

                                    </div>
                                </div>
                                <!-- ./col -->
                                <div class="col-md-3 col-6">
                                    <div class="info-box bg-olive-light">
                                        <span class="info-box-icon bg-olive"><i class="fi fi-tr-dolly-flatbed-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Completed Orders</span>
                                            <span class="info-box-number"><?= $deliveredOrders ?></span>
                                        </div>

                                    </div>
                                </div>
                                <!-- ./col -->
                                <div class="col-md-3 col-6">
                                    <div class="info-box bg-purple-light">
                                        <span class="info-box-icon bg-purple"><i class="fi fi-tr-shipping-fast"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Pending Orders</span>
                                            <span class="info-box-number"><?= $pendingOrders ?></span>
                                        </div>

                                    </div>
                                </div>
                                <!-- ./col -->
                                <div class="col-md-3 col-6">
                                    <div class="info-box bg-danger-light">
                                        <span class="info-box-icon bg-danger"><i class="fi fi-tr-cart-arrow-down"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Cancelled Orders</span>
                                            <span class="info-box-number"><?= $shippedOrders ?></span>
                                        </div>

                                    </div>
                                </div>
                                <!-- ./col -->

                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Order - <?= date('M Y') ?></h3>
                                        </div>
                                        <div class="card-body" id="chart">

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Order - <?= date('Y') ?></h3>
                                        </div>
                                        <div class="card-body" id="chart1">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6 col-6">
                                    <a href="/seller/stock-management?stock=0">
                                        <div class="info-box bg-maroon-light">
                                            <span class="info-box-icon bg-maroon "><i class="fi fi-tr-box-open-full"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Product Sold Out</span>
                                                <span class="info-box-number"><?= $outOfStockCount ?></span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <!-- ./col -->
                                <div class="col-md-6 col-6">
                                    <a href="/seller/stock-management?stock=2">
                                        <div class="info-box bg-warning-light">
                                            <span class="info-box-icon bg-warning"><i class="fi fi-tr-boxes"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Product low on Stock</span>
                                                <span class="info-box-number"><?= $lowStockCount ?></span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card card-<?= $settings['primary_color'] ?>">
                                        <div class="card-header">
                                            <h3 class="card-title">View New Orders</h3>
                                        </div>
                                        <div class="card-body">
                                            <table id="view_order" class="table table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <?php if ($sellerInfo['view_customer_details'] == 1) { ?>
                                                            <th>Customer Details</th>
                                                        <?php } ?>
                                                        <th>O. Date</th>
                                                        <th>Status</th>
                                                        <th>Amount</th>
                                                        <th>Action</th>
                                                    </tr>
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
                    </div>
                </div>
            </section>
            <!-- /.content -->

        </div>

        <!-- /.content-wrapper -->
        <?= $this->include('sellerPanel/template/footer') ?>

        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->

    <?= $this->include('sellerPanel/template/script') ?>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        function monthWiseOrder() {
            var categories = <?php echo $categories; ?>;
            var data = <?php echo $data; ?>;
            var options = {
                chart: {
                    height: 280,
                    type: "area",
                    toolbar: {
                        show: true,
                        tools: {
                            download: false // <== line to add
                        }
                    }

                },
                dataLabels: {
                    enabled: false
                },
                series: [{
                    name: "Orders",
                    data: data
                }],
                fill: {
                    colors: ['#00897B']
                },

                xaxis: {
                    categories: categories
                },
            };

            var chart = new ApexCharts(document.querySelector("#chart"), options);

            chart.render();

        }

        function yearWiseOrder() {
            var categories = <?php echo $categoriesMonthWise; ?>;
            var data = <?php echo $dataMonthWise; ?>;
            var options = {
                chart: {
                    height: 280,
                    type: "area",
                    toolbar: {
                        show: true,
                        tools: {
                            download: false // <== line to add
                        }
                    }

                },
                dataLabels: {
                    enabled: false
                },
                series: [{
                    name: "Orders",
                    data: data
                }],
                fill: {
                    colors: ['#00897B']
                },

                xaxis: {
                    categories: categories
                },
            };

            var chart = new ApexCharts(document.querySelector("#chart1"), options);

            chart.render();

        }
        $(document).ready(function() {
            monthWiseOrder()
            yearWiseOrder()
        });
        $('#view_order').dataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": true,
            "responsive": true,
            ajax: {
                url: "/seller/orders/list/20",
                type: "POST",
                dataType: "json",
                dataSrc: "data",
            },
        });
    </script>
</body>

</html>