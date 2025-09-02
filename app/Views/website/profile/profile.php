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
            <div class="row bg-white mb-2 p-4 rounded-lg">
                <div class="flex justify-between">
                    <h2 class="text-lg font-medium z-10"><?php echo lang('website.profile'); ?></h2>
                </div>
            </div>
        </section>
        <section class="mt-2 md:mt-4 md:container md:mx-auto md:px-3">
            <div class="flex flex-wrap lg:flex-nowrap lg:gap-x-6 gap-y-6">
                <?= $this->include('website/template/dashboardSidebar') ?>
                <div class="w-full lg:w-full md:w-full mx-auto">
                    <div class="w-full rounded-2xl border border-gray-100 bg-white p-4">
                        <form class="flex md:flex-wrap lg:flex-nowrap gap-4 flex-col needs-validation updateProfile" novalidate="">
                            <!-- input -->
                            <div class="flex flex-col md:flex-row gap-3">
                                <div class="w-full flex flex-col gap-2">
                                    <label class="text-gray-800 font-medium inline-block" for="contactFName">
                                    <?php echo lang('website.name'); ?>
                                        <span class="text-red-600">*</span>
                                    </label>
                                    <input type="text" id="contactFName" value="<?= $user_name ?>" class="form-control border border-gray-300 text-gray-900 rounded-lg focus:shadow-[0_0_0_.25rem_rgba(10,173,10,.25)] focus:ring-green-600 focus:ring-0 focus:border-green-600 block p-2 px-3 disabled:opacity-50 disabled:pointer-events-none w-full text-base" name="contactFName" placeholder="Enter <?php echo lang('website.name'); ?>" required="" >
                                </div>
                            </div>

                            <div class="flex flex-col md:flex-row gap-3">
                                <div class="w-full md:w-1/2 flex flex-col gap-2">
                                    <label class="text-gray-800 font-medium inline-block" for="contactEmail">
                                    <?php echo lang('website.email'); ?>
                                        <span class="text-red-600">*</span>
                                        <?php if ($is_email_verified == 1) {
                                        ?>
                                            <span class="text-blue-700 text-sm border border-blue-600 rounded-lg p-[2px]"><i class="fi fi-rr-shield-trust"></i> Verified</span>
                                        <?php
                                        } ?>
                                    </label>
                                    <input type="email" id="contactEmail" <?php if ($is_email_verified == 1) {
                                                                                echo 'readonly';
                                                                            } ?> value="<?= $user_email ?>" name="contactEmail" class="form-control border border-gray-300 text-gray-900 rounded-lg focus:shadow-[0_0_0_.25rem_rgba(10,173,10,.25)] focus:ring-green-600 focus:ring-0 focus:border-green-600 block p-2 px-3 disabled:opacity-50 disabled:pointer-events-none w-full text-base" placeholder="Enter <?php echo lang('website.email'); ?>" required="">
                                    <span id="emailError" class="text-red-500 text-sm hidden"></span>

                                </div>

                                <div class="w-full md:w-1/2 flex flex-col gap-2">
                                    <!-- input -->
                                    <label class="text-gray-800 font-medium inline-block" for="contactPhone"><?php echo lang('website.phone'); ?> <span class="text-red-600">*</span>
                                        <?php if ($is_mobile_verified == 1) {
                                        ?>
                                            <span class="text-blue-700 text-sm border border-blue-600 rounded-lg p-[2px]"><i class="fi fi-rr-shield-trust"></i> Verified</span>
                                        <?php
                                        } ?>
                                    </label>
                                    <input type="text" id="contactPhone" <?php if ($is_mobile_verified == 1) {
                                                                                echo 'readonly';
                                                                            } ?> name="contactPhone" value="<?= $user_mobile ?>" class="form-control border border-gray-300 text-gray-900 rounded-lg focus:shadow-[0_0_0_.25rem_rgba(10,173,10,.25)] focus:ring-green-600 focus:ring-0 focus:border-green-600 block p-2 px-3 disabled:opacity-50 disabled:pointer-events-none w-full text-base" placeholder="Your <?php echo lang('website.phone'); ?>" required="" >
                                    <span id="phoneError" class="text-red-500 text-sm hidden"></span>
                                </div>
                            </div>

                            <div class="w-full flex flex-row gap-2">
                                <!-- btn -->
                                <button type="submit" class="p-2 rounded-lg btn inline-flex items-center gap-x-2 bg-green-600 text-white border-green-600 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 active:bg-green-700 active:border-green-700 focus:outline-none focus:ring-4 focus:ring-green-300">
                                    <?php echo lang('website.update_profile'); ?>
                                </button>
                            </div>
                        </form>
                    </div>

                    <?php if ($user['login_type'] != 'google'): ?>
                        <div class="w-full rounded-2xl border border-gray-100 bg-white p-4 mt-2">
                            <h5 class="text-xl font-semibold mb-4"><?php echo lang('website.change_password'); ?></h5>

                            <div class="flex md:flex-wrap lg:flex-nowrap gap-4 flex-col">
                                <div class="flex flex-col md:flex-row gap-3">
                                    <div class="w-full md:w-1/2 flex flex-col gap-2">
                                        <label class="text-gray-800 font-medium inline-block" for="contactPassword"><?php echo lang('website.password'); ?><span class="text-red-600">*</span></label>
                                        <input type="password" id="contactPassword" name="contactPassword"
                                            class="form-control border border-gray-300 text-gray-900 rounded-lg focus:shadow-[0_0_0_.25rem_rgba(10,173,10,.25)] focus:ring-green-600 focus:ring-0 focus:border-green-600 block p-2 px-3 disabled:opacity-50 disabled:pointer-events-none w-full text-base"
                                            placeholder="<?php echo lang('website.password'); ?>" required>
                                        <div id="passwordError" class="text-red-500 text-sm mt-1" style="display: none;"></div>
                                    </div>
                                    <div class="w-full md:w-1/2 flex flex-col gap-2">
                                        <label class="text-gray-800 font-medium inline-block" for="contactConfirempassword"><?php echo lang('website.confirm_password'); ?></label>
                                        <input type="password" id="contactConfirempassword" name="contactConfirempassword"
                                            class="form-control border border-gray-300 text-gray-900 rounded-lg focus:shadow-[0_0_0_.25rem_rgba(10,173,10,.25)] focus:ring-green-600 focus:ring-0 focus:border-green-600 block p-2 px-3 disabled:opacity-50 disabled:pointer-events-none w-full text-base"
                                            placeholder="<?php echo lang('website.confirm_password'); ?>" required>
                                    </div>
                                </div>

                                <div class="w-full flex flex-row gap-2">
                                    <button type="button" id="updatePasswordButton"
                                        class="p-2 rounded-lg btn inline-flex items-center gap-x-2 bg-green-600 text-white border-green-600 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 active:bg-green-700 active:border-green-700 focus:outline-none focus:ring-4 focus:ring-green-300">
                                        <?php echo lang('website.update_pssword'); ?>
                                    </button>
                                </div>
                            </div>

                        </div>
                    <?php endif; ?>

                    <div class="w-full rounded-2xl border border-gray-100 bg-white p-4 mt-2">
                        <!-- Heading -->
                        <h5 class="text-xl font-semibold mb-4"><?php echo lang('website.delete_account'); ?></h5>

                        <!-- Description -->
                        <p class="mb-2 text-gray-600"><?php echo lang('website.would_you_like_to_delete_your_account'); ?></p>
                        <p class="mb-5 text-gray-600">
                        <?php echo lang('website.deleting_your_account_will_remove_all_the_order_details_associated_with_it'); ?>
                        </p>

                        <!-- Button -->
                        <button onclick="deleteAccount()" href="#" class="inline-block px-4 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition">
                        <?php echo lang('website.i_want_to_delete_my_account'); ?>
                        </button>
                    </div>

                </div>
            </div>
        </section>


        <?= $this->include('website/template/mobileBottomMenu') ?>
    </main>
    <?= $this->include('website/template/shopCart') ?>
    <?= $this->include('website/template/footer') ?>
    <?= $this->include('website/template/script') ?>


    <?= $this->include('website/template/profileScript') ?>
 
</body>

</html>