<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bulk Import Product | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
                            <h1 class="m-0">Bulk Import Product</h1>
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
                    <div class="row">


                        <div class="col-md-12">
                            <div class="card card-<?php echo $settings['primary_color']; ?>">
                                <div class="card-header">
                                    <h4>Product Bulk Upload Form</h4>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-default-danger">
                                    <p>Always download and use new sample file</p>
                                        <pre>Steps to bulk upload of products
1. Firstly, read Notes carefully.
2. Images will need to update later manually.
3. Create/ Edit .csv file for product as explain below:

        Note - * Indicates Mandatory Fields

        Product Name * -&gt; Name of the product. 
        Brand ID * -&gt; Brand ID of the product (You can find Brand ID in Categories section).
        Category ID * -&gt; Category ID of the product (You can find Category ID in Categories section).
        Subcategory ID -&gt; Subcategory ID of the product (You can find Subcategory ID in Subcategories section).
        Description * -&gt; Description about product
        Publish Status * -&gt; 0 - Unpublish, 1 - Publish.
        Popular Status * -&gt; 0 - Unpopular, 1 - Popular.
        Deal Of The Day Status * -&gt; 0 - No, 1 - Yes.
        Tax ID -&gt; Tax ID of the tax (You can find Tax ID in Subcategories section, Enter 0 if you don't want to use).
        Manufacturer -&gt; Manufacturer of the product.
        Made in -&gt; Product Made In.
        Is Returnable * -&gt; 0 - No, 1 - Yes.
        Maximum Return Days -&gt; If Is Returnable then only mention date in number (Ex: if product is can be return in 7 days so enter <b>7</b>).
        Total Allowed Quantity * -&gt; It is number of quantity user can add at a cart in single order (Ex: if you want to sell product limitedly per user then enter quantity here or leave blank)
        FSSAI NO -&gt; Fssai Number should be 14 numeric. (ex. 12345678909876). Leave it blank if don't want to add fssai no. 
        -&gt; Add following columns for variants of products. you need to add this columns in continued(You can check example on sample file)
        -&gt; for example, If you want to add 3 variants then you need to add this column 3 times. Product must have 1 variant. You can add maximum 3 variant at a time.
        
            Product variant title * -&gt; Enter Variation title Ex: <b>100Gm</b>.
            Price -&gt; * Price of the variant. (Must be greater than discounted price).
            Discounted Price * -&gt; Discounted Price of the variant 0 if no discount. (Must be less than price).
            Stock * -&gt; Enter number of stocks (If stock is unlimited, enter 0).
            Status * -&gt; Availability of the variant 0 - Sold out, 1 - Available. .
            
        Note - Do not set empty field. if you have no value on specific column then add "0"(zero) in that column.</pre>
                                    </div>
                                    <div class="row">
                                        <form method="post" action="/seller/product/bulk-import/insert" enctype="multipart/form-data">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="import_file">CSV File</label>
                                                    <input type="file" name="import_file" id="import_file" required="required" accept=".csv" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <a href="<?= base_url() ?>sample-file/import-products-for-seller.csv" download="import-products.csv" class="btn btn-warning"><em class="fa fa-download"></em>
                                                    Download Sample File</a>
                                                <button type="submit" id="submit_btn" name="btnAdd" class="btn btn-primary" fdprocessedid="11szbn"><i class="fa fa-upload"></i> Upload
                                                </button>
                                                <button type="reset" class="btn btn btn-secondary" fdprocessedid="w5dac"><i aria-hidden="true" class="fa fa-undo"></i> Clear
                                                </button>

                                            </div>
                                        </form>
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
    <?php if (session()->getFlashdata('success')): ?>
        <script type="text/javascript">
            $(document).ready(function() {
                toastr.success('<?= session()->getFlashdata('success'); ?>');

            });
        </script>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <script type="text/javascript">
            $(document).ready(function() {
                toastr.error('<?= session()->getFlashdata('error'); ?>');
            });
        </script>
    <?php endif; ?>
</body>

</body>

</html>