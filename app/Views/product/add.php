<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Product | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

    <?= $this->include('template/style') ?>
    <!-- Include Dropzone CSS and JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>

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
                            <h1 class="m-0">Add Product</h1>
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
                            <form method="post" enctype="multipart/form-data" id="addproduct1">
                                <div class="card card-<?php echo $settings['primary_color']; ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Add Product</h3>
                                    </div>
                                    <!-- /.card-header -->

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="exampleInputBorder">Product Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control " name="productname" id="productname" placeholder="Enter Product Name">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Select Category <span class="text-danger">*</span></label>
                                                    <select id="categoryname" name="categoryname" class="form-control " required>
                                                        <option value="" selected="" disabled="">Select Category</option>
                                                        <?php foreach ($categories as $category): ?>
                                                            <option value="<?= esc($category['id']); ?>"><?= esc($category['category_name']); ?></option>
                                                        <?php endforeach; ?>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Select SubCategory <span class="text-danger">*</span></label>
                                                    <select id="subcategoryname" name="subcategoryname" class="form-control " required>
                                                        <option value="" selected="" disabled="">Select Subcategory</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Product Publish Or Unpublish?</label>
                                                    <select id="ispublish" name="ispublish" class="form-control ">

                                                        <option value="0">Unpublish</option>
                                                        <option selected="" value="1">Publish</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Make Product Popular?</label>
                                                    <select id="popular" name="popular" class="form-control ">

                                                        <option value="1">Yes</option>
                                                        <option selected="" value="0">No</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Insert to Deal of the day?</label>
                                                    <select id="deal_of_the_day" name="deal_of_the_day" class="form-control ">
                                                        <option selected="" value="0">No</option>
                                                        <option value="1">Yes</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Select Brand <span class="text-danger">*</span></label>
                                                    <div class="select2-olive">
                                                        <select id="brandname" name="brandname" class="form-control select2 select2-olive" data-dropdown-css-class="select2-olive" ata-placeholder="Select Brand" required>
                                                            <option value="" selected="" disabled="">Select Brand</option>

                                                            <?php foreach ($brands as $brand): ?>
                                                                <option value="<?= esc($brand['id']); ?>"><?= esc($brand['brand']); ?> <img src="<?= base_url($brand['image']) ?>"></option>
                                                            <?php endforeach; ?>

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Select Seller <span class="text-danger">*</span></label>
                                                    <div class="select2-olive">
                                                        <select id="seller" name="seller" class="form-control select2 select2-olive" data-dropdown-css-class="select2-olive" ata-placeholder="Select Seller" required>
                                                            <option value="" selected="" disabled="">Select Seller</option>
                                                            <?php foreach ($sellers as $seller): ?>
                                                                <option value="<?= esc($seller['id']); ?>"><?= esc($seller['store_name']); ?></option>
                                                            <?php endforeach; ?>

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="tags">Select Tags <span class="text-danger">This will help for search</span></label>
                                                    <div class="select2-olive">
                                                        <select id="tags" name="tags[]" class="form-control select2" multiple="multiple" style="width: 100%;" data-dropdown-css-class="select2-olive" data-placeholder="Select or create tags">
                                                        </select>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Product Small Description <span class="text-danger">*</span> <?php if ($settings['chatgpt_status'] == 1) { ?> <button type="button" onclick="generateDescriptionUsingAI()" class="btn-ai"><i class="fi fi-bs-artificial-intelligence"></i> Generate using AI</button> <?php } ?></label>
                                                    <textarea class="form-control " name="description" id="description" placeholder="Enter Product Small Description" required></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.card-body -->
                                </div>
                                <div class="card card-<?php echo $settings['primary_color']; ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Add SEO Content <?php if ($settings['chatgpt_status'] == 1) { ?> <button type="button" onclick="generateSEOUsingAI()" class="btn-ai"><i class="fi fi-bs-artificial-intelligence"></i> Generate using AI</button><?php } ?></h3>
                                    </div>
                                    <!-- /.card-header -->

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="exampleInputBorder">SEO Title</label>
                                                    <input type="text" class="form-control " name="seo_title" id="seo_title" placeholder="Enter SEO Title">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="exampleInputBorder">SEO Keywords</label>
                                                    <input type="text" class="form-control " name="seo_keywords" id="seo_keywords" placeholder="Enter SEO Keywords">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="exampleInputBorder">SEO Image Alt Text</label>
                                                    <input type="text" class="form-control " name="seo_alt_text" id="seo_alt_text" placeholder="Enter SEO Image Alt Text">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="exampleInputBorder">SEO Description</label>
                                                    <input type="text" class="form-control " name="seo_description" id="seo_description" placeholder="Enter SEO Description">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.card-body -->
                                </div>
                                <div class="card card-<?php echo $settings['primary_color']; ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Add Variation</h3>
                                    </div>
                                    <!-- /.card-header -->

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-md-4">
                                                <label>Select Product Variation Type <span class="text-danger text-sm">*</span></label>
                                                <select class="form-control " id="ptype" name="ptype" style="width: 100%;">
                                                    <option value="">Select Product Type</option>
                                                    <option value="simple_product">Simple Product</option>
                                                    <option value="variation_product">Variation Product</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div id="new_variation_div">
                                        </div>
                                    </div>

                                    <!-- /.card-body -->
                                </div>
                                <div class="card card-<?php echo $settings['primary_color']; ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Add Other Details</h3>
                                    </div>
                                    <!-- /.card-header -->

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="exampleInputBorder">Manufacturer</label>
                                                    <input type="text" class="form-control " name="manufacturer" id="manufacturer" placeholder="Enter Manufacturer">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Made In</label>
                                                    <input type="text" id="made_in" class="form-control " placeholder="Enter Made In" name="made_in">

                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Select Tax</label>
                                                    <select id="tax_id" name="tax_id" class="form-control ">
                                                        <option value="" selected="" disabled="">Select Tax</option>
                                                        <?php foreach ($taxes as $tax): ?>
                                                            <option value="<?= esc($tax['id']); ?>"><?= esc($tax['tax']); ?> (<?= esc($tax['percentage']); ?>%)</option>
                                                        <?php endforeach; ?>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>is Returnable?</label>
                                                    <select id="is_returnable" name="is_returnable" class="form-control ">
                                                        <option value="0">No</option>
                                                        <option value="1">Yes</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="return_days">Max Return Days</label>
                                                    <input type="text" class="form-control " name="return_days" id="return_days" placeholder="Enter Max Return Days">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="fssai_lic_no">FSSAI Lic. No.</label>
                                                    <input type="text" class="form-control " name="fssai_lic_no" id="fssai_lic_no" placeholder="Enter FSSAI Lic. No.">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="total_allowed_quantity">Total Allowed Quantity In Cart <span class="text-danger">Keep blank if no such limit</span></label>
                                                    <input type="number" class="form-control " name="total_allowed_quantity" id="total_allowed_quantity" placeholder="Enter Total Allowed Quantity">
                                                </div>
                                            </div>

                                        </div>


                                    </div>


                                    <!-- /.card-body -->
                                </div>
                                <div class="card card-<?php echo $settings['primary_color']; ?>">
                                    <div class="card-header">
                                        <h3 class="card-title">Add Images</h3>
                                    </div>
                                    <!-- /.card-header -->

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputBorder">Product Main Image <span class="text-danger">*</span></label>
                                                    <div class="dropzone custom-dropzone" id="main-file-dropzone">
                                                        <div class="dropzone-clickable-area">
                                                            <div class="icon"><i class="fi fi-br-upload"></i></div>
                                                            <p>Upload Main Image </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="form-group">
                                                    <label for="exampleInputBorder">Product Other Images </label>
                                                    <div class="dropzone custom-dropzone" id="images-dropzone">
                                                        <div class="dropzone-clickable-area1">
                                                            <div class="icon"><i class="fi fi-br-upload"></i></div>
                                                            <p>Upload Other Product Images Here</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button type="submit" id="submitBtn" class="btn btn-primary">
                                            Add Product
                                        </button>
                                    </div>


                                    <!-- /.card-body -->
                                </div>
                            </form>
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
    <script src="<?= base_url('/assets/page-script/product_add.js') ?>"></script>
    <script>
        $(document).ready(function () {
             $("#tags").select2({
                tags: true, // Allow new tags
                placeholder: "Select or create tags",
                tokenSeparators: [","], // Comma will trigger a tag creation
                ajax: {
                  url: "/admin/tags/get-tags", // URL to fetch existing tags
                  dataType: "json",
                  delay: 250,
                  type: "POST",
                  data: function (params) {
                    return {
                      tags: params.term, // search term
                    };
                  },
                  processResults: function (data) {
                    return {
                      results: $.map(data, function (item) {
                        return {
                          id: item.text,
                          text: item.text,
                        };
                      }),
                    };
                  },
                },
                createTag: function (params) {
                  var term = $.trim(params.term); // Trim spaces before checking
            
                  // Prevent tag creation if the input is just spaces or empty
                  if (term === "" || term.length < 2) {
                    return null;
                  }
            
                  return {
                    id: term, // For now, assign the term as ID
                    text: term,
                    newTag: true, // Mark this as a new tag
                  };
                },
                insertTag: function (data, tag) {
                  // Prevent adding tags with only spaces
                  var found = false;
                  for (var i = 0; i < data.length; i++) {
                    if ($.trim(tag.text).toLowerCase() === data[i].text.toLowerCase()) {
                      found = true;
                      break;
                    }
                  }
                  if (!found) {
                    data.push(tag);
                  }
                },
              });
        
          // General Select2 init for everything else
          $('.select2-except').select2();
        });
    </script>

</body>

</html>