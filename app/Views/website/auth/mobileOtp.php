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
                    <p class="mt-2 text-center text-gray-500"><?php echo lang('website.an_6_digit_OTP_code_has_been_sent_to'); ?> <br><span class="underline-offset-4 font-semibold text-green-700 underline"><?= $country['country_code'] . ' ' . session()->get('mobile') ?></span> </p>
                    <form class="flex flex-col pt-3 md:pt-8" id="otpForm">
                        <?php if ($is_mobile_verified == 0 && $is_active == 0) { ?>
                            <div class="flex flex-col pt-4">
                                <label class="text-gray-800 font-medium inline-block" for="name">
                                <?php echo lang('website.name'); ?>
                                    <span class="text-red-600">*</span>
                                </label>
                                <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600 focus-within:shadow-[0_0_0_.25rem_rgba(10,173,10,.25)]">
                                    <span class="px-3 text-gray-500">
                                        <i class="fi fi-tr-circle-user"></i>
                                    </span>
                                    <input type="text" id="name" name="name" class="text-gray-900 focus:ring-0 focus:border-0 block p-2 w-full text-base border-0 disabled:opacity-50 disabled:pointer-events-none" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.name'); ?>" autocomplete="off">
                                </div>
                                <div id="nameError" class="text-red-500 text-sm mt-1 hidden"></div>
                            </div>

                            <?php if ($settings['refer_and_earn_status']): ?>
                                <div class="flex flex-col pt-4">
                                    <label class="text-gray-800 font-medium inline-block" for="referal">
                                    <?php echo lang('website.referal_code'); ?>
                                    </label>
                                    <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600 focus-within:shadow-[0_0_0_.25rem_rgba(10,173,10,.25)]">
                                        <span class="px-3 text-gray-500">
                                            <i class="fi fi-tr-gift"></i>
                                        </span>
                                        <input type="text" id="referal" name="referal" class="text-gray-900 focus:ring-0 focus:border-0 block p-2 w-full text-base border-0 disabled:opacity-50 disabled:pointer-events-none" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.referal_code'); ?>" autocomplete="off">
                                    </div>
                                    <div id="referalError" class="text-red-500 text-sm mt-1 hidden"></div>
                                </div>
                            <?php endif; ?>
                        <?php } ?>


                        <div class=" flex gap-2 justify-between items-center pt-4">
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 text-center border border-gray-300 rounded-md focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200 text-2xl" id="otp1" />
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 text-center border border-gray-300 rounded-md focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200 text-2xl" id="otp2" />
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 text-center border border-gray-300 rounded-md focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200 text-2xl" id="otp3" />
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 text-center border border-gray-300 rounded-md focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200 text-2xl" id="otp4" />
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 text-center border border-gray-300 rounded-md focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200 text-2xl" id="otp5" />
                            <input type="text" maxlength="1" class="otp-input w-12 h-12 text-center border border-gray-300 rounded-md focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-200 text-2xl" id="otp6" />
                        </div>
                        <div id="otpError" class="text-red-500 text-sm mt-1 hidden ml-[7%] md:ml-[19%]"><?php echo lang('website.please_enter_a_valid_6_digit_OTP'); ?></div>
                        <div id="message" class="mt-2 mb-6"></div>
                        <button type="submit" class="w-full rounded-lg bg-green-700 px-4 py-2 text-center text-base font-semibold text-white shadow-md ring-gray-500 ring-offset-2 transition focus:ring-2"><?php echo lang('website.verify_OTP'); ?></button>
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
    <script>
        const otpInputs = document.querySelectorAll('.otp-input');

        otpInputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                if (input.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && index > 0 && input.value === '') {
                    otpInputs[index - 1].focus();
                }
            });
        });

        document.getElementById("otpForm").addEventListener("submit", async function(e) {
            e.preventDefault();

            // Initialize variables for dynamic fields
            let name = '';
            let referal = '';
            let hasErrors = false;

            // Check if name field exists and validate it
            const nameField = document.getElementById('name');
            if (nameField) {
                name = nameField.value.trim();
                const nameError = document.getElementById('nameError');

                if (!name) {
                    nameError.textContent = 'Name is required';
                    nameError.classList.remove('hidden');
                    hasErrors = true;
                } else if (name.length < 2 || name.length > 50) {
                    nameError.textContent = 'Name must be between 2 and 50 characters';
                    nameError.classList.remove('hidden');
                    hasErrors = true;
                } else {
                    nameError.classList.add('hidden');
                }
            }

            // Check if referral field exists and validate it
            const referalField = document.getElementById('referal');
            if (referalField) {
                referal = referalField.value.trim();
                const referalError = document.getElementById('referalError');

                // Only validate referral if it's not empty (since it's optional)
                if (referal && (referal.length == 8)) {
                    referalError.textContent = 'Referral code must be 8 characters Long';
                    referalError.classList.remove('hidden');
                    hasErrors = true;
                } else {
                    referalError.classList.add('hidden');
                }
            }

            // Get OTP values from input fields
            let otp = '';
            for (let i = 1; i <= 6; i++) {
                otp += document.getElementById('otp' + i).value;
            }

            // Validate OTP
            const otpError = document.getElementById('otpError');
            if (otp.length !== 6 || isNaN(otp)) {
                otpError.textContent = 'Please enter a valid 6-digit OTP';
                otpError.classList.remove('hidden');
                hasErrors = true;
            } else {
                otpError.classList.add('hidden');
            }

            // If there are any validation errors, stop submission
            if (hasErrors) {
                return;
            }

            // Prepare data to send
            const dataToSend = {
                otp: otp,
                name, referal
            };

            // Add name and referral to data if they exist
            if (nameField) dataToSend.name = name;
            if (referalField && referal) dataToSend.referal = referal;

            // Send data to the backend via Fetch API
            try {
                const response = await fetch('/mobileOtp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dataToSend)
                });

                const result = await response.json();
                const messageDiv = document.getElementById('message');
                messageDiv.textContent = result.message;
                messageDiv.className = result.status === 'success' ? "text-green-700 text-sm mt-1" : "text-red-500 text-sm mt-1";

                if (result.status === 'success') {
                    location.href = '/';
                }
            } catch (error) {
                const messageDiv = document.getElementById('message');
                messageDiv.textContent = "Error to verify OTP. Please try again later.";
                messageDiv.className = "text-red-500 text-sm mt-1";
            }
        });

        // Add input event listeners for OTP fields to auto-focus next field
        for (let i = 1; i <= 6; i++) {
            document.getElementById('otp' + i).addEventListener('input', function(e) {
                if (this.value.length === 1 && i < 6) {
                    document.getElementById('otp' + (i + 1)).focus();
                }
            });

            // Add backspace handling to move to previous field
            document.getElementById('otp' + i).addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value.length === 0 && i > 1) {
                    document.getElementById('otp' + (i - 1)).focus();
                }
            });
        }
    </script>

</body>

</html>