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

        <div class="flex flex-wrap h-screen">
            <div class="flex w-screen flex-col lg:w-1/2">

                <div class="md:mx-[15%] mx-[5%] my-auto flex flex-col justify-center lg:justify-start py-6">
                    <p class="text-center text-xl md:text-3xl font-bold"><?php echo lang('website.create_your_account'); ?></p>
                    <p class="mt-2 text-center text-gray-500"><?php echo lang('website.welcome_to'); ?> <?= $settings['business_name'] ?>. <?php echo lang('website.lets_create_your_account'); ?></p>
                    <form class="flex flex-col pt-3 md:pt-8">
                        <!-- Name -->
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

                        <!-- Mobile -->
                        <div class="flex flex-col pt-4">
                            <label class="text-gray-800 font-medium inline-block" for="mobile">
                                <?php echo lang('website.mobile'); ?>
                                <span class="text-red-600">*</span>
                            </label>
                            <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600 focus-within:shadow-[0_0_0_.25rem_rgba(10,173,10,.25)]">
                                <span class="px-3 text-gray-500">
                                    <i class="fi fi-tr-mobile-notch"></i>
                                </span>
                                <input type="text" id="mobile" name="mobile" class="text-gray-900 focus:ring-0 focus:border-0 block p-2 w-full text-base border-0 disabled:opacity-50 disabled:pointer-events-none" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.mobile'); ?>" autocomplete="off">
                            </div>
                            <div id="mobileError" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Email -->
                        <div class="flex flex-col pt-4">
                            <label class="text-gray-800 font-medium inline-block" for="email">
                                <?php echo lang('website.email'); ?> (<?php echo lang('website.required_for_login'); ?>)
                                <span class="text-red-600">*</span>
                            </label>
                            <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600 focus-within:shadow-[0_0_0_.25rem_rgba(10,173,10,.25)]">
                                <span class="px-3 text-gray-500">
                                    <i class="fi fi-tr-envelopes"></i>
                                </span>
                                <input type="email" id="email" name="email" class="text-gray-900 focus:ring-0 focus:border-0 block p-2 w-full text-base border-0 disabled:opacity-50 disabled:pointer-events-none" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.email'); ?>" autocomplete="off">
                            </div>
                            <div id="emailError" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Password -->
                        <div class="flex flex-col pt-4">
                            <label class="text-gray-800 font-medium inline-block" for="password">
                                <?php echo lang('website.password'); ?>
                                <span class="text-red-600">*</span>
                            </label>
                            <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600 focus-within:shadow-[0_0_0_.25rem_rgba(10,173,10,.25)]">
                                <span class="px-3 text-gray-500">
                                    <i class="fi fi-tr-padlock-check"></i>
                                </span>
                                <input type="password" id="password" name="password" class="text-gray-900 focus:ring-0 focus:border-0 block p-2 w-full text-base border-0 disabled:opacity-50 disabled:pointer-events-none" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.password'); ?>" autocomplete="off">
                                <span class="px-3 text-gray-500 cursor-pointer" onclick="togglePasswordVisibility()">
                                    <i class="fi fi-tr-low-vision" id="togglePasswordIcon"></i>
                                </span>
                            </div>
                            <div id="passwordError" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="flex flex-col pt-4">
                            <label class="text-gray-800 font-medium inline-block" for="confirmPassword">
                                <?php echo lang('website.confirm_password'); ?>
                                <span class="text-red-600">*</span>
                            </label>
                            <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600 focus-within:shadow-[0_0_0_.25rem_rgba(10,173,10,.25)]">
                                <span class="px-3 text-gray-500">
                                    <i class="fi fi-tr-padlock-check"></i>
                                </span>
                                <input type="password" id="confirmPassword" name="confirmPassword" class="text-gray-900 focus:ring-0 focus:border-0 block p-2 w-full text-base border-0 disabled:opacity-50 disabled:pointer-events-none" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.confirm_password'); ?>" autocomplete="off">
                                <span class="px-3 text-gray-500 cursor-pointer" onclick="toggleConfirmPasswordVisibility()">
                                    <i class="fi fi-tr-low-vision" id="toggleConfirmPasswordIcon"></i>
                                </span>
                            </div>
                            <div id="confirmPasswordError" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>

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

                        <!-- Message Display -->
                        <div id="message" class="mt-2"></div>

                        <!-- Terms & Conditions -->
                        <div class="mb-6 mt-2">
                            <p class="text-gray-400 text-sm"><?php echo lang('website.by_signing_up_you_agree_to_our'); ?> <a href="#" class="text-green-700 underline"><?php echo lang('website.terms_condition'); ?></a> <?php echo lang('website.and'); ?> <a href="#" class="text-green-700 underline"><?php echo lang('website.privacy_policy'); ?></a>.</p>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full rounded-lg bg-green-700 px-4 py-2 text-center text-base font-semibold text-white shadow-md ring-gray-500 ring-offset-2 transition focus:ring-2"><?php echo lang('website.create_your_account'); ?></button>
                    </form>

                    <div class="relative mt-8 flex h-px place-items-center bg-green-200">
                        <div class="absolute left-1/2 h-6 w-14 -translate-x-1/2 bg-white text-center text-sm text-gray-500"><?php echo lang('website.or'); ?></div>
                    </div>
                    <div class="pt-6 text-center mb-[20%] md:mb-0">
                        <p class="whitespace-nowrap text-gray-600">
                        <?php echo lang('website.already_have_an_account'); ?>
                            <a href="/login" class="underline-offset-4 font-semibold text-green-700 underline"><?php echo lang('website.login_here'); ?></a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="pointer-events-none relative hidden select-none bg-white lg:block lg:w-1/2">

                <img class="absolute top-0 h-full w-full" src="<?= base_url('/assets/dist/img/signup-banner.svg') ?>" />
            </div>
        </div>

        <?= $this->include('website/template/mobileBottomMenu') ?>

    </main>
    <?= $this->include('website/template/script') ?>
    <script src="<?= base_url('/assets/page-script/website/signup.js') ?>"></script>

    <script>
        document.querySelector('form').addEventListener('submit', async function(event) {
            event.preventDefault();

            // Clear all previous error messages and styles
            document.querySelectorAll('.text-sm').forEach(errorDiv => {
                errorDiv.classList.add('hidden');
                errorDiv.textContent = "";
            });
            document.querySelectorAll('input').forEach(input => {
                input.parentElement.classList.remove('border-red-500');
            });

            let referal = '';
            const name = document.getElementById('name').value.trim();
            const mobile = document.getElementById('mobile').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            const confirmPassword = document.getElementById('confirmPassword').value.trim();

            let hasError = false;

            // Validation logic with individual error messages
            if (!name) {
                document.getElementById('nameError').textContent = "Name is required.";
                document.getElementById('nameError').classList.remove('hidden');
                document.getElementById('name').parentElement.classList.add('border-red-500');
                hasError = true;
            }

            if (!/^\d{<?= $country['validation_no'] ?>}$/.test(mobile)) {
                document.getElementById('mobileError').textContent = "Please enter a valid <?= $country['validation_no'] ?>-digit mobile number.";
                document.getElementById('mobileError').classList.remove('hidden');
                document.getElementById('mobile').parentElement.classList.add('border-red-500');
                hasError = true;
            }

            if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) {
                document.getElementById('emailError').textContent = "Please enter a valid email address.";
                document.getElementById('emailError').classList.remove('hidden');
                document.getElementById('email').parentElement.classList.add('border-red-500');
                hasError = true;
            }


            if (password.length < 6) {
                document.getElementById('passwordError').textContent = "Password must be at least 6 characters.";
                document.getElementById('passwordError').classList.remove('hidden');
                document.getElementById('password').parentElement.classList.add('border-red-500');
                hasError = true;
            } else if (password !== confirmPassword) {
                document.getElementById('confirmPasswordError').textContent = "Passwords do not match.";
                document.getElementById('confirmPasswordError').classList.remove('hidden');
                document.getElementById('confirmPassword').parentElement.classList.add('border-red-500');
                hasError = true;
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

            if (hasError) {
                return;
            }



            // Remove error styles if all fields are correct
            document.querySelectorAll('input').forEach(input => {
                input.parentElement.classList.remove('border-red-500');
                input.parentElement.classList.add('border-gray-300');
            });

            // Submit form data using the Fetch API
            try {
                const response = await fetch('/signup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name,
                        mobile,
                        email,
                        password,
                        referal
                    }),
                });

                const result = await response.json();

                // Display the message
                const messageDiv = document.getElementById('message');
                messageDiv.textContent = result.message;
                messageDiv.className = result.status === 'success' ? "text-green-700 text-sm mt-1" : "text-red-500 text-sm mt-1";

                // Redirect if signup is successful
                if (result.status === 'success') {
                    location.href = '/signupOtp';
                }

            } catch (error) {
                const messageDiv = document.getElementById('message');
                messageDiv.textContent = "Error signing up. Please try again later.";
                messageDiv.className = "text-red-500 text-sm mt-1";
            }
        });
    </script>
</body>

</html>