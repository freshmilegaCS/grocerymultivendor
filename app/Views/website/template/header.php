<div id="pageLoader" class="fixed inset-0 w-full h-full bg-white/95 z-[9999] flex justify-center items-center font-sans">
    <div class="text-center w-full max-w-xs">
        <div class="relative h-32 mb-5">
            <!-- Cart Icon -->
            <div class="absolute left-[41%] -translate-x-1/2 w-14 h-14 animate-bounce">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6 2L3 6V20C3 20.5304 3.21071 21.0391 3.58579 21.4142C3.96086 21.7893 4.46957 22 5 22H19C19.5304 22 20.0391 21.7893 20.4142 21.4142C20.7893 21.0391 21 20.5304 21 20V6L18 2H6Z" stroke="#4CAF50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M3 6H21" stroke="#4CAF50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M16 10C16 11.0609 15.5786 12.0783 14.8284 12.8284C14.0783 13.5786 13.0609 14 12 14C10.9391 14 9.92172 13.5786 9.17157 12.8284C8.42143 12.0783 8 11.0609 8 10" stroke="#4CAF50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <!-- Produce Items -->
            <div class="absolute bottom-0 w-full flex justify-center gap-4">
                <!-- Apple -->
                <div class="fadeInDrop w-7 h-7 rounded-full bg-red-500 relative" style="animation-delay: 0.2s">
                    <div class="absolute -top-1 left-2 w-1 h-3 bg-green-500 rounded-sm -rotate-6"></div>
                </div>

                <!-- Banana -->
                <div class="fadeInDrop w-7 h-7 rounded-full bg-yellow-400 relative" style="animation-delay: 0.4s">
                    <div class="absolute top-3 -left-1 w-1 h-4 bg-amber-800 rounded-sm rotate-12"></div>
                </div>

                <!-- Carrot -->
                <div class="fadeInDrop w-7 h-7 rounded-full bg-orange-500 relative" style="animation-delay: 0.6s">
                    <div class="absolute -top-2 left-3 w-1 h-4 bg-lime-400 rounded-sm rotate-3"></div>
                </div>
            </div>
        </div>

        <p class="text-green-600 text-xl font-semibold mb-4"><?= $settings['website_loading_text'] ?></p>

        <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
            <div class="progress-bar h-full bg-gradient-to-r from-green-600 to-lime-500 rounded-full w-0"></div>
        </div>
    </div>
</div>

