<!-- Shop Cart model -->
<div class="mini-shopping-cart mini-shopping-cart-md mini-shopping-cart-right duration-2000 transition-all md:w-1/2 lg:w-1/3"
	tabindex="-1" id="mini-shopping-cartRight">
	<div class="flex justify-between py-2 px-4 border-b">
		<div>
			<h5 class="text-lg font-semibold text-gray-800 cartsHeading"><?php echo lang('website.your_cart');?></h5>
		</div>
		<button type="button" class="btn-close text-reset" onclick="toggleShoppingCart()">
			<i class="fi fi-rr-x"></i>
		</button>
	</div>
	<div class="mini-shopping-cart-body p-2 overflow-y-auto ">
		<div class="discountedPricesaving"></div>
		<ul class="list-none" id="mini-shop-cart-item-list"></ul>
		<ul class="list-none" id="mini-seller-list"></ul>
		<div class="text-center flex flex-col items-center justify-center h-full hidden" id="emptyCartDiv">
			<img src="https://grocery-ci.apksoftwaresolution.com/assets/dist/img/no-data.png" class="w-24 mx-auto" />
			<p class="mt-2 text-gray-600 text-sm"> <?php echo lang('website.no_item_in_Cart');?></p>
		</div>
	</div>

	<div class="mini-shopping-cart-footer"></div>
</div>
<!-- Shop Cart model --> 

<div id="locationModal" class="fixed inset-0 flex items-end md:items-center justify-center bg-black bg-opacity-50 hidden z-40 md:px-[22%]">
	<div class="bg-gray-100 rounded-t-lg md:rounded-lg shadow-lg w-full min-h-min ">
		<div class="flex flex-col mb-4 p-6 pb-0">
			<h3 class="text-gray-600 mb-2"><?php echo lang('website.welcome_to');?> <span class="text-gray-800 font-medium"><?= $settings['business_name'] ?></span></h3>
			<div class="flex flex-row py-2">
				<i class="fi fi-tr-marker text-3xl md:text-lg self-center"></i>
				<p class="ml-2 text-sm text-gray-600 self-center"><?php echo lang('website.please_provide_your_delivery_location_to_see_products_at_nearby_store');?></p>
			</div>
			<div class="flex flex-row space-x-4 <?= flex_direction() ?> mt-4">
				<button onclick="useMyLocation()" type="button" class="w-full text-sm px-2 py-1 rounded-lg items-center gap-x-1 bg-green-600 text-white border-green-600 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 btn-sm">
					<span><?php echo lang('website.use_my_location');?></span>
				</button>
				<form action="#" class="w-full">
					<div class="relative">
						<button class="absolute left-3 top-2" type="button">
							<i class="fi fi-tr-marker text-lg"></i>
						</button>

						<input type="search" placeholder="Search City" id="citySearch" class="border w-full pl-8 p-2 rounded-lg text-gray-900" oninput="searchCity(this.value)">
					</div>
				</form>
			</div>
		</div>
		<div class="hidden" id="cityNotFoundMsg">
			<img src="<?= base_url('/assets/dist/img/not-found.svg') ?>" class="w-1/2 ml-auto mr-auto" />
			<p class="p-2 text-center text-sm text-gray-600"><?php echo lang('website.we_are_not_available_at_this_location_at_the_moment');?><br><?php echo lang('website.please_select_a_different_location');?></p>
		</div>
		<div id="citySuggestions" class="border border-gray-300 rounded-t-lg md:rounded-lg bg-white max-h-60 overflow-y-auto shadow-lg w-full z-10"></div>
	</div>
</div>