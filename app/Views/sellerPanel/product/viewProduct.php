<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Product | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>
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
            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card ">
                                <div class="card-header justify-content-between">
                                    <h3 class="card-title">View Product </h3> <a href="/admin/product/edit/<?= $product['id']?>" class="btn btn-primary float-right">Edit Product</a>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 ">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <!-- Product Information -->
                                                    <tr>
                                                        <th>Name</th>
                                                        <td colspan="3"><?= $product['product_name'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Product Id</th>
                                                        <td><?= $product['id'] ?></td>
                                                        <th>Brand</th>
                                                        <td><?= $brand['brand'] ?? 'N/A' ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Seller</th>
                                                        <td><?= $seller['store_name'] ?? 'N/A' ?></td>
                                                        <th>Category</th>
                                                        <td><?= $category['category_name'] ?? 'N/A' ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Subcategory</th>
                                                        <td><?= $subcategory['name'] ?? 'N/A' ?></td>
                                                        <th>FSSAI Lic. No.</th>
                                                        <td><?= $product['fssai_lic_no'] ?></td> <!-- Replace with actual FSSAI Lic. No. if available -->
                                                    </tr>

                                                    <!-- Status and Conditions -->
                                                    <tr>
                                                        <th>Status</th>
                                                        <td><span class="badge badge-<?= $product['status'] == 1 ? 'success' : 'danger' ?>"><?= $product['status'] == 1 ? 'Published' : 'UnPublished' ?></span></td>
                                                        <th>Return</th>
                                                        <td><span class="badge badge-<?= $product['is_returnable'] == 1 ? 'success' : 'danger' ?>"><?= $product['is_returnable'] == 1 ? 'Allowed' : 'Not Allowed' ?></span></td>
                                                    </tr>

                                                    <tr>
                                                        <th>Is Popular</th>
                                                        <td><span class="badge badge-info"><?= $product['popular'] ? 'Yes' : 'No' ?></span></td>
                                                        <th>Is Deal of the Day</th>
                                                        <td><span class="badge badge-warning"><?= $product['deal_of_the_day'] ? 'Yes' : 'No' ?></span></td>
                                                    </tr>

                                                    <!-- Additional Details -->
                                                    <tr>
                                                        <th>Tax</th>
                                                        <td><?= $tax['tax'] ?> (<?= $tax['percentage'] ?> %)</td>
                                                        <th>Made In</th>
                                                        <td><?= $product['made_in'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Manufacturer</th>
                                                        <td><?= $product['manufacturer'] ?></td>
                                                        <th>Tags</th>
                                                        <td><?php foreach ($tags as $tag): ?>
                                                                <?= $tag['name'] ?>,
                                                            <?php endforeach; ?></td>
                                                    </tr>

                                                    <!-- Images -->
                                                    <tr>
                                                        <th>Main Image</th>
                                                        <td>
                                                            <img src="<?= base_url($product['main_img']) ?>" height="75" alt="Main Product Image">
                                                        </td>
                                                        <th>Other Images</th>
                                                        <td>
                                                            <?php foreach ($productImages as $image): ?>
                                                                <img src="<?= base_url() ?>" height="75" alt="Other Image">
                                                            <?php endforeach; ?>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-<?php echo $settings['primary_color']; ?>">
                                <div class="card-header">
                                    <h3 class="card-title">Product Description</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div>
                                            <?= $product['description'] ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-<?php echo $settings['primary_color']; ?>">
                                <div class="card-header">
                                    <h3 class="card-title">Product Variants</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <table class="table table-bordered">
                                            <thead>
                                                <th>Variant Id</th>
                                                <th>Title</th>
                                                <th>Price</th>
                                                <th>Discounted Price</th>
                                                <th>Stock</th>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($variants)): ?>
                                                    <?php foreach ($variants as $variant): ?>
                                                        <tr>
                                                            <td><?= $variant['id'] ?></td>
                                                            <td><?= $variant['title'] ?></td>
                                                            <td><?= number_format($variant['price'], 2) ?> <?= $country['currency_symbol']?></td>
                                                            <td><?= number_format($variant['discounted_price'], 2) ?> <?= $country['currency_symbol']?></td>
                                                            <td>
                                                                <?= $variant['is_unlimited_stock'] ? 'Unlimited' : $variant['stock'] ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5">No variants available for this product.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
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

</body>

</html>