<header class="bg-white">
    <div class="border-b">
        <div class="p-3">
            <div class="container md:mx-auto max-w-[85rem]">
                <div class="flex items-center justify-between w-full">
                    <!-- Logo -->
                    <!--<div class="flex flex-row items-center space-x-4 <?= space_reverse() ?>">-->
                    <!--    <div>-->
                    <!--        <a href="/">-->
                    <!--            <img src="<?= base_url($settings['logo']) ?>" class="rounded-lg w-8 drop-shadow" alt="<?= $settings['business_name'] ?>" />-->
                    <!--        </a>-->
                    <!--    </div>-->
                    <!--    <div>-->
                    <!--        <div class="text-lg font-semibold text-gray-800"><?php echo lang('website.delivery_to'); ?></div>-->
                    <!--        <div class="flex items-center text-sm text-gray-600" onclick="openLocationModel()">-->
                    <!--            <span id="locationBarSubtitle"><?php echo lang('website.choose_location'); ?> </span><i class="fi fi-tr-caret-down"></i>-->
                    <!--        </div>-->
                    <!--    </div>-->
                    <!--</div>-->
                    
                    <div class="flex items-center space-x-3 <?= space_reverse() ?>">
    
                        <!-- Logo -->
                        <a href="/" class="flex-shrink-0">
                            <img src="<?= base_url($settings['logo']) ?>" 
                                 class="rounded-lg w-28 h-16 object-contain drop-shadow" 
                                 alt="<?= $settings['business_name'] ?>" />
                        </a>
                    
                        <!-- Delivery text -->
                        <div>
                            <div class="text-lg font-semibold text-gray-800">
                                <?php echo lang('website.delivery_to'); ?> <span id="proxyDeliveryTime"></span> <?php echo lang('website.minutes'); ?>
                            </div>
                            <div class="flex items-center text-sm text-gray-600 cursor-pointer" onclick="openLocationModel()">
                                <span id="locationBarSubtitle"><?php echo lang('website.choose_location'); ?></span>
                                <i class="fi fi-tr-caret-down ml-1"></i>
                            </div>
                        </div>
                    </div>


                    <div class="flex lg:w-3/5 lg:items-center lg:justify-center space-x-4 <?= flex_direction() ?>">
                        <!-- Search Bar -->
                        <a class="hidden md:block w-full" href="/search">
                            <div class="relative">
                                <!-- Search Icon on the Left -->
                                <button class="absolute left-3 top-2">
                                    <i class="fi fi-tr-issue-loupe text-lg"></i>
                                </button>

                                <!-- Dummy Search Input -->
                                <input type="search" placeholder="<?php echo lang('website.search_for_products'); ?>" readonly class="w-full pl-10 p-2 border rounded-lg text-gray-400 bg-gray-100">
                            </div>
                        </a>
                    </div>

                    <!-- Right Section (Cart & Profile) -->
                    <div class="flex items-center space-x-4 <?= flex_direction() ?> md:space-x-6">
                        <!-- Cart Button -->
                        <button type="button" class="text-gray-600 relative" onclick="toggleShoppingCart()">
                            <i class="fi fi-tr-cart-shopping-fast text-2xl"></i>
                            <span id="cartCount"
                                class="absolute top-0 -mt-1 left-full rounded-full h-4 w-4 -ml-3 bg-green-600 text-white text-center font-semibold text-xs">
                                <?= $cartItemCount ?>
                            </span>
                        </button>
                        <!-- Profile Button -->
                        <?php if ((session()->has('email') && session()->get('is_email_verified') == 1) || (session()->has('mobile') && session()->get('is_mobile_verified') == 1)) {
                        ?>
                            <div>
                                <a href="#!" class="flex dropdown-toggle text-reset flex items-center md:block hidden" id="dropdownUserLink">

                                    <img class="h-6 w-6 rounded-full ring-2 ring-white" src="<?php
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
                                                                                                ?>" alt="">
                                </a>
                                <ul class="dropdown-menu absolute bg-white border border-gray-300 mt-2 rounded-lg shadow-lg z-10 hidden <?= session()->get('is_rtl') ? 'xl:left-[9%] lg:left-[1%] md:left-[1%]' : 'xl:right-[9%] lg:right-[1%] md:right-[1%]' ?>" id="dropdownUser">
                                    <li>
                                        <a href="/order-history" class="dropdown-item block px-2 py-1 text-gray-700 hover:bg-gray-200 whitespace-nowrap"><i class="fi fi-rr-order-history"></i><?php echo lang('website.order'); ?></a>
                                    </li>
                                    <li>
                                        <a href="/profile" class="dropdown-item block px-2 py-1 text-gray-700 hover:bg-gray-200 whitespace-nowrap"><i class="fi fi-rr-circle-user"></i> <?php echo lang('website.account'); ?></a>
                                    </li>
                                    <li>
                                        <a href="/address" class="dropdown-item block px-2 py-1 text-gray-700 hover:bg-gray-200 whitespace-nowrap"><i class="fi fi-rr-marker"></i> <?php echo lang('website.address'); ?></a>
                                    </li>
                                    <li>
                                        <a href="/wallet" class="dropdown-item block px-2 py-1 text-gray-700 hover:bg-gray-200 whitespace-nowrap"><i class="fi fi-rr-wallet"></i> <?php echo lang('website.wallet'); ?></a>
                                    </li>
                                    <li>
                                        <a href="/logout" class="dropdown-item block px-2 py-1 text-gray-700 hover:bg-gray-200 whitespace-nowrap"><i class="fi fi-rr-sign-out-alt"></i> <?php echo lang('website.logout'); ?></a>
                                    </li>
                                </ul>
                            </div>
                        <?php
                        } else {
                        ?>
                            <a href="/login" class="hidden md:block" class="text-gray-600">
                                <i class="fi fi-tr-circle-user text-2xl"></i>
                            </a>
                        <?php
                        } ?>


                        <!-- Serach Button -->
                        <a href="/search" class="md:hidden" class="text-gray-600">
                            <i class="fi fi-tr-issue-loupe text-2xl"></i>
                        </a>


                    </div>
                </div>



            </div>
        </div>
    </div>
</header>