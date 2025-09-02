<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product Rating List for <?= $productInfo['product_name'] ?> | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

    <?= $this->include('template/style') ?>

</head>

<body class=" text-sm  layout-fixed <?php echo  $settings['thememode'] == 'Light' ? '' : 'dark-mode'; ?> layout-navbar-fixed text-sm " id="body">
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
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">View Product Rating List for <b><?= $productInfo['product_name'] ?></b></h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class=" d-flex">

                                        <div class="d-flex align-items-center ">
                                            <div class="mx-2">

                                                <select id="custom-length-change" class=" form-control form-control-sm primary-bprder">
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                </select>
                                            </div>
                                            <!-- Export Dropdown Button -->
                                            <div class="btn-group mx-2">

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
                                    </div>
                                    <table id="view_product" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order Id</th>
                                                <th>User Name</th>
                                                <th>Rating</th>
                                                <th>Feedback title</th>
                                                <th>Review</th>
                                                <th>Review at</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($ratingLists as $ratingList) { ?>
                                                <tr>
                                                    <td><?php echo $ratingList['order_id'] != 0 ? "<a class='btn btn-primary-light btn-sm' href='/admin/orders/view/".$ratingList['order_id']."'>".$ratingList['order_id']."</a>"  : '0' ?></td>
                                                    <td><?= $ratingList['name'] ?></td>
                                                    <td><?= $ratingList['rate'] ?></td>
                                                    <td><?= $ratingList['title'] ?></td>
                                                    <td><?= $ratingList['review'] ?></td>
                                                    <td><?= date('d-M-Y', strtotime($ratingList['created_at'])) ?></td>
                                                    <td id="status-<?= $ratingList['product_ratings_id'] ?>"><?php
                                                        if ($ratingList['is_approved_to_show'] == 1) {
                                                            echo '<span class="badge badge-success">Approved</span>';
                                                        } elseif ($ratingList['is_approved_to_show'] == 2) {
                                                            echo '<span class="badge badge-danger">Rejected</span>';
                                                        } else {
                                                            echo '<span class="badge badge-warning">Pending</span>';
                                                        }
                                                        ?></td>
                                                    <td id="actions-<?= $ratingList['product_ratings_id'] ?>">
                                                        <?php
                                                        if ($ratingList['is_approved_to_show'] == 1) {
                                                            echo '<button class="btn btn-sm btn-danger-light" data-tooltip="Reject Rating" onclick="updateReview(' . $ratingList['product_ratings_id'] . ', 2, this)"><i class="fi fi-br-hand"></i></button>
';
                                                        } elseif ($ratingList['is_approved_to_show'] == 2) {
                                                            echo '<button class="btn btn-sm btn-primary-light" data-tooltip="Approve Rating" onclick="updateReview(' . $ratingList['product_ratings_id'] . ', 1, this)"><i class="fi fi-br-social-network"></i></button>';
                                                        } else {
                                                            echo '<button class="btn btn-sm btn-primary-light" data-tooltip="Approve Rating" onclick="updateReview(' . $ratingList['product_ratings_id'] . ', 1, this)"><i class="fi fi-br-social-network"></i></button>
                                                        <button class="btn btn-sm btn-danger-light" data-tooltip="Reject Rating" onclick="updateReview(' . $ratingList['product_ratings_id'] . ', 2, this)"><i class="fi fi-br-hand"></i></button>';
                                                        }
                                                        ?>

                                                    </td>
                                                </tr>
                                            <?php }
                                            ?>

                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.card-body -->
                            </div>
                        </div>
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
    <script src="<?= base_url('/assets/page-script/product_rating.js') ?>"></script>



</body>

</html>