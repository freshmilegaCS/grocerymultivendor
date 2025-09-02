<!doctype html>
<html lang="<?= session()->get('site_lang') ?? 'en' ?>" dir="<?= dir_attribute() ?>">

<head>
    <?= $this->include('website/template/style') ?>
    <title><?= $settings['business_name'] ?></title>
</head>

<body class="bg-gray-100">
    <?= $this->include('website/template/header') ?>
    <main class="max-w-7xl mx-auto">

        <div class="mt-2 md:mt-4 md:container md:mx-auto px-3">
            <div class="relative flex flex-col min-w-0 rounded-lg break-words bg-white p-4 mb-6">
                <div class="flex-auto">
                    <h1 class="text-base">Popular Products</h1>
                </div>
            </div>
            <section class="w-full bg-white rounded-lg p-4">
                
                <div class="flex flex-col md:flex-row justify-between lg:items-center mb-6 gap-3">
                    <div>
                        <p>
                            <span class="text-gray-900"><?= count($popularProducts) ?></span>
                            Products found
                        </p>
                    </div>
                    <div class="flex flex-col md:flex-row justify-between md:items-center gap-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <a href="shop-list.html" class="text-gray-800">
                                    <i class="fi fi-tr-list-timeline text-lg"></i>
                                </a>
                                <a href="shop-grid.html" class="active text-green-600">
                                    <i class="fi fi-tr-objects-column text-lg"></i>
                                </a>
                            </div>
                            <div class="ml-3 lg:hidden">
                                <a class="btn inline-flex items-center gap-x-2 bg-white text-gray-800 border-gray-300 border disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-gray-700 hover:border-gray-700 active:bg-gray-700 active:border-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300" data-bs-toggle="mini-shopping-cart" href="#mini-shopping-cartCategory" role="button" aria-controls="mini-shopping-cartCategory">
                                    <i class="fi fi-tr-filter"></i>
                                    Filters
                                </a>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div class="flex-grow-1">
                                <select class="text-sm p-2 block w-full border text-gray-700 border-gray-300 rounded-lg focus:border-green-600 focus:ring-green-600 disabled:opacity-50 disabled:pointer-events-none">
                                    <option selected="">Show: 50</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="30">30</option>
                                </select>
                            </div>
                            <div>
                                <select class="text-sm p-2 block w-full border text-gray-700 border-gray-300 rounded-lg focus:border-green-600 focus:ring-green-600 disabled:opacity-50 disabled:pointer-events-none">
                                    <option selected="">Sort by: Featured</option>
                                    <option value="Low to High">Price: Low to High</option>
                                    <option value="High to Low">Price: High to Low</option>
                                    <option value="Release Date">Release Date</option>
                                    <option value="Avg. Rating">Avg. Rating</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid lg:grid-cols-6 md:grid-cols-4 grid-cols-2 gap-4">
                    <?php foreach ($popularProducts as $product): ?>
                        <?php $firstVarient = $product['variants'][0]; ?>
                        <div class="rounded-lg bg-white border border-green-500" id="<?= $product['slug'] ?>">
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
                                        <span class="absolute  text-xs text-white font-bold left-[8px] top-1 break-words">off</span>
                                    <?php endif; ?>
                                    <a href="#!">
                                        <img src="<?= base_url($product['main_img']) ?>"
                                            alt="<?= $product['product_name'] ?>" class="w-4/5 h-auto ml-auto mr-auto" />
                                    </a>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <h3 class="text-sm truncate font-semibold"><a href="shop-single.html"><?= $product['product_name'] ?></a></h3>
                                    <span class="text-xs text-gray-500 "><?php echo $firstVarient['title'] ?></span>
                                    <div class="flex justify-between items-center mt-2">
                                        <div class="flex flex-col">
                                            <?php if ($firstVarient['discounted_price'] > 0): ?>
                                                <span class="text-sm text-gray-900 font-semibold"><?= $firstVarient['discounted_price'] ?></span>
                                                <span class="line-through text-gray-500 text-xs"><?= $firstVarient['price'] ?></span>
                                            <?php else: ?>
                                                <span class="text-sm text-gray-900 font-semibold"><?= $firstVarient['price'] ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <button type="button" onclick="openProductVariantPopup(<?= $product['id'] ?>, '<?= $product['slug'] ?>')" class="text-sm px-2 py-1 rounded-lg items-center gap-x-1 bg-green-600 text-white border-green-600 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 btn-sm">
                                            <i class="fi fi-tr-cart-shopping-fast"></i>
                                            <span>Add</span>
                                        </button>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
                
            </section>
        </div>


        <?= $this->include('website/template/mobileBottomMenu') ?>
        <?= $this->include('website/template/productVarientPopup') ?>

    </main>
    <?= $this->include('website/template/shopCart') ?>
    <?= $this->include('website/template/footer') ?>
    <?= $this->include('website/template/script') ?>
</body>

</html>