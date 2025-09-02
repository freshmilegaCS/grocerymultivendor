<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?> | Log in</title>

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

    .auth-page {
      display: flex;
      min-height: 100vh;
      margin: 0;
      background-color: #f4f6f9;
    }

    .login-left {
      display: flex;

      background-size: cover;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      color: #333;
      padding: 20px;
    }

    .login-left h1 {
      font-size: 2.5rem;
      color: #00897B;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .login-left p {
      font-size: 1.5rem;
      font-weight: 300;
      line-height: 1.5;
    }

    .login-left p strong {
      color: #00897B;
    }

    .login-right {
      display: flex;

      justify-content: center;
      align-items: center;
      background: #fff;
      padding: 40px;
    }

    .login-card {
      width: 100%;
      max-width: 400px;
    }
  </style>
  <?php
  if ($settings['google_recaptcha_status'] == 1) { ?>
    <script src="https://www.google.com/recaptcha/api.js?render=<?= $settings['google_recaptcha_site_key'] ?>"></script>
  <?php }
  ?>
</head>

<body class="hold-transition  <?php echo $settings['thememode'] == 'Light' ? '' : 'dark-mode' ?>   text-sm">

  <div class="auth-page row">
    <!-- Left Section -->
    <div class="login-left col-md-7">
      <h1><?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></h1>
      <img src="/assets/dist/img/login.png" alt="" style="width: 50%; display: flex;   justify-content: center; align-items: center;">
    </div>

    <!-- Right Section -->
    <div class="login-right col-md-5">
      <div class=" login-card">
        <div class=" login-card-body">
          <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
              <h5><i class="icon fas fa-ban"></i> Alert!</h5>
              <?= session()->getFlashdata('error') ?>
            </div>
          <?php endif; ?>
          <h2 class=" mb-3">Seller Signin</h2>
          <p class="">Welcome back! Login to your panel.</p>
          <form action="/seller/auth/processLogin" id="loginForm" method="post">
            <?php
            if ($settings['google_recaptcha_status'] == 1) { ?>
              <input type="hidden" name="recaptcha_token" id="recaptcha_token">
            <?php }
            ?>
            <div class="form-group">
              <label for="email">Your Email</label>
              <div class="input-group">
                <input type="email" id="email" name="email" class="form-control" placeholder="Your Email" required>
                <div class="input-group-append">
                  <span class="input-group-text"><i class="fi fi-rr-user"></i></span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="password">Password</label>
              <div class="input-group">
                <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                <div class="input-group-append">
                  <span class="input-group-text" id="toggle-password" style="cursor: pointer;"><i class="fi fi-rr-eye" id="password-icon"></i></span>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-6">
                <div class="icheck-primary">
                  <input type="checkbox" id="remember">
                  <label for="remember">Remember me</label>
                </div>
              </div>
              <div class="col-6 text-right">
                <p class="" style="color: #393f81;"> <button id="forgotpassword" class="btn p-0" type="button" onclick="forgotPassword()"
                    style="color: #00897B; font-weight:bold; text-decoration:underline">Forgot Password <i class="fi fi-bs-key"></i></button></p>
              </div>
            </div>
            <div class="form-group mt-3">
              <button type="submit" class="btn btn-primary btn-block">Login</button>
            </div>
            <div class="form-group mt-3">
              <a href="/admin/auth/login" class="btn btn-primary btn-block">Admin Login</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- jQuery -->
  <script src="<?= base_url('/assets/plugins/jquery/jquery.min.js') ?>"></script>
  <!-- Bootstrap 4 -->
  <script src="<?= base_url('/assets/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
  <!-- AdminLTE App -->
  <script src="<?= base_url('/assets/dist/js/adminlte.min.js') ?>"></script>
  <script src="<?= base_url('/assets/page-script/login.js') ?>"></script>
  <script src="<?= base_url('/assets/plugins/toastr/toastr.min.js') ?>"></script>

  <script>
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

    function forgotPassword() {
      var email = $("#email").val();
      <?php
      if ($settings['google_recaptcha_status'] == 1) { ?>
        var recaptcha_token = $("#recaptcha_token").val()
      <?php } ?>

      $.ajax({
        url: "/seller/auth/forgot-password/send-link",
        type: "POST",
        data: {
          email,
          <?php
          if ($settings['google_recaptcha_status'] == 1) { ?>
            recaptcha_token
          <?php } ?>
        },
        dataType: "json",
        beforeSend: function() {
          toastr.info('Sending mail', "Admin says");

        },
        success: function(response) {
          if (response.success == true) {
            toastr.success(response.message, "Admin says");
          } else {
            toastr.error(response.message, "Admin says");
          }

        },
      });
    }
  </script>
</body>

</html>