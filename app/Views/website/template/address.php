<div id="addressModal" class="fixed inset-0 flex items-end md:items-center justify-center bg-black bg-opacity-50 hidden z-40 md:px-[22%]">
    <div class="bg-gray-100 rounded-t-lg md:rounded-lg shadow-lg w-full min-h-min h-[80vh] md:h-[70vh] md:flex">

        <!-- Left Side (Map Section) -->
        <div class="md:w-1/2 w-full h-[35%] md:h-[90%] p-4">
            <form action="#" class="w-full mb-2">
                <div class="relative">
                    <button class="absolute left-3 top-2" type="button">
                        <i class="fi fi-tr-marker text-lg"></i>
                    </button>
                    <input type="search" placeholder="Search City" id="cityAreaSearch" class="border w-full pl-8 p-2 rounded-lg text-gray-900" oninput="searchCityArea(this.value)">
                </div>
            </form>
            <div id="cityAreaSuggestions" class="absolute border border-gray-300 rounded-lg bg-white max-h-60 overflow-y-auto shadow-lg z-10"></div>


            <div id="map" class="w-full h-full rounded-lg"></div>
        </div>

        <!-- Right Side (Content Section) -->
        <div class="md:w-1/2 w-full h-[65%] md:h-full p-4 overflow-y-auto max-h-full">
            <div class="flex justify-between border-b">
                <h5 class="text-lg font-semibold text-gray-800 pb-2"><?php echo lang('website.enter_complete_address'); ?></h5>
                <button type="button" class="btn-close text-reset" onclick="closeAddressPopup()">
                    <i class="fi fi-tr-x"></i>
                </button>
            </div>

            <div class="areaNotFound hidden">
                <img src="<?= base_url('/assets/dist/img/not-found.svg') ?>" class="w-1/2 ml-auto mr-auto" />
                <p class="p-2 text-center text-sm text-gray-600"><?php echo lang('website.we_are_not_available_at_this_location_at_the_moment'); ?><br> <?php echo lang('website.please_select_a_different_location'); ?></p>
            </div>

            <form class="flex flex-col pt-3 hidden addressForm">
                <input type="hidden" id="address_type" value="Home" name="address_type" />
                <div class="test flex space-x-4 <?= flex_direction() ?>">
                    <!-- Home Location -->
                    <div class="flex items-center space-x-2 <?= flex_direction() ?> cursor-pointer text-sm border p-2 rounded-lg border-green-700 bg-green-100 shadow-md" onclick="selectAddressType('Home')">
                        <i class="fi fi-rr-home"></i>
                        <span class="text-gray-700"><?php echo lang('website.home'); ?></span>
                    </div>

                    <!-- Work Location -->
                    <div class="flex items-center space-x-2 <?= flex_direction() ?> cursor-pointer text-sm border p-2 rounded-lg border-gray-300" onclick="selectAddressType('Work')">
                        <i class="fi fi-rr-building"></i>
                        <span class="text-gray-700"><?php echo lang('website.work'); ?></span>
                    </div>

                    <!-- Other Location -->
                    <div class="flex items-center space-x-2 <?= flex_direction() ?> cursor-pointer text-sm border p-2 rounded-lg border-gray-300" onclick="selectAddressType('Other')">
                        <i class="fi fi-rr-marker"></i>
                        <span class="text-gray-700"><?php echo lang('website.other'); ?></span>
                    </div>
                </div>


                <div class="flex gap-4 pt-4">
                    <div class="flex flex-col w-1/2">
                        <label class="text-gray-800 font-medium inline-block" for="flat">
                            <?php echo lang('website.flat_House_no_Building_name'); ?> <span class="text-red-600">*</span>
                        </label>
                        <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600">
                            <input type="text" id="flat" name="flat" class="text-gray-900 block p-2 w-full text-base border rounded-lg" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.flat_House_no_Building_name'); ?>" autocomplete="off">
                        </div>
                        <div id="flatError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <div class="flex flex-col w-1/2">
                        <label class="text-gray-800 font-medium inline-block" for="floor">
                            <?php echo lang('website.floor'); ?>
                        </label>
                        <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600">
                            <input type="text" id="floor" name="floor" class="text-gray-900 block p-2 w-full text-base border rounded-lg" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.floor'); ?>" autocomplete="off">
                        </div>
                        <div id="floorError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>

                <!-- Address Field (Full Width) -->
                <div class="flex flex-col pt-4">
                    <label class="text-gray-800 font-medium inline-block" for="address">
                        <?php echo lang('website.address'); ?>
                        <span class="text-red-600">*</span>
                    </label>
                    <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600">
                        <!-- <input type="text" id="address" name="address" class="text-gray-900 block p-2 w-full text-base border rounded-lg" placeholder="Enter Address" autocomplete="off"> -->
                        <textarea id="address" name="address" class="text-gray-900 block p-2 w-full text-base border rounded-lg" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.address'); ?>"></textarea>
                    </div>
                    <div id="addressError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <!-- Area and City Fields (50-50) -->
                <div class="flex gap-4 pt-4">
                    <div class="flex flex-col w-1/2">
                        <label class="text-gray-800 font-medium inline-block" for="area">
                            <?php echo lang('website.area'); ?>
                            <span class="text-red-600">*</span>
                        </label>
                        <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600">
                            <input type="text" id="area" name="area" class="text-gray-900 block p-2 w-full text-base border rounded-lg" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.area'); ?>" autocomplete="off">
                        </div>
                        <div id="areaError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <div class="flex flex-col w-1/2">
                        <label class="text-gray-800 font-medium inline-block" for="city">
                            <?php echo lang('website.city'); ?>
                            <span class="text-red-600">*</span>
                        </label>
                        <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600">
                            <input type="text" id="city" name="city" class="text-gray-900 block p-2 w-full text-base border rounded-lg" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.city'); ?>" autocomplete="off" readonly>
                        </div>
                        <div id="cityError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>

                <!-- State and Pincode Fields (50-50) -->
                <div class="flex gap-4 pt-4">
                    <div class="flex flex-col w-1/2">
                        <label class="text-gray-800 font-medium inline-block" for="state">
                            <?php echo lang('website.state'); ?>
                            <span class="text-red-600">*</span>
                        </label>
                        <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600">
                            <input type="text" id="state" name="state" class="text-gray-900 block p-2 w-full text-base border rounded-lg" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.state'); ?>" autocomplete="off" readonly>
                        </div>
                        <div id="stateError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <div class="flex flex-col w-1/2">
                        <label class="text-gray-800 font-medium inline-block" for="pincode">
                            <?php echo lang('website.pincode'); ?>
                        </label>
                        <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600">
                            <input type="text" id="pincode" name="pincode" class="text-gray-900 block p-2 w-full text-base border rounded-lg" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.pincode'); ?>" autocomplete="off">
                        </div>
                        <div id="pincodeError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>

                <div class="text-gray-400 text-sm pt-4"><?php echo lang('website.enter_your_details_for_seamless_delivery_experience'); ?></div>

                <div class="flex gap-4 ">
                    <div class="flex flex-col w-1/2">
                        <label class="text-gray-800 font-medium inline-block" for="user_name">
                            <?php echo lang('website.your_name'); ?> <span class="text-red-600">*</span>
                        </label>
                        <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600">
                            <input type="text" id="user_name" name="user_name" value="<?= $user_name ?>" class="text-gray-900 block p-2 w-full text-base border rounded-lg" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.your_name'); ?>" autocomplete="off">
                        </div>
                        <div id="userNameError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <div class="flex flex-col w-1/2">
                        <label class="text-gray-800 font-medium inline-block" for="user_mobile">
                            <?php echo lang('website.mobile'); ?> <span class="text-red-600">*</span>
                        </label>
                        <div class="flex items-center border border-gray-300 rounded-lg focus-within:border-green-600">
                            <input type="text" id="user_mobile" name="user_mobile" value="<?= $user_mobile ?>" class="text-gray-900 block p-2 w-full text-base border rounded-lg" placeholder="<?php echo lang('website.enter'); ?> <?php echo lang('website.mobile'); ?>" autocomplete="off">
                        </div>
                        <div id="userMobileError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>

                <!-- Message Display -->
                <div id="message" class="mt-2"></div>

                <!-- Submit Button -->
                <button type="submit" class="w-full rounded-lg bg-green-700 px-4 py-2 mt-4 text-center text-base font-semibold text-white shadow-md ring-gray-500 ring-offset-2 transition focus:ring-2"><?php echo lang('website.save_address'); ?></button>
            </form>

        </div>
    </div>
</div>