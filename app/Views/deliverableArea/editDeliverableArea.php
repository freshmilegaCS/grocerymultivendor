<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Deliverable Area | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
                            <h1 class="m-0">Edit Deliverable Area</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Edit Deliverable Area</li>
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
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="hidden" name="edit_id" id="edit_id" value="<?= $deliverable_area['id'] ?>">
                                            <input type="hidden" name="city_outlines" id="city_outlines" value='<?= $deliverable_area['boundry_points'] ?>'>
                                            <input type="hidden" name="city_outlines_web" id="city_outlines_web" value='<?= $deliverable_area['boundary_points_web'] ?>'>
                                            <input type="hidden" name="geolocation_type" id="geolocation_type" value="<?= $deliverable_area['geolocation_type'] ?>">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label for="city_id" class="control-label col-md-12">Select City <span class='text-danger text-xs'>*</span></label>

                                                    <select class="target form-control" name="city" id="city_id">

                                                        <option value=" ">Select City</option>
                                                        <?php foreach ($city as $key => $val) { ?>
                                                            <option value="<?php echo $val['id'] ?>" data-coordinate="<?= $val['latitude'] ?>, <?= $val['longitude'] ?>" <?php echo $deliverable_area['city_id'] == $val['id'] ? 'selected' : '' ?> data-city_id="<?php echo $deliverable_area['id'] ?>" data-geolocation_type="<?php echo $deliverable_area['geolocation_type'] ?>" data-boundary_points='<?php echo $deliverable_area['boundary_points_web'] ?>' data-radius="<?php echo $deliverable_area['radius'] ?>"><?php echo $val['name'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                    <small class="text text-primary text-sm">Search your city in which you will provide the service and city points.</small>
                                                </div>
                                                <div class="col-md-12 position-relative form-group">
                                                    <label for="deliverable_area">Enter Area Name <span class='text-danger text-xs'>*</span></label>
                                                    <input type="text" class="form-control" name="deliverable_area" id="deliverable_area" placeholder="Enter Area Name" value="<?php echo $deliverable_area['deliverable_area_title'] ?>">
                                                </div>
                                                <div class="form-group col-md-12">
                                                    <label for="time_to_travel">Time to travel 1 (km) <span class="text-danger text-sm">*</span>
                                                        <small>(Enter in minutes)</small>
                                                    </label>
                                                    <input type="number" name="time_to_travel" id="time_to_travel"
                                                        min="0" max="999999999" placeholder="Enter Time to travel 1 (km)." required="required" class="form-control"
                                                        fdprocessedid="17j6xc" autocomplete="off" value="<?php echo $deliverable_area['time_to_travel'] ?>">
                                                </div>
                                                <div class="form-group col-md-12">
                                                        <label for="base_delivery_time">Base Delivery Time <span class="text-danger text-sm">*</span>
                                                            <small>(will be added in Time to Travel, using these quickcommerce time will be calculated in app)</small>
                                                        </label>
                                                        <input type="number" name="base_delivery_time" id="base_delivery_time"
                                                            min="0" max="999999999" placeholder="Enter Base Delivery Time." required="required" class="form-control"
                                                            fdprocessedid="17j6xc" autocomplete="off" value="<?php echo $deliverable_area['base_delivery_time'] ?>">
                                                    </div>
                                                <div class="form-group col-md-12">
                                                    <label for="min_amount_for_free_delivery">Minimum Amount for Free Delivery<span
                                                            class="text-danger text-xs">*</span>
                                                    </label>
                                                    <input type="number"
                                                        name="min_amount_for_free_delivery" id="min_amount_for_free_delivery"
                                                        placeholder="Enter Delivarable Maximum Distance in km" min="0" max="999999999" required="required"
                                                        class="form-control" fdprocessedid="yw6589" value="<?php echo $deliverable_area['min_amount_for_free_delivery'] ?>">
                                                </div>
                                                <div class="form-group col-md-12">
                                                    <label for="delivery_charge_method" class=" col-12 col-form-label">Delivery Charge Methods
                                                        <span class="text-danger text-sm">*</span>
                                                    </label>
                                                    <select name="delivery_charge_method"
                                                        id="delivery_charge_method" required="required" class="form-control form-select" fdprocessedid="t2g25n">
                                                        <option value="">Select Method</option>
                                                        <option value="fixed_charge" <?php echo  $deliverable_area['delivery_charge_method'] == 'fixed_charge' ? 'selected' : '' ?>>Fixed Delivery Charges</option>
                                                        <option value="per_km_charge" <?php echo  $deliverable_area['delivery_charge_method'] == 'per_km_charge' ? 'selected' : '' ?>>Per KM Delivery Charges</option>
                                                        <option value="range_wise_charges" <?php echo  $deliverable_area['delivery_charge_method'] == 'range_wise_charges' ? 'selected' : '' ?>>Range Wise Delivery Charges</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-12" id="method_id">
                                                    <?php

                                                    if ($deliverable_area['delivery_charge_method'] == 'fixed_charge') { ?>
                                                        <label for="fixed_charge"> Fixed Delivery Charges<span class="text-danger text-sm">*</span></label> 
                                                        <input type="number" name="fixed_charge" id="fixed_charge" placeholder="Global Flat Charges" min="0" max="999999999" step="any" class="form-control" fdprocessedid="qer9w" autocomplete="off" value="<?= $deliverable_area['fixed_charge'] ?>">
                                                    <?php } elseif ($deliverable_area['delivery_charge_method'] == 'per_km_charge') {  ?>
                                                        <label for="fixed_charge">Per Kilometer Delivery Charge<span class="text-danger text-sm">*</span></label> 
                                                        <input type="number" name="per_km_charge" id="per_km_charge" placeholder="Per Kilometer Delivery Charge" min="0" max="999999999" class="form-control" fdprocessedid="kqih5p" autocomplete="off" value="<?= $deliverable_area['per_km_charge'] ?>">
                                                    <?php } elseif ($deliverable_area['delivery_charge_method'] == 'range_wise_charges') {  ?>
                                                        <div class="form-group col-sm-12">
                                                            <label>Range Wise Delivery Charges<span class="text-danger text-sm">* </span>
                                                                <span class="text-secondary text-sm">(Set Proper ranges for delivery charge. Do not repeat the range value to next range. For e.g. 1-3,4-6)</span>
                                                            </label>

                                                            <?php
                                                            $deliverable_area['range_wise_charges'] = json_decode($deliverable_area['range_wise_charges'], true);
                                                            foreach ($deliverable_area['range_wise_charges'] as $index => $charge): ?>
                                                                <div class="form-group row range-row">
                                                                    <div class="col-sm-1 index-label"><?= $index + 1; ?>.</div>

                                                                    <div class="col-sm-3">
                                                                        <input type="number"
                                                                            name="from_range[]"
                                                                            placeholder="From Range"
                                                                            min="0"
                                                                            max="999999999"
                                                                            class="form-control from-range"
                                                                            value="<?= esc($charge['from_range']); ?>">
                                                                    </div>

                                                                    <div class="col-sm-1 btn btn-secondary">To</div>

                                                                    <div class="col-sm-3">
                                                                        <input type="number"
                                                                            name="to_range[]"
                                                                            placeholder="To Range"
                                                                            min="0"
                                                                            max="999999999"
                                                                            class="form-control to-range"
                                                                            value="<?= esc($charge['to_range']); ?>">
                                                                    </div>
                                                                    <div class="col-sm-3">
                                                                        <input type="number"
                                                                            name="price[]"
                                                                            placeholder="Price"
                                                                            min="0"
                                                                            max="999999999"
                                                                            class="form-control price"
                                                                            value="<?= esc($charge['price']); ?>">
                                                                    </div>
                                                                    <div class="col-sm-1">
                                                                        <!-- Add Row Button -->


                                                                        <!-- Delete Button: Only visible for rows except the first one -->
                                                                        <?php if ($index > 0) { ?>
                                                                            <div class="col-sm-1"><a class="btn btn-danger remove-row" title="Remove Row" style="cursor: pointer;"><i class="fi fi-tr-trash-xmark fi-2x" style="font-size: 15px;"></i></a></div>
                                                                        <?php } else { ?>

                                                                            <a class="btn btn-primary add-row" title="Add Row" style="cursor: pointer;">
                                                                                <i class="fi fi-tr-add fi-2x" style="font-size: 15px;"></i>
                                                                            </a>
                                                                        <?php   } ?>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="latitudesandlongitudes" class="control-label col-md-12">Boundry Points<span class='text-danger text-xs'>*</span> </label>
                                                <small class="text-danger ">Please edit Map or City Deliverable Area in desktop. It may not work in mobile device.</small>

                                                <textarea class="form-control d-none" placeholder="here will be your selected outlines latitude and longitude" name="vertices" id="vertices" cols="30" rows="10"></textarea>
                                            </div>
                                            <div class="">
                                                <button onclick="removeNewPolygons()" class="btn btn-primary mb-3 btn-xs">Remove Newly Added Line</button>
                                                <button onclick="clearMap()" class="btn btn-danger mb-3 btn-xs">Clear Map</button>
                                                <button onclick="restoreOriginalMap()" class="btn btn-success mb-3 btn-xs">Restore Old Map</button>
                                                <!-- <input id="add-line" type="button" class="btn btn-success mb-3 btn-xs" value="Restore Old Map" /> -->
                                            </div>
                                            <!-- <div class="form-group mt-5"> -->
                                            <!-- </div> -->
                                            <div class="map-canvas" id="map-canvas" style="height: 450px"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="button" class="btn btn-primary mt-3" id="edit_deliverable_area" name="edit_deliverable_area">Save Boundries</button>

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

    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $settings['map_api_key'] ?>&libraries=drawing&v=weekly&callback=initMapBoundry" defer></script>
    <script src="<?= base_url('/assets/page-script/editDeliverableArea.js') ?>"></script>
    <script>
        var map;
        var originalPolygon;
        var newPolygon;
        var drawingManager;
        var drawnPolygons = [];

        function initMapBoundry() {
            // Center of the map (adjust if needed)
            var center = {
                lat: <?= $deliverable_area['latitude']  ?>,
                lng: <?= $deliverable_area['longitude'] ?>
            };

            // Initialize the map
            map = new google.maps.Map(document.getElementById('map-canvas'), {
                zoom: 12, // Adjust zoom level
                center: center,
            });

            // Define the original polygon's boundary points
            var polygonCoordinates = <?= $deliverable_area['boundary_points_web'] ?>;

            // Create and display the original polygon
            originalPolygon = new google.maps.Polygon({
                paths: polygonCoordinates,
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.35,
            });
            originalPolygon.setMap(map);

            // Initialize the drawing manager
            drawingManager = new google.maps.drawing.DrawingManager({
                drawingMode: google.maps.drawing.OverlayType.POLYGON,
                drawingControl: true,
                drawingControlOptions: {
                    position: google.maps.ControlPosition.TOP_CENTER,
                    drawingModes: [google.maps.drawing.OverlayType.POLYGON],
                },
                polygonOptions: {
                    strokeColor: '#0000FF',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#0000FF',
                    fillOpacity: 0.35,
                    editable: true,
                },
            });

            // Add the drawing manager to the map
            drawingManager.setMap(map);

            // Event listener for when a new polygon is completed
            google.maps.event.addListener(drawingManager, 'polygoncomplete', function(polygon) {
                // Remove previously drawn polygons from map
                drawnPolygons.forEach(function(p) {
                    p.setMap(null);
                });
                drawnPolygons = [];
            
                // Save the new polygon
                drawnPolygons.push(polygon);
                newPolygon = polygon;
            
                // Extract coordinates and update #vertices
                var path = polygon.getPath();
                var vertices = [];
                path.forEach(function(latlng, index) {
                    vertices.push("(" + latlng.lat() + "," + latlng.lng() + ")");
                });
            
                $("#vertices").val(vertices.join(","));
            });
        }
    </script>
</body>

</html>