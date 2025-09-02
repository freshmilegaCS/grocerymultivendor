<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Brand | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
                  <h3 class="card-title">Edit Brand</h3>
                </div>
                <!-- /.card-header -->
                <form method="post" enctype="multipart/form-data">
                  <input type="hidden" name="id" id="id" value="<?= $brand['id'] ?>">
                  <div class="card-body">

                    <div class="form-group">
                      <label for="exampleInputBorder">Brand Name</label>
                      <input type="text" class="form-control " name="brand_name" id="brand_name" value="<?= $brand['brand'] ?>" placeholder="Enter Brand Name">
                    </div>

                    <div class="form-group">
                      <label for="exampleInputBorder">Brand Image</label>
                      <input type="file" accept=".jpeg,.png, .gif" onchange="convertImage(event)" class=" " id="brand_image" name="brand_image">
                    </div>
                    <div class="form-group">
                      <img src="" id="brand_image_webp" style="width:100%">
                    </div>
                    <div class="form-group">
                      <label for="exampleInputBorder">Old Image</label>
                      <img src="<?= base_url() . $brand['image'] ?>"  style="width:100%">
                    </div>
                  </div>
                  <div class="card-footer">
                    <button type="button" name="add_cat" id="add_cat" class="btn btn-primary" onclick="updateBrand()">
                      Edit Brand
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
  <script src="<?= base_url('/assets/page-script/brand.js') ?>"></script>

</body>

</html>