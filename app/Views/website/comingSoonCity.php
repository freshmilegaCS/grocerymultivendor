<!doctype html>
<html lang="<?= session()->get('site_lang') ?? 'en' ?>" dir="<?= dir_attribute() ?>">

<head>
    <?= $this->include('website/template/style') ?>
    <title><?= $settings['business_name'] ?></title>
</head>

<body class="bg-gray-100">
    <?= $this->include('website/template/header') ?>
    <main class="flex flex-col gap-4 w-full p-4 bg-white rounded-lg">

        <div class="text-center space-y-6">
            <!-- Image Section -->
            <div>
                <img
                    src="<?= base_url('assets/dist/img/coming-soon.svg') ?>"
                    alt="Coming Soon"
                    class="mx-auto w-2/3 sm:w-1/3 rounded-lg" />
            </div>

            <!-- Text Section -->
            <div class="text-xl sm:text-2xl text-gray-700 font-semibold">
                <?php echo lang('website.very_soon_we_are_coming_to_your_city'); ?>
            </div>

            <!-- Button Section -->
            <div>
                <button onclick="openLocationModel()" class="bg-green-600 text-white rounded-lg p-3 shadow-md hover:bg-green-700 hover:border-green-700 focus:outline-none focus:ring-4 focus:ring-green-300 active:bg-green-700 disabled:opacity-50 disabled:pointer-events-none">

                    <?php echo lang('website.checkout_other_location'); ?>
                </button>
            </div>
        </div>

        <?= $this->include('website/template/mobileBottomMenu') ?>
        <?= $this->include('website/template/productVarientPopup') ?>

    </main>
    <?= $this->include('website/template/shopCart') ?>
    <?= $this->include('website/template/footer') ?>
    <?= $this->include('website/template/script') ?>
</body>

</html>