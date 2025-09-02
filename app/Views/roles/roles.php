<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Rolea | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
                    <!-- Small boxes (Stat box) -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card card-<?php echo $settings['primary_color']; ?>">
                                <div class="card-header">
                                    <h3 class="card-title">Add Roles</h3>
                                </div>
                                <!-- /.card-header -->
                                <form method="post" action="/admin/roles/add" enctype="multipart/form-data">
                                    <div class="card-body">

                                        <div class="form-group">
                                            <label>Enter Role</label>
                                            <input type="text" autocomplete="false" id="role_name" class="form-control" require name="role_name" required placeholder="Enter Role">
                                        </div>

                                    </div>
                                    <div class="card-footer">
                                        <button type="submit" name="add_cat" id="add_cat" class="btn btn-primary">
                                            Add Roles
                                        </button>
                                    </div>

                                </form>
                                <!-- /.card-body -->
                            </div>
                        </div>
                        <!-- ./col -->

                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">View Roles</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class="d-flex justify-content-end align-items-center mb-3">
                                        <div class="mr-2">
                                            <select id="custom-length-change" class=" form-control form-control-sm primary-bprder">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                        </div>
                                        <!-- Export Dropdown Button -->
                                        <div class="btn-group mr-2">
                                            <a href="#!" class="btn btn-primary btn-sm  dropdown-toggle " data-toggle="dropdown" aria-expanded="false">
                                                <i class="fi fi-tr-file-export"></i> Export
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                <!-- Placeholder for DataTable export buttons -->
                                                <li><a class="dropdown-item dt-export-copy" href="#">Copy</a></li>
                                                <li><a class="dropdown-item dt-export-csv" href="#">CSV</a></li>
                                                <li><a class="dropdown-item dt-export-excel" href="#">Excel</a></li>
                                                <li><a class="dropdown-item dt-export-pdf" href="#">PDF</a></li>
                                                <li><a class="dropdown-item dt-export-print" href="#">Print</a></li>
                                            </ul>
                                        </div>

                                        <!-- Search box aligned next to Export button -->
                                        <div>
                                            <input type="search" id="custom-search" class="form-control form-control-sm primary-bprder" placeholder="Search:" aria-controls="example">
                                        </div>
                                    </div>

                                    <table id="view_role" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Role</th>
                                                <th>Type</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $x = 1; ?>
                                            <?php foreach ($roles as $role): ?>
                                                <tr>
                                                    <td><?= $x++; ?></td>
                                                    <td><?= esc($role['name']); ?></td>
                                                    <td>
                                                        <?php if ($role['is_system'] == 1): ?>
                                                            <span class="badge badge-success">System</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger">Custom</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($role['is_superadmin'] != 1): ?>
                                                            <a class="btn btn-primary-light btn-xs" title="Assign permission" href="<?= site_url('admin/roles/assign-permission/' . $role['id']); ?>"><i class="fi fi-tr-tags"></i></a>
                                                        <?php endif; ?>
                                                        <?php if ($role['is_system'] != 1): ?>
                                                            <a class="btn btn-danger btn-xs" title="Delete Role" href="javascript:void(0);" onclick="deleteRole(<?= $role['id']; ?>)"><i class="fi fi-tr-trash-xmark"></i></a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
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
    <script src="<?= base_url('/assets/page-script/roles.js') ?>"></script>
    <?php if (session()->getFlashdata('success')) : ?>
        <script>
            $(document).ready(function() {
                toastr.success('<?= session()->getFlashdata('success') ?>');
            });
        </script>
    <?php endif; ?>

    <?php if (isset($validation)) : ?>
        <script>
            $(document).ready(function() {
                toastr.error('<?= session()->getFlashdata('error') ?>');

            });
        </script>

    <?php endif; ?>
</body>

</html>