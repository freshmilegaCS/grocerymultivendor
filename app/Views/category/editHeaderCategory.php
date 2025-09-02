<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Header Category | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
              <h1 class="m-0">Edit Header Category</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Edit Header Category</li>
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
                  <h3 class="card-title">Edit Header Category</h3>
                </div>
                <!-- /.card-header -->
                <form method="post" enctype="multipart/form-data">
                  <input type="hidden" name="header_category_id" id="header_category_id" value="<?= $header_category['id'] ?>">
                  <div class="card-body">

                    <div class="form-group">
                      <label for="exampleInputBorder">Header Category Name</label>
                      <input type="text" class="form-control " name="header_category_title" id="header_category_title" value="<?= $header_category['title'] ?>" placeholder="Enter Category Name">
                    </div>
                    <div class="form-group">
                      <label for="icon_library">Select Icon Library <span class="text-danger text-sm">Use the correct icon library—wrong names may crash the app.</span></label>
                      <select class="custom-select form-control" id="icon_library" name="icon_library">
                        <option value="">Select Icon Library</option>
                            <!--<option value="1">  MaterialDesignIcons </option>-->
                            <option value="2" <?= (isset($header_category['icon_library']) && $header_category['icon_library'] == 2) ? 'selected' : '' ?>>  FontAwesome </option>
                            <option value="3" <?= (isset($header_category['icon_library']) && $header_category['icon_library'] == 3) ? 'selected' : '' ?>>  Ionicons </option>
                            <option value="4" <?= (isset($header_category['icon_library']) && $header_category['icon_library'] == 4) ? 'selected' : '' ?>>  MaterialIcons </option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="exampleInputBorder">Header Category Icon<span class="text-danger text-sm">Enter correct icon name from selected library —wrong names may crash the app.</span></label> Find Icon from <a href="https://oblador.github.io/react-native-vector-icons/" class="text-danger " target="_blank"> Here</a>
                      <input type="text" class="form-control " name="header_category_icon" id="header_category_icon" value="<?= $header_category['icon'] ?>" placeholder="Enter Category Icon">
                    </div>
                    
                    <div class="form-group">
                      <label for="exampleSelectBorder">Select Header Category</label>
                      <select class="custom-select form-control" id="category_id" name="category_id">
                        <option value="">Select Header Category</option>
                        <?php if (!empty($categories)): ?>
                          <?php foreach ($categories as $category): ?>
                            <option value="<?= esc($category['id']); ?>"
                              <?= (isset($header_category['category_id']) && $header_category['category_id'] == $category['id']) ? 'selected' : '' ?>>
                              <?= esc($category['category_name']); ?>
                            </option>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </select>
                    </div>

                  </div>
                  <div class="card-footer">
                    <button type="button" name="add_cat" id="add_cat" class="btn btn-primary" onclick="updateHeadercategory(<?= $header_category['id'] ?>)">
                      Edit Header Category
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
  <script src="<?= base_url('/assets/page-script/category_header.js') ?>"></script>

</body>

</html>