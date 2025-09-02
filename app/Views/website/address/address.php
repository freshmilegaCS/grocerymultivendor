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
                    <h2 class="text-lg font-medium z-10"><?php echo lang('website.address'); ?></h2>
                </div>
            </div>
        </section>
        <section class="mt-2 md:mt-4 md:container md:mx-auto md:px-3">

            <div class="flex flex-wrap lg:flex-nowrap lg:gap-x-6 gap-y-6">
                <?= $this->include('website/template/dashboardSidebar') ?>

                <div class="w-full lg:w-full md:w-full mx-auto">
                    <div class="mb-2 rounded-lg bg-white">
                        <div
                            class="flex  items-center justify-between gap-3 p-4 border-b border-gray-100">
                            <h4 class="font-bold capitalize"> <?php echo lang('website.delivery_address'); ?></h4>
                            <div class="flex  items-center gap-4">
                                <button type="button" onclick="openAddressPopup()"
                                    class="px-3 h-8 leading-8 rounded-lg flex items-center gap-2 bg-[#FFF4F1] text-primary">
                                    <i class="fi fi-tr-add"></i>
                                    <span
                                        class="text-sm font-medium capitalize whitespace-nowrap text-red-600"><?php echo lang('website.add_new'); ?></span>
                                </button>
                            </div>
                        </div>
                        <div class=" grid md:grid-cols-3 grid-cols-1 gap-6 p-4 address-div"></div>

                        <div class="no-address-found"></div>
                    </div>
                </div>
            </div>


        </section>


        <?= $this->include('website/template/mobileBottomMenu') ?>
    </main>
    <?= $this->include('website/template/shopCart') ?>
    <?= $this->include('website/template/address') ?>
    <?= $this->include('website/template/footer') ?>
    <?= $this->include('website/template/script') ?>
    <script src="<?= base_url('/assets/page-script/website/address.js') ?>"></script>
    <script>
        const addressModal = document.getElementById('addressModal');
        let latitude
        let longitude
        let map_address

        async function fetchAddressList() {
            try {
                const response = await fetch('/fetchAllAddressList', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                });

                const result = await response.json();

                if (result.status === 'success') {
                    const {
                        addressesWithInActiveStatus,
                        addressWithActiveStatus
                    } = result;

                    // Clear the current addresses in the DOM
                    const addressDiv = document.querySelector('.address-div');
                    const noaddressfound = document.querySelector('.no-address-found');
                    addressDiv.innerHTML = '';
                    noaddressfound.innerHTML = '';

                    // Append Active Address
                    if (addressWithActiveStatus) {
                        const activeAddressHTML = generateAddressHTML(addressWithActiveStatus, true);
                        addressDiv.insertAdjacentHTML('beforeend', activeAddressHTML);
                    }

                    // Append Inactive Addresses
                    if (addressesWithInActiveStatus.length > 0) {
                        addressesWithInActiveStatus.forEach(inactiveAddress => {
                            const inactiveAddressHTML = generateAddressHTML(inactiveAddress, false);
                            addressDiv.insertAdjacentHTML('beforeend', inactiveAddressHTML);
                        });
                    }

                    // If no addresses found
                    if (!addressWithActiveStatus && addressesWithInActiveStatus.length === 0) {
                        noaddressfound.innerHTML = '<div class="flex flex-col gap-4 text-center"><img src="<?= base_url('assets/dist/img/no-data.png') ?>" alt="" class="mx-auto w-2/3 sm:w-1/3 rounded-lg" /><div class="text-sm text-gray-700">No Order History Available</div></div>';
                    }
                } else {
                    showToast(result.message, "danger");
                    console.error('Failed to fetch addresses:', result.message);
                }
            } catch (error) {
                console.error('Error fetching address list:', error);
            }
        }



        document.querySelector('form.addressForm').addEventListener('submit', async (event) => {
            event.preventDefault();

            const address_type = document.getElementById('address_type').value;
            const address = document.getElementById('address').value.trim();
            const area = document.getElementById('area').value.trim();
            const city = document.getElementById('city').value.trim();
            const state = document.getElementById('state').value.trim();
            const pincode = document.getElementById('pincode').value.trim();

            const flat = document.getElementById('flat').value.trim();
            const floor = document.getElementById('floor').value.trim();
            const user_name = document.getElementById('user_name').value.trim();
            const user_mobile = document.getElementById('user_mobile').value.trim();


            const messageDiv = document.getElementById('message');

            // Basic validation
            if (!address || !area || !city || !state || !pincode || !flat || !user_mobile || !user_name) {
                messageDiv.textContent = 'Please enter required fields'
                messageDiv.className = 'text-red-500 text-sm mt-1';
                return;
            }

            if (!/^\d{<?= $country['validation_no'] ?>}$/.test(user_mobile)) {
                document.getElementById('userMobileError').textContent = "Please enter a valid <?= $country['validation_no'] ?>-digit mobile number.";
                document.getElementById('userMobileError').classList.remove('hidden');
                document.getElementById('user_mobile').parentElement.classList.add('border-red-500');
                return
            }

            try {
                const response = await fetch('/saveAddress', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        address,
                        area,
                        city,
                        state,
                        pincode,
                        latitude,
                        longitude,
                        map_address,
                        address_type,
                        flat,
                        floor,
                        user_name,
                        user_mobile
                    }),
                });

                const result = await response.json();

                // Handle success or error response
                if (result.status === 'success') {
                    event.target.reset();
                    closeAddressPopup()

                    const subtotal = document.getElementsByClassName('subtotal')
                    const taxTotal = document.getElementsByClassName('taxTotal')
                    const deliveryCharge = document.getElementsByClassName('deliveryCharge')
                    const grand_total = document.getElementsByClassName('grand_total')

                    const wallet = JSON.parse(localStorage.getItem('wallet')) || {
                        wallet_applied: 0,
                        remaining_wallet_balance: 0
                    };

                    const appliedCoupon = JSON.parse(localStorage.getItem('appliedCoupon')) || {
                        coupon_amount: 0
                    };

                    Array.from(subtotal).forEach(element => {
                        element.innerText = result.subTotal;
                    });
                    Array.from(taxTotal).forEach(element => {
                        element.innerText = result.taxTotal;
                    });
                    Array.from(deliveryCharge).forEach(element => {
                        element.innerText = result.deliveryCharge;
                    });

                    Array.from(grand_total).forEach(element => {
                        const grandTotal =
                            (parseFloat(result.subTotal) || 0) +
                            (parseFloat(result.taxTotal) || 0) +
                            (parseFloat(result.deliveryCharge) || 0) -
                            (parseFloat(appliedCoupon.coupon_amount) || 0) -
                            (parseFloat(wallet.wallet_applied) || 0);

                        // Round to 2 decimal places
                        element.innerText = grandTotal.toFixed(2);
                    });

                    fetchAddressList();
                    showToast(result.message, "success");

                } else {
                    showToast(result.message, "danger");
                }
            } catch (error) {
                document.getElementById('message').innerHTML =
                    `<p class="text-red-500 text-sm">Error: ${error.message}</p>`;
            }

        })

        const cityAreaSuggestionsContainer = document.getElementById('cityAreaSuggestions');

        function searchCityArea(query) {
            if (query.length < 3) {
                document.getElementById('cityAreaSuggestions').classList.add('hidden');
                return;
            }

            const options = {
                input: query,
            };
            let autocompleteService = new google.maps.places.AutocompleteService();

            autocompleteService.getPlacePredictions(options, (predictions, status) => {
                if (status === google.maps.places.PlacesServiceStatus.OK) {
                    displayCityAreaSuggestions(predictions);
                } else {
                    document.getElementById('cityAreaSuggestions').classList.add('hidden');
                }
            });
        }

        function displayCityAreaSuggestions(predictions) {
            cityAreaSuggestionsContainer.innerHTML = ''; // Clear previous suggestions
            cityAreaSuggestionsContainer.classList.remove('hidden');

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

                            const currLocation = {
                                lat: place.geometry.location.lat(),
                                lng: place.geometry.location.lng(),
                            };

                            // Initialize map
                            const map = new google.maps.Map(document.getElementById("map"), {
                                zoom: 15,
                                center: currLocation,
                            });

                            // Place a draggable marker at the current location
                            const marker = new google.maps.Marker({
                                position: currLocation,
                                map: map,
                                title: "Your location",
                                draggable: true,
                            });

                            // Fetch delivery area status
                            fetchIsInDeliveryArea(place.geometry.location.lat(), place.geometry.location.lng())

                            // Event listener to update latitude and longitude after moving the marker
                            marker.addListener('dragend', function(event) {
                                const newLat = event.latLng.lat();
                                const newLng = event.latLng.lng();

                                latitude = newLat
                                longitude = newLng

                                fetchIsInDeliveryArea(newLat, newLng)
                            });

                        } else {
                            console.error('Failed to fetch place details:', status);
                        }
                    });
                };

                cityAreaSuggestionsContainer.appendChild(suggestionItem);
            });
        }

        function fetchIsInDeliveryArea(lat, lng) {
            fetch('/fetchIsInDeliveryArea', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        lat: lat,
                        lng: lng,
                    }),
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return response.json();
                })
                .then(result => {
                    console.log(result);
                    const areaNotFoundElements = document.getElementsByClassName('areaNotFound');
                    const addressFormElements = document.getElementsByClassName('addressForm');

                    if (result.status === 'success') {
                        fetchAddress(lat, lng);
                        // Hide elements with the 'areaNotFound' class
                        for (let element of areaNotFoundElements) {
                            element.classList.add('hidden');
                        }
                        // Show elements with the 'addressForm' class by removing the 'hidden' class
                        for (let element of addressFormElements) {
                            element.classList.remove('hidden');
                        }

                        // Display success message or handle successful area validation
                        console.log("Delivery is available in this area.");
                        cityAreaSuggestionsContainer.innerHTML = ''; // Clear previous suggestions
                    } else {

                        // Hide elements with the 'areaNotFound' class
                        for (let element of addressFormElements) {
                            element.classList.add('hidden');
                        }
                        // Show elements with the 'addressForm' class by removing the 'hidden' class
                        for (let element of areaNotFoundElements) {
                            element.classList.remove('hidden');
                        }

                        // Display error message for non-deliverable area
                        console.log("We are not available at this location.");
                    }
                })
                .catch(error => {
                    console.error("Error checking delivery area:", error);
                });
        }
    </script>

</body>

</html>