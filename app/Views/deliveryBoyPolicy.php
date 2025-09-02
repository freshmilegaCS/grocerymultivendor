<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delivery App Policy | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

    <?= $this->include('template/style') ?>
    <!-- summernote -->
    <link rel="stylesheet" href="<?= base_url('/assets/plugins/summernote/summernote-bs4.min.css') ?>">
</head>

<body class="sidebar-mini accent control-sidebar-slide-open text-sm  layout-fixed <?php echo  $settings['thememode'] == 'Light' ? '' : 'dark-mode'; ?> layout-navbar-fixed text-sm" id="body">
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
                    <form id="deliveryPolicyForm">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title">About Us</h5>
                                    </div>
                                    <div class="card-body">
                                        <textarea id="delivery_app_about" name="delivery_app_about"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title">Privacy Policy</h5>
                                    </div>
                                    <div class="card-body">
                                        <textarea id="delivery_app_privacy_policy" name="editordata"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title">Terms & Condition</h5>
                                    </div>
                                    <div class="card-body">
                                        <textarea id="delivery_app_terms_policy" name="editordata"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <button type="submit" class="mt-1 btn btn-primary">Update Policy</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
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
    <script src="<?= base_url('/assets/plugins/summernote/summernote.min.js') ?>"></script>
    <script>
        $(function() {
            $('#delivery_app_about').summernote({
                placeholder: 'Enter About App',
                tabsize: 2,
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                ],
            });

            $('#delivery_app_privacy_policy').summernote({
                placeholder: 'Enter Privacy policy',
                tabsize: 2,
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                ],
            });
            $('#delivery_app_terms_policy').summernote({
                placeholder: 'enter Terms & Condition',
                tabsize: 2,
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                ],
            });
            $('#delivery_app_about').summernote('code', '<?php echo str_replace("'", "’",str_replace(array("\r", "\n", ), "",str_replace(PHP_EOL, "", $settings['delivery_app_about'])))  ?>');
            $('#delivery_app_privacy_policy').summernote('code', '<?php echo str_replace("'", "’",str_replace(array("\r", "\n", ), "",str_replace(PHP_EOL, "", $settings['delivery_app_privacy_policy'])))  ?>');
            $('#delivery_app_terms_policy').summernote('code', '<?php echo str_replace("'", "’",str_replace(array("\r", "\n", ), "",str_replace(PHP_EOL, "", $settings['delivery_app_terms_policy'])))  ?>');
        });
        $("#deliveryPolicyForm").on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData();
            formData.append('delivery_app_about', $('#delivery_app_about').summernote('code'));
            formData.append('delivery_app_privacy_policy', $('#delivery_app_privacy_policy').summernote('code'));
            formData.append('delivery_app_terms_policy', $('#delivery_app_terms_policy').summernote('code'));
            $.ajax({
                url: '/admin/setting/update-delivery-policy',
                method: 'POST',
                dataType: 'JSON',
                data: formData,
                processData: false, // Prevent jQuery from automatically transforming the data
                contentType: false, // Set contentType to false for FormData
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message, "Admin says");
                    } else {
                        toastr.error(response.message, "Admin says");
                    }
                },
                error: function(err) {
                    console.error('Error submitting form:', err);
                }
            });
        })
    </script>
</body>

</html>