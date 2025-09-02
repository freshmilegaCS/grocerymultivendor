<!doctype html>
<html lang="<?= session()->get('site_lang') ?? 'en' ?>" dir="<?= dir_attribute() ?>">

<head>
    <?= $this->include('website/template/style') ?>
    <title><?= $settings['business_name'] ?></title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100">
    <?= $this->include('website/template/header') ?>
    <main class="max-w-7xl mx-auto">
        <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
            <div class="row bg-white mb-2 p-4 rounded-lg">
                <div class="flex justify-between">
                    <h2 class="text-lg font-medium z-10"><?php echo lang('website.checkout'); ?></h2>
                </div>
            </div>
        </section>
        <section class="mt-2 md:mt-4 md:container md:mx-auto md:px-3">
            <ul class="w-full max-w-lg mx-auto my-12 pt-2 pb-5 px-4 flex items-center justify-center">
                <li class="w-full flex  after:w-full after:h-1 after:content-[''] last:after:hidden last:w-fit after:bg-green-600">
                    <a href="/cart" class="flex flex-col items-center gap-4 -mt-[13px] relative"><i class="fi fi-tr-check-circle text-lg w-[30px] h-[30px] leading-[34px] text-center rounded-2xl text-white bg-green-600"></i><small class="text-secondary text-sm font-medium capitalize absolute -bottom-8">Cart</small></a>
                </li>
                <li class="w-full flex  after:w-full after:h-1 last:after:hidden last:w-fit after:bg-gray-200">
                    <a href="/checkout" class="flex flex-col items-center gap-4 -mt-[13px] relative"><span
                            class="w-[30px] h-[30px] border-[4px] rounded-2xl border-[#D9DBE9] bg-[#D9DBE9]"></span><small
                            class="text-secondary text-sm font-medium capitalize absolute -bottom-8">Checkout</small></a>
                </li>
                <li class="w-full flex  after:w-full after:h-1 last:after:hidden last:w-fit after:bg-gray-200">
                    <a href="#" class="flex flex-col items-center gap-4 -mt-[13px] relative"><span
                            class="w-[30px] h-[30px] border-[4px] rounded-2xl border-[#D9DBE9] bg-[#D9DBE9]"></span><small
                            class="text-secondary text-sm font-medium capitalize absolute -bottom-8">Order</small></a>
                </li>
            </ul>



            <div class="flex flex-wrap lg:flex-nowrap lg:gap-x-12 gap-y-6">
                <div class="lg:w-2/3 w-full">
                    <div class="flex flex-col gap-1">

                        <div class="mb-2 rounded-lg bg-white">
                            <div
                                class="flex  items-center justify-between gap-3 p-4 border-b border-gray-100">
                                <h4 class="font-bold capitalize"><?php echo lang('website.delivery_address'); ?></h4>
                                <div class="flex  items-center gap-4">
                                    <!-- <button type="button"
                                        class="px-3 h-8 leading-8 rounded-lg flex items-center gap-2 bg-[#E6FFF0] text-success">
                                        <i class="fi fi-tr-pen-nib"></i>
                                        <span
                                            class="text-sm font-medium capitalize whitespace-nowrap text-green-600">Edit</span>
                                    </button> -->
                                    <button type="button" onclick="openAddressPopup()"
                                        class="px-3 h-8 leading-8 rounded-lg flex items-center gap-2 bg-[#FFF4F1] text-primary">
                                        <i class="fi fi-tr-add"></i>
                                        <span
                                            class="text-sm font-medium capitalize whitespace-nowrap text-red-600"><?php echo lang('website.add_new'); ?></span>
                                    </button>
                                </div>
                            </div>
                            <div class="md:flex gap-6 p-4 address-div"></div>
                        </div>

                        <div class="mb-2 rounded-lg bg-white">
                            <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-100">
                                <h4 class="font-bold capitalize"><?php echo lang('website.delivery_method'); ?></h4>
                            </div>


                            <div class="flex flex-col md:flex-row gap-4 p-4">
                                <?php if (!empty($home_delivery_status) && isset($home_delivery_status['status']) && $home_delivery_status['status'] == 1): ?>
                                    <div id="<?= $home_delivery_status['id'] ?>" class="deliveryMethod flex flex-row w-full sm:w-1/3 p-2 border border-green-700 rounded-lg cursor-pointer items-center text-green-700 bg-white hover:bg-green-50" onclick="selectDeliveryMethod('<?= $home_delivery_status['id'] ?>')">
                                        <img src="<?= base_url() . '/' . $home_delivery_status['image'] ?>" class="w-10 h-10 mr-2" alt="<?= esc($home_delivery_status['title']) ?>" />
                                        <div>
                                            <p class="font-semibold"><?= esc($home_delivery_status['title']) ?></p>
                                            <p class="text-gray-500 text-xs"><?= esc($home_delivery_status['description']) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($schedule_delivery_status) && isset($schedule_delivery_status['status']) && $schedule_delivery_status['status'] == 1): ?>
                                    <div id="<?= esc($schedule_delivery_status['id']) ?>" class="deliveryMethod flex flex-row w-full sm:w-1/3 p-2 border border-green-700 rounded-lg cursor-pointer items-center text-green-700 bg-white hover:bg-green-50" onclick="selectDeliveryMethod('<?= esc($schedule_delivery_status['id']) ?>')">
                                        <img src="<?= base_url() . '/' . esc($schedule_delivery_status['image']) ?>" class="w-10 h-10 mr-2" alt="<?= esc($schedule_delivery_status['title']) ?>" />
                                        <div>
                                            <p class="font-semibold"><?= esc($schedule_delivery_status['title']) ?></p>
                                            <p class="text-gray-500 text-xs"><?= esc($schedule_delivery_status['description']) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($takeaway_status) && isset($takeaway_status['status']) && $takeaway_status['status'] == 1 && $seller_only_one_seller_cart == 1): ?>
                                    <div id="<?= esc($takeaway_status['id']) ?>" class="deliveryMethod flex flex-row w-full sm:w-1/3 p-2 border border-green-700 rounded-lg cursor-pointer items-center text-green-700 bg-white hover:bg-green-50" onclick="selectDeliveryMethod('<?= esc($takeaway_status['id']) ?>')">
                                        <img src="<?= base_url() . '/' . esc($takeaway_status['image']) ?>" class="w-10 h-10 mr-2" alt="<?= esc($takeaway_status['title']) ?>" />
                                        <div>
                                            <p class="font-semibold"><?= esc($takeaway_status['title']) ?></p>
                                            <p class="text-gray-500 text-xs"><?= esc($takeaway_status['description']) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>


                            </div>

                        </div>



                        <div class="mb-2 rounded-lg bg-white hidden" id="deliveryDateDiv">
                            <div
                                class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-100">
                                <h4 class="font-bold capitalize"><?php echo lang('website.delivery_date'); ?></h4>
                            </div>

                            <div class="swiper-container swiper rounded-lg bg-white" data-pagination-type="" data-speed="400" data-space-between="20" data-pagination="false" data-navigation="true" data-autoplay="false" data-autoplay-delay="3000"
                                data-effect="slide" data-breakpoints='{"320": {"slidesPerView": 3}, "375": {"slidesPerView": 3}, "768": {"slidesPerView": 6}, "1024": {"slidesPerView": 6}, "1280": {"slidesPerView": 7}}'>
                                <div class="swiper-wrapper p-2 text-center">
                                    <?php foreach ($days as $day): ?>
                                        <div class="swiper-slide">
                                            <button class="flex flex-col border py-2 px-6 rounded-lg bg-gray-100 border-gray-500 text-gray-700 date"
                                                data-day="<?= $day['day'] ?>" data-date="<?= $day['date'] ?>"
                                                onclick="setActiveDate(this)">
                                                <?= $day['day'] ?>
                                                <small><?= $day['date'] ?></small>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="swiper-pagination"></div>
                            </div>
                        </div>

                        <div class="mb-2 rounded-lg bg-white hidden" id="deliveryTimeDiv">
                            <div
                                class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-100">
                                <h4 class="font-bold capitalize"><?php echo lang('website.delivery_time'); ?></h4>
                            </div>

                            <div class="swiper-container swiper rounded-lg bg-white" data-pagination-type=""
                                data-speed="400" data-space-between="20" data-pagination="false"
                                data-navigation="true" data-autoplay="true" data-autoplay-delay="3000"
                                data-effect="slide"
                                data-breakpoints='{"320": {"slidesPerView": 2}, "375": {"slidesPerView": 2}, "768": {"slidesPerView": 4}, "1024": {"slidesPerView": 4}}'>
                                <div class="swiper-wrapper p-2 text-center" id="timeslotDiv">

                                </div>
                            </div>
                        </div>

                        <div class="mb-2 rounded-lg bg-white">
                            <h4 class="font-bold capitalize p-4 border-b border-gray-100"><?php echo lang('website.select_payment_method'); ?></h4>
                            <div class="grid grid-cols-3 sm:grid-cols-5 gap-4 p-4">
                                <?php foreach ($paymentMethods as $paymentMethod): ?>
                                    <div class="border flex flex-col items-center justify-center gap-2.5 py-4 rounded-lg cursor-pointer" id="paymentMethod_<?= $paymentMethod['id'] ?>" onclick="setPaymentMethode(<?= $paymentMethod['id'] ?>)">
                                        <img class="h-6" src="<?= base_url() . $paymentMethod['img'] ?>" alt="<?= $paymentMethod['title'] ?>">
                                        <span class="text-xs font-medium"><?= $paymentMethod['title'] ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- sidebar -->
                <div class="w-full lg:w-1/3 md:w-full lg:sticky lg:top-6 self-start">
                    <div id="couponDiv">
                        <!-- Apply Coupon Section -->
                        <div id="applyCouponDiv" class="mb-2 rounded-lg border border-green-500 flex items-center gap-4 p-3 cursor-pointer bg-white" onclick="openCouponPopup()">
                            <div class="relative flex-shrink-0 w-10 h-10 flex items-center justify-center">
                                <i class="fi fi-rr-badge-percent text-2xl text-green-600"></i>
                            </div>
                            <div class="flex-auto overflow-hidden">
                                <h4 class="font-semibold leading-5 mb-1 text-ellipsis capitalize text-green-700"><?php echo lang('website.apply_coupon_code'); ?></h4>
                                <h5 class="text-sm font-normal text-gray-500"><?php echo lang('website.get_discount_with_your_order'); ?></h5>
                            </div>
                            <i class="fi fi-tr-angle-right text-2xl text-green-600"></i>
                        </div>

                        <!-- Coupon Applied Section -->
                        <div id="couponAppliedDiv" class="mb-2 rounded-lg border border-green-500 flex items-center gap-4 p-3 cursor-pointer bg-white hidden">
                            <div class="relative flex-shrink-0 w-10 h-10 flex items-center justify-center bg-green-50 rounded-full">
                                <i class="fi fi-rr-badge-percent text-2xl text-green-600"></i>
                            </div>
                            <div class="flex-auto overflow-hidden">
                                <h4 class="font-semibold leading-5 mb-1 text-ellipsis capitalize text-green-700"><?php echo lang('website.coupon_applied'); ?></h4>
                                <h5 class="text-sm font-normal text-gray-500">
                                    <?php echo lang('website.you_saved'); ?> <span class="couponAmount"></span>
                                </h5>
                            </div>
                            <i class="fi fi-rr-trash text-red-500 text-lg cursor-pointer" onclick="removeCoupon()"></i>
                        </div>
                    </div>



                    <!-- card -->
                    <div x-data="{ showSummary: false }" class="relative card min-w-0 bg-white p-4 rounded-lg">
                        <div class="card-body flex flex-col gap-4">
                            <!-- heading -->
                            <div class="flex justify-between items-center pb-3 border-b">
                                <h2 class="text-md font-bold"><?php echo lang('website.order_summary'); ?></h2>
                                <button id="toggleSummaryBtn" class="text-sm text-green-600"><?php echo lang('website.show_details'); ?></button>
                            </div>
                            <div id="orderSummaryDetails" class="relative flex flex-col min-w-0 rounded-lg break-words bg-white hidden">
                                <!-- list group -->
                                <ul class="flex flex-col">
                                    <li class="relative py-2 -mb-px  no-underline flex justify-between items-start">
                                        <div>
                                            <div><?php echo lang('website.subtotal'); ?></div>
                                        </div>
                                        <div>
                                            <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                <?= $country['currency_symbol'] ?><span class="subtotal"><?= esc($subtotal) ?></span>
                                            <?php else: ?>
                                                <span class="subtotal"><?= esc($subtotal) ?></span><?= $country['currency_symbol'] ?>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="relative py-2 -mb-px  no-underline flex justify-between items-start">
                                        <div>
                                            <div class="flex flex-row"><?php echo lang('website.tax'); ?>
                                                <div class="relative group">
                                                    <i class="fi fi-rr-lightbulb-question cursor-pointer text-xs"></i>
                                                    <!-- Tooltip -->
                                                    <div class="absolute hidden group-hover:block bg-gray-800 text-white text-xs py-1 px-2 rounded-lg shadow-md left-[750%] transform -translate-x-1/2 whitespace-nowrap">
                                                        <?php echo lang('website.some_product_based_tax'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                <?= $country['currency_symbol'] ?><span class="taxTotal"><?= esc($taxTotal) ?></span>
                                            <?php else: ?>
                                                <span class="taxTotal"><?= esc($taxTotal) ?></span><?= $country['currency_symbol'] ?>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="relative py-2 -mb-px  no-underline flex justify-between items-start">
                                        <div>
                                            <div class="flex flex-row"><?php echo lang('website.delivery_charge');?>
                                                <div class="relative group">
                                                    <i class="fi fi-rr-lightbulb-question cursor-pointer text-xs"></i>
                                                    <!-- Tooltip -->
                                                    <div class="absolute hidden group-hover:block bg-gray-800 text-white text-xs py-1 px-2 rounded-lg shadow-md left-[750%] transform -translate-x-1/2 whitespace-nowrap">
                                                    <?php echo lang('website.calculate_based_on_delivery_distance');?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                <?= $country['currency_symbol'] ?><span class="deliveryCharge"><?= esc($deliveryCharge) ?></span>
                                            <?php else: ?>
                                                <span class="deliveryCharge"><?= esc($deliveryCharge) ?></span><?= $country['currency_symbol'] ?>
                                            <?php endif; ?>
                                        </div>
                                    </li>

                                    <?php if ($additional_charge_status == 1): ?>
                                        <li class="relative py-2 -mb-px  no-underline flex justify-between items-start additional_charge_div">
                                            <div>
                                                <?= $additional_charge_name ?>
                                            </div>
                                            <div>
                                                <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                    <?= $country['currency_symbol'] ?><span class="additional_charge"><?= esc($additional_charge) ?></span>
                                                <?php else: ?>
                                                    <span class="additional_charge"><?= esc($additional_charge) ?></span><?= $country['currency_symbol'] ?>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                    <?php endif; ?>

                                    <li class="relative py-2 -mb-px  no-underline flex justify-between items-start">
                                        <div>
                                            <div><?php echo lang('website.discount');?> <span class="text-xs text-red-700 discountInPercIfApplicable"></span></div>
                                        </div>
                                        <div>
                                            <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                <?= $country['currency_symbol'] ?><span class="couponAmount">0</span>
                                            <?php else: ?>
                                                <span class="couponAmount">0</span><?= $country['currency_symbol'] ?>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li class="relative py-2 -mb-px  no-underline flex justify-between items-start">
                                        <div>
                                            <div><?php echo lang('website.wallet');?> <span class="text-xs text-green-700 cursor-pointer remaining_wallet_balance" onclick="applyWallet()">(<?= esc($wallet) ?> apply)</span></div>
                                        </div>
                                        <div>
                                            <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                <?= $country['currency_symbol'] ?><span class="wallet_applied">0</span>
                                            <?php else: ?>
                                                <span class="wallet_applied">0</span><?= $country['currency_symbol'] ?>
                                            <?php endif; ?>
                                        </div>

                                    </li>

                                </ul>
                            </div>
                            <div class="flex justify-between border-t pt-3 font-bold text-gray-800 text-base">
                                <div><?php echo lang('website.grand_total');?></div>
                                <div>
                                    <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                        <?= $country['currency_symbol'] ?><span class="grand_total">0</span>
                                    <?php else: ?>
                                        <span class="grand_total">0</span><?= $country['currency_symbol'] ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <div class="grid">
                                    <!-- btn -->
                                    <button onclick="verifyOrderDetails()" id="verifyOrderDetails"
                                        class="btn flex justify-between bg-green-600 text-white rounded-lg p-3 border-green-600 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 active:bg-green-700 active:border-green-700 focus:outline-none focus:ring-4 focus:ring-green-300 btn-lg">
                                        <?php echo lang('website.save_&_pay');?>
                                        <div>
                                            <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                <?= $country['currency_symbol'] ?><span class="font-bold grand_total">0</span>
                                            <?php else: ?>
                                                <span class="font-bold grand_total">0</span><?= $country['currency_symbol'] ?>
                                            <?php endif; ?>
                                        </div>

                                    </button>

                                    <div id="paypal-button-container" class="hidden"></div>

                                </div>
                                <!-- text -->
                                <p class="mt-1">
                                    <span class="text-sm">
                                    <?php echo lang('website.by_placing_your_order_you_agree_to_be_bound_by_the');?> <b><?= $settings['business_name'] ?></b>
                                        <a href="<?php echo base_url() . 'terms-condition' ?>" target="_blank" class="text-green-600"><?php echo lang('website.terms_condition');?></a>
                                        <?php echo lang('website.and');?>
                                        <a href="<?php echo base_url() . 'privacy-policy' ?>" target="_blank" class="text-green-600"><?php echo lang('website.privacy_policy');?>.</a>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="card-element"></div>

        </section>


        <?= $this->include('website/template/mobileBottomMenu') ?>
        <div id="orderResponsePopup" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 hidden"></div>
    </main>
    <?= $this->include('website/template/shopCart') ?>
    <?= $this->include('website/template/address') ?>
    <?= $this->include('website/template/coupon') ?>
    <?= $this->include('website/template/footer') ?>
    <?= $this->include('website/template/script') ?>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="https://js.paystack.co/v2/inline.js"></script>
    <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
    <script type="text/javascript" src="https://js.stripe.com/v3/"></script>

    <?= $this->include('website/template/checkoutScript') ?>

    <script>
        const toggleBtn = document.getElementById('toggleSummaryBtn');
        const summaryDetails = document.getElementById('orderSummaryDetails');

        let isVisible = false;

        toggleBtn.addEventListener('click', () => {
            isVisible = !isVisible;
            if (isVisible) {
                summaryDetails.classList.remove('hidden');
                toggleBtn.textContent = 'Hide Details';
            } else {
                summaryDetails.classList.add('hidden');
                toggleBtn.textContent = 'Show Details';
            }
        });
    </script>

</body>

</html>