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
                    <h2 class="text-lg font-medium z-10"><?php echo lang('website.language'); ?></h2>
                </div>
            </div>
        </section>

        <section class="mt-2 md:mt-4 md:container md:mx-auto md:px-3">
            <div class="flex flex-wrap lg:flex-nowrap lg:gap-x-6 gap-y-6">
                <?= $this->include('website/template/dashboardSidebar') ?>

                <div class="w-full lg:w-full md:w-full mx-auto">
                    <div class="overflow-x-auto mt-2">
                        <table class="min-w-full table-auto bg-white rounded-lg shadow-md">
                            <thead class="border-b">
                                <tr>
                                    <th class="px-4 py-2 text-left text-base font-medium w-auto"><?php echo lang('website.language'); ?></th>
                                    <th class="px-4 py-2 text-left text-base font-medium w-auto"><?php echo lang('website.status'); ?></th>
                                    <th class="px-4 py-2 text-left text-base font-medium w-auto"><?php echo lang('website.action'); ?></th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                <?php if (!empty($languageList) && is_array($languageList)): ?>
                                    <?php foreach ($languageList as $language): ?>
                                        <tr class="hover:bg-gray-100">
                                            <td class="px-4 py-2 text-sm w-auto"><?= htmlspecialchars($language['language']) ?></td>
                                            <td class="px-4 py-2 text-sm w-auto">
                                                <?php if (session()->get('site_lang') == $language['lang_short']): ?>
                                                    <div class="inline-flex items-center gap-2 border border-blue-600 rounded-lg px-2 py-1">
                                                        <i class="fi fi-rr-check-circle text-blue-600"></i>
                                                        <span class="text-blue-600 text-sm font-medium"> <?php echo lang('website.default'); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-2 text-sm w-auto">
                                                <a href="<?= base_url("language/".$language['id'])?>" class="inline-flex items-center gap-2 px-3 h-8 leading-8 rounded-lg bg-green-100 text-green-600 whitespace-nowrap">
                                                    <span class="text-sm font-medium capitalize"><?php echo lang('website.make_default'); ?></span>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>

                                <?php endif; ?>
                            </tbody>
                        </table>
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