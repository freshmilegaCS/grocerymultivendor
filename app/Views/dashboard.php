<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

    <?= $this->include('template/style') ?>

</head>

<body class="sidebar-mini accent control-sidebar-slide-open text-sm  layout-fixed <?php echo  $settings['thememode'] == 'Light' ? '' : 'dark-mode'; ?> layout-navbar-fixed text-sm" id="body">
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
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6 col-6">
                                    <div class="info-box bg-lightblue-light">
                                        <span class="info-box-icon bg-lightblue "><i class="fi fi-tr-member-list"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total User</span>
                                            <span class="info-box-number"><?= $totalUsers ?></span>
                                        </div>

                                    </div>
                                </div>
                                <!-- ./col -->
                                <div class="col-md-6 col-6">
                                    <div class="info-box bg-warning-light">
                                        <span class="info-box-icon bg-warning"><i class="fi fi-tr-rectangle-list"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Category</span>
                                            <span class="info-box-number"><?= $totalCategories ?></span>
                                        </div>

                                    </div>
                                </div>
                                <!-- ./col -->
                                <div class="col-md-6 col-6">
                                    <div class="info-box bg-maroon-light">
                                        <span class="info-box-icon bg-maroon"><i class="fi fi-tr-rectangle-list"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Subcategory</span>
                                            <span class="info-box-number"><?= $totalSubcategories ?></span>
                                        </div>

                                    </div>
                                </div>
                                <!-- ./col -->
                                <div class="col-md-6 col-6">
                                    <div class="info-box bg-orange-light">
                                        <span class="info-box-icon bg-orange"><i class="fi fi-tr-box-open"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Product</span>
                                            <span class="info-box-number"><?= $totalProducts ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-6">
                                    <div class="info-box bg-navy-light">
                                        <span class="info-box-icon bg-navy"><i class="fi fi-tr-shipping-fast"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Orders</span>
                                            <span class="info-box-number"><?= $totalOrders ?></span>
                                        </div>

                                    </div>
                                </div>
                                <!-- ./col -->
                                <div class="col-md-6 col-6">
                                    <div class="info-box bg-olive-light">
                                        <span class="info-box-icon bg-olive"><i class="fi fi-tr-dolly-flatbed-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Completed Orders</span>
                                            <span class="info-box-number"><?= $deliveredOrders ?></span>
                                        </div>

                                    </div>
                                </div>
                                <!-- ./col -->
                                <div class="col-md-6 col-6">
                                    <div class="info-box bg-purple-light">
                                        <span class="info-box-icon bg-purple"><i class="fi fi-tr-shipping-fast"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Pending Orders</span>
                                            <span class="info-box-number"><?= $pendingOrders ?></span>
                                        </div>

                                    </div>
                                </div>
                                <!-- ./col -->
                                <div class="col-md-6 col-6">
                                    <div class="info-box bg-danger-light">
                                        <span class="info-box-icon bg-danger"><i class="fi fi-tr-cart-arrow-down"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Cancelled Orders</span>
                                            <span class="info-box-number"><?= $shippedOrders ?></span>
                                        </div>

                                    </div>
                                </div>
                                <!-- ./col -->
                                <div class="col-md-6 col-6">
                                    <a href="/admin/stock-management?stock=0">
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
                                    <a href="/admin/stock-management?stock=2">
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
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Total Sales Today</h3>
                                        </div>
                                        <div class="card-body">
                                            <h2><?= $country['currency_symbol'] ?><?= number_format($total_sales_today, 2) ?></h2>
                                            <p class="sales-increase">
                                                <?php if ($isIncrease): ?>
                                                    <span style="color: #2ecc71;">▲</span>
                                                <?php else: ?>
                                                    <span style="color: #e74c3c;">▼</span>
                                                <?php endif; ?>
                                                <?= $country['currency_symbol'] ?><?= number_format($sales_difference, 2) ?> (<?= $sales_percentage ?>%) vs same day last week
                                            </p>
                                            <div id="salesChart">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Sales by Location</h3>
                                        </div>
                                        <div class="card-body">
                                            <table class="table ">
                                                <tbody>
                                                    <?php foreach ($salesByLocation as $location) : ?>
                                                        <tr>
                                                            <td><?= esc($location['city_name']) ?: 'Unknown' ?></td>
                                                            <td class="text-right"><?= $country['currency_symbol'] ?> <?= number_format($location['total_sales'] / 1000, 1) ?>K</td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Avg. Completed Order Value</h3>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="avgOrderValueGauge"></canvas>
                                            <h3 class="text-center mt-2"><?= $country['currency_symbol'] ?> <?= $averageOrderValue ?></h3>
                                        </div>
                                    </div>
                                </div>

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
                                <div class="col-md-6">
                                    <div class="card card-<?= $settings['primary_color'] ?>">
                                        <div class="card-header">
                                            <h3 class="card-title">View New Orders</h3>
                                        </div>
                                        <div class="card-body">
                                            <table id="view_order" class="table table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>User Details</th>
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
                                <div class="col-md-6">
                                    <div class="card card-<?= $settings['primary_color'] ?>">
                                        <div class="card-header">
                                            <h3 class="card-title">View Top Seller</h3>
                                        </div>
                                        <!-- /.card-header -->
                                        <div class="card-body">
                                            <table id="view_seller" class="table table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Seller Name</th>
                                                        <th>Store Name</th>
                                                        <th>Total Revenue</th>
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
        <?= $this->include('template/footer') ?>

        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->

    <?= $this->include('template/script') ?>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gaugeJS/dist/gauge.min.js"></script>
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
                    colors: ['#71419f']
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
                    colors: ['#71419f']
                },

                xaxis: {
                    categories: categories
                },
            };

            var chart = new ApexCharts(document.querySelector("#chart1"), options);

            chart.render();

        }

        function salesChart() {
            var options = {
                chart: {
                    type: "line",
                    height: 250,
                    toolbar: {
                        show: false,
                    },
                },
                series: [{
                        name: "This Month",
                        data: <?= $totalsThisMonth; ?>
                    },
                    {
                        name: "Last Month",
                        data: <?= $totalsLastMonth; ?>
                    }
                ],
                xaxis: {
                    categories: <?= $weeks ?>,
                    labels: {
                        show: false // Hides X-axis labels
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: "#000"
                        }
                    }
                },
                colors: ["#008FFB", "#FFC107"],
            };

            var chart = new ApexCharts(document.querySelector("#salesChart"), options);
            chart.render();
        }

        function avgOrderValueGauge() {
            var opts = {
                angle: 0, // angle of the gauge
                lineWidth: 0.2, // thickness of the gauge's lines
                radiusScale: 1, // scale of the radius (size of the gauge)
                pointer: {
                    length: 0.6, // length of the pointer
                    strokeWidth: 0.05, // thickness of the pointer
                    color: '#000', // color of the pointer
                    cap: 'round' // rounded pointer end
                },
                limitMax: false, // no limit on the max value
                limitMin: false, // no limit on the min value
                colorStart: "#ff8c00", // starting color for the gradient
                colorStop: "#ff8c00", // ending color for the gradient
                strokeColor: "#000", // color of the stroke
                generateGradient: true, // enable gradient
                highDpiSupport: true, // better support for high DPI screens
                staticLabels: {
                    font: "14px Arial", // larger font size for labels
                    labels: [0, <?= $averageOrderValue / 1.5 ?>],
                    color: "#000", // white color for labels
                    fractionDigits: 0
                },
                staticZones: [{
                        strokeStyle: "#FFC107", // red zone
                        min: 0,
                        max: <?= $averageOrderValue / 3 ?> // half of the average value
                    },
                    {
                        strokeStyle: "#008FFB", // blue zone
                        min: <?= $averageOrderValue / 3 ?>,
                        max: <?= $averageOrderValue ?> // average value
                    },
                    {
                        strokeStyle: "#00897B", // green zone
                        min: <?= $averageOrderValue ?>,
                        max: <?= $averageOrderValue * 1.5 ?> // average value + 150
                    }
                ],
                animationSpeed: 35, // faster animation for a smoother look
                renderTicks: true // renders ticks on the gauge
            };

            var target = document.getElementById('avgOrderValueGauge');
            var gauge = new Gauge(target).setOptions(opts);

            // Set max and min values
            gauge.maxValue = <?= $averageOrderValue * 1.5 ?>;
            gauge.setMinValue(0);
            gauge.set(<?= $averageOrderValue ?>);

            // Add a subtle glow effect to the pointer (using CSS)
            target.querySelector('.gauge-pointer').style.boxShadow = "0 0 15px 5px rgba(0, 255, 0, 0.7)";
        }
        $(document).ready(function() {
            monthWiseOrder()
            yearWiseOrder()
            salesChart()
            avgOrderValueGauge()
        });

    </script>
    <script src="<?= base_url()."assets/page-script/dashboard.js"?>"></script>  

</body>

</html>