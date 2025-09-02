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
                    <h2 class="text-lg font-medium z-10"><?php echo lang('website.wallet'); ?></h2>
                </div>
            </div>
        </section>

        <section class="mt-2 md:mt-4 md:container md:mx-auto md:px-3">
            <div class="flex flex-wrap lg:flex-nowrap lg:gap-x-6 gap-y-6">
                <?= $this->include('website/template/dashboardSidebar') ?>

                <div class="w-full lg:w-full md:w-full mx-auto">
                    <div class="bg-gray-900 text-white rounded-xl shadow-lg p-5 w-full max-w-md mx-auto md:hidden">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-medium"><?= $user['name'] ?></h2>
                            <img src="<?= base_url($settings['logo']) ?>" alt="<?= $settings['business_name'] ?>" class="w-10 h-10 rounded-full object-contain">
                        </div>
                        <p class="text-2xl tracking-wider mt-4">** ** ** 1234</p>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-gray-400 text-sm"> <?php echo lang('website.expiry_date'); ?></span>
                            <span class="text-white font-bold text-lg">&infin;</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 sm:grid-cols-3 lg:grid-cols-3 gap-4 mt-2">
                        <!-- Current Wallet Amount Box -->
                        <div class="bg-white rounded-lg shadow-md p-4 flex flex-col items-center text-center">
                            <p class="text-gray-700 font-medium text-base"><?php echo lang('website.current_wallet_amount'); ?></p>
                            <p class="text-green-600 font-bold text-2xl mt-2"><?= $settings['currency_symbol_position'] == 'left' ? $country['currency_symbol'] . $currentWalletAmount : $currentWalletAmount . $country['currency_symbol']; ?></p>
                        </div>

                        <!-- Total Credit Box -->
                        <div class="bg-white rounded-lg shadow-md p-4 flex flex-col items-center text-center">
                            <p class="text-gray-700 font-medium text-base"><?php echo lang('website.total_credit'); ?></p>
                            <p class="text-green-600 font-bold text-2xl mt-2"><?= $settings['currency_symbol_position'] == 'left' ? $country['currency_symbol'] . $totalCredit : $totalCredit . $country['currency_symbol']; ?></p>
                        </div>

                        <!-- Total Debit Box -->
                        <div class="bg-white rounded-lg shadow-md p-4 flex flex-col items-center text-center">
                            <p class="text-gray-700 font-medium text-base"><?php echo lang('website.total_debit'); ?></p>
                            <p class="text-red-600 font-bold text-2xl mt-2"><?= $settings['currency_symbol_position'] == 'left' ? $country['currency_symbol'] . $totalDebit : $totalDebit . $country['currency_symbol']; ?></p>

                        </div>
                    </div>


                    <div class="overflow-x-auto mt-2">
                        <table class="min-w-full table-auto bg-white rounded-lg shadow-md">
                            <thead class="border-b">
                                <tr>
                                    <th class="px-4 py-2 text-left text-base font-medium"><?php echo lang('website.date'); ?></th>
                                    <th class="px-4 py-2 text-left text-base font-medium"><?php echo lang('website.amount'); ?></th>
                                    <th class="px-4 py-2 text-left text-base font-medium"><?php echo lang('website.status'); ?></th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                <?php if (!empty($wallets) && is_array($wallets)): ?>
                                    <?php foreach ($wallets as $wallet): ?>
                                        <tr class="hover:bg-gray-100">
                                            <td class="px-4 py-2 text-sm"><?= htmlspecialchars(date('Y-m-d', strtotime($wallet['date']))) ?></td>
                                            <td class="px-4 py-2 text-sm"><?= $settings['currency_symbol_position'] == 'left' ? $country['currency_symbol'] . htmlspecialchars($wallet['amount']) : htmlspecialchars($wallet['amount']) . $country['currency_symbol']; ?></td>

                                            <td class="px-4 py-2 text-sm">
                                                <span class="font-medium <?= $wallet['flag'] === 'credit' ? 'text-green-600' : 'text-red-600' ?>">
                                                    <?= ucfirst($wallet['flag']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 text-center text-sm text-gray-500"><?php echo lang('website.no_wallet_records_found'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                    </div>
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