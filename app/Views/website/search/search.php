<!doctype html>
<html lang="<?= session()->get('site_lang') ?? 'en' ?>" dir="<?= dir_attribute() ?>">

<head>
    <?= $this->include('website/template/style') ?>
    <title><?= $settings['business_name'] ?></title>
</head>

<body class="bg-gray-100">
    <?= $this->include('website/template/searchHeader') ?>
    <main class="max-w-7xl mx-auto">

        <section class="mt-2 md:mt-4 md:container md:mx-auto md:px-3 h-[100vh] ">
            <div class="row bg-white mb-2 p-4 rounded-lg hidden" id="searchDiv">
                <div class="flex justify-between">
                    <h2 class="text-lg font-medium z-10" id="searchText"></h2>
                </div>
            </div>

            <div class="md:bg-white rounded-lg">
                <div class="grid grid-cols-2 xl:grid-cols-6 lg:grid-cols-4 md:grid-cols-3 gap-2 md:p-2 mx-3 md:mx-0" id="searchItemDiv"></div>
            </div>

            <div class="md:bg-white rounded-lg" id="searchItemEmptyDiv">
                <img src="<?= base_url() . 'assets/dist/img/no-data.png' ?>" class="w-[50%] mx-auto" />
                <p class="text-base text-center text-gray-600 z-10"><?php echo lang('website.search_will_appers_here');?></p>
            </div>

        </section>


        <?= $this->include('website/template/mobileBottomMenu') ?>
        <?= $this->include('website/template/productVarientPopup') ?>

    </main>
    <?= $this->include('website/template/shopCart') ?>
    <?= $this->include('website/template/footer') ?>
    <?= $this->include('website/template/script') ?>
    <script src="<?= base_url('/assets/page-script/website/search.js') ?>"></script>

</body>

</html>