<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Area | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
                            <h1 class="m-0">Area</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Area</li>
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
                            <div class="card card-<?php echo $settings['primary_color']; ?>">
                                <div class="card-header">
                                    <h3 class="card-title">Edit Area</h3>
                                </div>
                                <!-- /.card-header -->
                                <form class="form" action="/area/update" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="edit_id" value="<?= $area['id']?>">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Area Name</label>
                                            <input type="text" id="area_name"  value="<?= $area['name']?>" class="form-control " name="area_name" placeholder="Area Name" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Delivery Charge(Only Digit)</label>
                                            <input type="number" id="delivery_charge"  value="<?= $area['delivery_charge']?>" class="form-control  " placeholder="Delivery Charge (Only Digit)" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*?)\..*/g, '$1');" name="delivery_charge" required min="0">
                                        </div>

                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="is_active" class="form-control ">
                                                <option>Select Status</option>
                                                <option value="1" <?php echo $area['is_active'] == 1 ? "selected" : "" ?>>Publish</option>
                                                <option value="0" <?php echo $area['is_active'] == 0 ? "selected" : "" ?>>Unpublish</option>
                                            </select>
                                        </div>

                                    </div>
                                    <div class="card-footer">
                                        <button type="submit" name="submit_area" class="btn btn-primary">
                                            Edit Area
                                        </button>
                                    </div>
                                </form>
                                <!-- /.card-body -->
                            </div>
                        </div>
                        <!-- ./col -->
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
    <script src="<?= base_url('/assets/page-script/area.js') ?>"></script>
    <?php if (session()->getFlashdata('success')): ?>
        <script type="text/javascript">
            $(document).ready(function() {
                toastr.success('<?= session()->getFlashdata('success'); ?>');
                
            });
        </script>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <script type="text/javascript">
            $(document).ready(function() {
                toastr.error('<?= session()->getFlashdata('error'); ?>');
            });
        </script>
    <?php endif; ?>
</body>

</html>