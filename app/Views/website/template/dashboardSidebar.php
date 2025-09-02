<div class="lg:w-1/6 w-full">
    <div class="flex flex-col gap-1">
        <div class="hidden lg:block">
            <div class="py-6 lg:rounded-2xl lg:shadow-card bg-white">
                <div class="flex flex-col items-center justify-center pb-3 mb-2 border-b">
                    <a href="/dashboard" class="router-link-exact-active w-20 h-20 mb-3 rounded-full border border-primary">
                        <img id="profilePic" src="<?php
                                                    echo isset($user)
                                                        ? (
                                                            $user['login_type'] === 'mobile'
                                                            ? (isset($user['img']) ? $user['img'] : base_url() . $settings['logo']) // mobile login
                                                            : (
                                                                $user['login_type'] === 'google'
                                                                ? $user['img'] // google login
                                                                : base_url() . $settings['logo'] // other login types
                                                            )
                                                        )
                                                        : base_url() . $settings['logo']; // no user
                                                    ?>" alt="avatar" class="w-full h-full object-cover rounded-full border-2 border-white">


                    </a>
                    <?php if ($user['login_type'] === 'normal' || $user['login_type'] === 'mobile'): ?>
                        <div class="absolute bg-white border-1 rounded-full cursor-pointer" onclick="document.getElementById('profilePicInput').click();">
                            <i class="fi fi-rr-pen-circle"></i>
                        </div>
                        <input id="profilePicInput" name="profilePicInput" type="file" class="hidden" accept=".png, .jpg, .jpeg" onchange="uploadUserProfilePic(event)">
                    <?php endif; ?>

                    <h3 class="capitalize text-lg font-semibold text-center mb-0.5"><?= $user_name ?></h3>
                    <?php
                    if ($user_mobile != ''):
                    ?>
                        <p class="text-center text-text"><?= $country['country_code'] . ' ' . $user_mobile ?></p>
                    <?php endif; ?>
                </div>
                <nav class="flex flex-col">
                    <a href="/dashboard" class="font-normal flex items-center gap-4 capitalize py-2 px-4 group hover:text-primary transition-all duration-500">
                        <i class="fi fi-rr-dashboard"></i><span><?php echo lang('website.overview'); ?></span>
                    </a>
                    <a href="/order-history" class="active router-link-exact-active font-normal flex items-center gap-4 capitalize py-2 px-4 group hover:text-primary transition-all duration-500" aria-current="page">
                        <i class="fi fi-rr-order-history"></i><span><?php echo lang('website.order_history'); ?></span>
                    </a>
                    <a href="/address" class="font-normal flex items-center gap-4 capitalize py-2 px-4 group hover:text-primary transition-all duration-500">
                        <i class="fi fi-rr-marker"></i><span><?php echo lang('website.address'); ?></span>
                    </a>
                    <a href="/profile" class="font-normal flex items-center gap-4 capitalize py-2 px-4 group hover:text-primary transition-all duration-500">
                        <i class="fi fi-rr-circle-user"></i><span><?php echo lang('website.account'); ?></span>
                    </a>
                    <a href="/wallet" class="font-normal flex items-center gap-4 capitalize py-2 px-4 group hover:text-primary transition-all duration-500">
                        <i class="fi fi-rr-wallet"></i><span><?php echo lang('website.wallet'); ?></span>
                    </a>
                    <?php if ($settings['user_can_select_language'] == 1): ?>
                        <a href="/language" class="font-normal flex items-center gap-4 capitalize py-2 px-4 group hover:text-primary transition-all duration-500">
                            <i class="fi fi-rr-language"></i><span><?php echo lang('website.language'); ?></span>
                        </a>
                    <?php endif; ?>

                    <a href="/logout" class="font-normal flex items-center gap-4 capitalize py-2 px-4 group hover:text-primary transition-all duration-500">
                        <i class="fi fi-rr-sign-out-alt"></i><span><?php echo lang('website.sign_out'); ?></span>
                    </a>
                </nav>
            </div>
        </div>
    </div>
</div>