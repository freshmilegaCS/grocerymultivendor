<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?= base_url('/assets/website/js/custom.js') ?>"></script>

<script>
    function hideLoader() {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        loader.classList.add('fade-out');
        loader.addEventListener('animationend', () => loader.remove());
    }
}

    function simulateProgress() {
        const progressBar = document.querySelector('.progress-bar');
        let width = 0;
        const interval = setInterval(() => {
            if (width >= 100) {
                clearInterval(interval);
            } else {
                width += Math.random() * 10;
                progressBar.style.width = Math.min(width, 100) + '%';
            }
        }, 300);
    }

    window.addEventListener('load', () => {
        simulateProgress();
        setTimeout(hideLoader, 1500);
    });
    
    setTimeout(hideLoader, 10000);
</script>


<script>
	const dropdownUserLink = document.getElementById('dropdownUserLink');
	const dropdownUser = document.getElementById('dropdownUser');

	// Toggle dropdown visibility
	if (dropdownUserLink) {
		dropdownUserLink.addEventListener('click', function(event) {
			event.preventDefault();
			dropdownUser.classList.toggle('hidden');
		});
	}


	// Close dropdown when clicking outside
	document.addEventListener('click', function(event) {
		if (dropdownUserLink) {
			const isClickInside = dropdownUserLink.contains(event.target);
			if (!isClickInside) {
				dropdownUser.classList.add('hidden');
			}
		}
	});

	async function toggleShoppingCart() {
		const miniShoppingCart = document.getElementById('mini-shopping-cartRight');
		if (miniShoppingCart.classList.contains('show')) {
			miniShoppingCart.classList.remove('show');
			const backdropDiv = document.querySelector('.mini-shopping-cart-backdrop');
			if (backdropDiv) {
				backdropDiv.remove();
			}
		} else {
			miniShoppingCart.classList.add('show');
			const backdropDiv = document.createElement('div'); // Create the div element
			backdropDiv.className = 'mini-shopping-cart-backdrop fade show'; // Add the class names
			document.body.appendChild(backdropDiv); // Append the div to the body
		}

		let guest_id = localStorage.getItem('guest_id');

		try {
			const response = await fetch('/cartItemList', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({
					guest_id,
				}),
			});

			const result = await response.json();

			if (result.status === 'success') {
				if (!result.sellers && !result.productItems) {
					document.getElementById('emptyCartDiv').classList.add('block');
				}

				// Check for sellers
				document.querySelector('.mini-shopping-cart-footer').innerHTML = '';
				if (result.sellers) {
					Array.from(document.getElementsByClassName('cartsHeading')).forEach((element) => {
						element.textContent = 'Your Carts (' + result.sellers.length + ')'
					});
					const sellerList = document.getElementById('mini-seller-list');
					sellerList.innerHTML = ''; // Clear existing seller list

					result.sellers.forEach((seller) => {
						const sellerHtml = `
							<li class="py-3 border-b border-gray-300">
								<div class="flex items-center justify-between">
									<!-- Left Section -->
									<div class="flex items-center gap-3">
										<img src="${seller.logo}" alt="${seller.store_name}" class="w-12 h-12 rounded-full border" />
										<div>
											<span class="text-base font-medium block">${seller.store_name}</span>
											<span class="text-sm text-gray-500 block">Total items: ${seller.item_count}</span>
										</div>
									</div>
									<!-- Right Section -->
									<div class="flex flex-col items-end">
										<a href="/cart/${seller.seller_id}" class="flex flex-col text-center bg-green-600 text-white text-sm px-4 py-1 rounded-lg shadow-md hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
											<?php echo lang('website.view_cart'); ?>
											<span class="text-sm mt-1">Items: ${seller.item_count}</span>
										</a>
									</div>
								</div>
							</li>
						`;
						sellerList.insertAdjacentHTML('beforeend', sellerHtml);
					});


					return; // Exit function if sellers are returned
				}

				// If product items exist, execute the toggleShoppingCart logic
				if (result.productItems) {
					Array.from(document.getElementsByClassName('cartsHeading')).forEach((element) => {
						element.textContent = 'Your Carts (' + result.productItems.length + ')'
					});

					const cartItemList = document.getElementById('mini-shop-cart-item-list');
					cartItemList.innerHTML = ''; // Clear existing items

					const currency_symbol = result.currency_symbol;
					const currency_symbol_position = result.currency_symbol_position;

					const formatPrice = (price) => {
						return currency_symbol_position === 'left' ?
							`${currency_symbol}${price}` :
							`${price}${currency_symbol}`;
					};

					result.productItems.forEach((product) => {
						let priceHtml = product.discounted_price > 0 ?
							`
							<div class="flex gap-2">
								<span class="font-bold text-gray-800">${formatPrice(product.discounted_price)}</span>
								<div class="line-through text-gray-500 text-sm self-end">${formatPrice(product.price)}</div>
							</div>
						` :
							`
							<div class="flex gap-2">
								<span class="font-bold text-gray-800">${formatPrice(product.price)}</span>
							</div>
						`;

						let newHtml = `
						<li class="py-2 pl-2 pr-4 border-gray-300 border-b py-3 border-gray-200 ${product.slug}-maindiv-${product.product_variant_id}">
							<div class="flex gap-5">
								<img src="${product.main_img}" alt="${product.product_name}" class="w-28 h-28 border border-gray-300 rounded-lg" />
								<div class="flex flex-col gap-1 w-full">
									<div>
										<a href="#" class="text-base font-semibold">
											<h6 class="">${product.product_name}</h6>
										</a>
										<span class="text-gray-500 text-sm">${product.variant_title}</span>
									</div>
									${priceHtml}
									<div class="flex items-center justify-between">
										<div class="${product.slug}-mainbtndiv-${product.product_variant_id}">
											<div class="flex items-center gap-1 p-1 rounded-lg bg-green-700 border border-green-700 shadow-md">
												<button type="button" onclick="removeFromCart(${product.product_id}, ${product.product_variant_id})" class="text-lg leading-none hover:text-primary ${product.slug}-removebtn-${product.product_variant_id}">
													<i class="fi fi-rr-minus-small text-white"></i>
												</button>
												<span class="text-center h-5 text-sm font-medium text-white ${product.slug}-qty-${product.product_variant_id}">${product.quantity}</span>
												<button type="button" onclick="addToCart(${product.product_id}, ${product.product_variant_id})" class="text-lg leading-none hover:text-primary ${product.slug}-addbtn-${product.product_variant_id}">
													<i class="fi fi-rr-plus-small text-white"></i>
												</button>
											</div>
										</div>
										<div class="text-sm bg-red-100 p-1 rounded-lg shadow">
											<button class="text-red-900 flex gap-1" onclick="removeItem(${product.product_id}, ${product.product_variant_id})">
												<span class="align-text-bottom">
													<i class="fi fi-tr-trash-xmark text-xs"></i>
												</span>
												<span class="text-gray-500 text-xs text-red-600"><?php echo lang('website.remove'); ?></span>
											</button>
										</div>
									</div>
								</div>
							</div>
						</li>
					`;
						cartItemList.insertAdjacentHTML('beforeend', newHtml);
					});


					let cartFooterHtml = `
					<div class="p-4 w-full border-t bg-gray-50 bottom-0 shadow-lg">
						<div class="flex flex-col space-y-3">
							<div class="grid gap-2">
								<a href="/checkout" class="flex justify-between items-center bg-green-600 text-white rounded-lg p-3 shadow-md hover:bg-green-700 hover:border-green-700 focus:outline-none focus:ring-4 focus:ring-green-300 active:bg-green-700 disabled:opacity-50 disabled:pointer-events-none">
									<span class="text-lg font-medium"><?php echo lang('website.go_to_checkout'); ?></span>
									<span class="font-bold subtotal text-white">0</span>
								</a>
							</div>
							<p class="text-center text-sm text-gray-600">
							<?php echo lang('website.delivery_Taxes_&_Discounts_calculated_at_checkout'); ?>
							</p> 
						</div>
					</div>
					`;

					// Add the cartFooterHtml to the DOM
					document.querySelector('.mini-shopping-cart-footer').innerHTML = cartFooterHtml;

					// Update the subtotal once the footer is loaded
					Array.from(document.getElementsByClassName('subtotal')).forEach((element) => {
						element.textContent = formatPrice(result.subtotal);
					});

					<?php if (!$settings['seller_only_one_seller_cart']): ?>
						discountedPricesavingHtmlManipulate(result.discountedPricesaving, result.currency_symbol, result.currency_symbol_position)
					<?php endif; ?>
				}


			} else {
				// Handle error
			}
		} catch (error) {
			console.log(error);
		}
	}


	//common model product varient popup code
	const closeModalButton = document.getElementById('closeModalButton');
	const modalOverlay = document.getElementById('modalOverlay');
	const modal = document.getElementById('modal');

	function openProductVariantPopup(product_id, slug) {
		// Fetch product and variant details
		fetch(`/product/variants/${product_id}`)
			.then(response => response.json())
			.then(data => {
				if (data.status === 'success') {
					// Assuming you have a modal where product data should be displayed
					const product = data.product;
					const variants = data.variants;

					const currency_symbol = data.currency_symbol;
					const currency_symbol_position = data.currency_symbol_position;

					// Example: Set product name in the modal
					document.getElementById('modalProductName').textContent = product.product_name;

					// Assuming the variantsContainer is your #productVariantData element
					const variantsContainer = document.getElementById('productVarientData');

					// Clear previous variants (optional)
					variantsContainer.innerHTML = '';

					// Function to format price with currency symbol based on its position
					const formatPrice = (price) => {
						return currency_symbol_position === 'left' ?
							`${currency_symbol}${price}` :
							`${price}${currency_symbol}`;
					};

					if (variants.length > 1) {
						variants.forEach(variant => {
							const variantElement = document.createElement('div');
							variantElement.classList.add('flex', 'justify-between', 'mb-2');

							const formattedPrice = variant.discounted_price > 0 ?
								formatPrice(variant.discounted_price) :
								formatPrice(variant.price);

							variantElement.innerHTML = `
							<div class="flex items-center w-full justify-between p-3 mb-2 border rounded-xl shadow-sm bg-white">
								    <div>
                                      <div class="text-sm font-medium text-gray-800">${variant.title}</div>
                                    </div>
                                    
                                    <div>
                                      <div class="flex items-center gap-2 mt-1">
                                        <span class="text-base font-semibold text-gray-900">${formattedPrice}</span>
                                        ${variant.mrp ? `<span class="line-through text-gray-400 text-sm">₹${variant.mrp}</span>` : ''}
                                      </div>
                                    </div>
                                
                                    <div class="${slug}-mainbtndiv-${variant.id}">
                                      <button 
                                        type="button" 
                                        onclick="addToCart(${product_id}, ${variant.id})"
                                        class="px-4 py-1.5 text-sm rounded-lg bg-green-600 text-white font-medium hover:bg-green-700 transition ${slug}-${variant.id}">
                                        <?php echo lang('website.add'); ?>
                                      </button>
                                    </div>
                                </div>
							`;
                                    

							// Append this variant to the container
							variantsContainer.appendChild(variantElement);
						});

						// Show the modal
						document.getElementById('modal').classList.remove('hidden');
						document.getElementById('modalOverlay').classList.remove('hidden');
					} else {
						addToCart(product_id, variants[0].id)
					}
				} else {
					console.error('Product not found');
				}
			})
			.catch(error => console.error('Error fetching product data:', error));
	}

	function discountedPricesavingHtmlManipulate(discountedPricesaving, currency_symbol, currency_symbol_position) {
		document.querySelector('.discountedPricesaving').innerHTML = '';
		if (+discountedPricesaving > 0) {
			let discountedPricesavingHtml = `<div class="flex items-center justify-between bg-emerald-50 border border-emerald-300 rounded-lg shadow-lg p-4 w-full">
                    <div class="flex items-center gap-4">
                        <div class="bg-emerald-400 text-white font-bold rounded-full w-12 h-12 flex items-center justify-center shadow-lg">
                            <i class="fi fi-rr-piggy-bank text-lg"></i>
                        </div>
                        <div>
                            <p class="text-base font-semibold text-emerald-800"><?php echo lang('website.congratulations'); ?>!</p>
                            <p class="text-sm text-emerald-600"><?php echo lang('website.youre_saving'); ?> <span class="font-bold text-emerald-800 discountedPricesavingAmt"></span> <?php echo lang('website.on_this_purchase'); ?>!</p>
                        </div>
                    </div>
                </div>`;

			document.querySelector('.discountedPricesaving').innerHTML = discountedPricesavingHtml;

			// Format amount based on currency position
			let formattedAmount = currency_symbol_position === 'left' ?
				`${currency_symbol}${discountedPricesaving}` :
				`${discountedPricesaving}${currency_symbol}`;

			Array.from(document.getElementsByClassName('discountedPricesavingAmt')).forEach((element) => {
				element.textContent = formattedAmount;
			});
		}
	}

	async function addToCart(product_id, variant_id) {
		let guest_id = localStorage.getItem('guest_id');

		localStorage.setItem('wallet', JSON.stringify({
			wallet_applied: 0,
			remaining_wallet_balance: 0
		}));

		localStorage.setItem('wallet', JSON.stringify({
			coupon_id: 0,
			coupon_code: '',
			coupon_amount: 0,
			coupon_minOrderAmount: 0,
			coupon_type:0
		}));

		try {
			const response = await fetch('/addToCart', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({
					product_id,
					variant_id,
					guest_id
				}),
			});

			const result = await response.json(); // Await here to parse the JSON response

			if (result.status === 'success') {
				const currency_symbol = result.currency_symbol;
				const currency_symbol_position = result.currency_symbol_position;

				const formatPrice = (price) => {
					return currency_symbol_position === 'left' ?
						`${currency_symbol}${price}` :
						`${price}${currency_symbol}`;
				};

				// Handle success
				let mainAddBtn = document.getElementsByClassName(`${result.slug}-${variant_id}`)
				while (mainAddBtn.length > 0) {
					mainAddBtn[0].parentNode.removeChild(mainAddBtn[0]);
				}

				let mainbtndiv = document.getElementsByClassName(`${result.slug}-mainbtndiv-${variant_id}`)
				let cartQuantity = result.quantity;

				// Generate HTML string to insert
				let newHtml = `
					<div class="inline-flex items-center gap-1 p-1 rounded-lg bg-green-700 border border-green-700 shadow-md">
						<button type="button" onclick="removeFromCart(${product_id}, ${variant_id})"
							class="text-lg leading-none hover:text-primary ${result.slug}-removebtn-${variant_id}">
							<i class="fi fi-rr-minus-small text-white"></i>
						</button>
						<span class="text-center h-5 text-sm font-medium text-white ${result.slug}-qty-${variant_id}">${cartQuantity}</span>
						<button type="button" onclick="addToCart(${product_id}, ${variant_id})"
							class="text-lg leading-none hover:text-primary ${result.slug}-addbtn-${variant_id}">
							<i class="fi fi-rr-plus-small text-white"></i>
						</button>
					</div>
				`;

				// Insert the new HTML into each matching element
				for (let i = 0; i < mainbtndiv.length; i++) {
					mainbtndiv[i].innerHTML = newHtml;
				}

				let cartCount = document.getElementById('cartCount');
				cartCount.innerText = result.itemCount

				let subtotalElements = document.getElementsByClassName('subtotal');
				for (let i = 0; i < subtotalElements.length; i++) {
					subtotalElements[i].textContent = formatPrice(result.subtotal);
				}

				discountedPricesavingHtmlManipulate(result.discountedPricesaving, result.currency_symbol, result.currency_symbol_position)

				showToast(result.message, "success");

			} else {
				// Handle error
				showToast(result.message, "danger");

			}
		} catch (error) {
			console.log(error);
		}
	}

	async function removeFromCart(product_id, variant_id) {
		let guest_id = localStorage.getItem('guest_id');

		localStorage.setItem('wallet', JSON.stringify({
			wallet_applied: 0,
			remaining_wallet_balance: 0
		}));

		localStorage.setItem('wallet', JSON.stringify({
			coupon_id: 0,
			coupon_code: '',
			coupon_amount: 0,
			coupon_minOrderAmount: 0,
			coupon_type:0
		}));

		try {
			const response = await fetch('/removeFromCart', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({
					product_id,
					variant_id,
					guest_id
				}),
			});

			const result = await response.json();
			console.log(result)
			if (result.status === 'success') {
				let cartQuantity = result.quantity;
				let qtySpans = document.getElementsByClassName(`${result.slug}-qty-${variant_id}`)

				for (let i = 0; i < qtySpans.length; i++) {
					qtySpans[i].textContent = cartQuantity;
				}

				const currency_symbol = result.currency_symbol;
				const currency_symbol_position = result.currency_symbol_position;

				const formatPrice = (price) => {
					return currency_symbol_position === 'left' ?
						`${currency_symbol}${price}` :
						`${price}${currency_symbol}`;
				};

				let cartCount = document.getElementById('cartCount');
				cartCount.innerText = result.itemCount

				let subtotalElements = document.getElementsByClassName('subtotal');
				for (let i = 0; i < subtotalElements.length; i++) {
					subtotalElements[i].textContent = formatPrice(result.subtotal);
				}
				showToast(result.message, "success");

				discountedPricesavingHtmlManipulate(result.discountedPricesaving, result.currency_symbol, result.currency_symbol_position)


			} else {
				let mainAddBtn = document.getElementsByClassName(`${result.slug}-${variant_id}`)
				showToast(result.message, "danger");

			}
		} catch (error) {
			console.log(error)
		}
	}

	async function removeItem(product_id, variant_id) {
		let guest_id = localStorage.getItem('guest_id');

		localStorage.setItem('wallet', JSON.stringify({
			wallet_applied: 0,
			remaining_wallet_balance: 0
		}));

		localStorage.setItem('wallet', JSON.stringify({
			coupon_id: 0,
			coupon_code: '',
			coupon_amount: 0,
			coupon_minOrderAmount: 0,
			coupon_type:0
		}));

		try {
			const response = await fetch('/removeItem', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({
					product_id,
					variant_id,
					guest_id
				}),
			});

			const result = await response.json();
			console.log(result)
			if (result.status === 'success') {
				let removeItems = document.getElementsByClassName(`${result.slug}-maindiv-${variant_id}`);
				Array.from(removeItems).forEach((removeItem) => {
					removeItem.remove();
				});

				let cartCount = document.getElementById('cartCount');
				cartCount.innerText = result.itemCount

				const currency_symbol = result.currency_symbol;
				const currency_symbol_position = result.currency_symbol_position;

				const formatPrice = (price) => {
					return currency_symbol_position === 'left' ?
						`${currency_symbol}${price}` :
						`${price}${currency_symbol}`;
				};

				let subtotalElements = document.getElementsByClassName('subtotal');
				for (let i = 0; i < subtotalElements.length; i++) {
					subtotalElements[i].textContent = formatPrice(result.subtotal);
				}
				showToast(result.message, "success");
				discountedPricesavingHtmlManipulate(result.discountedPricesaving, result.currency_symbol, result.currency_symbol_position)


			} else {
				showToast(result.message, "danger");

			}
		} catch (error) {
			console.log(error)
		}
	}

	if (closeModalButton) {
		closeModalButton.addEventListener('click', () => {
			modal.classList.add('hidden');
			modalOverlay.classList.add('hidden');
		});
	}

	// Hide modal when clicking outside modal content
	if (modalOverlay) {
		modalOverlay.addEventListener('click', () => {
			modal.classList.add('hidden');
			modalOverlay.classList.add('hidden');
		});
	}
