<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Brand | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

  <?= $this->include('sellerPanel/template/style') ?>

</head>

<body class="sidebar-mini control-sidebar-slide-open text-sm  layout-fixed <?php echo  $settings['thememode'] == 'Light' ? '' : 'dark-mode'; ?> layout-navbar-fixed text-sm" id="body">
  <div class="wrapper">


    <?= $this->include('sellerPanel/template/header') ?>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <?= $this->include('sellerPanel/template/sidebar') ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">Brand</h1>
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
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">View Brand</h3>
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

                  <table id="view_brand" class="table table-bordered table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Brand Name</th>
                        <th>Brand Image</th>
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
    <?= $this->include('sellerPanel/template/footer') ?>

    <!-- /.control-sidebar -->
  </div>
  <!-- ./wrapper -->

  <!-- jQuery -->

  <?= $this->include('sellerPanel/template/script') ?>
  <script src="<?= base_url('/assets/page-script/sellerPanel/brand.js') ?>"></script>

</body>

</html>