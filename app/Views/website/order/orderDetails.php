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
                    <h2 class="text-lg font-medium z-10"><?php echo lang('website.order_details'); ?></h2>
                </div>
            </div>
        </section>

        <section class="mt-2 md:mt-4 md:container md:mx-auto md:px-3">

            <div class="flex flex-wrap lg:flex-nowrap lg:gap-x-6 gap-y-6">
                <?= $this->include('website/template/dashboardSidebar') ?>

                <div class="w-full lg:w-full md:w-full mx-auto">
                    <?php if ($order['status'] < 7 || $order['status'] == 8): ?>
                        <div class="text-center rounded-2xl border border-gray-100 bg-white p-4 mb-2" id="orderTrackingDiv">
                            <h3 class="text-xl font-semibold capitalize mb-4"><?php echo lang('website.thank_you'); ?></h3>
                            <p class="text-sm font-medium mb-3"><?php echo lang('website.your_order_status_is_as_follows'); ?></p>
                            <h4 class="text-base font-medium text-gray-800">
                                <?php echo lang('website.order_id'); ?>: <span class="text-orange-600 font-semibold">#<?= $order['order_id'] ?></span>
                            </h4>
                            <ul class="w-full flex items-center justify-center pb-24 mt-8">
                                <?php foreach ($orderStatuses as $status): ?>
                                    <?php
                                    $isActive = $status['is_active'];
                                    $createdAt = $isActive ? date('Y-m-d h:i A', strtotime($status['created_at'])) : 'Pending';
                                    $bgColor = $isActive ? 'bg-green-500' : 'bg-gray-200';
                                    $textColor = $isActive ? 'text-white' : 'text-gray-500';
                                    $iconText = $isActive ? '✔' : '';
                                    ?>
                                    <li class="w-full flex items-center justify-center gap-1 relative">
                                        <hr class="<?= $isActive ? 'bg-green-500' : 'bg-gray-200' ?> block border-none w-full h-1 rounded-xl">
                                        <div class="<?= $bgColor ?> <?= $textColor ?> flex-shrink-0 w-7 h-7 flex items-center justify-center rounded-full text-sm font-bold"><?= $iconText ?></div>
                                        <hr class="<?= $isActive ? 'bg-green-500' : 'bg-gray-200' ?> block border-none w-full h-1 rounded-xl">
                                        <span class="absolute top-10 left-1/2 -translate-x-1/2 w-14 sm:w-20 text-xs sm:text-sm leading-[18px] text-center capitalize">
                                            <?php echo lang('website.order'); ?> <?= $status['name'] ?>
                                            <br>
                                            <span class="text-gray-500 text-[10px] sm:text-xs"><?= $createdAt ?></span>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>


                    <div class="w-full space-y-2 md:space-y-0 md:flex md:space-x-4 <?= flex_direction() ?>">
                        <!-- Order Details -->
                        <div class=" mb-2 rounded-2xl border border-gray-100 bg-white w-full md:w-1/2">
                            <div class="flex justify-between items-center p-4">
                                <h3 class="font-bold capitalize"><?php echo lang('website.order_details'); ?></h3>
                                <?php if ($is_order_cancelleble == 1): ?>
                                    <button
                                        type="button"
                                        id="openCancelOrderPopup"
                                        onclick="openCancelOrderPopup()"
                                        class="px-3 h-8 leading-8 rounded-lg flex items-center gap-2 bg-[#FFF4F1] text-red-600">
                                        <i class="fi fi-rr-trash"></i>
                                        <span class="text-sm font-medium capitalize whitespace-nowrap"><?php echo lang('website.cancel_order'); ?></span>
                                    </button>
                                <?php endif; ?>
                            </div>

                            <ul class="p-4 space-y-2.5 border-t border-dashed border-gray-100">
                                <li class="flex flex-wrap sm:flex-nowrap gap-2">
                                    <span class="text-sm font-semibold capitalize w-28 flex-shrink-0"><?php echo lang('website.order_id'); ?> :</span>
                                    <span class="text-sm font-semibold capitalize">#<?= $order['order_id'] ?></span>
                                </li>
                                <li class="flex flex-wrap sm:flex-nowrap gap-2">
                                    <span class="text-sm font-semibold capitalize w-28 flex-shrink-0"><?php echo lang('website.order_date'); ?> :</span>
                                    <span class="text-sm font-normal capitalize"><?= $order['order_date'] ?></span>
                                </li>
                                <li class="flex flex-wrap sm:flex-nowrap gap-2">
                                    <span class="text-sm font-semibold capitalize w-28 flex-shrink-0"><?php echo lang('website.delivery_datetime'); ?> :</span>
                                    <span class="text-sm font-normal capitalize <?php if ($order['status'] != 7): ?>
    <span class=" <?= isset($order['delivery_date']) && $order['delivery_date']
                                                                                        ? ''
                                                                                        : 'bg-red-50 text-red-600 px-2 py-1 text-xs'; ?>">
                                        <?= isset($order['delivery_date']) && $order['delivery_date']
                                                                                        ? htmlspecialchars($order['delivery_date']) . ' ' . htmlspecialchars($order['timeslot'])
                                                                                        : 'Delivery date will be added soon'; ?>
                                    </span>
                                <?php endif; ?>

                                </span>
                                </li>

                                <li class="flex flex-wrap sm:flex-nowrap gap-2">
                                    <span class="text-sm font-semibold capitalize w-28 flex-shrink-0"><?php echo lang('website.order_type'); ?>:</span>
                                    <span class="text-sm font-normal capitalize"><?= $order['delivery_method'] ?></span>
                                </li>
                                <li class="flex flex-wrap sm:flex-nowrap gap-2">
                                    <span class="text-sm font-semibold capitalize w-28 flex-shrink-0"> <?php echo lang('website.order_status'); ?>:</span>
                                    <span class="font-medium <?= $order['text_color'] ?> <?= $order['bg_color'] ?>  px-2 py-1 rounded text-xs"><?= $order['status_name'] ?></span>
                                </li>

                                <li class="flex flex-wrap sm:flex-nowrap gap-2">
                                    <span class="text-sm font-semibold capitalize w-28 flex-shrink-0"><?php echo lang('website.payment_method'); ?>:</span>
                                    <span class="text-sm font-normal capitalize"><?= $paymentMethod['title'] ?></span>
                                </li>
                            </ul>
                        </div>

                        <!-- Shipping Address -->
                        <div class="rounded-2xl border border-gray-100 bg-white w-full md:w-1/2">
                            <h3 class="p-4 font-bold capitalize"><?php echo lang('website.shipping_address'); ?></h3>
                            <ul class="p-4 border-t border-dashed border-gray-100 space-y-2.5">
                                <li class="flex flex-wrap sm:flex-nowrap gap-2">
                                    <span class="text-sm font-semibold capitalize w-20"><?php echo lang('website.name'); ?>:</span>
                                    <span class="text-sm font-normal capitalize"><?= $address['user_name'] ?></span>
                                </li>
                                <li class="flex flex-wrap sm:flex-nowrap gap-2">
                                    <span class="text-sm font-semibold capitalize w-20"><?php echo lang('website.phone'); ?>:</span>
                                    <span class="text-sm font-normal capitalize"><?= $address['user_mobile'] ?></span>
                                </li>
                                <li class="flex flex-wrap sm:flex-nowrap gap-2">
                                    <span class="text-sm font-semibold capitalize w-20"><?php echo lang('website.email'); ?>:</span>
                                    <span class="text-sm font-normal"><?= $user['email'] ?></span>
                                </li>
                                <li class="flex flex-wrap sm:flex-nowrap gap-2">
                                    <span class="text-sm font-semibold capitalize w-20"><?php echo lang('website.address'); ?>:</span>
                                    <span class="text-sm font-normal capitalize"><?= $address['address'] . ", " . $address['area'] . ", " . $address['city'] . ", " . $address['state'] . ", " . $address['pincode'] ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <?php if ($settings['order_delivery_verification'] == 1  && $order['status'] != 7): ?>
                        <div class="w-full rounded-2xl border border-gray-100 bg-white mt-2">
                            <div class="flex justify-between items-center p-4">
                                <div>
                                    <h3 class="font-bold capitalize"><?php echo lang('website.order_delivery_OTP'); ?></h3>
                                    <p class="text-gray-500 text-xs block mt-1"><?php echo lang('website.delivery_boy_asked_for_OTP_during_Delivery'); ?></p>
                                </div>
                                <button
                                    type="button"
                                    class="px-3 h-8 leading-8 rounded-lg flex items-center gap-2 bg-red-100 text-red-600">
                                    <?= $order['order_delivery_otp'] ?>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="w-full rounded-2xl border border-gray-100 bg-white mt-2">
                        <div class="flex justify-between items-center p-4">
                            <h3 class="font-bold capitalize"><?php echo lang('website.order_summery'); ?></h3>
                            <button
                                type="button"
                                onclick="downloadInvoice(<?= $order['id'] ?>, this)"
                                class="px-3 h-8 leading-8 rounded-lg flex items-center gap-2 bg-green-100 text-green-600">
                                <i class="fi fi-rr-cloud-download-alt"></i>
                                <span class="text-sm font-medium capitalize whitespace-nowrap"><?php echo lang('website.download_invoice'); ?></span>
                            </button>
                        </div>



                        <ul class="border-b border-t border-dashed border-gray-100 space-y-4">
                            <!-- Product Item -->
                            <?php
                            $totalProductPrice = 0; // Initialize total price variable
                            $totalProductTax = 0;   // Initialize total tax variable
                            ?>
                            <?php foreach ($orderProducts as $orderProduct): ?>
                                <?php
                                // Calculate item price (use discounted_price if available, otherwise price)
                                $itemPrice = (($orderProduct['discounted_price'] > 0)
                                    ? $orderProduct['discounted_price']
                                    : $orderProduct['price']) * $orderProduct['quantity'];

                                // Add to total price
                                $totalProductPrice += $itemPrice;

                                // Add tax amount to total tax
                                $totalProductTax += ($orderProduct['tax_amount']);
                                ?>

                                <li class="py-4 mx-4 flex gap-4 border-b border-dashed border-gray-200">
                                    <img src="<?= $orderProduct['main_img'] ?>"
                                        alt="<?= $orderProduct['product_name'] ?>"
                                        class="w-20 h-20 object-cover rounded-lg shadow-md">
                                    <div class="flex-auto">
                                        <div class="flex justify-between items-center">
                                            <h6 class="truncate font-semibold text-gray-900 text-base whitespace-normal">
                                                <?= $orderProduct['product_name'] ?>
                                            </h6>
                                            <?php if ($orderProduct['discounted_price'] > 0): ?>
                                                <span class="text-lg font-bold text-gray-800">
                                                    <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                        <?= $country['currency_symbol'] ?><?= $orderProduct['discounted_price'] ?>
                                                    <?php else: ?>
                                                        <?= $orderProduct['discounted_price'] ?><?= $country['currency_symbol'] ?>
                                                    <?php endif; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-lg font-bold text-gray-800">
                                                    <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                        <?= $country['currency_symbol'] ?><?= $orderProduct['price'] ?>
                                                    <?php else: ?>
                                                        <?= $orderProduct['price'] ?><?= $country['currency_symbol'] ?>
                                                    <?php endif; ?>
                                                </span>
                                            <?php endif; ?>

                                        </div>
                                        <span class="text-gray-500 text-sm block mt-1"><?= $orderProduct['product_variant_name'] ?></span>
                                        <div class="flex items-center justify-between mt-2">
                                            <div class="text-sm font-medium text-gray-600">
                                                <?php echo lang('website.quantity'); ?>: <span class="text-gray-900"><?= $orderProduct['quantity'] ?></span>
                                            </div>
                                            <?php if ($order['status'] == 6 && $orderProduct['is_returnable']): ?>
                                                <div id="returningItem_<?= $order['id'] ?>_<?= $orderProduct['id'] ?>">

                                                    <?php if (is_null($orderProduct['order_retuning_status'])): ?>
                                                        <button
                                                            onclick="openReturningItemPopup(<?= $order['id'] ?>, <?= $orderProduct['id'] ?>)"
                                                            type="button"
                                                            class="px-3 h-8 leading-8 rounded-lg flex items-center gap-2 bg-[#FFF4F1] text-red-600">
                                                            <i class="fi fi-rr-trash"></i>
                                                            <span class="text-sm font-medium capitalize whitespace-nowrap"><?php echo lang('website.return_item'); ?></span>
                                                        </button>
                                                    <?php else: ?>
                                                        <?php
                                                        if ($orderProduct['order_retuning_status'] == 1) {
                                                            echo '<span class="font-medium text-yellow-800 bg-yellow-200 px-2 py-1 rounded text-xs">Pending</span>';
                                                        } elseif ($orderProduct['order_retuning_status'] == 2) {
                                                            echo '<span class="font-medium text-green-800 bg-green-200 px-2 py-1 rounded text-xs">Approved</span>';
                                                        } elseif ($orderProduct['order_retuning_status'] == 3) {
                                                            echo '<span class="font-medium text-red-800 bg-red-200 px-2 py-1 rounded text-xs">Rejected</span>';
                                                        } elseif ($orderProduct['order_retuning_status'] == 4) {
                                                            echo '<span class="font-medium text-blue-800 bg-blue-200 px-2 py-1 rounded text-xs">Return to Delivery Boy</span>';
                                                        } elseif ($orderProduct['order_retuning_status'] == 5) {
                                                            echo '<span class="font-medium text-red-800 bg-red-200 px-2 py-1 rounded text-xs">Refunded</span>';
                                                        } else {
                                                            echo '<span class="font-medium text-gray-800 bg-gray-200 px-2 py-1 rounded text-xs">Unknown</span>';
                                                        }
                                                        ?>
                                                    <?php endif; ?>
                                                </div>

                                            <?php endif; ?>

                                        </div>
                                    </div>
                                </li>

                            <?php endforeach; ?>

                        </ul>

                        <!-- Summary Breakdown -->
                        <ul class="flex flex-col gap-2 py-4 mx-4 border-b border-dashed border-gray-100">
                            <li class="flex items-center justify-between">
                                <span class="capitalize"><?php echo lang('website.subtotal'); ?></span>
                                <span class="font-medium"><?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                        <?= $country['currency_symbol'] ?><?= $totalProductPrice ?>
                                    <?php else: ?>
                                        <?= $totalProductPrice ?><?= $country['currency_symbol'] ?>
                                    <?php endif; ?></span>
                            </li>
                            <li class="flex items-center justify-between">
                                <span class="capitalize"><?php echo lang('website.tax'); ?></span>
                                <span class="font-medium"><?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                        <?= $country['currency_symbol'] ?><?= $totalProductTax ?>
                                    <?php else: ?>
                                        <?= $totalProductTax ?><?= $country['currency_symbol'] ?>
                                    <?php endif; ?></span>
                            </li>
                            <li class="flex items-center justify-between">
                                <span class="capitalize"><?php echo lang('website.delivery_charge'); ?></span>
                                <span class="font-medium"><?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                        <?= $country['currency_symbol'] ?><?= $order['delivery_charge'] ?>
                                    <?php else: ?>
                                        <?= $order['delivery_charge'] ?><?= $country['currency_symbol'] ?>
                                    <?php endif; ?></span>
                            </li>

                            <?php if ($additional_charge_status == 1): ?>
                                <li class="flex items-center justify-between">
                                    <span class="capitalize"><?= $additional_charge_name ?></span>
                                    <span class="font-medium"><?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                            <?= $country['currency_symbol'] ?><?= $order['additional_charge'] ?>
                                        <?php else: ?>
                                            <?= $order['additional_charge'] ?><?= $country['currency_symbol'] ?>
                                        <?php endif; ?></span>
                                </li>
                            <?php endif; ?>

                            <li class="flex items-center justify-between">
                                <span class="capitalize"><?php echo lang('website.discount'); ?>
                                    <?php if ($order['coupon_type'] == 1): ?>
                                        <span class="font-small text-red-700">
                                            (<?= $order['coupon_value'].' %' ?>)
                                        </span>
                                    <?php endif; ?>
                                </span>
                                <span class="font-medium"><?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                        <?= $country['currency_symbol'] ?><?= $order['coupon_amount'] ?>
                                    <?php else: ?>
                                        <?= $order['coupon_amount'] ?><?= $country['currency_symbol'] ?>
                                    <?php endif; ?></span>
                            </li>
                            <li class="flex items-center justify-between">
                                <span class="capitalize"><?php echo lang('website.wallet'); ?></span>
                                <span class="font-medium"><?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                        <?= $country['currency_symbol'] ?><?= $order['used_wallet_amount'] ?>
                                    <?php else: ?>
                                        <?= $order['used_wallet_amount'] ?><?= $country['currency_symbol'] ?>
                                    <?php endif; ?></span>
                            </li>
                        </ul>

                        <!-- Total -->
                        <div class="flex items-center justify-between py-3 px-4">
                            <span class="capitalize font-bold"><?php echo lang('website.grand_total'); ?></span>
                            <span class="capitalize font-bold">
                                <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                    <?= $country['currency_symbol'] ?><?= number_format($totalProductPrice + $totalProductTax + $order['delivery_charge'] + $order['additional_charge'] - $order['coupon_amount'] - $order['used_wallet_amount'], 2) ?>
                                <?php else: ?>
                                    <?= number_format($totalProductPrice + $totalProductTax + $order['delivery_charge'] + $order['additional_charge'] - $order['coupon_amount'] - $order['used_wallet_amount'], 2) ?><?= $country['currency_symbol'] ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <?php if (count($returned_item_list)): ?>
                        <div class="w-full rounded-2xl border border-gray-100 bg-white mt-2">
                            <div class="flex justify-between items-center p-4">
                                <h3 class="font-bold capitalize"><?php echo lang('website.returned_item_list'); ?></h3>
                            </div>


                            <ul class="border-b border-t border-dashed border-gray-100 space-y-4">
                                <!-- Product Item -->
                                <?php
                                $totalRetunedPrice = 0; // Initialize total price variable
                                $totalRetunedTax = 0;   // Initialize total tax variable
                                ?>

                                <?php foreach ($returned_item_list as $item): ?>
                                    <?php
                                    // Calculate item price (use discounted_price if available, otherwise price)
                                    $itemPrice = ($item['order_product']['discounted_price'] > 0)
                                        ? $item['order_product']['discounted_price']
                                        : $item['order_product']['price'];

                                    // Add to total price
                                    $totalRetunedPrice += $itemPrice;

                                    // Add tax amount to total tax
                                    $totalRetunedTax += $item['order_product']['tax_amount'];
                                    ?>
                                    <li class="py-4 mx-4 flex gap-4 border-b border-dashed border-gray-200">
                                        <img src="<?= base_url($item['product']['main_img']); ?>"
                                            alt="<?= $item['order_product']['product_name'] ?>"
                                            class="w-20 h-20 object-cover rounded-lg shadow-md">
                                        <div class="flex-auto">
                                            <div class="flex justify-between items-center">
                                                <h6 class="truncate font-semibold text-gray-900 text-lg">
                                                    <?= $item['order_product']['product_name'] ?>
                                                </h6>
                                                <?php if ($item['order_product']['discounted_price'] > 0): ?>
                                                    <span class="text-lg font-bold text-gray-800">
                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                            <?= $country['currency_symbol'] ?><?= $item['order_product']['discounted_price'] ?>
                                                        <?php else: ?>
                                                            <?= $item['order_product']['discounted_price'] ?><?= $country['currency_symbol'] ?>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-lg font-bold text-gray-800">
                                                        <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                            <?= $country['currency_symbol'] ?><?= $item['order_product']['price'] ?>
                                                        <?php else: ?>
                                                            <?= $item['order_product']['price'] ?><?= $country['currency_symbol'] ?>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endif; ?>

                                            </div>
                                            <span class="text-gray-500 text-sm block mt-1"><?= $item['order_product']['product_variant_name'] ?></span>
                                            <div class="flex items-center justify-between mt-2">
                                                <div class="text-sm font-medium text-gray-600">
                                                    <?php echo lang('website.quantity'); ?>: <span class="text-gray-900"><?= $item['order_product']['quantity'] ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>

                            </ul>

                            <div class="flex items-center justify-between py-3 px-4">
                                <span class="capitalize font-bold"><?php echo lang('website.return_total'); ?></span>
                                <span class="capitalize font-bold">
                                    <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                        <?= $country['currency_symbol'] ?><?= number_format($totalRetunedPrice + $totalRetunedTax, 2) ?>
                                    <?php else: ?>
                                        <?= number_format($totalRetunedPrice + $totalRetunedTax, 2) ?><?= $country['currency_symbol'] ?>
                                    <?php endif; ?>
                                </span>
                            </div>

                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </section>


        <?= $this->include('website/template/mobileBottomMenu') ?>
        <?= $this->include('website/template/productVarientPopup') ?>
    </main>
    <?= $this->include('website/template/shopCart') ?>
    <?= $this->include('website/template/footer') ?>
    <?= $this->include('website/template/script') ?>

    <?php if ($is_order_cancelleble == 1): ?>
        <div id="cancelOrderModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-40 px-4 md:px-0">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-4">
                <div class="flex justify-between mb-6 border-b">
                    <h2 class="text-base font-semibold pb-1 " id="">Cancel Order</h2>
                    <i class="fi fi-rr-circle-xmark text-red-800" onclick="closeCancelOrderPopup()"></i>
                </div>
                <form class="cancelOrderForm">
                    <!-- Address Input -->
                    <div class="mb-4">
                        <label for="note" class="block text-sm font-medium text-gray-700 mb-1">Notes/Message</label>
                        <textarea id="note" name="note" rows="3" class="w-full border border-gray-300 rounded-lg p-2 text-sm text-gray-900 focus:ring-green-600 focus:border-green-600" placeholder="Enter Note/Message"></textarea>
                    </div>

                    <!-- Save Button -->
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium text-sm py-2 px-4 rounded-lg shadow focus:ring-2 focus:ring-green-500 focus:ring-offset-1">
                        Confirm Cancel Order
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div id="returningItemModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-40 px-4 md:px-0">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-4">
            <div class="flex justify-between mb-6 border-b">
                <h2 class="text-base font-semibold pb-1 " id="">Returning Item Request</h2>
                <i class="fi fi-rr-circle-xmark text-red-800" onclick="closeReturningItemPopup()"></i>
            </div>
            <form class="returningItemForm">
                <input type="hidden" name="ri_order_id" id="ri_order_id" />
                <input type="hidden" name="ri_order_product_id" id="ri_order_product_id" />
                <!-- Address Input -->
                <div class="mb-4">
                    <label for="note" class="block text-sm font-medium text-gray-700 mb-1">Notes/Message</label>
                    <textarea id="note" name="note" rows="3" class="w-full border border-gray-300 rounded-lg p-2 text-sm text-gray-900 focus:ring-green-600 focus:border-green-600" placeholder="Enter Note/Message"></textarea>
                </div>

                <!-- Save Button -->
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium text-sm py-2 px-4 rounded-lg shadow focus:ring-2 focus:ring-green-500 focus:ring-offset-1">
                    Confirm Send Returning Item Request
                </button>
            </form>
        </div>
    </div>

    <?= $this->include('website/template/orderDetailsScript') ?>

</body>

</html>