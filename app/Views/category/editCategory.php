<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Category  | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
              <h1 class="m-0">Edit Category</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Edit Category</li>
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
                  <h3 class="card-title">Edit Category</h3>
                </div>
                <!-- /.card-header -->
                <form method="post" enctype="multipart/form-data">
                  <input type="hidden" name="id" id="id" value="<?= $category['id'] ?>">
                  <div class="card-body">

                    <div class="form-group">
                      <label for="exampleInputBorder">Category Name</label>
                      <input type="text" class="form-control " name="cat_name" id="cat_name" value="<?= $category['category_name'] ?>" placeholder="Enter Category Name">
                    </div>

                    <div class="form-group">
                      <label for="exampleInputBorder">Category Image</label>
                      <input type="file" accept=".jpeg,.png, .gif" onchange="convertImage(event)" class="form-control" id="cat_img" name="cat_img">
                    </div>
                    <div class="form-group">
                      <label>Is Bestseller Category?</label>
                      <select id="is_bestseller_category" name="is_bestseller_category" class="form-control ">
                        <option value="0" <?php echo $category['is_bestseller_category'] == 0 ? "selected" : "" ?>>No</option>
                        <option value="1" <?php echo $category['is_bestseller_category'] == 1 ? "selected" : "" ?>>Yes</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Is it have Warning?</label>
                      <select id="is_it_have_warning" name="is_it_have_warning" class="form-control ">
                        <option value="0" <?php echo $category['is_it_have_warning'] == 0 ? "selected" : "" ?>>No</option>
                        <option value="1" <?php echo $category['is_it_have_warning'] == 1 ? "selected" : "" ?>>Yes</option>
                      </select>
                    </div>
                    
                    <div class="form-group <?php echo $category['is_it_have_warning'] == 0 ? "d-none" : "" ?> " id="warning_content_div" >
                      <label for="exampleInputBorder">Warning Content</label>
                      <textarea rows="4" class="form-control " name="warning_content" id="warning_content" placeholder="Enter Warning Content"><?= $category['warning_content'] ?></textarea>
                    </div>
                    
                    <div class="form-group">
                      <img src="" id="cat_img_webp" style="width:100%">
                    </div>
                    <div class="form-group">
                      <label for="exampleInputBorder">Old Image</label>
                      <img src="<?= base_url() . $category['category_img'] ?>" style="width:100%">
                    </div>
                    <div class="form-group">
                      <label for="exampleSelectBorder">Select Group Category</label>
                      <select class="custom-select form-control" id="category_group_id" name="category_group_id">
                        <option value="">Select Group Category</option>
                        <?php if (!empty($groupcategories)): ?>
                          <?php foreach ($groupcategories as $groupcategory): ?>
                            <option value="<?= esc($groupcategory['id']); ?>" aaaaaa="<?= esc($category['category_group_id']); ?>"
                              <?php echo   $category['category_group_id'] == $groupcategory['id'] ? 'selected' : '' ?>>
                              <?= esc($groupcategory['title']); ?>
                            </option>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </select>
                    </div>

                  </div>
                  <div class="card-footer">
                    <button type="button" name="add_cat" id="add_cat" class="btn btn-primary" onclick="updateCat()">
                      Edit Category
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