</script>

<!-- /this script for productDetails -->
<script>
	// Function to show the relevant tab pane
	function showTab(element) {
		// Get all tab buttons and remove 'active-tab' class from them
		const allTabs = document.querySelectorAll('.nav-link');
		allTabs.forEach(tab => tab.classList.remove('active-tab'));

		// Add 'active-tab' class to the clicked button
		element.classList.add('active-tab');

		// Get all tab panes and hide them
		const allTabPanes = document.querySelectorAll('.tab-pane');
		allTabPanes.forEach(pane => pane.classList.add('hidden'));

		// Get the target tab-pane from the clicked button
		const targetPaneId = element.getAttribute('data-bs-target');
		const targetPane = document.querySelector(targetPaneId);

		// Show the target tab pane
		targetPane.classList.remove('hidden');
		targetPane.classList.add('block');
	}

	function zoom(f) {
		var t = f.currentTarget;
		offsetX = f.offsetX || f.touches[0].pageX, f.offsetY ? offsetY = f.offsetY : offsetX = f.touches[0].pageX, x = offsetX / t.offsetWidth * 100, y = offsetY / t.offsetHeight * 100, t.style.backgroundPosition = x + "% " + y + "%"
	}

	// Initialize Swiper
	const mainSwiper = new Swiper('#productSwiper', {
		slidesPerView: 1,
		spaceBetween: 10,
		on: {
			slideChange: updateActiveThumbnail // Update active thumbnail on slide change
		}
	});

	// Get all thumbnail elements
	const thumbnails = document.querySelectorAll('#productThumbnails .thumbnails-img');

	// Function to set the active thumbnail
	function updateActiveThumbnail() {
		// Remove active class from all thumbnails
		thumbnails.forEach(thumbnail => thumbnail.classList.remove('active-thumbnail'));

		// Add active class to the current thumbnail
		const activeIndex = mainSwiper.activeIndex;
		if (thumbnails[activeIndex]) {
			thumbnails[activeIndex].classList.add('active-thumbnail');
		}
	}

	// Add click event to each thumbnail
	thumbnails.forEach((thumbnail, index) => {
		thumbnail.addEventListener('click', () => {
			mainSwiper.slideTo(index); // Slide to the clicked thumbnail index
		});
	});

	// Set the initial active thumbnail
	updateActiveThumbnail();

	function copyLink() {
		const url = window.location.href;
		navigator.clipboard.writeText(url)
			.then(() => alert('Link copied to clipboard!'))
			.catch(err => console.error('Error copying text: ', err));
	}

	// Check if the shareButton exists in the DOM
	const shareButton = document.getElementById('shareButton');
	if (shareButton) {
		// Add event listener only if shareButton is available
		shareButton.addEventListener('click', async () => {
			if (navigator.share) {
				try {
					await navigator.share({
						title: 'Check out this page!',
						url: '<?= current_url(); ?>',
					});
					showToast('Successfully shared', 'success');
				} catch (error) {
					console.error('Error sharing:', error);
				}
			} else {
				showToast('Web Share API not supported in your browser.', 'error');
			}
		});
	} else {
		console.log('shareButton element is not available in the DOM.');
	}
