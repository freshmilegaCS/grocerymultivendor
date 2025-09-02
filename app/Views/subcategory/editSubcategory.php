<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Subcategory | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
              <h1 class="m-0">Edit Subcategory</h1>
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
                  <h3 class="card-title">Edit Subcategory</h3>
                </div>
                <!-- /.card-header -->
                <form method="post" enctype="multipart/form-data">
                  <input type="hidden" name="sub_cat_id" id="sub_cat_id" value="<?= $subcategory['id'] ?>">

                  <div class="card-body">
                    <div class="form-group">
                      <label for="exampleSelectBorder">Select category</label>
                      <select class="custom-select " id="cat_id" name="cat_id">
                        <option value="">Select category</option>
                        <?php foreach ($categories as $category): ?>
                          <option value="<?= esc($category['id']); ?>" <?php echo $category['id'] == $subcategory['category_id'] ? "selected" : "" ?>><?= esc($category['category_name']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="exampleInputBorder">Subcategory Name</label>
                      <input type="text" class="form-control " name="sub_cat_name" id="sub_cat_name" value="<?= $subcategory['name'] ?>" placeholder="Enter Subcategory Name">
                    </div>

                    <div class="form-group">
                      <label for="exampleInputBorder">Subcategory Image</label>
                      <input type="file" accept=".jpeg,.png, .gif" onchange="convertImage(event)" class=" " id="sub_cat_img" name="sub_cat_img">
                    </div>
                    <div class="form-group">
                      <img src="" id="sub_cat_img_webp" style="width:100%">
                    </div>
                    <div class="form-group">
                      <label for="exampleInputBorder">Old Image</label>
                      <img src="<?= base_url() . $subcategory['img'] ?>" style="width:100%">
                    </div>
                  </div>
                  <div class="card-footer">
                    <button type="button" name="update_subcat" id="update_subcat" class="btn btn-primary" onclick="updateSubCat()">
                      Add Subcategory
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
  <script src="<?= base_url('/assets/page-script/subcategory.js') ?>"></script>

</body>

</html>