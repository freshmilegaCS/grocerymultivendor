<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SMS Gateway | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
                            <h1 class="m-0">SMS Gateway</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">SMS Gateway</li>
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
                        <?php foreach ($smsGateways as $smsGateway): ?>
                            <div class="col-md-6">
                                <form action="/admin/sms-gateway/update" method="post">
                                    <input type="hidden" name="sms_gateway_id" value="<?php echo $smsGateway['id'] ?>">
                                    <div class="card card-<?php echo $settings['primary_color'] ?>">
                                        <div class="card-header">
                                            <h3 class="card-title"><?php echo $smsGateway['name'] ?></h3>
                                        </div>
                                        <div class="card-body row">
                                            <?php
                                            $data = json_decode($smsGateway['value'], true);
                                            ?>
                                            <?php if (is_array($data)): ?>
                                                <?php foreach ($data as $key => $value): ?>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label><?= ucwords(str_replace('_', ' ', $key)) ?></label>
                                                            <?php if ($key === 'otp_template'): ?>
                                                                <textarea class="form-control" name="<?= $key ?>"><?= htmlspecialchars($value) ?></textarea>
                                                            <?php else: ?>
                                                                <input type="text" class="form-control" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>">
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="col-md-12">
                                                    <div class="alert alert-warning">
                                                        Invalid or empty configuration data.
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Status</label>
                                                    <select class="form-control " name="is_active">
                                                        <option value="0" <?php echo $smsGateway['is_active'] == 0 ? "selected" : "" ?>>InActive </option>
                                                        <option value="1" <?php echo $smsGateway['is_active'] == 1 ? "selected" : "" ?>>Active</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" name="submit" id="submit" class="btn btn-primary">
                                                Update SMS Gateway
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