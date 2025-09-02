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
                    <h2 class="text-lg font-medium z-10"><?php echo lang('website.order_history'); ?></h2>
                </div>
            </div>
        </section>

        <section class="mt-2 md:mt-4 md:container md:mx-auto md:px-3">

            <div class="flex flex-wrap lg:flex-nowrap lg:gap-x-6 gap-y-6">
                <?= $this->include('website/template/dashboardSidebar') ?>

                <div class="w-full lg:w-full md:w-full mx-auto">

                    <?php if (!empty($orders) && is_array($orders)) : ?>
                        <div class="grid md:grid-cols-3 grid-cols-1 gap-1">
                            <?php foreach ($orders as $order): ?>

                                <a href="/order-details/<?= $order['id'] ?>" class="w-full bg-white border-gray-100 p-4 rounded-lg cursor-pointer border mb-2 md:mb-0">
                                    <div>
                                        <p class="text-base font-medium truncate"><?php echo lang('website.order_id'); ?>: #<?= $order['order_id'] ?></p>

                                        <div class="flex flex-row mt-1">
                                            <p class="text-sm">
                                                <?php echo lang('website.status'); ?>:
                                                <span class="font-medium <?= $order['text_color'] ?> <?= $order['bg_color'] ?>  px-2 py-1 rounded"><?= $order['status_name'] ?></span>
                                            </p>
                                        </div>

                                    </div>
                                    <div class="flex flex-col mt-3">
                                        <p class="text-sm"><?php echo lang('website.order_type'); ?>: <span class="font-medium capitalize"><?= $order['delivery_method'] ?></span></p>
                                        <p class="text-sm"><?php echo lang('website.order_date'); ?>: <span class="font-medium capitalize"><?= date('d-m-Y h:i:s A', strtotime($order['order_date'])) ?></span></p>
                                        <p class="text-sm"><?php echo lang('website.delivery_date'); ?>: <span class="font-medium"><?= isset($order['delivery_date']) ? date('d-m-Y', strtotime($order['delivery_date'])) : '' ?></span></p>
                                        <p class="text-sm"><?php echo lang('website.timeslot'); ?>: <span class="font-medium"><?= $order['timeslot'] ?></span></p>
                                        <p class="text-sm"><?php echo lang('website.payment'); ?>: <span class="font-medium"> 
                                                <?php
                                                $totalProductPrice = 0; // Initialize total price variable
                                                $totalProductTax = 0;   // Initialize total tax variable

                                                foreach ($order['orderProducts'] as $orderProduct) {
                                                    $itemPrice = (($orderProduct['discounted_price'] > 0)
                                                        ? $orderProduct['discounted_price']
                                                        : $orderProduct['price']) * $orderProduct['quantity'];

                                                    // Add to total price
                                                    $totalProductPrice += $itemPrice;

                                                    // Add tax amount to total tax
                                                    $totalProductTax += ($orderProduct['tax_amount']);
                                                }

                                                ?>
                                                <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                                    <?= $country['currency_symbol'] ?><?= number_format($totalProductPrice + $totalProductTax - $order['used_wallet_amount'] + $order['delivery_charge'] + $order['additional_charge'] - $order['coupon_amount'], 2) ?>
                                                <?php else: ?>
                                                    <?= number_format($totalProductPrice + $totalProductTax - $order['used_wallet_amount'] + $order['delivery_charge'] + $order['additional_charge'] - $order['coupon_amount'], 2) ?><?= $country['currency_symbol'] ?>
                                                <?php endif; ?>
                                            </span></p>
                                    </div>

                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="flex flex-col gap-4 text-center">
                            <img
                                src="<?= base_url('assets/dist/img/no-data.png') ?>"
                                alt="Coming Soon"
                                class="mx-auto w-2/3 sm:w-1/3 rounded-lg" />
                            <div class="text-sm text-gray-700">
                                <?php echo lang('website.no_order_history_available'); ?>
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
</body>

</html>