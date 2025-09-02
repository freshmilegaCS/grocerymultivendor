<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deliverable Area | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
                            <h1 class="m-0">Deliverable Area </h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Deliverable Area </li>
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

                            <div class="card card-<?= $settings['primary_color'] ?>">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        Deliverable Area for City

                                    </h3>
                                </div>
                                <div class="card-body">
                                    <form id="myForm">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="hidden" name="city_outlines" id="city_outlines" value="">
                                                <input type="hidden" name="city_outlines_web" id="city_outlines_web" value="">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label for="city_id" class="control-label col-md-12">Select City <span class='text-danger text-xs'>*</span></label>

                                                        <select class="target form-control" name="city" id="city_id">

                                                            <option value="">Select City</option>
                                                            <?php foreach ($city as $key => $val) { ?>
                                                                <option value="<?php echo $val['id'] ?>" data-coordinate="<?= $val['latitude'] ?>, <?= $val['longitude'] ?>"><?php echo $val['name'] ?></option>
                                                            <?php } ?>
                                                        </select>
                                                        <small class="text text-primary text-sm">Search your city in which you will provide the service and city points.</small>
                                                    </div>
                                                    <div class="col-md-12 position-relative form-group">
                                                        <label for="deliverable_area">Enter Area Name <span class='text-danger text-xs'>*</span></label>
                                                        <input type="text" class="form-control" name="deliverable_area" id="deliverable_area" placeholder="Enter Area Name">
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label for="time_to_travel">Time to travel 1 (km) <span class="text-danger text-sm">*</span>
                                                            <small>(Enter in minutes)</small>
                                                        </label>
                                                        <input type="number" name="time_to_travel" id="time_to_travel"
                                                            min="0" max="999999999" placeholder="Enter Time to travel 1 (km)." required="required" class="form-control"
                                                            fdprocessedid="17j6xc" autocomplete="off">
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label for="base_delivery_time">Base Delivery Time <span class="text-danger text-sm">*</span>
                                                            <small>(will be added in Time to Travel, using these quickcommerce time will be calculated in app)</small>
                                                        </label>
                                                        <input type="number" name="base_delivery_time" id="base_delivery_time"
                                                            min="0" max="999999999" placeholder="Enter Base Delivery Time." required="required" class="form-control"
                                                            fdprocessedid="17j6xc" autocomplete="off">
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label for="min_amount_for_free_delivery">Minimum Amount for Free Delivery<span
                                                                class="text-danger text-xs">*</span>
                                                        </label>
                                                        <input type="number"
                                                            name="min_amount_for_free_delivery" id="min_amount_for_free_delivery"
                                                            placeholder="Enter Delivarable Maximum Distance in km" min="0" max="999999999" required="required"
                                                            class="form-control" fdprocessedid="yw6589">
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label for="delivery_charge_method" class=" col-12 col-form-label">Delivery Charge Methods
                                                            <span class="text-danger text-sm">*</span>
                                                        </label>
                                                        <select name="delivery_charge_method"
                                                            id="delivery_charge_method" required="required" class="form-control form-select" fdprocessedid="t2g25n">
                                                            <option value="">Select Method</option>
                                                            <option value="fixed_charge">Fixed Delivery Charges</option>
                                                            <option value="per_km_charge">Per KM Delivery Charges</option>
                                                            <option value="range_wise_charges">Range Wise Delivery Charges</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-12" id="method_id">

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="latitudesandlongitudes" class="control-label col-md-12">Boundry Points <span class='text-danger text-xs'>*</span></label>
                                                    <small class="text-danger ">Please edit Map or City Deliverable Area in desktop. It may not work in mobile device.</small>

                                                    <textarea class="form-control d-none" placeholder="here will be your selected outlines latitude and longitude" name="vertices" id="vertices" cols="30" rows="10"></textarea>
                                                </div>
                                                <div class="">
                                                    <input id="remove-line" type="button" class="btn btn-primary mb-3 btn-xs" value="Remove Newly Added Line" />
                                                    <input id="clear-line" type="button" class="btn btn-danger mb-3 btn-xs" value="Clear Map" />
                                                    <!-- <input id="add-line" type="button" class="btn btn-success mb-3 btn-xs" value="Restore Old Map" /> -->
                                                </div>
                                                <!-- <div class="form-group mt-5"> -->
                                                <!-- </div> -->
                                                <div class="map-canvas" id="map-canvas" style="height: 450px"></div>
                                            </div>
                                        </div> 
                                    </form>
                                </div>
                                <div class="card-footer">
                                    <button type="button" class="btn btn-info mt-3" id="add_deliverable_area" name="add_deliverable_area">Save Boundries</button>

                                </div>
                            </div>
                            <!--/.card-->
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
    <script src="<?= base_url('/assets/page-script/deliverableArea.js') ?>"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= $settings['map_api_key'] ?>&libraries=drawing&v=weekly" defer></script>

</body>

</html>