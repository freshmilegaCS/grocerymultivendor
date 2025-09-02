<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tax | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
            <!-- Main content -->

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card card-<?php echo $settings['primary_color']; ?>">
                                <div class="card-header">
                                    <h3 class="card-title">Edit Tax</h3>
                                </div>
                                <!-- /.card-header -->
                                <form method="post" enctype="multipart/form-data">
                                    <input type="hidden" id="taxid" name="taxid" value="<?= $tax['id']?>">
                                    <div class="card-body">

                                        <div class="form-group">
                                            <label for="tax">Tax Title</label>
                                            <input type="text" class="form-control " name="tax" id="tax" placeholder="Enter Tax Title"value="<?= $tax['tax']?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="percentage">Percentage</label>
                                            <input type="number"  class="form-control  " id="percentage" name="percentage" placeholder="Enter Percentage "value="<?= $tax['percentage']?>">
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button type="button" name="update_tax" id="update_tax" class="btn btn-primary" onclick="updateTax()">
                                            Edit Tax
                                        </button>
                                    </div>

                                </form>
                                <!-- /.card-body -->
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
    <script src="<?= base_url('/assets/page-script/taxes.js') ?>"></script>


</body>

</html>