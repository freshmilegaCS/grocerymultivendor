<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?> | Reset Password</title>

    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-thin-rounded/css/uicons-thin-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-bold-straight/css/uicons-bold-straight.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-thin-straight/css/uicons-thin-straight.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('/assets/plugins/toastr/toastr.min.css') ?>">

    <!-- Theme style -->
    <link rel="stylesheet" href="<?= base_url('/assets/dist/css/adminlte.min.css') ?>">
    <style>
        .b-0 {
            border: 0;
            border-radius: 0;
        }

        .bb-1 {
            border-bottom: 1px solid #ced4da;
        }
    </style>

    <?php
    if ($settings['google_recaptcha_status'] == 1) { ?>
        <script src="https://www.google.com/recaptcha/api.js?render=<?= $settings['google_recaptcha_site_key'] ?>"></script>
    <?php }
    ?>

</head>

<body class="hold-transition  <?php echo $settings['thememode'] == 'Light' ? '' : 'dark-mode' ?>   text-sm">

    <section class="vh-100 bg-olive">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <h1>Reset Password</h1>
                <div class="col col-xl-10">
                    <div class="card" style="border-radius: 1rem;">
                        <div class="row g-0">
                            <div class="col-md-6 col-lg-5 d-none d-md-block">
                                <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-login-form/img1.webp"
                                    alt="login form" class="img-fluid" style="border-radius: 1rem 0 0 1rem;" />
                            </div>
                            <div class="col-md-6 col-lg-7 d-flex align-items-center">
                                <div class="card-body p-4 p-lg-3 text-black">

                                    <form>
                                        <?php
                                        if ($settings['google_recaptcha_status'] == 1) { ?>
                                            <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                                        <?php }
                                        ?>
                                        <input type="hidden" name="email" id="email" value="<?= $email ?>">
                                        <input type="hidden" name="token" id="token" value="<?= $token ?>">
                                        <div class="d-flex align-items-center mb-3 pb-1">
                                            <img src="<?= base_url($settings['logo']); ?>"
                                                alt="<?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?> Logo" class="img-fluid" style="border-radius: 1rem ; width: 60px;" />
                                            <span class="h3 fw-bold mb-0 text-dark"> <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></span>
                                        </div>

                                        <h5 class="fw-normal mb-3 pb-3 text-dark" style="letter-spacing: 1px;">Your'e resetting password.</h5>


                                        <div data-mdb-input-init class="form-outline mb-4">
                                            <label class="form-label text-dark" for="email">New Password</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white b-0 bb-1">
                                                        <i class="fi fi-rr-password-lock"></i>
                                                    </span>
                                                </div>
                                                <input type="password" id="pass" name="pass" autocomplete="new-password" class="form-control ">
                                            </div>
                                        </div>


                                        <div data-mdb-input-init class="form-outline mb-4">
                                            <label class="form-label text-dark" for="password">Confirm Password</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white b-0 bb-1">
                                                        <i class="fi fi-rr-password-lock"></i>
                                                    </span>
                                                </div>
                                                <input type="password" id="cpass" name="cpass" autocomplete="new-password" class="form-control ">
                                                <div class="input-group-append">
                                                    <div class="input-group-text bg-white b-0 bb-1" id="toggle-password" style="cursor: pointer;">
                                                        <i class="fi fi-rr-eye" id="password-icon"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="pt-1 mb-4">
                                            <button class="btn btn-primary btn-lg btn-block" type="button" id="submit_pass">Reset Password</button>
                                        </div>

                                        <div class="pt-1 mb-2">
                                            <button class="btn btn-primary btn-lg btn-block" href="/admin/auth/login">Login</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- jQuery -->
    <script src="<?= base_url('/assets/plugins/jquery/jquery.min.js') ?>"></script>
    <!-- Bootstrap 4 -->
    <script src="<?= base_url('/assets/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <!-- AdminLTE App -->
    <script src="<?= base_url('/assets/dist/js/adminlte.min.js') ?>"></script>
    <script src="<?= base_url('/assets/plugins/toastr/toastr.min.js') ?>"></script>

    <script type="text/javascript">
        <?php
        if ($settings['google_recaptcha_status'] == 1) { ?>
            $(document).ready(function() {
                grecaptcha.ready(function() {
                    grecaptcha.execute('<?= $settings['google_recaptcha_site_key'] ?>', {
                            action: 'submit'
                        })
                        .then(function(token) {
                            $("#recaptcha_token").val(token)
                        }).catch((error) => {
                            console.log(error);
                            e.preventDefault();
                            return false;
                        })
                })

            })
        <?php }
        ?>

        $('#submit_pass').on('click', function(ev) {
            var pass = $('#pass').val();
            var cpass = $('#cpass').val();
            if (pass == "") {
                alert("Enter Password");
                return false
            }
            if (pass == cpass) {} else {
                alert("Entered Password not matched. \nEnter correctly");
                return false
            }
            var email = $("#email").val();
            var token = $("#token").val();
            <?php
            if ($settings['google_recaptcha_status'] == 1) { ?>
                var recaptcha_token = $("#recaptcha_token").val()
            <?php } ?>
            $.ajax({
                url: "/admin/auth/reset-password",
                type: "POST",
                data: {
                    pass,
                    cpass,
                    email,
                    token,
                    <?php
                    if ($settings['google_recaptcha_status'] == 1) { ?>
                        recaptcha_token
                    <?php } ?>
                },
                dataType: "json",
                success: function(response) {
                    if (response.success == true) {
                        toastr.success(response.message, "Admin says");
                        setTimeout(function() {
                            location = "/admin/login"
                        }, 2500)
                    } else {
                        toastr.error(response.message, "Admin says");
                    }
                },
            });

        });
    </script>
</body>

</html>