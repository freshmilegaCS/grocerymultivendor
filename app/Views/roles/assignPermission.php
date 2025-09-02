<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assign Permission | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

    <?= $this->include('template/style') ?>
    <link rel="stylesheet" href="<?= base_url('/assets/plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') ?>">

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
                    <form class="form" method="post" enctype="multipart/form-data" autocomplete="off">
                        <input type="hidden" name="editid" id="editid" value="<?php  echo  $role_info['id'] 
                                                                                    ?>">
                        <div class="row">

                            <div class=" col-md-12 ">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Assign Permission for <?php  echo $role_info['name'] 
                                                                                        ?> </h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table permission-table ">
                                            <thead>
                                                <tr>
                                                    <th>Module/Permissions</th>

                                                    <th>View</th>
                                                    <th>Create</th>
                                                    <th>Update</th>
                                                    <th>Delete</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($categories as $category): ?>
                                                    <input type="hidden" value="<?= $role_permissions_map[$category['id']]['id'] ?? '' ?>" id="select_permission_id_<?= $category['id'] ?>" name="select_permission_id_<?= $category['id'] ?>">

                                                    <tr>
                                                        <td><?= esc($category['name']); ?></td>

                                                        <?php if ($category['enable_view'] == 1): ?>
                                                            <td>
                                                                <input type="checkbox" name="<?= $category['id'] ?>_can_view" id="<?= $category['id'] ?>_can_view"
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success"
                                                                    <?= isset($role_permissions_map[$category['id']]) && $role_permissions_map[$category['id']]['can_view'] ? 'checked' : ''; ?>>
                                                            </td>
                                                        <?php else: ?>
                                                            <td></td>
                                                        <?php endif; ?>

                                                        <?php if ($category['enable_add'] == 1): ?>
                                                            <td>
                                                                <input type="checkbox" name="<?= $category['id'] ?>_can_add" id="<?= $category['id'] ?>_can_add"
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success"
                                                                    <?= isset($role_permissions_map[$category['id']]) && $role_permissions_map[$category['id']]['can_add'] ? 'checked' : ''; ?>>
                                                            </td>
                                                        <?php else: ?>
                                                            <td></td>
                                                        <?php endif; ?>

                                                        <?php if ($category['enable_edit'] == 1): ?>
                                                            <td>
                                                                <input type="checkbox" name="<?= $category['id'] ?>_can_edit" id="<?= $category['id'] ?>_can_edit"
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success"
                                                                    <?= isset($role_permissions_map[$category['id']]) && $role_permissions_map[$category['id']]['can_edit'] ? 'checked' : ''; ?>>
                                                            </td>
                                                        <?php else: ?>
                                                            <td></td>
                                                        <?php endif; ?>

                                                        <?php if ($category['enable_delete'] == 1): ?>
                                                            <td>
                                                                <input type="checkbox" name="<?= $category['id'] ?>_can_delete" id="<?= $category['id'] ?>_can_delete"
                                                                    data-bootstrap-switch data-off-color="danger" class='system-users-switch' data-on-color="success"
                                                                    <?= isset($role_permissions_map[$category['id']]) && $role_permissions_map[$category['id']]['can_delete'] ? 'checked' : ''; ?>>
                                                            </td>
                                                        <?php else: ?>
                                                            <td></td>
                                                        <?php endif; ?>
                                                    </tr> <?php endforeach; ?>
                                            </tbody>

                                        </table>

                                        <div class="d-flex justify-content-center">
                                            <div class="form-group" id="error_box">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <button type="button" class="btn btn-success" id="submit_btn" name="submit" onclick="updatePermission()">Update Role Permission</button>
                                        </div>
                                    </div>
                                </div> 
                            </div>

                        </div>
                    </form>
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
    <script src="<?= base_url('/assets/plugins/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>
    <script src="<?= base_url('/assets/page-script/assign_permission.js') ?>"></script>

</body>

</html>  