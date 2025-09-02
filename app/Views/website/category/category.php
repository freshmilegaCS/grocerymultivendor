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
            <div class="relative flex flex-col min-w-0 rounded-lg break-words bg-white p-4 mb-6">
                <div class="flex justify-between">
                    <h1 class="text-lg font-medium z-10"><?php echo lang('website.category');?></h1>
                </div>
            </div>
        </section>

        <section class="mt-2 md:mt-4 md:container md:mx-auto px-3">
            <div class="grid grid-cols-4 md:grid-cols-5 lg:grid-cols-7 gap-2 lg:gap-2 bg-white rounded-lg px-2">
                <?php foreach ($categories as $category): ?>
                    <?php if (!empty($category['firstSubcategory'])): ?>
                        <a href="subcategory/<?= $category['firstSubcategory']['slug'] ?>" class="text-decoration-none text-inherit">
                            <div class="text-center py-2">
                                <div class="flex justify-center">
                                    <img src="<?= $category['category_img'] ?>" alt="<?= $category['category_name'] ?>" class="mb-2 bg-[#edf8f1] rounded-lg">
                                </div>
                                <h6 class="text-sm font-semibold mt-2"><?= $category['category_name'] ?></h6>
                            </div>
                        </a>
                    <?php else: ?>
                        <a href="#" class="text-decoration-none text-inherit">
                            <div class="text-center py-2">
                                <div class="flex justify-center">
                                    <img src="<?= $category['category_img'] ?>" alt="<?= $category['category_name'] ?>" class="mb-2 bg-[#edf8f1] rounded-lg">
                                </div>
                                <h6 class="text-sm font-semibold mt-2"><?= $category['category_name'] ?></h6>
                            </div>
                        </a>
                    <?php endif; ?>

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