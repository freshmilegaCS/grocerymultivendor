<!-- Preloader -->
<div class="preloader flex-column justify-content-center align-items-center">
    <?php
    if ($settings['logo'] != null) {
    ?>
        <img class="animation__shake" src="<?php echo base_url($settings['logo']) ?>" alt="<?php echo  $settings['logo']; ?>" height="60" width="60">
    <?php
    }
    ?>
</div>

<!-- Navbar -->
<nav class="main-header navbar navbar-expand border-bottom-0 <?php if ($settings['thememode'] == 'Light') {
                                                                    echo "navbar-light navbar-white";
                                                                } else {
                                                                    echo "navbar-dark";
                                                                } ?>  text-sm">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fi fi-br-bars-staggered"></i></a>
        </li>
        <li class="nav-item dropdown ">
            <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="true">
                <i class="fi fi-br-order-history"></i> Orders&nbsp;
                <span class="badge badge-danger "><?php echo orderCountStatusWiseForSeller(2) ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right " style="left: inherit; right: 0px;">
                <a href="/seller/orders" class="dropdown-item nav-dropdowm-item">
                    <div class="media-body">
                        <h3 class="dropdown-item-title">
                            All Orders
                        </h3>
                    </div>
                </a>
                <div class="dropdown-divider"></div>
                <a href="/seller/orders?status=1" class="dropdown-item nav-dropdowm-item">
                    <div class="media-body">
                        <h3 class="dropdown-item-title">
                            Pending Order
                        </h3>
                    </div>
                </a>
                <div class="dropdown-divider"></div>
                <a href="/seller/orders?status=6" class="dropdown-item nav-dropdowm-item">
                    <div class="media-body">
                        <h3 class="dropdown-item-title">
                            Delivered Order
                        </h3>
                    </div>
                </a>
                <div class="dropdown-divider"></div>
            </div>
        </li>
        <li class="nav-item d-none d-md-block">
            <a class="nav-link" href="/seller/return-request">
                <i class="fi fi-br-member-list"></i> Return Order
            </a>
        </li>
        <li class="nav-item d-none d-md-block">
            <a class="nav-link" href="/seller/wallet-transaction">
                <i class="fi fi-br-refund-alt"></i>Wallet Transaction
            </a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item d-none d-md-block">
            <a class="nav-link" title="Website" href="<?= base_url() ?>" target="_blank">
                <i class="fi fi-bs-site-alt"></i>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" title="Change Password" data-tooltip="Change Password" href="/seller/auth/change-password" role="button">
                <i class="fi fi-bs-key"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" title="Logout" data-tooltip="Logout" data-controlsidebar-slide="true" href="/seller/auth/logout" role="button">
                <i class="fi fi-bs-power"></i>
            </a>
        </li>
    </ul>
</nav>