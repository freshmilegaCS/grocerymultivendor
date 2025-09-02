<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit FAQ | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

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
                            <h1 class="m-0">Edit FAQ</h1>
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
                                    <h3 class="card-title"> Edit FAQ</h3>
                                </div>
                                <!-- /.card-header -->
                                <form method="post" id="faqForm" enctype="multipart/form-data">
                                    <input type="hidden" name="edit_id" id="edit_id" value="<?= $faq['id'] ?>">
                                    <div class="card-body">

                                        <div class="form-group">
                                            <label for="exampleInputBorder">FAQ Question</label>
                                            <input type="text" class="form-control " name="question" id="question" value="<?= $faq['question'] ?>" placeholder="Enter FAQ Question">
                                        </div>

                                        <div class="form-group">
                                            <label for="exampleInputBorder">FAQ Answer</label>
                                            <input type="text" class="form-control " name="answer" id="answer" value="<?= $faq['answer'] ?>" placeholder="Enter FAQ Answer">
                                        </div>

                                    </div>
                                    <div class="card-footer">
                                        <button type="button" name="update_faq" id="update_faq" class="btn btn-primary" onclick="updateFaq()">
                                            Update FAQ
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
    <script src="<?= base_url('/assets/page-script/faq.js') ?>"></script>

</body>

</html>