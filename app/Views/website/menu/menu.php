<!doctype html>
<html lang="<?= session()->get('site_lang') ?? 'en' ?>" dir="<?= dir_attribute() ?>">

<head>
    <?= $this->include('website/template/style') ?>
    <title><?= $settings['business_name'] ?></title>
</head>

<body class="bg-gray-100">
    <?= $this->include('website/template/header') ?>
    <main class="max-w-7xl mx-auto">
        <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
            <!-- Profile Section -->


            <a href="<?= isset($email) || isset($mobile)  ? "/profile" : "/login" ?>" class="flex items-center justify-between space-x-4 <?= flex_direction() ?> bg-white rounded-lg p-4">
                <div class="flex space-x-4 <?= flex_direction() ?>">
                    <img class="w-12 rounded-full" src="<?php
                                                        echo isset($user)
                                                            ? (($user['login_type'] === 'normal')
                                                                ? (isset($user['img']) ? $user['img'] : base_url() . $settings['logo'])
                                                                : (isset($user['img']) ? $user['img'] : base_url() . $settings['logo']))
                                                            : base_url() . $settings['logo'];
                                                        ?>
" alt="" />

                    <div>
                        <?php if (isset($email) || isset($mobile)): ?>
                            <p class="text-lg font-semibold"><?php echo lang('website.hi'); ?>, <?= htmlspecialchars($name) ?></p>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($email) ?></p>
                        <?php else: ?>
                            <p class="text-lg font-semibold"><?php echo lang('website.hi'); ?>, <?php echo lang('website.user'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <i class="fi fi-rr-pen-circle"></i>
                </div>

            </a>




            <!-- Quick Links -->
            <?php if (isset($email) || isset($mobile)): ?>
                <div class="grid grid-cols-2 gap-4 mt-2 bg-white rounded-lg p-4">
                    <a href="/order-history" class="block border border-green-700 text-center py-3 rounded-lg shadow-sm">
                        <?php echo lang('website.order'); ?>
                    </a>
                    <a href="/address" class="block border border-green-700 text-center py-3 rounded-lg shadow-sm">
                        <?php echo lang('website.address'); ?>
                    </a>
                    <a href="/wallet" class="block border border-green-700 text-center py-3 rounded-lg shadow-sm">
                        <?php echo lang('website.wallet'); ?>
                    </a>
                    <a href="/profile" class="block border border-green-700 text-center py-3 rounded-lg shadow-sm">
                        <?php echo lang('website.profile'); ?>
                    </a>
                    <?php if ($settings['user_can_select_language'] == 1): ?>
                        <a href="/language" class="block border border-green-700 text-center py-3 rounded-lg shadow-sm">
                            <?php echo lang('website.language'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 gap-4 mt-2 bg-white rounded-lg p-4">
                    <a href="/login" class="block border border-green-700 text-center py-3 rounded-lg shadow-sm">
                        <?php echo lang('website.order'); ?>
                    </a>
                    <a href="/login" class="block border border-green-700 text-center py-3 rounded-lg shadow-sm">
                        <?php echo lang('website.address'); ?>
                    </a>
                    <a href="/login" class="block border border-green-700 text-center py-3 rounded-lg shadow-sm">
                        <?php echo lang('website.wallet'); ?>
                    </a>
                    <a href="/login" class="block border border-green-700 text-center py-3 rounded-lg shadow-sm">
                        <?php echo lang('website.profile'); ?>
                    </a>
                </div>
            <?php endif; ?>



            <!-- Menu List -->
            <ul class="mt-2 space-y-4 bg-white rounded-lg p-4">
                <?php if ($settings['user_can_select_language'] == 1): ?>
                    <li class="flex items-center space-x-4 <?= flex_direction() ?>">
                        <i class="fi fi-rr-language text-xl text-green-700"></i>
                        <a href="/language" class="text-gray-700 hover:text-green-700"><?php echo lang('website.language'); ?></a>
                    </li>
                <?php endif; ?>

                <li class="flex items-center space-x-4 <?= flex_direction() ?>">
                    <i class="fi fi-rr-envelope text-xl text-green-700"></i>
                    <a href="/contact-us" class="text-gray-700 hover:text-green-700"><?php echo lang('website.contact_us'); ?></a>
                </li>
                <li class="flex items-center space-x-4 <?= flex_direction() ?>">
                    <i class="fi fi-rr-user text-xl text-green-700"></i>
                    <a href="/about-us" class="text-gray-700 hover:text-green-700"><?php echo lang('website.about_us'); ?></a>
                </li>
                <li class="flex items-center space-x-4 <?= flex_direction() ?>">
                    <i class="fi fi-rr-lock text-xl text-green-700"></i>
                    <a href="/privacy-policy" class="text-gray-700 hover:text-green-700"><?php echo lang('website.privacy_policy'); ?></a>
                </li>
                <li class="flex items-center space-x-4 <?= flex_direction() ?>">
                    <i class="fi fi-rr-document text-xl text-green-700"></i>
                    <a href="/terms-condition" class="text-gray-700 hover:text-green-700"><?php echo lang('website.terms_condition'); ?></a>
                </li>
                <li class="flex items-center space-x-4 <?= flex_direction() ?>">
                    <i class="fi fi-rr-money text-xl text-green-700"></i>
                    <a href="/refund-policy" class="text-gray-700 hover:text-green-700"><?php echo lang('website.refund_policy'); ?></a>
                </li>
                <hr>

                <?php if (isset($email) || isset($mobile)): ?>
                    <li class="flex items-center space-x-4 <?= flex_direction() ?>">
                        <i class="fi fi-rr-sign-out-alt text-xl text-red-500"></i>
                        <a href="/logout" class="hover:text-red-600"><?php echo lang('website.logout'); ?></a>
                    </li>
                <?php else: ?>
                    <li class="flex items-center space-x-4 <?= flex_direction() ?>">
                        <i class="fi fi-rr-sign-in-alt text-xl text-red-500"></i>
                        <a href="/login" class="hover:text-red-600"><?php echo lang('website.login'); ?></a>
                    </li>
                <?php endif; ?>

            </ul>
        </section>


        <?= $this->include('website/template/mobileBottomMenu') ?>
        <?= $this->include('website/template/productVarientPopup') ?>

    </main>
    <?= $this->include('website/template/shopCart') ?>
    <?= $this->include('website/template/footer') ?>
    <?= $this->include('website/template/script') ?>
</body>

</html>