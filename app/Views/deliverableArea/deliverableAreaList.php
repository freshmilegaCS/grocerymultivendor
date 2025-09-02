<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deliverable Area List | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

    <?= $this->include('template/style') ?>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
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
                            <h1 class="m-0">Deliverable Area List</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Deliverable Area List</li>
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
                                    <h3 class="card-title">Deliverable Area List</h3>
                                </div>
                                <div class="card-body">
                                    <table id="deliverable_area_list" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>City</th>
                                                <th>Deliverable Area Name</th>
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
            
            <div id="addDeliveryDateModal" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg d_data">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"> Delivery Date <span id="wallet_user_name" style="text-transform: capitalize;" class="text-primary"></span> </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span id="" aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="" method="post">
                                <input type="hidden" id="deliverable_area_id" name="deliverable_area_id" >
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="delivery_date">Delivery Date <span class="text-danger">*</span></label>
                                            <input type="date" name="delivery_date" id="delivery_date" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-primary" onclick="addDeliveryDateModal()">Add Delivery Date</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="row">
                                <div class="col-md-12">
                                    <table id="delivery_date_list" class="table table-bordered table-striped w-100">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Delivery Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
        
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="addTimeslotModal" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg d_data">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"> Timeslot  <span id="wallet_user_name" style="text-transform: capitalize;" class="text-primary"></span> </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span id="" aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="" method="post">
                                <input type="hidden" id="deliverable_area_id_for_timeslot" name="deliverable_area_id_for_timeslot" >
                                <div class="row">
                                    <div class="col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>Minimum Time Slot</label>
                                                <input type="text" name="min_time" class="form-control  " id="min_time" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-6">
                                            <div class="form-group">
                                                <label>Maximum Time Slot</label>
                                                <input type="text" name="max_time" class="form-control  " id="max_time" required>
                                            </div>
                                        </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-primary" onclick="addTimeslotModal()">Add Timeslot </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="row">
                                <div class="col-md-12">
                                    <table id="timeslot_list" class="table table-bordered table-striped w-100">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Timeslot </th>
                                                <th>Status </th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
        
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
        <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
    <script src="<?= base_url('/assets/page-script/deliverableAreaList.js') ?>"></script> 
        <script>
          $("#min_time").timepicker({
            timeFormat: "h:mm p",
            interval: 60,
            minTime: "10",
            defaultTime: "11",
            startTime: "10:00",
            dynamic: false,
            dropdown: true,
            scrollbar: true,
          });
          $("#max_time").timepicker({
            timeFormat: "h:mm p",
            interval: 60,
            minTime: "10",
            defaultTime: "11",
            startTime: "10:00",
            dynamic: false,
            dropdown: true,
            scrollbar: true,
          });
    </script>
</body>

</html>