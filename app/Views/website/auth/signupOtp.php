<!doctype html>
<html lang="<?= session()->get('site_lang') ?? 'en' ?>" dir="<?= dir_attribute() ?>">

<head>
    <?= $this->include('website/template/style') ?>
    <title><?= $settings['business_name'] ?></title>
</head>

<body class="bg-gray-100">
    <div class="block md:hidden">
        <?= $this->include('website/template/header') ?>
    </div>
    <main class="max-w-7xl mx-auto">

        <div class="flex flex-wrap md:h-screen">
            <div class="flex w-screen flex-col lg:w-1/2">

                <div class="md:mx-[15%] mx-[5%] my-auto flex flex-col justify-center lg:justify-start py-6">
                    <p class="text-center text-xl md:text-3xl font-bold"><?php echo lang('website.enter_otp'); ?></p>
                    <p class="mt-2 text-center text-gray-500"><?php echo lang('website.an_6_digit_OTP_code_has_been_sent_to'); ?> <br><span class="underline-offset-4 font-semibold text-green-700 underline"><?= session()->get('email') ?></span> </p>
                    <form class="flex flex-col pt-3 md:pt-8" id="otpForm">

                        <div class=" flex gap-2 justify-center items-center pt-4">
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 text-center border border-gray-300 rounded-md focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200 text-2xl" id="otp1" />
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 text-center border border-gray-300 rounded-md focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200 text-2xl" id="otp2" />
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 text-center border border-gray-300 rounded-md focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200 text-2xl" id="otp3" />
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 text-center border border-gray-300 rounded-md focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200 text-2xl" id="otp4" />
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 text-center border border-gray-300 rounded-md focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200 text-2xl" id="otp5" />
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 text-center border border-gray-300 rounded-md focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200 text-2xl" id="otp6" />
                        </div>
                        <div id="otpError" class="text-red-500 text-sm mt-1 hidden ml-[7%] md:ml-[19%]"><?php echo lang('website.please_enter_a_valid_6_digit_OTP'); ?></div>
                        <div id="message" class="mt-2 mb-6"></div>
                        <button type="submit" class="w-full rounded-lg bg-green-700 px-4 py-2 text-center text-base font-semibold text-white shadow-md ring-gray-500 ring-offset-2 transition focus:ring-2">Verify OTP</button>
                    </form>

                </div>
            </div>
            <div class="pointer-events-none relative hidden select-none bg-white lg:block lg:w-1/2">

                <img class="absolute top-0 h-full w-full" src="<?= base_url('/assets/dist/img/otp-banner.svg') ?>" />
            </div>
        </div>

        <?= $this->include('website/template/mobileBottomMenu') ?>

    </main>
    <?= $this->include('website/template/script') ?>
    <script src="<?= base_url('/assets/page-script/website/signupOtp.js') ?>"></script>

</body>

</html>