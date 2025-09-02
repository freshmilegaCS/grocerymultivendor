<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Seller | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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


            <!-- Main content -->

            <section class="content">
                <div class="container-fluid">
                    <div class="row">

                        <div class="col-md-12">
                            <form method="post" id="editSellerForm" enctype="multipart/form-data">
                                <input type="hidden" name="map_address" id="map_address" value="<?= $seller['map_address'] ?>">

                                <input type="hidden" name="seller_id" value="<?= $seller['id'] ?>">
                                <div class="card card-<?php echo $settings['primary_color'] ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Seller Info</h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name"> Seller Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control " name="name" value="<?= $seller['name'] ?>" id="name" placeholder="Enter  Seller Name">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="email">Email <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control " name="email" value="<?= $seller['email'] ?>" id="email" placeholder="Enter email">
                                                </div>
                                            </div>



                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="mobile">Mobile <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control " name="mobile" value="<?= $seller['mobile'] ?>" id="mobile" placeholder="Enter Mobile">
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                                <div class="card card-<?php echo $settings['primary_color'] ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Store Info</h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="store_name"> Store Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control " name="store_name" value="<?= $seller['store_name'] ?>" id="store_name" placeholder="Enter Store Name">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="category">Select Category <span class="text-danger">*</span></label>
                                                    <div class="select2-olive">
                                                        <select class="form-control " multiple="multiple" name="category[]" data-dropdown-css-class="select2-olive" id="category">
                                                            <?php foreach ($categories as $key => $val) { ?>
                                                                <?php
                                                                // Check if the category is in seller_categories
                                                                $isSelected = false;
                                                                foreach ($seller_categories as $seller_category) {
                                                                    if ($val['id'] == $seller_category['category_id']) {
                                                                        $isSelected = true;
                                                                        break;
                                                                    }
                                                                }
                                                                ?>
                                                                <option value="<?php echo $val['id']; ?>" <?= $isSelected ? 'selected' : ''; ?>>
                                                                    <?php echo $val['category_name']; ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="store_address"> Address </label>
                                                    <input type="text" class="form-control " name="store_address" value="<?= $seller['store_address'] ?>" id="store_address" placeholder="Enter  Address">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">




                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="pan_number">Pan Card </label>
                                                    <input type="text" class="form-control " name="pan_number" value="<?= $seller['pan_number'] ?>" id="pan_number" placeholder="Enter PAN">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="tax_name">Tax Name/ GST Name </label>
                                                    <input type="text" class="form-control " name="tax_name" value="<?= $seller['tax_name'] ?>" id="tax_name" placeholder="Enter Tax Name">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="tax_number">Tax Number/ GST Number </label>
                                                    <input type="text" class="form-control " name="tax_number" value="<?= $seller['tax_number'] ?>" id="tax_number" placeholder="Enter Tax Number">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card card-<?php echo $settings['primary_color'] ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Store Location Info</h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="city_id">City <span class="text-danger">*</span> </label>
                                                    <select class="form-control " name="city_id" id="city_id">
                                                        <option>Select City</option>
                                                        <?php foreach ($city as $key => $val) { ?>
                                                            <option value="<?php echo $val['id'] ?>" <?= $val['id'] == $seller['city_id'] ? 'selected' : '' ?>><?php echo $val['name'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="deliverable_area_id">Serviceable Area <span class="text-danger">*</span> </label>
                                                    <select class="form-control " name="deliverable_area_id" id="deliverable_area_id">
                                                        <option value="">Select Serviceable Area</option>
                                                        <?php foreach ($deliverable_area as $key => $val) { ?>
                                                            <option value="<?php echo $val['id'] ?>" <?= $val['id'] == $seller['deliverable_area_id'] ? 'selected' : '' ?>><?php echo $val['deliverable_area_title'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="pan_number">Search Location <span class="text-danger">Search only if its necessory</span></label>
                                                    <input type="text" class="form-control " id="pac-input" placeholder="Search Location">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="latitude">Latitude </label>
                                                    <input type="text" class="form-control " readonly value="<?= $seller['latitude'] ?>" name="latitude" id="latitude" placeholder="Enter Latitude">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="longitude">Longitude</label>
                                                    <input type="text" class="form-control " readonly value="<?= $seller['longitude'] ?>" name="longitude" id="longitude" placeholder="Enter Longitude">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div id="map"></div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card card-<?php echo $settings['primary_color'] ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Payment Details</h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="account_name">Account Name</label>
                                                    <input type="text" class="form-control " name="account_name" value="<?= $seller['account_name'] ?>" id="account_name" placeholder="Enter Account Name">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="bank_name">Bank Name</label>
                                                    <input type="text" class="form-control " name="bank_name" value="<?= $seller['bank_name'] ?>" id="bank_name" placeholder="Enter  Bank Name">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="branch">Branch </label>
                                                    <input type="text" class="form-control " name="branch" value="<?= $seller['branch'] ?>" id="branch" placeholder="Enter Branch">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="account_number">Account Number </label>
                                                    <input type="text" class="form-control " name="account_number" value="<?= $seller['account_number'] ?>" id="account_number" placeholder="Enter Account Number">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="bank_ifsc_code">IFSC </label>
                                                    <input type="text" class="form-control " name="bank_ifsc_code" value="<?= $seller['bank_ifsc_code'] ?>" id="bank_ifsc_code" placeholder="Enter IFSC">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="card card-<?php echo $settings['primary_color'] ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Document Section</h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="">Profile <?php if ($seller['logo'] != '') { ?>
                                                            <a class="btn btn-primary" href="<?= base_url($seller['logo']) ?>" target="_blank">View Old File</a>
                                                        <?php } ?> </label>
                                                    <input type="file" class="form-control" name="logo" id="logo" accept="image/png, image/jpg, image/jpeg">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="national_id_proof">Id Proof <?php if ($seller['national_id_proof'] != '') { ?>
                                                            <a class="btn btn-primary" href="<?= base_url($seller['national_id_proof']) ?>" target="_blank">View Old File</a>
                                                        <?php } ?></label>
                                                    <input type="file" class="form-control " name="national_id_proof" id="national_id_proof" placeholder="Enter Id Proof">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="address_proof">Address Proof <?php if ($seller['address_proof'] != '') { ?>
                                                            <a class="btn btn-primary" href="<?= base_url($seller['address_proof']) ?>" target="_blank">View Old File</a>
                                                        <?php } ?></label>
                                                    <input type="file" class="form-control" name="address_proof" id="address_proof" placeholder="Enter Address Proof">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="card card-<?php echo $settings['primary_color'] ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Other Info</h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <div class="card-body">

                                        <div class="row">

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="require_products_approval">Require Product's Approval? <span class="text-danger">*</span></label>
                                                    <select class="form-control " name="require_products_approval" id="require_products_approval">
                                                        <option value="0">No</option>
                                                        <option value="1">Yes</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="view_customer_details">View Customer's Details? <span class="text-danger">*</span></label>
                                                    <select class="form-control " name="view_customer_details" id="view_customer_details">
                                                        <option value="0">No</option>
                                                        <option value="1">Yes</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="commission">Commission % <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control " name="commission" value="<?= $seller['commission'] ?>" id="commission" placeholder="Enter Commission">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="card-footer">
                                        <input type="submit" class="btn btn-primary" name="update_franchaise" id="update_franchaise" value="Edit Seller">
                                    </div>
                                </div>
                            </form>
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
    <script src="<?= base_url('/assets/page-script/seller_edit.js') ?>"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $settings['map_api_key'] ?>&callback=initAutocomplete&libraries=places&v=weekly" defer></script>
    <script>
        let map;
        let marker;
          $("#category").select2({
            placeholder: "Select Categories",
            multiple: true,
            allowClear: true,
          });
          window.initAutocomplete = initAutocomplete;
        function initAutocomplete() {
            map = new google.maps.Map(document.getElementById("map"), {
                center: {
                    lat: <?= $seller['latitude'] ?>,
                    lng: <?= $seller['longitude'] ?>
                }, // Default center
                zoom: 13,
                mapTypeId: "roadmap",
            });
        
            // Marker Initialization
            marker = new google.maps.Marker({
                map: map,
                draggable: true, // Allows user to drag marker
                position: {
                    lat: <?= $seller['latitude'] ?>,
                    lng: <?= $seller['longitude'] ?>
                }, // Default marker position
            });
        
            // Event listener for marker drag to capture new coordinates
            google.maps.event.addListener(marker, 'dragend', function() {
                const position = marker.getPosition();
                $("#latitude").val(position.lat());
                $("#longitude").val(position.lng());
            });
        
            // Autocomplete for City Search
            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
        
            map.addListener("bounds_changed", () => {
                searchBox.setBounds(map.getBounds());
            });
        
            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();
        
                if (places.length == 0) return;
        
                const place = places[0];
                if (!place.geometry || !place.geometry.location) return;
        
                // Move map to searched location
                map.panTo(place.geometry.location);
                map.setZoom(15);
        
                // Move marker to searched location
                marker.setPosition(place.geometry.location);
        
                // Extract latitude and longitude
                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();
        
                $("#latitude").val(lat);
                $("#longitude").val(lng);
                getAddressFromLatLng(lat, lng);
                // Extract city name (if available)
                const parser = new DOMParser();
                const doc = parser.parseFromString(place.adr_address, 'text/html');
                const locality = doc.querySelector('.locality') ? doc.querySelector('.locality').textContent : place.name;
        
                $("#city_name").val(locality);
            });
        }
    </script>

</body>

</html>