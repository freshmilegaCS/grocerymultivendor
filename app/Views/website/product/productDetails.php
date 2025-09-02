<!doctype html>
<html lang="<?= session()->get('site_lang') ?? 'en' ?>" dir="<?= dir_attribute() ?>">

<head>
    <?= $this->include('website/template/style') ?>

    <title><?= $product['product_name'] ?> - <?= $settings['business_name'] ?></title>
    <meta name="description" content="<?= $product['seo_description'] ?>" />
    <meta name="keywords" content="<?= $product['seo_keywords'] ?>" />

    <!-- Canonical URL -->
    <link rel="canonical" href="<?= base_url("product/" . $product['slug']) ?>" />

    <!-- Robots Meta Tag -->
    <meta name="robots" content="index, follow" />

    <!-- Open Graph Meta Tags (For Facebook, LinkedIn, etc.) -->
    <meta property="og:url" content="<?= current_url(); ?>" />
    <meta property="og:type" content="product" />
    <meta property="og:title" content="<?= $product['seo_title'] ?>" />
    <meta property="og:description" content="<?= $product['seo_description'] ?>" />
    <meta property="og:image" content="<?= base_url($product['main_img']) ?>" />
    <meta property="og:image:alt" content="<?= $product['product_name'] ?>" />
    <meta property="og:site_name" content="<?= $settings['business_name'] ?>" />

    <!-- Twitter Card Meta Tags (For Twitter Sharing) -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= $product['seo_title'] ?>" />
    <meta name="twitter:description" content="<?= $product['seo_description'] ?>" />
    <meta name="twitter:image" content="<?= base_url($product['main_img']) ?>" />
    <meta name="twitter:site" content="@yourtwitterhandle" />

    <!-- Schema Markup (Structured Data for Google Rich Snippets) -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Product",
            "name": "<?= $product['product_name'] ?>",
            "image": "<?= base_url($product['main_img']) ?>",
            "description": "<?= $product['seo_description'] ?>",
            "brand": {
                "@type": "Brand",
                "name": "<?= $settings['business_name'] ?>"
            },
            "offers": {
                "@type": "Offer",
                "priceCurrency": "<?= $country['currency_symbol'] ?>",
                "price": "<?= $product['variants'][0]['discounted_price'] ?: $product['variants'][0]['price'] ?>",
                "itemCondition": "https://schema.org/NewCondition",
                "availability": "https://schema.org/InStock",
                "seller": {
                    "@type": "Organization",
                    "name": "<?= $settings['business_name'] ?>"
                }
            }
        }
    </script>
    <style>
        .zoom-container {
            position: relative;
            cursor: zoom-in;
        }

        .image-wrapper {
            position: relative;
            background: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
        }

        .main-product-image {
            transition: transform 0.3s ease;
        }

        .thumbnails-img {
            position: relative;
            transition: all 0.3s ease;
        }

        .thumbnails-img:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .thumbnails-img.active {
            border-color: #007bff !important;
            box-shadow: 0 0 0 1px #007bff;
        }

        .thumbnail-image {
            aspect-ratio: 1;
            object-fit: cover;
        }

        /* Zoom lens styling */
        .zoom-lens {
            border: 2px solid #007bff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.3);
        }

        /* Zoom window styling */
        .zoom-window {
            background: #ffffff;
            border: 1px solid #dee2e6;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        }

        /* Modal styling */
        .zoom-modal {
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        .zoom-modal-content {
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .zoom-modal-close {
            transition: all 0.2s ease;
        }

        .zoom-modal-close:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: scale(1.1);
        }

        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .zoom-window {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .thumbnails {
                flex-wrap: wrap;
            }

            .thumbnail-wrapper {
                width: calc(25% - 0.5rem);
            }
        }
    </style>
</head>

<body class="bg-gray-100">
    <?= $this->include('website/template/header') ?>
    <main class="max-w-7xl mx-auto">

        <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
            <div class="flex flex-wrap">
                <div class="lg:w-1/3 w-full">
                    <!-- Main Swiper Container -->
                    <div class="swiper-container swiper mb-4" id="productSwiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($product['images'] as $index => $image): ?>
                                <div class="swiper-slide zoom-container" data-index="<?= $index; ?>">
                                    <div class="image-wrapper bg-white" style="position: relative; overflow: hidden;">
                                        <img src="<?= base_url($image['image']) ?>"
                                            alt="Product Image <?= $index + 1; ?>"
                                            class="main-product-image w-full h-auto object-contain"
                                            style="display: block; max-width: 100%; height: auto;" />
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Thumbnails -->
                    <div class="thumbnails flex gap-3 mt-4" id="productThumbnails">
                        <?php foreach ($product['images'] as $index => $image): ?>
                            <div class="thumbnail-wrapper w-1/4">
                                <div class="thumbnails-img cursor-pointer border-2 border-transparent rounded-lg overflow-hidden transition-all duration-300 hover:border-gray-300"
                                    data-index="<?= $index; ?>">
                                    <img src="<?= base_url($image['image']) ?>"
                                        alt="Thumbnail <?= $index + 1; ?>"
                                        class="thumbnail-image w-full h-auto object-cover rounded-lg" />
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>


                <div class="lg:w-2/3 w-full lg:pl-4">
                    <div class="px-4 mt-6 md:mt-0 bg-white rounded-lg py-4">
                        <div class="flex flex-col gap-4">
                            <!-- content -->
                            <a href="#!" class="text-sm block text-gray-500"><?= $product['category']['category_name'] ?> / <?= $product['subcategory']['name'] ?></a>
                            <!-- heading -->
                            <div class="flex flex-col">
                                <div class="flex flex-row justify-between gap-3">
                                    <h1 class="text-lg md:text-xl lg:text-2xl font-semibold mb-2"><?= $product['product_name'] ?></h1>
                                    <i class="fi fi-rr-share-square md:hidden p-1 border rounded-full shadow-sm self-center w-7 h-7" id="shareButton"></i>
                                </div>

                                <div class="flex flex-col gap-4">
                                    <div class="flex items-center gap-2">
                                        <small class="text-yellow-500 inline-flex items-center">
                                            <?php
                                            // Calculate full and half stars
                                            $fullStars = floor($product['average_rating']); // Number of full stars
                                            $hasHalfStar = ($product['average_rating'] - $fullStars) >= 0.5; // Determines if there’s a half star

                                            // Render full stars
                                            for ($i = 0; $i < $fullStars; $i++) {
                                                echo '<i class="fi fi-sc-star"></i>';
                                            }

                                            // Render half star if applicable
                                            if ($hasHalfStar) {
                                                echo '<i class="fi fi-rr-star-sharp-half-stroke"></i>';
                                            }

                                            // Render empty stars to fill up to 5 stars
                                            for ($i = $fullStars + ($hasHalfStar ? 1 : 0); $i < 5; $i++) {
                                                echo '<i class="fi fi-rr-star-exclamation"></i>';
                                            }
                                            ?>
                                        </small>
                                        <a href="#" class="text-green-600 text-sm">(<?= $product['rating_count'] ?> reviews)</a>
                                    </div>

                                    <hr>

                                    <div class="flex flex-wrap gap-2">

                                        <?php $first = true;
                                        foreach ($product['variants'] as $varient): if ($first): ?>
                                                <div id="variant-<?= $varient['id'] ?>" class="border border-green-700 bg-[#F7FFF9] rounded-lg p-4 shadow-md cursor-pointer active" onclick="switchVarient(<?= $product['id'] ?>, <?= $varient['id'] ?>, '<?= $product['slug'] ?>')">
                                                    <div class="flex flex-col items-center">
                                                        <p class="text-sm font-semibold"><?= $varient['title'] ?></p>
                                                        <div class="flex justify-between w-full mt-2 gap-4">
                                                            <?php if ($varient['discounted_price'] > 0): ?>
                                                                <p class="text-green-700 font-bold">
                                                                    <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                                        <?= $country['currency_symbol'] ?><?= $varient['discounted_price'] ?>
                                                                    <?php else: ?>
                                                                        <?= $varient['discounted_price'] ?><?= $country['currency_symbol'] ?>
                                                                    <?php endif; ?>
                                                                </p>
                                                                <p class="text-xs text-gray-400 font-semibold self-center">
                                                                    <span class="line-through font-bold">
                                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                                            <?= $country['currency_symbol'] ?><?= $varient['price'] ?>
                                                                        <?php else: ?>
                                                                            <?= $varient['price'] ?><?= $country['currency_symbol'] ?>
                                                                        <?php endif; ?>
                                                                    </span>
                                                                </p>
                                                            <?php else: ?>
                                                                <p class="text-sm text-gray-800">
                                                                    <span class="font-bold">
                                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                                            <?= $country['currency_symbol'] ?><?= $varient['price'] ?>
                                                                        <?php else: ?>
                                                                            <?= $varient['price'] ?><?= $country['currency_symbol'] ?>
                                                                        <?php endif; ?>
                                                                    </span>
                                                                </p>
                                                            <?php endif; ?>

                                                        </div>
                                                    </div>
                                                    <?php $first = false; ?>
                                                </div>
                                            <?php else: ?>
                                                <div id="variant-<?= $varient['id'] ?>" class="bg-white border rounded-lg p-4 cursor-pointer" onclick="switchVarient(<?= $product['id'] ?>, <?= $varient['id'] ?>, '<?= $product['slug'] ?>')">
                                                    <div class="flex flex-col items-center">
                                                        <p class="text-sm font-semibold"><?= $varient['title'] ?></p>
                                                        <div class="flex justify-between w-full mt-2 gap-4">
                                                            <?php if ($varient['discounted_price'] > 0): ?>
                                                                <p class="text-green-700 font-bold">
                                                                    <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                                        <?= $country['currency_symbol'] ?><?= $varient['discounted_price'] ?>
                                                                    <?php else: ?>
                                                                        <?= $varient['discounted_price'] ?><?= $country['currency_symbol'] ?>
                                                                    <?php endif; ?>
                                                                </p>
                                                                <p class="text-xs text-gray-400 font-semibold self-center">
                                                                    <span class="line-through font-bold">
                                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                                            <?= $country['currency_symbol'] ?><?= $varient['price'] ?>
                                                                        <?php else: ?>
                                                                            <?= $varient['price'] ?><?= $country['currency_symbol'] ?>
                                                                        <?php endif; ?>
                                                                    </span>
                                                                </p>
                                                            <?php else: ?>
                                                                <p class="text-sm text-gray-800">
                                                                    <span class="font-bold">
                                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                                            <?= $country['currency_symbol'] ?><?= $varient['price'] ?>
                                                                        <?php else: ?>
                                                                            <?= $varient['price'] ?><?= $country['currency_symbol'] ?>
                                                                        <?php endif; ?>
                                                                    </span>
                                                                </p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>

                                </div>
                            </div>

                            <div class="gap-6">
                                <div class="<?= $product['slug'] . '-mainbtndiv' ?>">
                                    <?php $first = true; ?>
                                    <?php foreach ($product['variants'] as $varient): ?>
                                        <?php if ($first): ?>
                                            <div class="<?= $product['slug'] . '-mainbtndiv-' . $varient['id'] ?>">
                                                <?php if ($varient['cart_quantity']): ?>
                                                    <div class="inline-flex items-center gap-1 p-1 rounded-lg bg-green-700 border border-green-700 shadow-md">
                                                        <button type="button" onclick="removeFromCart(<?= $product['id'] ?>, <?= $varient['id'] ?>)"
                                                            class="text-lg leading-none hover:text-primary <?= $product['slug'] . '-removebtn-' . $varient['id'] ?>">
                                                            <i class="fi fi-rr-minus-small text-white"></i>
                                                        </button>
                                                        <span class="text-center h-5 text-sm font-medium text-white <?= $product['slug'] . '-qty-' . $varient['id'] ?>">
                                                            <?= $varient['cart_quantity'] ?>
                                                        </span>
                                                        <button type="button" onclick="addToCart(<?= $product['id'] ?>, <?= $varient['id'] ?>)"
                                                            class="text-lg leading-none hover:text-primary <?= $product['slug'] . '-addbtn-' . $varient['id'] ?>">
                                                            <i class="fi fi-rr-plus-small text-white"></i>
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <button type="button" onclick="addToCart(<?= $product['id'] ?>, <?= $varient['id'] ?>)"
                                                        class="text-sm px-2 py-1 rounded-lg items-center gap-x-1 bg-green-700 text-white border-green-700 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 btn-sm">
                                                        <i class="fi fi-rr-shopping-cart"></i>
                                                        <span><?php echo lang('website.add'); ?></span>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <?php $first = false; ?>
                                        <?php else: ?>
                                            <div class="<?= $product['slug'] . '-mainbtndiv-' . $varient['id'] ?>"></div>

                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <hr>

                            <div class="">
                                <div class="flex flex-col mb-2">
                                    <h3 class="text-sm font-semibold"><?php echo lang('website.product_details'); ?>: </h3>
                                    <p class="text-sm text-gray-700"><?= $product['description'] ?></p>
                                </div>
                                <div class="flex flex-row mb-2">
                                    <h3 class="text-sm font-semibold"><?php echo lang('website.seller'); ?> :</h3>
                                    <p class="text-sm text-gray-700"><?= $product['seller']['store_name'] ?></p>
                                </div>
                                <div class="flex flex-row mb-2">
                                    <h3 class="text-sm font-semibold"><?php echo lang('website.FSSAI_license'); ?>:</h3>
                                    <p class="text-sm text-gray-700"><?= $product['fssai_lic_no'] ?></p>
                                </div>
                                <div class="flex flex-row mb-2">
                                    <h3 class="text-sm font-semibold"><?php echo lang('website.manufacturer'); ?>: </h3>
                                    <p class="text-sm text-gray-700"><?= $product['manufacturer'] ?></p>
                                </div>
                                <div class="flex flex-row mb-2">
                                    <h3 class="text-sm font-semibold"> <?php echo lang('website.made_in'); ?>: </h3>
                                    <p class="text-sm text-gray-700"><?= $product['made_in'] ?></p>
                                </div>
                            </div>

                            <hr>

                            <div class="flex flex-col gap-4">
                                <div class="font-medium text-sm">
                                    <i class="fi fi-rr-restock"></i><?php echo lang('website.is_returnable'); ?> : <?= $product['is_returnable'] ? '<span class="text-green-600">yes</span>' : '<span class="text-red-600">no</span>' ?>
                                    <?= $product['is_returnable'] ? '<span class="text-green-600">(in ' . $product['is_returnable'] . ' Days)</span>' : '' ?>
                                </div>
                                <ul class="flex items-center text-sm gap-4 mt-3">
                                    <li class="font-medium"><i class="fi fi-rr-share-square"></i><?php echo lang('website.share_product'); ?> : </li>
                                    <li>
                                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= current_url(); ?>" target="_blank">
                                            <i class="fi fi-brands-facebook text-xl text-blue-600"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://wa.me/?text=Check%20out%20this%20product:%20<?= current_url(); ?>" target="_blank">
                                            <i class="fi fi-brands-whatsapp text-xl text-green-600"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://t.me/share/url?url=<?= current_url(); ?>&text=Check%20out%20this%20product!" target="_blank">
                                            <i class="fi fi-brands-telegram text-xl text-cyan-500"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#!" onclick="copyLink()" class="">
                                            <i class="fi fi-rr-link-alt text-xl font-bold"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="px-4 mt-4 bg-white rounded-lg py-4"></div> -->
                </div>
            </div>
        </section>



        <section class="mt-2 md:mt-4 md:container md:mx-auto px-3 ">
            <div class="flex flex-wrap bg-white rounded-lg">
                <div class="w-full p-2 md:p-4">

                    <ul class="nav pl-0 gap-3 pb-6 border-b flex">

                        <li class="nav-item">
                            <button
                                class="inline-block py-2 px-3 border-2 border-gray-300 rounded-lg font-semibold nav-link active-tab"
                                data-bs-target="#reviews-tab-pane" type="button" onclick="showTab(this)">
                                <?php echo lang('website.rating_&_reviews'); ?>
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <div id="reviews-tab-pane" class="tab-pane block">
                            <div class="mt-4">
                                <div class="flex flex-wrap md:flex-nowrap gap-6">
                                    <div class="md:w-1/3 w-full">
                                        <div class="flex flex-col gap-6 mb-6">
                                            <div class="flex flex-col gap-2">
                                                <h3 class="text-sm font-semibold"><?php echo lang('website.customer_reviews'); ?> <span class="text-xs text-green-600">(<?= $product['rating_count'] ?> Review)</span></h3>
                                                <div class="lg:flex items-center gap-6 ">

                                                    <small class="text-yellow-500 inline-flex items-center">
                                                        <?php
                                                        // Calculate full and half stars
                                                        $fullStars = floor($product['average_rating']); // Number of full stars
                                                        $hasHalfStar = ($product['average_rating'] - $fullStars) >= 0.5; // Determines if there’s a half star

                                                        // Render full stars
                                                        for ($i = 0; $i < $fullStars; $i++) {
                                                            echo '<i class="fi fi-sc-star"></i>';
                                                        }

                                                        // Render half star if applicable
                                                        if ($hasHalfStar) {
                                                            echo '<i class="fi fi-rr-star-sharp-half-stroke"></i>';
                                                        }

                                                        // Render empty stars to fill up to 5 stars
                                                        for ($i = $fullStars + ($hasHalfStar ? 1 : 0); $i < 5; $i++) {
                                                            echo '<i class="fi fi-rr-star-exclamation"></i>';
                                                        }
                                                        ?>
                                                    </small>

                                                    <span><?= $product['average_rating'] ?>/5</span>

                                                </div>
                                            </div>
                                            <div class="flex flex-col gap-3">
                                                <!-- progress -->
                                                <div class="flex items-center gap-4">
                                                    <div class="text-gray-500 flex items-center gap-2">
                                                        <span class="inline-block align-middle text-gray-500">5</span>
                                                        <span class="text-yellow-500">
                                                            <i class="fi fi-sc-star"></i>
                                                        </span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                        <div class="bg-yellow-500 h-1.5 rounded-full" style="width: <?php echo (int)$product['rating_count'] ? ((int)$product['star_ratings']['5_star'] / (int)$product['rating_count']) * 100 : 0 ?>%"></div>
                                                    </div>
                                                    <span class="text-gray-500"><?php echo (int)$product['rating_count'] ? ((int)$product['star_ratings']['5_star'] / (int)$product['rating_count']) * 100 : 0 ?>%</span>
                                                </div>

                                                <!-- progress -->
                                                <div class="flex items-center gap-4">
                                                    <div class="text-gray-500 flex items-center gap-2">
                                                        <span class="inline-block align-middle text-gray-500">4</span>
                                                        <span class="text-yellow-500">
                                                            <i class="fi fi-sc-star"></i>
                                                        </span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                        <div class="bg-yellow-500 h-1.5 rounded-full" style="width: <?php echo (int)$product['rating_count'] ? ((int)$product['star_ratings']['4_star'] / (int)$product['rating_count']) * 100 : 0 ?>%"></div>
                                                    </div>
                                                    <span class="text-gray-500"><?php echo (int)$product['rating_count'] ? ((int)$product['star_ratings']['4_star'] / (int)$product['rating_count']) * 100 : 0 ?>%</span>
                                                </div>
                                                <!-- progress -->
                                                <div class="flex items-center gap-4">
                                                    <div class="text-gray-500 flex items-center gap-2">
                                                        <span class="inline-block align-middle text-gray-500">3</span>
                                                        <span class="text-yellow-500">
                                                            <i class="fi fi-sc-star"></i>
                                                        </span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                        <div class="bg-yellow-500 h-1.5 rounded-full" style="width: <?php echo (int)$product['rating_count'] ? ((int)$product['star_ratings']['3_star'] / (int)$product['rating_count']) * 100 : 0 ?>%"></div>
                                                    </div>
                                                    <span class="text-gray-500"><?php echo (int)$product['rating_count'] ? ((int)$product['star_ratings']['3_star'] / (int)$product['rating_count']) * 100 : 0 ?>%</span>
                                                </div>
                                                <!-- progress -->
                                                <div class="flex items-center gap-4">
                                                    <div class="text-gray-500 flex items-center gap-2">
                                                        <span class="inline-block align-middle text-gray-500">2</span>
                                                        <span class="text-yellow-500">
                                                            <i class="fi fi-sc-star"></i>
                                                        </span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                        <div class="bg-yellow-500 h-1.5 rounded-full" style="width: <?php echo (int)$product['rating_count'] ? ((int)$product['star_ratings']['2_star'] / (int)$product['rating_count']) * 100 : 0 ?>%"></div>
                                                    </div>
                                                    <span class="text-gray-500"><?php echo (int)$product['rating_count'] ? ((int)$product['star_ratings']['2_star'] / (int)$product['rating_count']) * 100 : 0 ?>%</span>
                                                </div>
                                                <!-- progress -->
                                                <div class="flex items-center gap-4">
                                                    <div class="text-gray-500 flex items-center gap-2">
                                                        <span class="inline-block align-middle text-gray-500">1</span>
                                                        <span class="text-yellow-500">
                                                            <i class="fi fi-sc-star"></i>
                                                        </span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                        <div class="bg-yellow-500 h-1.5 rounded-full" style="width: <?php echo (int)$product['rating_count'] ? ((int)$product['star_ratings']['1_star'] / (int)$product['rating_count']) * 100 : 0 ?>%"></div>
                                                    </div>
                                                    <span class="text-gray-500"><?php echo (int)$product['rating_count'] ? ((int)$product['star_ratings']['1_star'] / (int)$product['rating_count']) * 100 : 0 ?>%</span>
                                                </div>
                                            </div>
                                            <div class="flex flex-col gap-4">
                                                <div class="flex flex-col">
                                                    <h3 class="text-sm font-semibold"><?php echo lang('website.review_this_product'); ?></h3>
                                                    <p> <?php echo lang('website.share_your_thoughts_with_other_customers'); ?></p>
                                                </div>
                                                <button type="button" onclick="openWriteReviewPopup(<?= $product['id'] ?>)" class="btn inline-flex text-center items-center gap-x-2 p-2 rounded-lg bg-white text-gray-800 border-gray-300 border disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-gray-700 hover:border-gray-700 active:bg-gray-700 active:border-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300">
                                                    <?php echo lang('website.write_the_review'); ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="md:w-2/3">
                                        <div>
                                            <div class="flex justify-between mb-8">
                                                <div>
                                                    <!-- heading -->
                                                    <h3 class="text-sm font-semibold"> <?php echo lang('website.review'); ?></h3>
                                                </div>
                                            </div>
                                            <div class="flex flex-col gap-5 ">
                                                <?php foreach ($productRatings as $productRating): ?>
                                                    <div class="flex flex-row border-b mb-4 pb-2">
                                                        <img src="<?php
                                                                    echo $productRating['login_type'] === 'normal'
                                                                        ? (isset($productRating['img']) ? base_url() . $productRating['img'] : base_url() . $settings['logo'])
                                                                        : (isset($productRating['img']) ? $productRating['img'] : base_url() . $settings['logo'])

                                                                    ?>" alt="" class="rounded-full border border-gray-300 p-1 h-12 w-12 mr-4">
                                                        <div class="flex flex-col gap-4">
                                                            <div class="flex flex-col gap-1">
                                                                <h4 class="text-base"><?= $productRating['name'] ?></h4>
                                                                <!-- select option -->
                                                                <!-- content -->
                                                                <p class="text-xs md:flex flex-row gap-3">
                                                                    <span class="text-gray-500"><?= date('d-m-Y H:i:s a', strtotime($productRating['created_at'])) ?></span>
                                                                </p>
                                                            </div>
                                                            <!-- rating -->
                                                            <div class="md:flex md:items-center gap-3">
                                                                <small class="text-yellow-500 inline-flex items-center">

                                                                    <?php

                                                                    for ($i = 1; $i <= 5; $i++) {
                                                                        if ($i <= $productRating['rate']) {
                                                                            // Print filled star for ratings up to the current rate
                                                                            echo '<i class="fi fi-sc-star text-yellow-500"></i>';
                                                                        } else {
                                                                            // Print empty star for the remaining stars
                                                                            echo '<i class="fi fi-rr-star text-gray-300"></i>';
                                                                        }
                                                                    }
                                                                    ?>
                                                                </small>
                                                                <span class="text-gray-900 text-sm font-semibold"><?= $productRating['title'] ?></span>
                                                            </div>
                                                            <!-- text-->
                                                            <p class="text-gray-500 text-sm">
                                                                <?= $productRating['review'] ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <?php if (!empty($similarProducts)): ?>
            <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
                <div class="row bg-white p-4 rounded-t-lg">
                    <div class="flex justify-between">
                        <h2 class="text-lg font-medium z-10"><?php echo lang('website.similar_products'); ?></h2>
                    </div>
                </div>

                <div class="swiper-container swiper rounded-e-lg bg-white px-3" id="swiper-1" data-pagination-type=""
                    data-speed="400" data-space-between="20" data-pagination="false" data-navigation="true"
                    data-autoplay="true" data-autoplay-delay="3000" data-effect="slide"
                    data-breakpoints='{"320": {"slidesPerView": 2}, "768": {"slidesPerView": 3}, "1024": {"slidesPerView": 4}, "1440": {"slidesPerView": 6}}'>
                    <div class="swiper-wrapper py-4 text-center">
                        <?php foreach ($similarProducts as $product): ?>
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

        <?php if (!empty($categoryProducts)): ?>
            <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
                <div class="row bg-white p-4 rounded-t-lg">
                    <div class="flex justify-between">
                        <h2 class="text-lg font-medium z-10"><?php echo lang('website.category_products'); ?></h2>
                    </div>
                </div>

                <div class="swiper-container swiper rounded-e-lg bg-white px-3" id="swiper-1" data-pagination-type=""
                    data-speed="400" data-space-between="20" data-pagination="false" data-navigation="true"
                    data-autoplay="true" data-autoplay-delay="3000" data-effect="slide"
                    data-breakpoints='{"320": {"slidesPerView": 2}, "768": {"slidesPerView": 3}, "1024": {"slidesPerView": 4}, "1440": {"slidesPerView": 6}}'>
                    <div class="swiper-wrapper py-4 text-center">
                        <?php foreach ($categoryProducts as $product): ?>
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


        <?= $this->include('website/template/mobileBottomMenu') ?>
        <?= $this->include('website/template/productVarientPopup') ?>

    </main>
    <?= $this->include('website/template/shopCart') ?>
    <?= $this->include('website/template/footer') ?>
    <?= $this->include('website/template/script') ?>


    <div id="writeReviewModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-40 px-4 md:px-0">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-4">
            <div class="flex justify-between mb-6 border-b">
                <h2 class="text-base font-semibold pb-1"> </h2>
                <i class="fi fi-rr-circle-xmark text-red-800 cursor-pointer" onclick="closeWriteReviewPopup()"></i>
            </div>
            <form class="writeReviewForm">
                <input type="hidden" name="product_id" id="product_id" />

                <!-- Star Rating -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo lang('website.rating'); ?>
                        <div id="ratingError" class="text-red-500 text-sm hidden"></div>
                    </label>
                    <div class="flex space-x-2 <?= flex_direction() ?> text-2xl" id="starRating">
                        <i class="fi fi-rr-star-exclamation cursor-pointer text-gray-400" data-value="1"></i>
                        <i class="fi fi-rr-star-exclamation cursor-pointer text-gray-400" data-value="2"></i>
                        <i class="fi fi-rr-star-exclamation cursor-pointer text-gray-400" data-value="3"></i>
                        <i class="fi fi-rr-star-exclamation cursor-pointer text-gray-400" data-value="4"></i>
                        <i class="fi fi-rr-star-exclamation cursor-pointer text-gray-400" data-value="5"></i>
                    </div>
                    <input type="hidden" name="rating" id="rating" value="0" />
                </div>

                <!-- Title Input -->
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1"><?php echo lang('website.title'); ?> </label>
                    <input type="text" id="title" name="title" class="w-full border border-gray-300 rounded-lg p-2 text-sm text-gray-900 focus:ring-green-600 focus:border-green-600" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.title'); ?>" />
                    <div id="titleError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <!-- Notes/Message -->
                <div class="mb-4">
                    <label for="note" class="block text-sm font-medium text-gray-700 mb-1"><?php echo lang('website.review'); ?></label>
                    <textarea id="review" name="review" rows="3" class="w-full border border-gray-300 rounded-lg p-2 text-sm text-gray-900 focus:ring-green-600 focus:border-green-600" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.review'); ?>"></textarea>
                    <div id="reviewError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <!-- Save Button -->
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium text-sm py-2 px-4 rounded-lg shadow focus:ring-2 focus:ring-green-500 focus:ring-offset-1">
                    <?php echo lang('website.send_review'); ?>
                </button>
            </form>
        </div>
    </div>

    <script src="<?= base_url('/assets/page-script/website/productDetails.js') ?>"></script>

    <script>
        // Configuration
        const zoomConfig = {
            swiperContainer: '#productSwiper',
            thumbnailsContainer: '#productThumbnails',
            zoomLensSize: 50,
            zoomWindowSize: 600,
            zoomLevel: 2.5
        };

        // Global variables
        var currentSlideIndex = 0;
        var isZooming = false;
        var zoomLens = null;
        var zoomWindow = null;
        var zoomImage = null;
        var productZoomModal = null;
        var swiper = null; // This will hold our Swiper instance

        // Initialize zoom functionality
        function initProductZoom() {
            // Initialize Swiper first
            initSwiper();

            // Then initialize zoom functionality
            createZoomElements();
            setupEventListeners();
            setupThumbnailNavigation();
            setupModalZoom();
        }

        // Initialize Swiper
        function initSwiper() {
            swiper = new Swiper('#productSwiper', {
                slidesPerView: 1,
                spaceBetween: 0,
                loop: false,
                grabCursor: true,
                on: {
                    slideChange: function() {
                        currentSlideIndex = this.activeIndex;
                        updateActiveThumbnail(currentSlideIndex);
                    }
                }
            });
        }

        // Create zoom lens and window elements
        function createZoomElements() {
            // Create zoom lens
            zoomLens = document.createElement('div');
            zoomLens.className = 'zoom-lens';
            zoomLens.style.cssText = `
                position: absolute;
                width: ${zoomConfig.zoomLensSize}px;
                height: ${zoomConfig.zoomLensSize}px;
                border: 2px solid #ccc;
                background: rgba(255,255,255,0.3);
                cursor: none;
                pointer-events: none;
                z-index: 10;
                display: none;
                border-radius: 50%;
            `;

            // Create zoom window
            zoomWindow = document.createElement('div');
            zoomWindow.className = 'zoom-window';
            zoomWindow.style.cssText = `
                position: absolute;
                width: ${zoomConfig.zoomWindowSize}px;
                height: ${zoomConfig.zoomWindowSize}px;
                border: 2px solid #ccc;
                background: white;
                z-index: 20;
                display: none;
                overflow: hidden;
                box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                border-radius: 8px;
            `;

            // Create zoom image inside zoom window
            zoomImage = document.createElement('img');
            zoomImage.style.cssText = `
                position: absolute;
                pointer-events: none;
                max-width: none;
                max-height: none;
            `;
            zoomWindow.appendChild(zoomImage);

            // Append to body
            document.body.appendChild(zoomLens);
            document.body.appendChild(zoomWindow);
        }

        // Setup event listeners for zoom functionality
        function setupEventListeners() {
            const swiperContainer = document.querySelector(zoomConfig.swiperContainer);
            const swiperSlides = swiperContainer.querySelectorAll('.swiper-slide');

            swiperSlides.forEach(function(slide, index) {
                const img = slide.querySelector('img');

                // Mouse enter - show zoom elements
                slide.addEventListener('mouseenter', function(e) {
                    startZoom(slide, img, index);
                });

                // Mouse move - update zoom position
                slide.addEventListener('mousemove', function(e) {
                    if (isZooming) {
                        updateZoom(e, slide, img);
                    }
                });

                // Mouse leave - hide zoom elements
                slide.addEventListener('mouseleave', function() {
                    stopZoom();
                });

                // Click - open modal zoom
                slide.addEventListener('click', function(e) {
                    openModalZoom(img.src, index);
                });
            });
        }

        // Start zoom functionality
        function startZoom(slide, img, index) {
            isZooming = true;
            currentSlideIndex = index;

            // Set zoom image source
            zoomImage.src = img.src;

            // Calculate zoom image size
            const zoomImageWidth = img.naturalWidth || img.width;
            const zoomImageHeight = img.naturalHeight || img.height;

            zoomImage.style.width = (zoomImageWidth * zoomConfig.zoomLevel) + 'px';
            zoomImage.style.height = (zoomImageHeight * zoomConfig.zoomLevel) + 'px';

            // Position zoom window
            const slideRect = slide.getBoundingClientRect();
            zoomWindow.style.left = (slideRect.right + 20) + 'px';
            zoomWindow.style.top = slideRect.top + 'px';

            // Show zoom elements
            zoomLens.style.display = 'block';
            zoomWindow.style.display = 'block';

            // Add zoom cursor to slide
            slide.style.cursor = 'zoom-in';
        }

        // Update zoom position based on mouse movement
        function updateZoom(e, slide, img) {
            const slideRect = slide.getBoundingClientRect();
            const imgRect = img.getBoundingClientRect();

            // Calculate mouse position relative to image
            const mouseX = e.clientX - imgRect.left;
            const mouseY = e.clientY - imgRect.top;

            // Calculate lens position
            const lensX = mouseX - zoomConfig.zoomLensSize / 2;
            const lensY = mouseY - zoomConfig.zoomLensSize / 2;

            // Constrain lens within image bounds
            const maxLensX = imgRect.width - zoomConfig.zoomLensSize;
            const maxLensY = imgRect.height - zoomConfig.zoomLensSize;

            const constrainedLensX = Math.max(0, Math.min(lensX, maxLensX));
            const constrainedLensY = Math.max(0, Math.min(lensY, maxLensY));

            // Position lens
            zoomLens.style.left = (imgRect.left + constrainedLensX) + 'px';
            zoomLens.style.top = (imgRect.top + constrainedLensY) + 'px';

            // Calculate zoom image position
            const zoomImageX = -(constrainedLensX / imgRect.width) * (zoomImage.offsetWidth - zoomConfig.zoomWindowSize);
            const zoomImageY = -(constrainedLensY / imgRect.height) * (zoomImage.offsetHeight - zoomConfig.zoomWindowSize);

            // Position zoom image
            zoomImage.style.left = zoomImageX + 'px';
            zoomImage.style.top = zoomImageY + 'px';
        }

        // Stop zoom functionality
        function stopZoom() {
            isZooming = false;

            // Hide zoom elements
            zoomLens.style.display = 'none';
            zoomWindow.style.display = 'none';

            // Reset cursor
            const slides = document.querySelectorAll('.swiper-slide');
            slides.forEach(function(slide) {
                slide.style.cursor = 'default';
            });
        }

        // Setup thumbnail navigation
        function setupThumbnailNavigation() {
            const thumbnails = document.querySelectorAll('#productThumbnails .thumbnails-img');

            thumbnails.forEach(function(thumbnail, index) {
                thumbnail.addEventListener('click', function() {
                    switchToSlide(index);
                });
            });
        }

        // Switch to specific slide - FIXED VERSION
        function switchToSlide(index) {
            currentSlideIndex = index;

            // Use Swiper's slideTo method
            if (swiper && swiper.slideTo) {
                swiper.slideTo(index);
            }

            // Update active thumbnail
            updateActiveThumbnail(index);
        }

        // Update active thumbnail styling
        function updateActiveThumbnail(index) {
            const thumbnails = document.querySelectorAll('#productThumbnails .thumbnails-img');

            thumbnails.forEach(function(thumbnail, i) {
                if (i === index) {
                    thumbnail.classList.add('active');
                } else {
                    thumbnail.classList.remove('active');
                }
            });
        }

        // Setup modal zoom functionality
        function setupModalZoom() {
            // Create modal structure
            productZoomModal = document.createElement('div');
            productZoomModal.className = 'zoom-modal';
            productZoomModal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.9);
                z-index: 1000;
                display: none;
                justify-content: center;
                align-items: center;
            `;

            var modalContent = document.createElement('div');
            modalContent.className = 'zoom-modal-content';
            modalContent.style.cssText = `
                position: relative;
                max-width: 90%;
                max-height: 90%;
                background: white;
                border-radius: 8px;
                overflow: hidden;
            `;

            var modalImage = document.createElement('img');
            modalImage.className = 'zoom-modal-image';
            modalImage.style.cssText = `
                width: 100%;
                height: 100%;
                object-fit: contain;
                cursor: zoom-in;
            `;

            var closeButton = document.createElement('button');
            closeButton.innerHTML = '×';
            closeButton.className = 'zoom-modal-close';
            closeButton.style.cssText = `
                position: absolute;
                top: 10px;
                right: 15px;
                background: rgba(255,255,255,0.8);
                border: none;
                font-size: 24px;
                cursor: pointer;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            `;

            modalContent.appendChild(modalImage);
            modalContent.appendChild(closeButton);
            productZoomModal.appendChild(modalContent);
            document.body.appendChild(productZoomModal);

            // Modal event listeners
            closeButton.addEventListener('click', closeModalZoom);
            productZoomModal.addEventListener('click', function(e) {
                if (e.target === productZoomModal) {
                    closeModalZoom();
                }
            });

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (productZoomModal.style.display === 'flex') {
                    if (e.key === 'Escape') {
                        closeModalZoom();
                    } else if (e.key === 'ArrowLeft') {
                        navigateModal(-1);
                    } else if (e.key === 'ArrowRight') {
                        navigateModal(1);
                    }
                }
            });

            // Zoom functionality within modal
            var isModalZoomed = false;
            var modalZoomLevel = 1;

            modalImage.addEventListener('click', function(e) {
                if (!isModalZoomed) {
                    modalZoomLevel = 2;
                    modalImage.style.transform = 'scale(2)';
                    modalImage.style.cursor = 'zoom-out';
                    isModalZoomed = true;
                } else {
                    modalZoomLevel = 1;
                    modalImage.style.transform = 'scale(1)';
                    modalImage.style.cursor = 'zoom-in';
                    isModalZoomed = false;
                }
            });
        }

        // Open modal zoom
        function openModalZoom(imageSrc, index) {
            var modalImage = productZoomModal.querySelector('.zoom-modal-image');
            modalImage.src = imageSrc;
            productZoomModal.style.display = 'flex';
            currentSlideIndex = index;

            // Reset zoom
            modalImage.style.transform = 'scale(1)';
            modalImage.style.cursor = 'zoom-in';

            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }

        // Close modal zoom
        function closeModalZoom() {
            productZoomModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Navigate within modal
        function navigateModal(direction) {
            var slides = document.querySelectorAll('.swiper-slide');
            var totalSlides = slides.length;

            currentSlideIndex = (currentSlideIndex + direction + totalSlides) % totalSlides;

            var newImageSrc = slides[currentSlideIndex].querySelector('img').src;
            var modalImage = productZoomModal.querySelector('.zoom-modal-image');
            modalImage.src = newImageSrc;

            // Reset zoom
            modalImage.style.transform = 'scale(1)';
            modalImage.style.cursor = 'zoom-in';

            // Update main swiper and thumbnails
            switchToSlide(currentSlideIndex);
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            initProductZoom();
        });
    </script>
</body>

</html>