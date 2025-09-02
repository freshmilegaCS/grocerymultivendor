<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Category | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
              <h1 class="m-0">Category</h1>
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
            <div class="col-md-4">
              <div class="card card-<?php echo $settings['primary_color']; ?>">
                <div class="card-header">
                  <h3 class="card-title">Add Category</h3>
                </div>
                <!-- /.card-header -->
                <form method="post" id="catForm" enctype="multipart/form-data">
                  <div class="card-body">

                    <div class="form-group">
                      <label for="exampleInputBorder">Category Name</label>
                      <input type="text" class="form-control " name="cat_name" id="cat_name" placeholder="Enter Category Name">
                    </div>

                    
                    <div class="form-group">
                      <label>Is Bestseller Category?</label>
                      <select id="is_bestseller_category" name="is_bestseller_category" class="form-control ">
                        <option selected="" value="0">No</option>
                        <option value="1">Yes</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="exampleSelectBorder">Select Group Category</label>
                      <select class="custom-select form-control" id="category_group_id" name="category_group_id">
                        <option value="">Select Group Category</option>
                        <?php if (!empty($groupcategories)): ?>
                          <?php foreach ($groupcategories as $groupcategory): ?>
                            <option value="<?= esc($groupcategory['id']); ?>">
                              <?= esc($groupcategory['title']); ?>
                            </option>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Is it have Warning?</label>
                      <select id="is_it_have_warning" name="is_it_have_warning" class="form-control ">
                        <option selected="" value="0">No</option>
                        <option value="1">Yes</option>
                      </select>
                    </div>
                    
                    <div class="form-group d-none" id="warning_content_div" >
                      <label for="exampleInputBorder">Warning Content</label>
                      <textarea rows="4" class="form-control " name="warning_content" id="warning_content" placeholder="Enter Warning Content"></textarea>
                    </div>
                    
                    <div class="form-group">
                      <label for="exampleInputBorder">Category Image</label>
                      <input type="file" accept=".jpeg,.png, .gif" onchange="convertImage(event)" class="form-control " id="cat_img" name="cat_img">
                    </div>
                    <div class="form-group">
                      <img src="" id="cat_img_webp" style="width:100%">
                    </div>
                  </div>
                  <div class="card-footer">
                    <button type="button" name="add_cat" id="add_cat" class="btn btn-primary" onclick="addCat()">
                      Add Category
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
                  <h3 class="card-title">View Category</h3>
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

                  <table id="view_category" class="table table-bordered table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Category Image</th>
                        <th>Total Subcategory</th>
                        <th>Group Category</th>
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
  <script src="<?= base_url('/assets/page-script/category.js') ?>"></script>
  <script>
      $("#is_it_have_warning").on('change', function() {
    if ($(this).val() == 1) {
        $("#warning_content_div").removeClass('d-none');
    } else {
        $("#warning_content_div").addClass('d-none');
    }
});

  </script>

</body>

</html>