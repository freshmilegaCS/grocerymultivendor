<!doctype html>
<html lang="<?= session()->get('site_lang') ?? 'en' ?>" dir="<?= dir_attribute() ?>">

<head>
    <?= $this->include('website/template/style') ?>
    <title><?= $settings['business_name'] ?></title>
</head>

<body class="bg-white">
    <?= $this->include('website/template/header') ?>
    <main class="max-w-7xl mx-auto">
        
        
        <?php if (!empty($headerBanner)): ?>
            <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
                <div class="swiper-container swiper" data-pagination-type="" data-speed="400" data-space-between="100"
                    data-pagination="true" data-navigation="false" data-autoplay="true" data-autoplay-delay="3000"
                    data-effect="fade"
                    data-breakpoints='{"480": {"slidesPerView": 1}, "768": {"slidesPerView": 1}, "1024": {"slidesPerView": 1}}'>
                    <div class="swiper-wrapper">
                        <?php foreach ($headerBanner as $banner): ?>
                            <?php if (count($banner['firstSubcategory']) > 0): ?>
                                <a href="subcategory/<?= $banner['firstSubcategory']['slug'] ?>" class="swiper-slide">
                                    <img src="<?= esc($banner['banner_img']) ?>" class="rounded-lg w-full" />
                                </a>
                            <?php else: ?>
                                <a href="#" class="swiper-slide">
                                    <img src="<?= esc($banner['banner_img']) ?>" class="rounded-lg w-full" />
                                </a>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-navigation">
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
        
        <?php if (!empty($footerBanner)): ?>
            <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
                <div class="swiper-container swiper rounded-lg" data-pagination-type="" data-speed="400"
                    data-space-between="20" data-pagination="false" data-navigation="true" data-autoplay="true"
                    data-autoplay-delay="3000" data-effect="slide"
                    data-breakpoints='{"320": {"slidesPerView": 2}, "480": {"slidesPerView": 2}, "768": {"slidesPerView": 2}, "1024": {"slidesPerView": 3}}'>
                    <div class="swiper-wrapper">
                        <?php foreach ($footerBanner as $banner): ?>
                            <?php if (count($banner['firstSubcategory']) > 0): ?>
                                <a href="subcategory/<?= $banner['firstSubcategory']['slug'] ?>" class="swiper-slide">
                                    <img src="<?= esc($banner['banner_img']) ?>" class="rounded-lg w-full" />
                                </a>
                            <?php else: ?>
                                <a href="#" class="swiper-slide">
                                    <img src="<?= esc($banner['banner_img']) ?>" class="rounded-lg w-full" />
                                </a>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </section>
        <?php endif; ?>

        <!--<?php if (!empty($categories)): ?>-->
        <!--    <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">-->
        <!--        <div class="row bg-white p-4 rounded-t-lg">-->
        <!--            <div class="flex justify-between">-->
        <!--                <h2 class="text-lg font-medium z-10 flex">-->
        <!--                    <span class=""><?php echo lang('website.best_seller'); ?>&nbsp;</span><?php echo lang('website.categories'); ?>-->
        <!--                </h2>-->
        <!--            </div>-->
        <!--        </div>-->

        <!--        <div class="swiper-container swiper rounded-b-lg bg-white px-3"-->
        <!--            data-pagination-type=""-->
        <!--            data-speed="400"-->
        <!--            data-space-between="20"-->
        <!--            data-pagination="false"-->
        <!--            data-navigation="true"-->
        <!--            data-autoplay="true"-->
        <!--            data-autoplay-delay="3000"-->
        <!--            data-effect="slide"-->
        <!--            data-breakpoints='{"320": {"slidesPerView": 2},  "768": {"slidesPerView": 3}, "1024": {"slidesPerView": 6}}'>-->

        <!--            <div class="swiper-wrapper py-2 text-center">-->
                        <!--<?php foreach ($allBestsellerCategory as $category): ?>-->
        <!--                    <div class="swiper-slide">-->
        <!--                        <?php-->
        <!--                        $slug = !empty($category['firstSubcategory']) ? $category['firstSubcategory']['slug'] : 'no-product-avilable';-->
        <!--                        ?>-->
        <!--                        <a href="<?= base_url('subcategory/' . $slug) ?>" class="block bg-gray-100 p-2 rounded-lg hover:shadow-md transition">-->
        <!--                            <div class="rounded-lg p-2">-->
        <!--                                <div class="flex flex-wrap justify-center gap-2 mb-2">-->
        <!--                                    <?php if (!empty($category['images'])): ?>-->
        <!--                                        <?php foreach ($category['images'] as $index => $img): ?>-->
        <!--                                            <?php if ($index < 4): ?>-->
        <!--                                                <img src="<?= esc($img) ?>" alt="Product" class="w-12 h-12 rounded-xl object-cover" />-->
        <!--                                            <?php endif; ?>-->
        <!--                                        <?php endforeach; ?>-->
        <!--                                    <?php else: ?>-->
        <!--                                        <img src="<?= base_url('assets/images/no-image.png') ?>" alt="No Image" class="w-12 h-12 rounded-xl object-cover" />-->
        <!--                                    <?php endif; ?>-->
        <!--                                </div>-->

        <!--                                <div class="bg-white rounded-lg w-2/5 mx-auto -mt-3 border border-gray-200">-->
        <!--                                    <p class="text-[8px] text-gray-600 py-1 text-center">+<?= esc($category['total_count']) ?> more</p>-->
        <!--                                </div>-->

        <!--                                <p class="text-xs mt-2 font-medium text-center"><?= esc($category['category_name']) ?></p>-->
        <!--                            </div>-->
        <!--                        </a>-->
        <!--                    </div>-->
        <!--                <?php endforeach; ?>-->
        <!--            </div>-->

        <!--            <div class="swiper-pagination"></div>-->
        <!--        </div>-->
        <!--    </section>-->

        <!--<?php endif; ?>-->

        <?php if (!empty($categories)): ?>
    <?php 
    $rowsToShow = isset($settings['frontend_category_row_show']) ? (int)$settings['frontend_category_row_show'] : 1;
    $totalCategories = count($categories);
    $categoriesPerRow = ceil($totalCategories / $rowsToShow);
    $categoryChunks = array_chunk($categories, $categoriesPerRow);
    ?>
    <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
        <div class="row bg-white p-4 rounded-t-lg">
            <div class="flex justify-between">
                <h2 class="text-lg font-medium z-10 flex"><span class="hidden md:block"><?php echo lang('website.shop_by'); ?>&nbsp;</span><?php echo lang('website.categories'); ?></h2>
                <a href="/category" class="self-center text-sm"><?php echo lang('website.view_all'); ?> <i class="fi fi-tr-angle-small-right"></i></a>
            </div>
        </div>
        
        <?php foreach ($categoryChunks as $rowIndex => $categoryRow): ?>
            <div class="swiper-container swiper <?= $rowIndex === 0 ? 'rounded-b-lg' : 'rounded-lg ' ?> bg-white px-3"
                data-pagination-type=""
                data-speed="400"
                data-space-between="20"
                data-pagination="false"
                data-navigation="true"
                data-autoplay="true"
                data-autoplay-delay="3000"
                data-effect="slide"
                data-breakpoints='{"320": {"slidesPerView": 4}, "768": {"slidesPerView": 6}, "1024": {"slidesPerView": 8}}'>
                <div class="swiper-wrapper py-2 text-center">
                    <?php foreach ($categoryRow as $category): ?>
                        <div class="swiper-slide">
                            <?php if (!empty($category['firstSubcategory'])): ?>
                                <a href="subcategory/<?= $category['firstSubcategory']['slug'] ?>">
                                    <div class="flex flex-col justify-center items-center">
                                        <img src="<?= $category['category_img'] ?>" alt="<?= $category['category_name'] ?>" class="bg-[#edf8f1] rounded-lg" />
                                        <h6 class="text-sm font-semibold mt-2"><?= $category['category_name'] ?></h6>
                                    </div>
                                </a>
                            <?php else: ?>
                                <a href="/no-product-avilable">
                                    <div class="flex flex-col justify-center items-center">
                                        <img src="<?= $category['category_img'] ?>" alt="<?= $category['category_name'] ?>" class="bg-[#edf8f1] rounded-lg" />
                                        <h6 class="text-sm font-semibold mt-2"><?= $category['category_name'] ?></h6>
                                    </div>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

        <?php if (!empty($highlights)): ?>
            <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
                <div class="row bg-white p-4 rounded-t-lg">
                    <div class="flex justify-between">
                        <h2 class="text-lg font-medium z-10 flex"><span class="hidden md:block"></span><?php echo lang('website.highlights'); ?></h2>
                    </div>
                </div>
                <div class="swiper-container swiper rounded-b-lg bg-white px-3" data-pagination-type="" data-speed="400"
                    data-space-between="20" data-pagination="false" data-navigation="true" data-autoplay="true"
                    data-autoplay-delay="3000" data-effect="slide"
                    data-breakpoints='{"320": {"slidesPerView": 1}, "480": {"slidesPerView": 2}, "768": {"slidesPerView": 2}, "1024": {"slidesPerView": 3}}'>
                    <div class="swiper-wrapper py-4">
                        <?php foreach ($highlights as $item): ?>
                            <div class="swiper-slide w-[310px] mr-4 rounded-lg bg-white border overflow-hidden">
                                <div class="w-full rounded-lg overflow-hidden border border-gray-200">
                                    <?php if ($item['image'] === ''): ?>
                                        <!-- YouTube Embed -->
                                        <iframe
                                            class="w-full h-[200px] rounded-t-lg"
                                            src="https://www.youtube.com/embed/<?= esc($item['video']) ?>"
                                            title="YouTube video player"
                                            frameborder="0"
                                            allowfullscreen>
                                        </iframe>

                                        <a href="<?= base_url() . "seller/" . $item['seller_slug']; ?>"
                                            class="p-3 flex items-end justify-between block">
                                            <div class="flex-1 pr-3">
                                                <h3 class="text-base font-medium"><?= esc($item['title']) ?></h3>
                                                <p class="text-gray-500 text-sm line-clamp-2"><?= esc($item['description']) ?></p>
                                            </div>

                                            <div class="w-10 h-10 bg-green-700 rounded-lg flex items-center justify-center">
                                                <i class="fi fi-rr-arrow-right text-white text-lg pt-2"></i>
                                            </div>
                                        </a>
                                    <?php else: ?>
                                        <!-- Image -->
                                        <img loading="lazy" src="<?= esc($item['image']) ?>"
                                            alt="<?= esc($item['title']) ?>"
                                            class="w-full h-[180px] rounded-t-lg object-cover" />

                                        <a href="<?= base_url() . "seller" . $item['seller_slug']; ?>"
                                            class="p-3 flex items-end justify-between block">
                                            <div class="flex-1 pr-3">
                                                <h3 class="text-base font-medium"><?= esc($item['title']) ?></h3>
                                                <p class="text-gray-500 text-sm line-clamp-2"><?= esc($item['description']) ?></p>
                                            </div>

                                            <div class="w-10 h-10 bg-green-700 rounded-lg flex items-center justify-center">
                                                <i class="fi fi-rr-arrow-right text-white text-lg pt-2"></i>
                                            </div>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($popularProducts)): ?>
            <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
                <div class="row bg-white p-4 rounded-t-lg">
                    <div class="flex justify-between">
                        <h2 class="text-lg font-medium z-10"><?php echo lang('website.popular_products'); ?></h2>
                        <a href="/popular-products" class="self-center text-sm"><?php echo lang('website.view_all'); ?> <i class="fi fi-tr-angle-small-right"></i></a>
                    </div>
                </div>

                <div class="swiper-container swiper rounded-e-lg bg-white px-3" id="swiper-1" data-pagination-type=""
                    data-speed="400" data-space-between="20" data-pagination="false" data-navigation="true"
                    data-autoplay="true" data-autoplay-delay="3000" data-effect="slide"
                    data-breakpoints='{"320": {"slidesPerView": 2}, "768": {"slidesPerView": 3}, "1024": {"slidesPerView": 6}, "1440": {"slidesPerView": 6}}'>
                    <div class="swiper-wrapper py-4 text-center">
                        <?php foreach ($popularProducts as $product): ?>
                            <?php $firstVarient = $product['variants'][0]; ?>
                            <div class="swiper-slide rounded-lg bg-white border border-green-500" id="<?= $product['slug'] . '-' . $firstVarient['id'] ?>">
                                <div class="flex-auto p-2">
                                    <div class="text-center relative flex justify-center">
                                        <?php if ($firstVarient['discounted_price'] > 0):
                                            $discountPercentage = (($firstVarient['price'] - $firstVarient['discounted_price']) / $firstVarient['price']) * 100;
                                            $discountPercentage = round($discountPercentage);
                                        ?>
                                            <div class="absolute -top-2 left-1">
                                                <svg width="29" height="28" viewBox="0 0 29 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M28.9499 0C28.3999 0 27.9361 1.44696 27.9361 2.60412V27.9718L24.5708 25.9718L21.2055 27.9718L17.8402 25.9718L14.4749 27.9718L11.1096 25.9718L7.74436 27.9718L4.37907 25.9718L1.01378 27.9718V2.6037C1.01378 1.44655 0.549931 0 0 0H28.9499Z" fill="#15803D"></path>
                                                </svg>
                                            </div>
                                            <span class="absolute  text-xs text-white font-bold left-[6px] -top-2 break-words"><?= $discountPercentage ?>%</span>
                                            <span class="absolute  text-xs text-white font-bold left-[8px] top-1 break-words"><?php echo lang('website.off'); ?></span>
                                        <?php endif; ?>
                                        <a href="/product/<?= $product['slug'] ?>">
                                            <img src="<?= base_url($product['main_img']) ?>"
                                                alt="<?= $product['product_name'] ?>" class="w-4/5 h-auto ml-auto mr-auto" />
                                        </a>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <h3 class="text-sm truncate font-semibold"><a href="#"><?= $product['product_name'] ?></a></h3>
                                        <span class="text-xs text-gray-500 "><?php echo $firstVarient['title'] ?></span>
                                        <div class="flex justify-between items-center mt-2">
                                            <div class="flex flex-col justify-between" style="min-height: 2.5rem;">
                                                <?php if ($firstVarient['discounted_price'] > 0): ?>
                                                    <span class="text-sm text-gray-900 font-semibold">
                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                            <?= $country['currency_symbol'] ?><?= $firstVarient['discounted_price'] ?>
                                                        <?php else: ?>
                                                            <?= $firstVarient['discounted_price'] ?><?= $country['currency_symbol'] ?>
                                                        <?php endif; ?>
                                                    </span>
                                                    <span class="line-through text-gray-500 text-xs">
                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                            <?= $country['currency_symbol'] ?><?= $firstVarient['price'] ?>
                                                        <?php else: ?>
                                                            <?= $firstVarient['price'] ?><?= $country['currency_symbol'] ?>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-sm text-gray-900 font-semibold my-auto">
                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                            <?= $country['currency_symbol'] ?><?= $firstVarient['price'] ?>
                                                        <?php else: ?>
                                                            <?= $firstVarient['price'] ?><?= $country['currency_symbol'] ?>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>


                                            <div class="<?= $product['slug'] . '-mainbtndiv-' . $firstVarient['id'] ?>">
                                                <?php
                                                if ($firstVarient['stock'] == 0 && $firstVarient['is_unlimited_stock'] == 0) {
                                                ?>
                                                    <button type="button"
                                                        class="text-xs px-2 py-1 rounded-lg items-center gap-x-1 bg-red-700 text-white border-red-700 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-red-700 hover:border-green-700 btn-sm">
                                                        <span><?php echo lang('website.out_of_Stock'); ?></span>
                                                    </button>
                                                    <?php
                                                } else {
                                                    if ($product['cart_quantity'] > 0) {
                                                    ?>
                                                        <div class="flex items-center gap-1 p-1 rounded-lg bg-green-700 border border-green-700 shadow-md">
                                                            <button type="button" onclick="removeFromCart(<?= $product['id'] ?>, <?= $firstVarient['id'] ?>)"
                                                                class="text-lg leading-none hover:text-primary <?= $product['slug'] . '-removebtn-' . $firstVarient['id'] ?>">
                                                                <i class="fi fi-rr-minus-small text-white"></i>
                                                            </button>
                                                            <span class="text-center h-5 text-sm font-medium text-white <?= $product['slug'] . '-qty-' . $firstVarient['id'] ?>"><?= $product['cart_quantity'] ?></span>
                                                            <button type="button" onclick="addToCart(<?= $product['id'] ?>, <?= $firstVarient['id'] ?>)"
                                                                class="text-lg leading-none hover:text-primary <?= $product['slug'] . '-addbtn-' . $firstVarient['id'] ?>">
                                                                <i class="fi fi-rr-plus-small text-white"></i>
                                                            </button>
                                                        </div>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <button type="button" onclick="openProductVariantPopup(<?= $product['id'] ?>, '<?= $product['slug'] ?>')"
                                                            class="text-sm px-2 py-1 rounded-lg items-center gap-x-1 bg-green-700 text-white border-green-700 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 btn-sm <?= $product['slug'] . '-' . $firstVarient['id'] ?>">
                                                            <i class="fi fi-rr-shopping-cart"></i>
                                                            <span><?php echo lang('website.add'); ?></span>
                                                        </button>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                    <div class="swiper-pagination"></div>
                </div>

                <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:gap-4 xl:grid-cols-5">
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($dealOftheDayBanner)): ?>
            <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
                <div class="swiper-container swiper rounded-lg" data-pagination-type="" data-speed="400"
                    data-space-between="20" data-pagination="false" data-navigation="true" data-autoplay="true"
                    data-autoplay-delay="3000" data-effect="slide"
                    data-breakpoints='{"320": {"slidesPerView": 2}, "480": {"slidesPerView": 2}, "768": {"slidesPerView": 2}, "1024": {"slidesPerView": 3}}'>
                    <div class="swiper-wrapper">
                        <?php foreach ($dealOftheDayBanner as $banner): ?>
                            <?php if (count($banner['firstSubcategory']) > 0): ?>
                                <a href="subcategory/<?= $banner['firstSubcategory']['slug'] ?>" class="swiper-slide">
                                    <img src="<?= esc($banner['banner_img']) ?>" class="rounded-lg w-full" />
                                </a>
                            <?php else: ?>
                                <a href="#" class="swiper-slide">
                                    <img src="<?= esc($banner['banner_img']) ?>" class="rounded-lg w-full" />
                                </a>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($dealOfTheDayProducts)): ?>
            <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
                <div class="row bg-white p-4 rounded-t-lg">
                    <div class="flex justify-between">
                        <h2 class="text-lg font-medium z-10"> <?php echo lang('website.deal_of_the_day_products'); ?></h2>
                        <a href="deal-of-the-day-products" class="self-center text-sm"><?php echo lang('website.view_all'); ?> <i class="fi fi-tr-angle-small-right"></i></a>
                    </div>
                </div>

                <div class="swiper-container swiper rounded-e-lg bg-white px-3" id="swiper-1" data-pagination-type=""
                    data-speed="400" data-space-between="20" data-pagination="false" data-navigation="true"
                    data-autoplay="true" data-autoplay-delay="3000" data-effect="slide"
                    data-breakpoints='{"320": {"slidesPerView": 2}, "768": {"slidesPerView": 3}, "1024": {"slidesPerView": 6}, "1440": {"slidesPerView": 6}}'>
                    <div class="swiper-wrapper py-4 text-center">
                        <?php foreach ($dealOfTheDayProducts as $product): ?>
                            <?php $firstVarient = $product['variants'][0]; ?>
                            <div class="swiper-slide rounded-lg bg-white border border-green-500" id="<?= $product['slug'] . '-' . $firstVarient['id'] ?>">
                                <div class="flex-auto p-2">
                                    <div class="text-center relative flex justify-center">
                                        <?php if ($firstVarient['discounted_price'] > 0):
                                            $discountPercentage = (($firstVarient['price'] - $firstVarient['discounted_price']) / $firstVarient['price']) * 100;
                                            $discountPercentage = round($discountPercentage);
                                        ?>
                                            <div class="absolute -top-2 left-1">
                                                <svg width="29" height="28" viewBox="0 0 29 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M28.9499 0C28.3999 0 27.9361 1.44696 27.9361 2.60412V27.9718L24.5708 25.9718L21.2055 27.9718L17.8402 25.9718L14.4749 27.9718L11.1096 25.9718L7.74436 27.9718L4.37907 25.9718L1.01378 27.9718V2.6037C1.01378 1.44655 0.549931 0 0 0H28.9499Z" fill="#15803D"></path>
                                                </svg>
                                            </div>
                                            <span class="absolute  text-xs text-white font-bold left-[6px] -top-2 break-words"><?= $discountPercentage ?>%</span>
                                            <span class="absolute  text-xs text-white font-bold left-[8px] top-1 break-words"><?php echo lang('website.off'); ?></span>
                                        <?php endif; ?>
                                        <a href="/product/<?= $product['slug'] ?>">
                                            <img src="<?= base_url($product['main_img']) ?>"
                                                alt="<?= $product['product_name'] ?>" class="w-4/5 h-auto ml-auto mr-auto" />
                                        </a>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <h3 class="text-sm truncate font-semibold"><a href="#"><?= $product['product_name'] ?></a></h3>
                                        <span class="text-xs text-gray-500 "><?php echo $firstVarient['title'] ?></span>
                                        <div class="flex justify-between items-center mt-2">
                                            <div class="flex flex-col justify-between" style="min-height: 2.5rem;">
                                                <?php if ($firstVarient['discounted_price'] > 0): ?>
                                                    <span class="text-sm text-gray-900 font-semibold">
                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                            <?= $country['currency_symbol'] ?><?= $firstVarient['discounted_price'] ?>
                                                        <?php else: ?>
                                                            <?= $firstVarient['discounted_price'] ?><?= $country['currency_symbol'] ?>
                                                        <?php endif; ?>
                                                    </span>
                                                    <span class="line-through text-gray-500 text-xs">
                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                            <?= $country['currency_symbol'] ?><?= $firstVarient['price'] ?>
                                                        <?php else: ?>
                                                            <?= $firstVarient['price'] ?><?= $country['currency_symbol'] ?>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-sm text-gray-900 font-semibold my-auto">
                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                            <?= $country['currency_symbol'] ?><?= $firstVarient['price'] ?>
                                                        <?php else: ?>
                                                            <?= $firstVarient['price'] ?><?= $country['currency_symbol'] ?>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>


                                            <div class="<?= $product['slug'] . '-mainbtndiv-' . $firstVarient['id'] ?>">
                                                <?php
                                                if ($firstVarient['stock'] == 0 && $firstVarient['is_unlimited_stock'] == 0) {
                                                ?>
                                                    <button type="button"
                                                        class="text-xs px-2 py-1 rounded-lg items-center gap-x-1 bg-red-700 text-white border-red-700 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-red-700 hover:border-green-700 btn-sm">
                                                        <span><?php echo lang('website.out_of_Stock'); ?></span>
                                                    </button>
                                                    <?php
                                                } else {
                                                    if ($product['cart_quantity'] > 0) {
                                                    ?>
                                                        <div class="flex items-center gap-1 p-1 rounded-lg bg-green-700 border border-green-700 shadow-md">
                                                            <button type="button" onclick="removeFromCart(<?= $product['id'] ?>, <?= $firstVarient['id'] ?>)"
                                                                class="text-lg leading-none hover:text-primary <?= $product['slug'] . '-removebtn-' . $firstVarient['id'] ?>">
                                                                <i class="fi fi-rr-minus-small text-white"></i>
                                                            </button>
                                                            <span class="text-center h-5 text-sm font-medium text-white <?= $product['slug'] . '-qty-' . $firstVarient['id'] ?>"><?= $product['cart_quantity'] ?></span>
                                                            <button type="button" onclick="addToCart(<?= $product['id'] ?>, <?= $firstVarient['id'] ?>)"
                                                                class="text-lg leading-none hover:text-primary <?= $product['slug'] . '-addbtn-' . $firstVarient['id'] ?>">
                                                                <i class="fi fi-rr-plus-small text-white"></i>
                                                            </button>
                                                        </div>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <button type="button" onclick="openProductVariantPopup(<?= $product['id'] ?>, '<?= $product['slug'] ?>')"
                                                            class="text-sm px-2 py-1 rounded-lg items-center gap-x-1 bg-green-700 text-white border-green-700 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 btn-sm <?= $product['slug'] . '-' . $firstVarient['id'] ?>">
                                                            <i class="fi fi-rr-shopping-cart"></i>
                                                            <span><?php echo lang('website.add'); ?></span>
                                                        </button>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                    <div class="swiper-pagination"></div>
                </div>


            </section>
        <?php endif; ?>

        <?php if (!empty($brands)): ?>
            <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
                <div class="row bg-white p-4 rounded-t-lg">
                    <div class="flex justify-between">
                        <h2 class="text-lg font-medium z-10 flex"><span class="hidden md:block"><?php echo lang('website.shop_by'); ?>&nbsp;</span><?php echo lang('website.brand'); ?></h2>
                        <a href="/brand" class="self-center text-sm"><?php echo lang('website.view_all'); ?> <i class="fi fi-tr-angle-small-right"></i></a>
                    </div>
                </div>

                <div class="swiper-container swiper rounded-e-lg bg-white px-3" data-pagination-type="" data-speed="400"
                    data-space-between="20" data-pagination="false" data-navigation="true" data-autoplay="true"
                    data-autoplay-delay="3000" data-effect="slide"
                    data-breakpoints='{"320": {"slidesPerView": 4}, "768": {"slidesPerView": 6}, "1024": {"slidesPerView": 8}}'>
                    <div class="swiper-wrapper py-2  text-center">
                        <?php foreach ($brands as $brand): ?>
                            <div class="swiper-slide">
                                <a href="brand/<?= $brand['slug'] ?>">
                                    <div class="flex flex-col justify-center items-center">
                                        <img src="<?= $brand['image'] ?>" alt="<?= $brand['brand'] ?>" class="bg-[#edf8f1] rounded-lg" />
                                        <h6 class="text-sm font-semibold mt-2"><?= $brand['brand'] ?></h6>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($homeBanner)): ?>
            <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
                <div class="swiper-container swiper rounded-lg" data-pagination-type="" data-speed="400"
                    data-space-between="20" data-pagination="false" data-navigation="true" data-autoplay="true"
                    data-autoplay-delay="3000" data-effect="slide"
                    data-breakpoints='{"320": {"slidesPerView": 2}, "480": {"slidesPerView": 2}, "768": {"slidesPerView": 2}, "1024": {"slidesPerView": 3}}'>
                    <div class="swiper-wrapper">
                        <?php foreach ($homeBanner as $banner): ?>
                            <?php if (count($banner['firstSubcategory']) > 0): ?>
                                <a href="subcategory/<?= $banner['firstSubcategory']['slug'] ?>" class="swiper-slide">
                                    <img src="<?= esc($banner['banner_img']) ?>" class="rounded-lg w-full" />
                                </a>
                            <?php else: ?>
                                <a href="#" class="swiper-slide">
                                    <img src="<?= esc($banner['banner_img']) ?>" class="rounded-lg w-full" />
                                </a>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </section>
        <?php endif; ?>

        <?php foreach ($homeSectionProducts as $homeSectionProduct): ?>
            <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
                <div class="row bg-white p-4 rounded-t-lg">
                    <div class="flex justify-between">
                        <h2 class="text-lg font-medium z-10"><?= $homeSectionProduct['title'] ?></h2>
                    </div>
                </div>


                <div class="swiper-container swiper rounded-e-lg bg-white px-3" id="swiper-1" data-pagination-type=""
                    data-speed="400" data-space-between="20" data-pagination="false" data-navigation="true"
                    data-autoplay="true" data-autoplay-delay="3000" data-effect="slide"
                    data-breakpoints='{"320": {"slidesPerView": 2}, "768": {"slidesPerView": 3}, "1024": {"slidesPerView": 6}, "1440": {"slidesPerView": 6}}'>
                    <div class="swiper-wrapper py-4 text-center">
                        <?php foreach ($homeSectionProduct['section'] as $product): ?>
                             <?php $firstVarient = $product['variants'][0]; ?>
                            <div class="swiper-slide rounded-lg bg-white border border-green-500" id="<?= $product['slug'] . '-' . $firstVarient['id'] ?>">
                                <div class="flex-auto p-2">
                                    <div class="text-center relative flex justify-center">
                                        <?php if ($firstVarient['discounted_price'] > 0):
                                            $discountPercentage = (($firstVarient['price'] - $firstVarient['discounted_price']) / $firstVarient['price']) * 100;
                                            $discountPercentage = round($discountPercentage);
                                        ?>
                                            <div class="absolute -top-2 left-1">
                                                <svg width="29" height="28" viewBox="0 0 29 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M28.9499 0C28.3999 0 27.9361 1.44696 27.9361 2.60412V27.9718L24.5708 25.9718L21.2055 27.9718L17.8402 25.9718L14.4749 27.9718L11.1096 25.9718L7.74436 27.9718L4.37907 25.9718L1.01378 27.9718V2.6037C1.01378 1.44655 0.549931 0 0 0H28.9499Z" fill="#15803D"></path>
                                                </svg>
                                            </div>
                                            <span class="absolute  text-xs text-white font-bold left-[6px] -top-2 break-words"><?= $discountPercentage ?>%</span>
                                            <span class="absolute  text-xs text-white font-bold left-[8px] top-1 break-words"><?php echo lang('website.off'); ?></span>
                                        <?php endif; ?>
                                        <a href="/product/<?= $product['slug'] ?>">
                                            <img src="<?= base_url($product['main_img']) ?>"
                                                alt="<?= $product['product_name'] ?>" class="w-4/5 h-auto ml-auto mr-auto" />
                                        </a>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <h3 class="text-sm truncate font-semibold"><a href="#"><?= $product['product_name'] ?></a></h3>
                                        <span class="text-xs text-gray-500 "><?php echo $firstVarient['title'] ?></span>
                                        <div class="flex justify-between items-center mt-2">
                                        <div class="flex flex-col justify-between" style="min-height: 2.5rem;">
                                                <?php if ($firstVarient['discounted_price'] > 0): ?>
                                                    <span class="text-sm text-gray-900 font-semibold">
                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                            <?= $country['currency_symbol'] ?><?= $firstVarient['discounted_price'] ?>
                                                        <?php else: ?>
                                                            <?= $firstVarient['discounted_price'] ?><?= $country['currency_symbol'] ?>
                                                        <?php endif; ?>
                                                    </span>
                                                    <span class="line-through text-gray-500 text-xs">
                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                            <?= $country['currency_symbol'] ?><?= $firstVarient['price'] ?>
                                                        <?php else: ?>
                                                            <?= $firstVarient['price'] ?><?= $country['currency_symbol'] ?>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-sm text-gray-900 font-semibold my-auto">
                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                            <?= $country['currency_symbol'] ?><?= $firstVarient['price'] ?>
                                                        <?php else: ?>
                                                            <?= $firstVarient['price'] ?><?= $country['currency_symbol'] ?>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>


                                            <div class="<?= $product['slug'] . '-mainbtndiv-' . $firstVarient['id'] ?>">
                                                <?php
                                                if ($firstVarient['stock'] == 0 && $firstVarient['is_unlimited_stock'] == 0) {
                                                ?>
                                                    <button type="button"
                                                        class="text-xs px-2 py-1 rounded-lg items-center gap-x-1 bg-red-700 text-white border-red-700 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-red-700 hover:border-green-700 btn-sm">
                                                        <span><?php echo lang('website.out_of_Stock'); ?></span>
                                                    </button>
                                                    <?php
                                                } else {
                                                    if ($product['cart_quantity'] > 0) {
                                                    ?>
                                                        <div class="flex items-center gap-1 p-1 rounded-lg bg-green-700 border border-green-700 shadow-md">
                                                            <button type="button" onclick="removeFromCart(<?= $product['id'] ?>, <?= $firstVarient['id'] ?>)"
                                                                class="text-lg leading-none hover:text-primary <?= $product['slug'] . '-removebtn-' . $firstVarient['id'] ?>">
                                                                <i class="fi fi-rr-minus-small text-white"></i>
                                                            </button>
                                                            <span class="text-center h-5 text-sm font-medium text-white <?= $product['slug'] . '-qty-' . $firstVarient['id'] ?>"><?= $product['cart_quantity'] ?></span>
                                                            <button type="button" onclick="addToCart(<?= $product['id'] ?>, <?= $firstVarient['id'] ?>)"
                                                                class="text-lg leading-none hover:text-primary <?= $product['slug'] . '-addbtn-' . $firstVarient['id'] ?>">
                                                                <i class="fi fi-rr-plus-small text-white"></i>
                                                            </button>
                                                        </div>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <button type="button" onclick="openProductVariantPopup(<?= $product['id'] ?>, '<?= $product['slug'] ?>')"
                                                            class="text-sm px-2 py-1 rounded-lg items-center gap-x-1 bg-green-700 text-white border-green-700 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 btn-sm <?= $product['slug'] . '-' . $firstVarient['id'] ?>">
                                                            <i class="fi fi-rr-shopping-cart"></i>
                                                            <span><?php echo lang('website.add'); ?></span>
                                                        </button>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </section>
        <?php endforeach; ?>

        <?php if (!empty($sellers)): ?>
            <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
                <div class="row bg-white p-4 rounded-t-lg">
                    <div class="flex justify-between">
                        <h2 class="text-lg font-medium z-10 flex"><span class="hidden md:block"><?php echo lang('website.shop_by'); ?>&nbsp;</span><?php echo lang('website.seller'); ?></h2>

                    </div>
                </div>
                <div class="swiper-container swiper rounded-e-lg bg-white px-3" data-pagination-type="" data-speed="400"
                    data-space-between="20" data-pagination="false" data-navigation="true" data-autoplay="true"
                    data-autoplay-delay="3000" data-effect="slide"
                    data-breakpoints='{"320": {"slidesPerView": 3}, "768": {"slidesPerView": 4}, "1024": {"slidesPerView": 8}}'>
                    <div class="swiper-wrapper py-2 text-center">
                        <?php foreach ($sellers as $seller): ?>
                            <div class="swiper-slide">
                                <a href="seller/<?= $seller['slug'] ?>">
                                    <div class="flex flex-col justify-start items-center gap-2">
                                        <!-- seller Logo -->
                                        <img src="<?= $seller['logo'] ?>" alt="<?= $seller['store_name'] ?>" class="w-16 h-16 bg-[#edf8f1] md:w-28 md:h-28 lg:w-36 lg:h-36 rounded-full border" />
                                        <!-- seller Name -->
                                        <h6 class="text-sm font-semibold"><?= $seller['store_name'] ?></h6>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </section>
        <?php endif; ?>

        

        <?= $this->include('website/template/mobileBottomMenu') ?>
        <?= $this->include('website/template/productVarientPopup') ?>

    </main>
    <?= $this->include('website/template/shopCart') ?>
    <?= $this->include('website/template/footer') ?>
    <?= $this->include('website/template/script') ?>
</body>

</html>