</script>

<!-- /from get location code start -->
<script src="https://maps.googleapis.com/maps/api/js?key=<?= $settings['map_api_key'] ?>&libraries=places&callback=initAutocomplete" async defer></script>
<script>
	const locationData = {
		city: '',
		state: '',
		country: '',
		postalCode: '',
		lat: '',
		lng: '',
		area: '',
		landmark: '',
		city_id: 0,
		deliverable_area_id: 0
	};

	const locationModal = document.getElementById('locationModal');

	const cityNotFoundMsg = document.getElementById('cityNotFoundMsg');
	const suggestionsContainer = document.getElementById('citySuggestions');

	function setGuestId() {
		if (!localStorage.getItem('guest_id')) {
			const randomGuestId = Math.floor(100000 + Math.random() * 900000);
			localStorage.setItem('guest_id', randomGuestId);
		}
	}

	function openLocationModel() {
		locationModal.classList.remove('hidden');
		document.body.classList.add('modal-open');
	}

	let autocompleteService;

	function initAutocomplete() { 
		const input = document.getElementById('citySearch');
		autocompleteService = new google.maps.places.AutocompleteService();
	}

	function searchCity(query) {
		if (query.length < 3) {
			document.getElementById('citySuggestions').classList.add('hidden');
			return;
		}

		const options = {
			input: query,
		};

		autocompleteService.getPlacePredictions(options, (predictions, status) => {
			if (status === google.maps.places.PlacesServiceStatus.OK) {
				displaySuggestions(predictions);
			} else {
				document.getElementById('citySuggestions').classList.add('hidden');
			}
		});
	}

	function displaySuggestions(predictions) {
		cityNotFoundMsg.classList.add('hidden');

		suggestionsContainer.innerHTML = ''; // Clear previous suggestions
		suggestionsContainer.classList.remove('hidden');

		predictions.forEach((prediction) => {
			const suggestionItem = document.createElement('div');
			suggestionItem.classList.add('p-2', 'hover:bg-gray-100', 'cursor-pointer', 'flex', 'items-center');

			// Create the icon element
			const icon = document.createElement('i');
			icon.classList.add('fi', 'fi-tr-marker', 'text-sm', 'mr-1');

			// Create the text element for the location description
			const text = document.createElement('span');
			text.textContent = prediction.description;

			// Append the icon and text to the suggestionItem
			suggestionItem.appendChild(icon);
			suggestionItem.appendChild(text);

			// When a suggestion is clicked
			suggestionItem.onclick = () => {

				const placesService = new google.maps.places.PlacesService(document.createElement('div'));

				placesService.getDetails({
					placeId: prediction.place_id
				}, (place, status) => {
					if (status === google.maps.places.PlacesServiceStatus.OK) {
						const locationData = {
							city: '',
							state: '',
							country: '',
							postalCode: '',
							lat: place.geometry.location.lat(),
							lng: place.geometry.location.lng(),
							area: '',
							landmark: place.name,
							city_id: 0,
							deliverable_area_id: 0
						};

						// Parse address components
						place.address_components.forEach(component => {
							console.log(component)
							const types = component.types;
							if (types.includes("locality")) {
								locationData.city = component.long_name;
							} else if (types.includes("administrative_area_level_1")) {
								locationData.state = component.long_name;
							} else if (types.includes("country")) {
								locationData.country = component.long_name;
							} else if (types.includes("postal_code")) {
								locationData.postalCode = component.long_name;
							} else if (types.includes("sublocality") || types.includes("neighborhood")) {
								locationData.area = component.long_name;
							}
						});

						if (locationData.city) {
							fetch('/fetchDeliverableAreaByLatLong', {
									method: 'POST',
									headers: {
										'Content-Type': 'application/json',
									},
									body: JSON.stringify({
										name: locationData.city,
										lat: place.geometry.location.lat(),
										lng: place.geometry.location.lng(),
										guest_id: localStorage.getItem('guest_id')
									}),
								})
								.then(response => {
									if (!response.ok) {
										throw new Error('City not found');
									}
									return response.json();
								})
								.then(data => {
									if (data.id > 0) {
										locationData.city_id = data.id;
										locationData.deliverable_area_id = data.deliverable_area_id;
                                        document.getElementById("proxyDeliveryTime").textContent = data.delivery_time;
										localStorage.setItem('location', JSON.stringify(locationData));
										suggestionsContainer.classList.add('hidden');
										locationModal.classList.add('hidden');
										document.body.classList.remove('modal-open');
										setLocationBar()

										location.reload()
									} else {
										cityNotFoundMsg.classList.remove('hidden');
										suggestionsContainer.classList.add('hidden');
									}
								})
								.catch(error => {
									console.error(error);
								});
						} else {
							console.warn('');
						}

					} else {
						console.error('Failed to fetch place details:', status);
					}
				});
			};

			suggestionsContainer.appendChild(suggestionItem);
		});
	}

	async function fetchCartItemCount() {
		let cartCount = document.getElementById('cartCount');
		let guest_id = localStorage.getItem('guest_id');

		try {
			const response = await fetch('/fetchCartItemCount', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify({
					guest_id
				}),
			});
			const result = await response.json(); // Await here to parse the JSON response

			if (result.status === 'success') {
				cartCount.innerText = result.itemCount
			} else {}
		} catch (error) {
			console.log(error);
		}

	}

	window.onload = function() {
		setGuestId()
		initAutocomplete();
		setLocationBar()
		fetchCartItemCount();
		let locationData = JSON.parse(localStorage.getItem('location'));



		if (locationData && locationData.city) {
			fetch('/fetchDeliverableAreaByLatLong', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({
						name: locationData.city,
						lat: locationData.lat,
						lng: locationData.lng,
						guest_id: localStorage.getItem('guest_id'),
					}), // Send city name in the request
				})
				.then(response => {
					if (!response.ok) {
						throw new Error('City not found');
					}
					return response.json();
				})
				.then(data => {
					if (data.id > 0) {
					    document.getElementById("proxyDeliveryTime").textContent = data.delivery_time;
					} else {
						locationModal.classList.remove('hidden');
						document.body.classList.add('modal-open');
					}
				})
				.catch(error => {
					console.error('Error fetching city ID:', error);
				});
		} else {
			locationModal.classList.remove('hidden');
			document.body.classList.add('modal-open');
		}
	};

	function setLocationBar() {
		let locationData = JSON.parse(localStorage.getItem('location'));
		const locationBarSubtitle = document.getElementById('locationBarSubtitle');

		if (locationData && locationData.city) {

			let locationSubtitle = locationData.city + ' ,' + locationData.state + ' ,' + locationData.country
			locationBarSubtitle.innerText = locationSubtitle;
		} else {
			locationBarSubtitle.innerText = 'Choose Location';
		}
	}

	// Function to get user's current location and detect city
	function useMyLocation() {
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
		} else {
			alert("Geolocation is not supported by this browser.");
		}
	}

	function successCallback(position) {
		const lat = position.coords.latitude;
		const lng = position.coords.longitude;

		// Update latitude and longitude in locationData
		locationData.lat = lat;
		locationData.lng = lng;

		// Geocode the coordinates to get detailed location data
		const geocoder = new google.maps.Geocoder();
		const latlng = {
			lat,
			lng
		};

		geocoder.geocode({
			location: latlng
		}, (results, status) => {
			if (status === "OK" && results[0]) {
				// Populate locationData with address components
				results[0].address_components.forEach(component => {
					const types = component.types;
					if (types.includes("locality")) {
						locationData.city = component.long_name;
					} else if (types.includes("administrative_area_level_1")) {
						locationData.state = component.long_name;
					} else if (types.includes("country")) {
						locationData.country = component.long_name;
					} else if (types.includes("postal_code")) {
						locationData.postalCode = component.long_name;
					} else if (types.includes("sublocality") || types.includes("neighborhood")) {
						locationData.area = component.long_name;
					}
				});

				// Populate input field
				document.getElementById('citySearch').value = locationData.city;

				// Fetch city ID from server
				fetch('/fetchDeliverableAreaByLatLong', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
						},
						body: JSON.stringify({
							name: locationData.city,
							lat: locationData.lat,
							lng: locationData.lng,
						}),
					})
					.then(response => {
						if (!response.ok) {
							throw new Error('City not found');
						}
						return response.json();
					})
					.then(data => {
						if (data.id > 0) {
							locationData.city_id = data.id;
							locationData.deliverable_area_id = data.deliverable_area_id;
							// Save locationData to localStorage
							localStorage.setItem('location', JSON.stringify(locationData));
                            document.getElementById("proxyDeliveryTime").textContent = data.delivery_time;
							// Hide modal and backdrop
							suggestionsContainer.classList.add('hidden');
							locationModal.classList.add('hidden');
							document.body.classList.remove('modal-open');
							setLocationBar()

							location.reload()
						} else {
							// Handle city not found message
							cityNotFoundMsg.classList.remove('hidden');
							suggestionsContainer.classList.add('hidden');
						}
					})
					.catch(error => {
						console.error('Error:', error);
					});
			} else {
				console.error("Geocode error:", status);
				alert("Failed to get city name");
			}
		});
	}

	function errorCallback(error) {
		console.error(error);
		alert("Unable to retrieve your location");
	}
</script>

<script>
	async function uploadUserProfilePic(event) {
		const file = event.target.files[0];

		const formData = new FormData();
		formData.append('file', file);

		try {
			const response = await fetch('/uploadUserProfilePic', {
				method: 'POST',
				body: formData,
			});

			const result = await response.json();

			if (response.ok) {

				if (result.status == 'success') {
					showToast(result.message, "success")

					location.reload(); // Reload to reflect changes
				} else {
					showToast(result.message, "error")
				}

			} else {
				showToast(result.message, "error")
			}
		} catch (error) {
			alert('File upload failed. Please try again.');
			console.error(error);
		}
	}
</script>
<?php
if ($settings['twak_live_chat_status']) {
	echo $settings['twak_live_chat_widget_code'];
}
?>