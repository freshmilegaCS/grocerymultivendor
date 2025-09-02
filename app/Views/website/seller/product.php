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
                    <h2 class="text-lg font-medium z-10"><?= $seller ?></h2>
                </div>
            </div>
        </section>

        <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
            <div class="grid lg:grid-cols-6 md:grid-cols-4 grid-cols-2 gap-4">
                <?php foreach ($products as $product): ?>
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

                                    <div class="<?= $product['slug'] . '-mainbtndiv-' . $firstVarient['id'] ?>">
                                        <?php
                                        if ($product['cart_quantity']):
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
                                        <?php else: ?>
                                            <button type="button" onclick="openProductVariantPopup(<?= $product['id'] ?>, '<?= $product['slug'] ?>')"
                                                class="text-sm px-2 py-1 rounded-lg items-center gap-x-1 bg-green-700 text-white border-green-700 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 btn-sm <?= $product['slug'] . '-' . $firstVarient['id'] ?>">
                                                <i class="fi fi-rr-shopping-cart"></i>
                                                <span><?php echo lang('website.add'); ?></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>


        <?= $this->include('website/template/mobileBottomMenu') ?>
        <?= $this->include('website/template/productVarientPopup') ?>

    </main>
    <?= $this->include('website/template/shopCart') ?>
    <?= $this->include('website/template/footer') ?>
    <?= $this->include('website/template/script') ?>
</body>

</html>