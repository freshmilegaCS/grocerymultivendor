<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage City | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

    <?= $this->include('template/style') ?>

    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
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
                            <h1 class="m-0">Manage City</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Manage City</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->

            <section class="content">
                <div class="container-fluid">
                    <form class="form" id="insert_city_form" method="post" enctype="multipart/form-data" autocomplete="off">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card card-<?php echo $settings['primary_color'] ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Manage City</h3>
                                    </div>

                                    <!-- /.card-header -->

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Search City</label>
                                                    <input type="text" autocomplete="false" id="pac-input" class="form-control  " required placeholder="Search City">
                                                </div>
                                                <small class="text text-danger text-sm">Search your city where you will provide service and to find co-ordinates.</small>
                                            </div>
                                            <div class="col-md-8">
                                                <div id="map"></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Latitude</label>
                                                    <input type="text" readonly autocomplete="false" id="latitude" name="latitude" class="form-control  " required placeholder="Latitude">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Longitude</label>
                                                    <input type="text" readonly autocomplete="false" id="longitude" name="longitude" class="form-control  " required placeholder="Longitude">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>City Name</label>
                                                    <input type="text" autocomplete="false" id="city_name" name="city_name" class="form-control  " required placeholder="City Name">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button type="button" id="submit" name="submit" class="btn btn-primary" onclick="addCity()">Add City</button>
                                        <button type="reset" class="btn btn-warning">Reset</button>
                                    </div>
                                </div>
                                <!-- ./col -->
                            </div>
                        </div>
                    </form>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Staff List</h3>
                                </div>
                                <div class="card-body">
                                    <table id="city_list" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>City</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
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
    <script src="<?= base_url('/assets/page-script/manageCity.js') ?>"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $settings['map_api_key'] ?>&callback=initAutocomplete&libraries=places&v=weekly" defer></script>
</body>

</html>