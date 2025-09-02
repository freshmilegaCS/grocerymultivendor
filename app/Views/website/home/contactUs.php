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
                    <h2 class="text-lg font-medium z-10"><?php echo lang('website.contact_us'); ?></h2>
                </div>
            </div>

            <div class="bg-white rounded-lg px-4 py-2">
                <div class="grid md:p-2 mx-3 md:mx-0">
                    <ul class="flex flex-col gap-2">
                        <?php if (isset($settings['phone']) && $settings['phone'] != null && $settings['phone'] != ''): ?>
                            <li>
                                <a href="tel:<?= htmlspecialchars($settings['phone']); ?>" class="inline-block hover:text-green-600 text-sm font-medium">
                                    <i class="fi fi-rr-phone-call"></i> <?= htmlspecialchars($settings['phone']); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (isset($settings['email']) && $settings['email'] != null && $settings['email'] != ''): ?>
                            <li>
                                <a href="mailto:<?= htmlspecialchars($settings['email']); ?>" class="inline-block hover:text-green-600 text-sm font-medium">
                                    <i class="fi fi-rr-envelope"></i> <?= htmlspecialchars($settings['email']); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php
                        $location = json_decode($settings['address'], true); // Decode JSON into an associative array
                        if (
                            isset($location['address']) && $location['address'] != ''
                            && isset($location['latitude']) && $location['latitude'] != ''
                            && isset($location['longitude']) && $location['longitude'] != ''
                        ):
                            $googleMapsLink = "https://www.google.com/maps?q={$location['latitude']},{$location['longitude']}";
                        ?>
                            <li>
                                <a href="<?= htmlspecialchars($googleMapsLink); ?>" target="_blank" class="inline-block hover:text-green-600 text-sm font-medium">
                                    <i class="fi fi-rr-marker"></i> <?= htmlspecialchars($location['address']); ?>
                                </a>
                            </li>
                        <?php endif; ?>


                    </ul>


                </div>
                <div class="grid md:p-2 mx-3 md:mx-0">
                    <ul class="flex items-center text-sm gap-4 mt-3">
                        <?php $socialLinks = json_decode($settings['social_link'], true); ?>
                        <?php foreach ($socialLinks as $social): ?>
                            <?php if ($social['status'] == 1): ?>
                                <li>
                                    <a href="<?= htmlspecialchars($social['link']) ?>" target="_blank">
                                        <i class="<?= htmlspecialchars($social['icon']) ?> text-lg"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
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