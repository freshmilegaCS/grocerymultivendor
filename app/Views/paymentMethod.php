<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Method | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
                            <h1 class="m-0">Payment Method</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Payment Method</li>
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
                        <?php foreach ($paymentSettings as $paymentSetting): ?>
                            <div class="col-md-6">
                                <form action="/admin/payment/update" method="post">
                                    <input type="hidden" name="payment_method_id" value="<?php echo $paymentSetting['id'] ?>">
                                    <div class="card card-<?php echo $settings['primary_color'] ?>">
                                        <div class="card-header">
                                            <h3 class="card-title"><?php echo $paymentSetting['title'] ?> Payments</h3>
                                        </div>
                                        <div class="card-body row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Description</label>
                                                    <input type="text" class="form-control "  name="description" value="<?php echo $paymentSetting['description'] ?>">
                                                </div>
                                            </div>
                                            <?php if ($paymentSetting['id'] != 1) { ?>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>API Key/ Client Id/ Public Key</label>
                                                        <input type="text" class="form-control "  name="api_key" value="<?php echo $paymentSetting['api_key'] ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>Secret Key/ Client Secret</label>
                                                        <input type="text" class="form-control " name="secret_key" value="<?php echo $paymentSetting['secret_key'] ?>">
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Status</label>
                                                    <select class="form-control " name="status">
                                                        <option value="0" <?php echo $paymentSetting['status'] == 0 ? "selected" : "" ?>>InActive </option>
                                                        <option value="1" <?php echo $paymentSetting['status'] == 1 ? "selected" : "" ?>>Active</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" name="submit" id="submit" class="btn btn-primary">
                                                Update Payment Method
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </div>

        <!-- /.content-wrapper -->
        <?= $this->include('template/footer') ?>

        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->

    <?= $this->include('template/script') ?>
    <script type="text/javascript">
        var successMessage = '<?php echo session()->get('success'); ?>';
        var errorMessage = '<?php echo session()->get('error'); ?>';
        <?php if (session()->get('success')) { ?>
            toastr.success(successMessage, 'Admin says');
        <?php } ?>

        <?php if (session()->get('error')) { ?>
            toastr.error(errorMessage, 'Admin says');
        <?php } ?>
    </script>

</body>

</html>