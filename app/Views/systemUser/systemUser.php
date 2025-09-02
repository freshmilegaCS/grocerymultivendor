<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>System User | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
              <h1 class="m-0">System User</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
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
                  <h3 class="card-title">Add System User</h3>
                </div>
                <!-- /.card-header -->
                <form method="post" action="/admin" enctype="multipart/form-data" id="myform">
                  <div class="card-body">
                    <div class="row">

                    
                    <div class="form-group col-md-12">
                      <label for="exampleSelectBorder">Select role</label>
                      <select class="custom-select " id="role_id" name="role_id">
                        <option value="">Select role</option>
                        <?php foreach ($roles as $role): ?>
                          <option value="<?= esc($role['id']); ?>"><?= esc($role['name']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="exampleInputBorder">First Name</label>
                      <input type="text" class="form-control " name="fname" id="fname" placeholder="Enter First Name">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="exampleInputBorder">Last Name</label>
                      <input type="text" class="form-control " name="lname" id="lname" placeholder="Enter Last Name">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="exampleInputBorder">Mobile</label>
                      <input type="text" class="form-control " name="mobile" id="mobile" placeholder="Enter Mobile">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="exampleInputBorder">Email</label>
                      <input type="text" autocomplete="username" class="form-control " name="email" id="email" placeholder="Enter Email">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="exampleInputBorder">Password</label>
                      <input type="password" autocomplete="new-password" class="form-control " name="pass" id="pass" placeholder="Enter Password">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="exampleInputBorder">Confirm Password</label>
                      <input type="password" class="form-control " name="cpass" id="cpass" placeholder="Enter Confirm Password">
                      <span id="error-msg" class="text-danger"></span>
                    </div>
                    </div>
                  </div>
                  <div class="card-footer">
                    <button type="button" id="sub_cat" name="sub_cat" class="btn btn-primary" onclick="addSystemUser()">
                      Add System User
                    </button>
                  </div>


                </form>
                <!-- /.card-body -->
              </div>
            </div>
            <div class="col-md-7">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">View System User</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                  <table id="view_system_user" class="table table-bordered table-hover">
                    <thead>
                      <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>

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
  <script src="<?= base_url('/assets/page-script/systemUser.js') ?>"></script>

</body>

</html>