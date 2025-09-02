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
                    <h2 class="text-lg font-medium z-10"><?php echo lang('website.brand');?></h2>
                </div>
            </div>
        </section>

        <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
            <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 lg:gap-2 bg-white rounded-lg px-2">
                <?php foreach ($brands as $brand): ?>
                    <a href="brand/<?= $brand['slug'] ?>" class="text-decoration-none text-inherit">
                        <div class="text-center py-2">
                            <div class="flex justify-center">
                                <img src="<?= $brand['image'] ?>" alt="<?= $brand['brand'] ?>" class="mb-2 bg-[#edf8f1] rounded-lg">
                            </div>
                            <h6 class="text-sm font-semibold mt-2"><?= $brand['brand'] ?></h6>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>


        <?= $this->include('website/template/mobileBottomMenu') ?>
    </main>
    <?= $this->include('website/template/shopCart') ?>
    <?= $this->include('website/template/footer') ?>
    <?= $this->include('website/template/script') ?>
</body>

</html>