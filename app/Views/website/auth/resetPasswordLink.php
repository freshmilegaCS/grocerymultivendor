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
                    <p class="text-center text-xl md:text-3xl font-bold"><?php echo lang('website.change_your_password'); ?></p>
                    <p class="mt-2 text-center text-gray-500"><?php echo lang('website.enter_a_new_password_below_to_change_your_password'); ?></p>
                    <form class="flex flex-col pt-3 md:pt-8" id="resetPasswordLinkForm">
                    <input type="hidden" name="token" id="token" value="<?= esc($token) ?>" />
                    <input type="hidden" name="email" id="email" value="<?= esc($email) ?>" />
                        <!-- Password -->
                        <div class="flex flex-col pt-4">
                            <label class="text-gray-800 font-medium inline-block" for="password">
                            <?php echo lang('website.password'); ?>
                                <span class="text-red-600">*</span>
                            </label>
                            <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600 focus-within:shadow-[0_0_0_.25rem_rgba(10,173,10,.25)]">
                                <span class="px-3 text-gray-500">
                                    <i class="fi fi-tr-padlock-check"></i>
                                </span>
                                <input type="password" id="password" name="password" class="text-gray-900 focus:ring-0 focus:border-0 block p-2 w-full text-base border-0 disabled:opacity-50 disabled:pointer-events-none" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.password'); ?>" autocomplete="off">
                                <span class="px-3 text-gray-500 cursor-pointer" onclick="togglePasswordVisibility()">
                                    <i class="fi fi-tr-low-vision" id="togglePasswordIcon"></i>
                                </span>
                            </div>
                            <div id="passwordError" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="flex flex-col pt-4">
                            <label class="text-gray-800 font-medium inline-block" for="confirmPassword">
                            <?php echo lang('website.confirm_password'); ?>
                                <span class="text-red-600">*</span>
                            </label>
                            <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600 focus-within:shadow-[0_0_0_.25rem_rgba(10,173,10,.25)]">
                                <span class="px-3 text-gray-500">
                                    <i class="fi fi-tr-padlock-check"></i>
                                </span>
                                <input type="password" id="confirmPassword" name="confirmPassword" class="text-gray-900 focus:ring-0 focus:border-0 block p-2 w-full text-base border-0 disabled:opacity-50 disabled:pointer-events-none" placeholder="<?php echo lang('website.enter'); ?>  <?php echo lang('website.confirm_password'); ?>" autocomplete="off">
                                <span class="px-3 text-gray-500 cursor-pointer" onclick="toggleConfirmPasswordVisibility()">
                                    <i class="fi fi-tr-low-vision" id="toggleConfirmPasswordIcon"></i>
                                </span>
                            </div>
                            <div id="confirmPasswordError" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <div id="message" class="mt-2 mb-6"></div>
                        <button type="submit" class="w-full rounded-lg bg-green-700 px-4 py-2 text-center text-base font-semibold text-white shadow-md ring-gray-500 ring-offset-2 transition focus:ring-2"> <?php echo lang('website.change_password'); ?></button>
                    </form>

                </div>
            </div>
            <div class="pointer-events-none relative hidden select-none bg-white lg:block lg:w-1/2">

                <img class="absolute top-0 h-full w-full" src="<?= base_url('/assets/dist/img/reset-password-banner.svg') ?>" />
            </div>
        </div>

        <?= $this->include('website/template/mobileBottomMenu') ?>

    </main>
    <?= $this->include('website/template/script') ?>
    <script src="<?= base_url('/assets/page-script/website/resetPasswordLink.js') ?>"></script>

</body>

</html>