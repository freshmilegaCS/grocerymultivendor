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
                    <p class="text-center text-xl md:text-3xl font-bold"><?php echo lang('website.login_with_mobile_number'); ?></p>
                    <p class="mt-2 text-center text-gray-500"><?php echo lang('website.enter_your_mobile_number_and_get_otp'); ?></p>
                    <form class="flex flex-col pt-3 md:pt-8">

                        <div class="flex flex-col pt-4 ">
                            <label class="text-gray-800 font-medium inline-block" for="mobile">
                                <?php echo lang('website.mobile'); ?>
                                <span class="text-red-600">*</span>
                            </label>
                            <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600 focus-within:shadow-[0_0_0_.25rem_rgba(10,173,10,.25)]">
                                <span class="px-3 text-gray-500">
                                    <!-- <i class="fi fi-tr-mobile"></i> -->
                                    <?= $country['country_code'] ?>
                                </span>
                                <input data-validation-no="<?= $country['validation_no'] ?> type=" number" id="mobile" name="mobile" class="text-gray-900 focus:ring-0 focus:border-0 block p-2 w-full text-base border-0 disabled:opacity-50 disabled:pointer-events-none" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.mobile'); ?>">
                            </div>
                            <div id="mobileError" class="text-red-500 text-sm mt-1 hidden"></div>

                        </div>

                        <div id="message" class="mt-2 mb-6"></div>

                        <button type="submit" class="w-full rounded-lg bg-green-700 px-4 py-2 text-center text-base font-semibold text-white shadow-md ring-gray-500 ring-offset-2 transition focus:ring-2"><?php echo lang('website.send_otp'); ?></button>
                    </form>

                    <?php if ($settings['direct_login'] == 1): ?>

                        <div class="relative mt-8 flex h-px place-items-center bg-green-200">
                            <div class="absolute left-1/2 h-6 w-14 -translate-x-1/2 bg-white text-center text-sm text-gray-500">or</div>
                        </div>
                        <div class="pt-6 text-center mb-[20%] md:mb-0">
                            <p class="whitespace-nowrap text-gray-600">
                                <?php echo lang('website.already_have_an_account_using_email'); ?>
                                <a href="/login" class="underline-offset-4 font-semibold text-green-700 underline"><?php echo lang('website.login_here'); ?></a>
                            </p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
            <div class="pointer-events-none relative hidden select-none bg-white lg:block lg:w-1/2">
                <img class="absolute top-0 h-full w-full" src="<?= base_url('/assets/dist/img/mobile-login-banner.svg') ?>" />
            </div>
        </div>

        <?= $this->include('website/template/mobileBottomMenu') ?>

    </main>
    <?= $this->include('website/template/script') ?>

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

            const mobileInput = document.getElementById('mobile');
            const mobile = mobileInput.value.trim();
            const validationLength = parseInt(mobileInput.dataset.validationNo, 10);
            const mobileError = document.getElementById('mobileError');
            const messageDiv = document.getElementById('message');

            let hasError = false;

            // Validation logic
            if (!/^\d+$/.test(mobile)) {
                mobileError.textContent = "Mobile number must contain digits only.";
                mobileError.classList.remove('hidden');
                mobileInput.parentElement.classList.add('border-red-500');
                hasError = true;
            } else if (mobile.length !== validationLength) {
                mobileError.textContent = `Mobile number must be exactly ${
                    validationLength
                }
                digits.`;
                mobileError.classList.remove('hidden');
                mobileInput.parentElement.classList.add('border-red-500');
                hasError = true;
            }

            if (hasError) return;

            // Re-apply normal styles if no error
            mobileInput.parentElement.classList.remove('border-red-500');
            mobileInput.parentElement.classList.add('border-gray-300');

            // Show loading message
            messageDiv.textContent = "Sending OTP, please wait...";
            messageDiv.className = "text-blue-600 text-sm mt-2 mb-6";

            // Submit form data using Fetch
            try {
                const response = await fetch('/mobileLogin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        mobile
                    }),
                });

                let result;

                // Try to parse JSON response
                try {
                    const responseText = await response.text();
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error("Failed to parse response as JSON:", e);
                    throw new Error("Invalid response from server");
                }

                // Check if we have a result with proper format
                if (!result || typeof result !== 'object') {
                    throw new Error("Invalid response format");
                }

                // Display message
                messageDiv.textContent = result.message || "Operation completed";
                messageDiv.className = result.status === 'success' ?
                    "text-green-700 text-sm mt-2 mb-6" :
                    "text-red-500 text-sm mt-2 mb-6";

                // IMPORTANT: Store the mobile for the next page
                if (result.status === 'success') {
                    // Use sessionStorage or localStorage to remember the mobile number
                    sessionStorage.setItem('mobile', mobile);

                    // Redirect to OTP page
                    setTimeout(() => {
                        location.href = '/mobileOtp';
                    }, 1000);
                }
            } catch (error) {
                console.error("Error:", error);

                // Show friendly error message
                messageDiv.textContent = "Error sending OTP. Please try again later.";
                messageDiv.className = "text-red-500 text-sm mt-2 mb-6";
            }
        });
    </script>

</body>

</html>