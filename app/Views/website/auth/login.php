<!doctype html>
<html lang="<?= session()->get('site_lang') ?? 'en' ?>" dir="<?= dir_attribute() ?>">

<head>
    <?= $this->include('website/template/style') ?>
    <title><?= $settings['business_name'] ?></title>
</head>

<body class="bg-gray-100">
    <div class="block md:hidden">
        <?= $this->include('website/template/header') ?>

    </div>
    <main class="max-w-7xl mx-auto">

        <div class="flex flex-wrap md:h-screen">
            <div class="flex w-screen flex-col lg:w-1/2">

                <div class="md:mx-[15%] mx-[5%] my-auto flex flex-col justify-center lg:justify-start py-6">
                    <p class="text-center text-xl md:text-3xl font-bold"><?php echo lang('website.welcome_back_user'); ?></p>
                    <p class="mt-2 text-center text-gray-500"><?php echo lang('website.welcome_to'); ?> <?= $settings['business_name'] ?>. <?php echo lang('website.please_log_in_or_sign_up'); ?></p>
                    <?php
                    $loginSettings = json_decode($settings['social_login'], true);
                    if (!empty($loginSettings) && $loginSettings[0]['login_medium'] === 'google' && $loginSettings[0]['status'] === '1'): ?>
                        <a href="<?= $authUrl ?>" id="login_with_google_link" class="-2 mt-8 flex items-center justify-center rounded-md bg-white shadow-sm border border-green-500 px-4 py-1 outline-none transition hover:border-transparent hover:bg-green-700 hover:text-white">
                            <img class="mr-2 h-5" src="<?= base_url() ?>/assets/dist/img/google-icon.svg" alt /> <?php echo lang('website.log_in_with_google'); ?>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($loginSettings) && $loginSettings[0]['login_medium'] === 'google' && $loginSettings[0]['status'] === '1' && $settings['phone_login'] === '1'): ?>
                        <div class="relative mt-8 flex h-px place-items-center bg-green-200">
                            <div class="absolute left-1/2 h-6 w-14 -translate-x-1/2 bg-white text-center text-sm text-gray-500"><?php echo lang('website.or'); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php
                    if ($settings['phone_login'] === '1'): ?>
                        <a href="/mobileLogin" id="" class="-2 mt-8 flex items-center justify-center rounded-md bg-white shadow-sm border border-green-500 px-4 py-1 outline-none transition hover:border-transparent hover:bg-green-700 hover:text-white">
                            <img class="mr-2 h-5" src="<?= base_url() ?>/assets/dist/img/mobile-icon.png" alt /> <?php echo lang('website.log_in_with_mobile'); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($settings['phone_login'] === '1' && $settings['direct_login'] == 1): ?>
                        <div class="relative mt-8 flex h-px place-items-center bg-green-200">
                            <div class="absolute left-1/2 h-6 w-14 -translate-x-1/2 bg-white text-center text-sm text-gray-500"><?php echo lang('website.or'); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($loginSettings) && $loginSettings[0]['login_medium'] === 'google' && $loginSettings[0]['status'] === '1' && $settings['phone_login'] === '0' && $settings['direct_login'] == 1): ?>
                        <div class="relative mt-8 flex h-px place-items-center bg-green-200">
                            <div class="absolute left-1/2 h-6 w-14 -translate-x-1/2 bg-white text-center text-sm text-gray-500"><?php echo lang('website.or'); ?></div>
                        </div>
                    <?php endif; ?>


                    <?php if ($settings['direct_login'] == 1): ?>
                        <form class="flex flex-col pt-3 md:pt-8">
                            <div class="flex flex-col pt-4">
                                <label class="text-gray-800 font-medium inline-block" for="email">
                                    <?php echo lang('website.email'); ?>
                                    <span class="text-red-600">*</span>
                                </label>
                                <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600 focus-within:shadow-[0_0_0_.25rem_rgba(10,173,10,.25)]">
                                    <span class="px-3 text-gray-500">
                                        <i class="fi fi-tr-envelopes"></i>
                                    </span>
                                    <input type="email" id="email" name="email" class="text-gray-900 focus:ring-0 focus:border-0 block p-2 w-full text-base border-0 disabled:opacity-50 disabled:pointer-events-none" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.email'); ?>">
                                </div>
                                <div id="emailError" class="text-red-500 text-sm mt-1 hidden"></div>
                            </div>
                            <div class=" flex flex-col pt-4">
                                <label class="text-gray-800 font-medium inline-block" for="password">
                                    <?php echo lang('website.password'); ?>
                                    <span class="text-red-600">*</span>
                                </label>
                                <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600 focus-within:shadow-[0_0_0_.25rem_rgba(10,173,10,.25)]">
                                    <span class="px-3 text-gray-500">
                                        <!-- Lock Icon -->
                                        <i class="fi fi-tr-padlock-check"></i>
                                    </span>
                                    <input type="password" id="password" name="password" class="text-gray-900 focus:ring-0 focus:border-0 block p-2 w-full text-base border-0 disabled:opacity-50 disabled:pointer-events-none" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.password'); ?>">
                                    <span class="px-3 text-gray-500 cursor-pointer" onclick="togglePasswordVisibility()">
                                        <!-- Eye Icon for Show/Hide Password -->
                                        <i class="fi fi-tr-low-vision" id="togglePasswordIcon"></i>
                                    </span>
                                </div>
                                <div id="passwordError" class="text-red-500 text-sm mt-1 hidden"></div>
                            </div>
                            <div id="message" class="mt-2"></div>

                            <div class="text-right mb-6 pt-4">
                                <a href="/resetPassword" class="underline-offset-4 font-semibold text-green-700 underline"><?php echo lang('website.forget_password'); ?></a>
                            </div>
                            <button type="submit" class="w-full rounded-lg bg-green-700 px-4 py-2 text-center text-base font-semibold text-white shadow-md ring-gray-500 ring-offset-2 transition focus:ring-2">Log in</button>
                        </form>
                        <div class="pt-6 text-center">
                            <p class="whitespace-nowrap text-gray-600">
                                <?php echo lang('website.dont_have_an_account'); ?>
                                <a href="/signup" class="underline-offset-4 font-semibold text-green-700 underline"><?php echo lang('website.sign_up_for_free'); ?></a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="pointer-events-none relative hidden select-none bg-white lg:block lg:w-1/2">

                <img class="absolute top-0 h-full w-full" src="<?= base_url('/assets/dist/img/login-banner.svg') ?>" />
            </div>
        </div>

        <?= $this->include('website/template/mobileBottomMenu') ?>

    </main>
    <?= $this->include('website/template/script') ?>
    <script src="<?= base_url('/assets/page-script/website/login.js') ?>"></script>

</body>

</html>