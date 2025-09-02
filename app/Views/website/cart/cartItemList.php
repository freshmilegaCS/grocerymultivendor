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
                    <h2 class="text-lg font-medium z-10"><?php echo lang('website.cart');?></h2>
                </div>
            </div>
        </section>
        <section class="mt-2 md:mt-4 md:container md:mx-auto md:px-3">
            <ul class="w-full max-w-lg mx-auto my-12 pt-2 pb-5 px-4 flex items-center justify-center">
                <li
                    class="w-full flex  after:w-full after:h-1 after:content-[''] last:after:hidden last:w-fit after:bg-gray-200">
                    <a href="/cart" class="flex flex-col items-center gap-4 -mt-[13px] relative">
                        <span class="w-[30px] h-[30px] text-center rounded-full border-4 border-green-600 bg-gray-200">
                        </span>
                        <small class="text-secondary text-sm font-medium capitalize absolute -bottom-8"><?php echo lang('website.cart');?></small>
                    </a>
                </li>
                <li class="w-full flex  after:w-full after:h-1 last:after:hidden last:w-fit after:bg-gray-200">
                    <a href="#" class="flex flex-col items-center gap-4 -mt-[13px] relative"><span
                            class="w-[30px] h-[30px] border-[4px] rounded-full border-[#D9DBE9] bg-[#D9DBE9]"></span><small
                            class="text-secondary text-sm font-medium capitalize absolute -bottom-8">Checkout</small></a>
                </li>
                <li class="w-full flex  after:w-full after:h-1 last:after:hidden last:w-fit after:bg-gray-200">
                    <a href="#" class="flex flex-col items-center gap-4 -mt-[13px] relative"><span
                            class="w-[30px] h-[30px] border-[4px] rounded-full border-[#D9DBE9] bg-[#D9DBE9]"></span><small
                            class="text-secondary text-sm font-medium capitalize absolute -bottom-8">Order</small></a>
                </li>
            </ul>


            <div class="flex flex-wrap lg:flex-nowrap lg:gap-x-12 gap-y-6">
                <div class="lg:w-2/3 w-full">
                    <div class="flex flex-col gap-5">
                        <ul class="list-none bg-white rounded-lg">
                            <?php foreach ($productDetails as $productDetail): ?>
                                <li class="py-2 px-2 border-gray-300 border-b py-3 border-gray-200 <?= $productDetail['slug'] . '-maindiv-' . $productDetail['product_variant_id'] ?>">
                                    <div class="flex gap-5">
                                        <img src="<?= base_url($productDetail['main_img']) ?>" alt="<?= esc($productDetail['product_name']) ?>"
                                            class="w-28 h-28 border border-gray-300 rounded-lg" />
                                        <div class="flex flex-col gap-1 w-full">
                                            <div>
                                                <a href="<?= base_url('product/' . esc($productDetail['slug'])) ?>" class="text-base font-semibold">
                                                    <h6><?= esc($productDetail['product_name']) ?></h6>
                                                </a>
                                                <span class="text-gray-500 text-sm"><?= esc($productDetail['variant_title']) ?></span>
                                            </div>
                                            <div class="flex gap-2">
                                                <?php if ($productDetail['discounted_price'] > 0 && $productDetail['discounted_price'] < $productDetail['price']): ?>
                                                    <span class="font-bold text-gray-800">
                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                            <?= $country['currency_symbol'] ?><?= number_format($productDetail['discounted_price'], 2) ?>
                                                        <?php else: ?>
                                                            <?= number_format($productDetail['discounted_price'], 2) ?><?= $country['currency_symbol'] ?>
                                                        <?php endif; ?>
                                                    </span>
                                                    <div class="line-through text-gray-500 text-sm self-end">
                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                            <?= $country['currency_symbol'] ?><?= number_format($productDetail['price'], 2) ?>
                                                        <?php else: ?>
                                                            <?= number_format($productDetail['price'], 2) ?><?= $country['currency_symbol'] ?>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="font-bold text-gray-800">
                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                            <?= $country['currency_symbol'] ?><?= number_format($productDetail['price'], 2) ?>
                                                        <?php else: ?>
                                                            <?= number_format($productDetail['price'], 2) ?><?= $country['currency_symbol'] ?>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endif; ?>


                                            </div>

                                            <div class="flex items-center justify-between">
                                                <!-- Input group for quantity -->
                                                <div class="<?= $productDetail['slug'] . '-mainbtndiv-' . $productDetail['product_variant_id'] ?>">
                                                    <div class="flex items-center gap-1 p-1 rounded-lg bg-green-700 border border-green-700 shadow-md">
                                                        <button type="button" onclick="removeFromCart(<?= esc($productDetail['product_id']) ?>, <?= esc($productDetail['product_variant_id']) ?>)"
                                                            class="lab-fill-circle-minus text-lg leading-none hover:text-primary">
                                                            <i class="fi fi-rr-minus-small text-white"></i>
                                                        </button>
                                                        <span class="text-center w-full h-5 text-sm font-medium text-white <?= $productDetail['slug'] . '-qty-' . $productDetail['product_variant_id'] ?>"><?= esc($productDetail['quantity']) ?></span>
                                                        <button type="button" onclick="addToCart(<?= esc($productDetail['product_id']) ?>, <?= esc($productDetail['product_variant_id']) ?>)"
                                                            class="lab-fill-circle-plus text-lg leading-none hover:text-primary">
                                                            <i class="fi fi-rr-plus-small text-white"></i>
                                                        </button>
                                                    </div>
                                                </div>


                                                <!-- Remove button positioned to the right -->
                                                <div class="text-sm bg-red-100 p-1 rounded-lg shadow">
                                                    <button class="text-red-900 flex gap-1 " onclick="removeItem(<?= esc($productDetail['product_id']) ?>, <?= esc($productDetail['product_variant_id']) ?>)">
                                                        <span class="align-text-bottom">
                                                            <i class="fi fi-tr-trash-xmark text-xs"></i>
                                                        </span>
                                                        <span class="text-gray-500 text-xs text-red-600"><?php echo lang('website.remove');?></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>

                        </ul>
                    </div>
                </div>

                <!-- sidebar -->
                <div class="w-full lg:w-1/3 md:w-full lg:sticky lg:top-6 self-start">
                    <!-- card -->
                    <div class="relative card min-w-0 bg-white p-4 rounded-lg">
                        <div class="card-body flex flex-col gap-4">
                            <!-- heading -->
                            <h2 class="text-md pb-3 border-b font-bold"><?php echo lang('website.order_summary');?></h2>
                            <div class="relative flex flex-col min-w-0 rounded-lg break-words bg-white">
                                <!-- list group -->
                                <ul class="flex flex-col">
                                    <li class="relative py-2 -mb-px  no-underline flex justify-between items-start">
                                        <div>
                                            <div><?php echo lang('website.subtotal');?></div>
                                        </div>
                                        <span class="subtotal">
                                            <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                <?= $country['currency_symbol'] ?><?= esc($subtotal) ?>
                                            <?php else: ?>
                                                <?= esc($subtotal) ?><?= $country['currency_symbol'] ?>
                                            <?php endif; ?>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <div class="grid">
                                    <!-- btn -->
                                    <a href="/checkout"
                                        class="btn flex justify-between bg-green-600 text-white rounded-lg p-3 border-green-600 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 active:bg-green-700 active:border-green-700 focus:outline-none focus:ring-4 focus:ring-green-300 btn-lg"
                                        type="submit">
                                        <?php echo lang('website.go_to_checkout');?>
                                        <span class="font-bold subtotal"><?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                <?= $country['currency_symbol'] ?><?= esc($subtotal) ?>
                                            <?php else: ?>
                                                <?= esc($subtotal) ?><?= $country['currency_symbol'] ?>
                                            <?php endif; ?></span>
                                    </a>
                                </div>
                                <!-- text -->
                                <p class="mt-1 text-center text-sm text-gray-600">
                                <?php echo lang('website.delivery_Taxes_&_Discounts_calculated_at_checkout');?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </section>


        <?= $this->include('website/template/mobileBottomMenu') ?>
    </main>
    <?= $this->include('website/template/shopCart') ?>
    <?= $this->include('website/template/footer') ?>
    <?= $this->include('website/template/script') ?>
</body>

</html>