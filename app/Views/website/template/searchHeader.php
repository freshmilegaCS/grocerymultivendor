<header class="bg-white">
    <div class="border-b">
        <div class="p-3">
            <div class="container md:mx-auto">
                <div class="flex items-center w-full space-x-4 <?= flex_direction() ?>">
                    <!-- Logo -->
                    <div>
                        <a href="/">
                            <img src="<?= base_url($settings['logo']) ?>" class="rounded-lg w-8 drop-shadow" alt="<?= $settings['business_name'] ?>" />
                        </a>
                    </div>
                    <div class="flex w-full lg:items-center lg:justify-center space-x-4 <?= flex_direction() ?>">
                        <!-- Search Bar -->
                        <form action="#" class="w-full">
                            <div class="relative">
                                <!-- Search Icon Button on the Left -->
                                <button class="absolute left-3 top-2" type="button">
                                    <i class="fi fi-tr-issue-loupe text-lg"></i>
                                </button>

                                <!-- Search Input -->
                                <input type="search" onkeyup="searchProducts(this.value)" placeholder="<?php echo lang('website.search_for_products');?>" class="w-full pl-10 p-2 border bg-gray-100 rounded-lg text-gray-900">
                            </div>
                        </form>

                        <button type="button" class="text-gray-600 relative" onclick="toggleShoppingCart()">
                            <i class="fi fi-tr-cart-shopping-fast text-2xl"></i>
                            <span id="cartCount"
                                class="absolute top-0 -mt-1 left-full rounded-full h-4 w-4 -ml-3 bg-green-600 text-white text-center font-semibold text-xs">
                                <?=$cartItemCount?>
                            </span>
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </div> 
</header>