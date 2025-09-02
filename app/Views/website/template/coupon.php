<div id="couponModal" class="fixed inset-0 flex items-end md:items-center justify-center bg-black bg-opacity-50 hidden z-40 md:px-[22%]">
    <div class="bg-gray-100 rounded-t-lg md:rounded-lg shadow-lg w-full md:w-[600px] lg:w-[800px] max-h-[80vh] flex flex-col">
        <!-- Header (Fixed, Non-Scrollable) -->
        <div class="flex justify-between border-b p-4">
            <h5 class="text-lg font-semibold text-gray-800"><?php echo lang('website.coupon_code'); ?></h5>
            <button type="button" class="btn-close text-reset" onclick="closeCouponPopup()">
                <i class="fi fi-tr-x"></i>
            </button>
        </div>

        <!-- Scrollable Body -->
        <div class="overflow-y-auto p-4 flex-grow">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2" id="couponListDiv"></div>

            <div id="noCouponAvialbleDiv" class="hidden">
                <img
                    src="<?= base_url('assets/dist/img/no-data.png') ?>"
                    alt="Coming Soon"
                    class="mx-auto w-2/3 sm:w-1/3 rounded-lg" />

                <!-- Text Section -->
                <div class="text-gray-700 text-center">
                    <?php echo lang('website.no_coupon_avialble'); ?>
                </div>
            </div>
        </div>
    </div>
</div>