<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit HighLight | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

    <?= $this->include('template/style') ?>
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
                            <h1 class="m-0">Edit HighLight</h1>
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
                            <div class="card card-<?php echo $settings['primary_color']; ?>">
                                <div class="card-header">
                                    <h3 class="card-title">Edit HighLight</h3>
                                </div>
                                <!-- /.card-header -->
                                <form method="post" id="highlightForm" enctype="multipart/form-data">
                                    <input type="hidden" name="highlights_id" id="highlights_id" value="<?= $highlights['id'] ?>">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group  col-md-4">
                                                <label>Select Seller <span class="text-danger">*</span></label>
                                                <div class="select2-olive">
                                                    <select id="seller_id" name="seller_id" class="form-control select2 select2-olive" data-dropdown-css-class="select2-olive" ata-placeholder="Select Seller" required>
                                                        <option value="" selected="" disabled="">Select Seller</option>
                                                        <?php foreach ($sellers as $seller): ?>
                                                            <option value="<?= esc($seller['id']); ?>" <?php echo $seller['id'] == $highlights['seller_id'] ? "selected" : "" ?>><?= esc($seller['store_name']); ?></option>
                                                        <?php endforeach; ?>

                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group  col-md-4">
                                                <label for="exampleInputBorder">Title</label>
                                                <input type="text" class="form-control " name="title" id="title" placeholder="Enter Title" value="<?= $highlights['title'] ?>">
                                            </div>
                                            <div class="form-group  col-md-4">
                                                <label for="exampleInputBorder">Description</label>
                                                <input type="text" class="form-control " name="description" id="description" placeholder="Enter Description" value="<?= $highlights['title'] ?>">
                                            </div>
                                            <div class="form-group  col-md-4">
                                                <label>Select Type <span class="text-danger">*</span></label>
                                                <select id="media_type" name="media_type" class="form-control " required>
                                                    <option value="" selected="" disabled="">Select Type</option>
                                                    <option value="image" <?php echo  $highlights['image'] != "" ? "selected" : "" ?>>Image</option>
                                                    <option value="video" <?php echo  $highlights['video'] != "" ? "selected" : "" ?>>Video</option>
                                                </select>
                                            </div>

                                            <div class="form-group  col-md-4 <?php echo  $highlights['video'] != "" ? "" : "d-none" ?>  " id="video-div">
                                                <label for="exampleInputBorder">Youtube Video Link</label>
                                                <input type="text" class="form-control " name="video" id="video" placeholder="Enter Youtube Video Link" value="https://www.youtube.com/watch?v=<?= $highlights['video'] ?>">
                                            </div>
                                            <?php if ($highlights['image'] != "") { ?>
                                                <div class="form-group  col-md-4">
                                                    <label for="exampleInputBorder">Old Image</label>
                                                    <img src="<?= base_url($highlights['image']) ?>" style="width: 100px;">
                                                </div>
                                            <?php } ?>
                                            <div class="form-group  col-md-12 <?php echo  $highlights['image'] != "" ? "" : "d-none" ?> " id="image-div">
                                                <label for="exampleInputBorder">Select New Image</label>
                                                <div class="dropzone custom-dropzone" id="highlights-image">
                                                    <div class="dropzone-clickable-area">
                                                        <div class="icon"><i class="fi fi-br-upload"></i></div>
                                                        <p>Upload New Image </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button type="button" name="add_cat" id="add_cat" class="btn btn-primary" onclick="updateHighlight()">
                                            Update HighLight
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
    <script src="<?= base_url('/assets/page-script/highlights_edit.js') ?>"></script>

</body>

</html>