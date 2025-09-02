<script>
    const addressModal = document.getElementById('addressModal');
    let latitude
    let longitude
    let map_address

    async function fetchAddressList() {
        try {
            const response = await fetch('/fetchAddressList', {
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
                addressDiv.innerHTML = '';

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
                    addressDiv.innerHTML = '<p>No addresses found.</p>';
                }
            } else {
                console.error('Failed to fetch addresses:', result.message);
            }
        } catch (error) {
            console.error('Error fetching address list:', error);
        }
    }

    // Helper function to generate address card HTML
    function generateAddressHTML(address, isActive) {
        const iconClass = address.address_type === 'Home' ?
            'fi fi-rr-home' :
            address.address_type === 'Work' ?
            'fi fi-rr-building' :
            'fi fi-rr-marker';

        const bgColor = isActive ? 'bg-[#FFF4F1] border-red-400' : 'bg-[#F7F7F7] border-[#F7F7F7]';

        const deleteBtnShow = isActive ? '' : '<div class="w-1/5 flex justify-end items-end"><i class="fi fi-rr-trash text-red-500 text-lg" onclick="event.stopPropagation(); deleteAddress(' + address.id + ');"></i></div>'

        return `<div class="w-full flex ${bgColor} py-2 px-2 rounded-lg cursor-pointer border mb-2 md:mb-0 address-card ${isActive ? 'active' : ''}" onclick="setActiveAddress(this, ${address.id})">
                <div class="w-4/5 pr-4" onclick="event.stopPropagation(); setActiveAddress(this.parentElement, ${address.id});">
                    <span class="text-base font-medium capitalize">
                        <i class="${iconClass}"></i>
                        ${address.address_type}:
                    </span>
                    <span class="text-base font-medium capitalize mb-1">${address.user_name}</span>
                    <span class="block text-sm leading-6">${address.user_mobile}</span>
                    <span class="block text-sm leading-6">${address.user_email || ''}</span>
                    <span class="block text-sm leading-6">${address.flat},</span>
                    <span class="block text-sm leading-6">${address.address},</span>
                    <span class="block text-sm leading-6">${address.area}, ${address.city},</span>
                    <span class="block text-sm leading-6">${address.state},</span>
                    <span class="block text-sm leading-6">${address.pincode}</span>
                </div>
                ${deleteBtnShow}
            </div>`;
    }

    function closeAddressPopup() {
        addressModal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    function openAddressPopup() {
        if (!addressModal) {
            console.error("Modal element not found.");
            return;
        }

        addressModal.classList.remove('hidden');
        document.body.classList.add('modal-open');

        // Retrieve location data from localStorage
        const locationData = JSON.parse(localStorage.getItem('location'));

        if (!locationData || !locationData.lat || !locationData.lng) {
            console.error("Location data is missing or incomplete in localStorage.");
            return;
        }

        latitude = locationData.lat
        longitude = locationData.lng

        const currLocation = {
            lat: locationData.lat,
            lng: locationData.lng,
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
        fetchIsInDeliveryArea(locationData.lat, locationData.lng)

        // Event listener to update latitude and longitude after moving the marker
        marker.addListener('dragend', function(event) {
            const newLat = event.latLng.lat();
            const newLng = event.latLng.lng();

            latitude = newLat
            longitude = newLng

            fetchIsInDeliveryArea(newLat, newLng)
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

    function fetchAddress(lat, lng) {
        const geocoder = new google.maps.Geocoder();
        const latlng = {
            lat,
            lng
        };

        document.getElementById('address').value

        geocoder.geocode({
            location: latlng
        }, (results, status) => {
            console.log(results)
            map_address = results[0].formatted_address
            if (status === "OK" && results[0]) {
                // Populate locationData with address components
                results[0].address_components.forEach(component => {
                    const types = component.types;
                    if (types.includes("sublocality") || types.includes("neighborhood")) {
                        document.getElementById('area').value = component.long_name;
                    } else if (types.includes("locality")) {
                        document.getElementById('city').value = component.long_name;
                    } else if (types.includes("administrative_area_level_1")) {
                        document.getElementById('state').value = component.long_name;
                    } else if (types.includes("country")) {
                        // locationData.country = component.long_name;
                    } else if (types.includes("postal_code")) {
                        document.getElementById('pincode').value = component.long_name;
                    }
                });
            }
        })
    }

    function selectAddressType(addressType) {
        // Update the hidden input value
        document.getElementById('address_type').value = addressType;

        // Get all the location div elements
        const locationDivs = document.querySelectorAll('.test.flex.space-x-4.<?= flex_direction() ?>>div');

        // Iterate over the divs and apply/remove classes based on selection
        locationDivs.forEach(div => {
            if (div.textContent.trim() === addressType) {
                // Add selected styles
                div.classList.add('border-green-700', 'bg-green-100', 'shadow-md');
                div.classList.remove('border-gray-300');
            } else {
                // Reset unselected styles
                div.classList.remove('border-green-700', 'bg-green-100', 'shadow-md');
                div.classList.add('border-gray-300');
            }
        });
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
        if (!address || !area || !city || !state || !flat || !user_mobile || !user_name) {
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

                calculateOrderSummery()

                fetchAddressList();

            } else {
                console.log(response.message)
            }
        } catch (error) {
            document.getElementById('message').innerHTML =
                `<p class="text-red-500 text-sm">Error: ${error.message}</p>`;
        }

    })

    async function calculateOrderSummery() {

        console.trace('calculateOrderSummery called');

        const wallet = JSON.parse(localStorage.getItem('wallet')) || {
            wallet_applied: 0,
            remaining_wallet_balance: 0
        };

        const appliedCoupon = JSON.parse(localStorage.getItem('appliedCoupon')) || {
            coupon_id: 0,
            coupon_code: '',
            coupon_amount: 0,
            coupon_minOrderAmount: 0,
            coupon_type: 0
        };

        try {
            const response = await fetch('/verifyOrderDetails', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    wallet,
                    appliedCoupon
                }),
            });

            const result = await response.json();
            console.log(result)

            // Handle success or error response
            if (result.status === 'success') {
                const subtotal = document.getElementsByClassName('subtotal')
                const taxTotal = document.getElementsByClassName('taxTotal')
                const deliveryCharge = document.getElementsByClassName('deliveryCharge')
                const grand_total = document.getElementsByClassName('grand_total')

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
                        (parseFloat(result.additional_charge) || 0) +
                        (parseFloat(result.deliveryCharge) || 0) -
                        (parseFloat(result.coupon_amount) || 0) -
                        (parseFloat(result.wallet_applied) || 0);

                    // Round to 2 decimal places
                    element.innerText = grandTotal.toFixed(2);
                });

                const couponAmount = appliedCoupon.coupon_amount || 0;
                const walletApplied = wallet.wallet_applied || 0;
                const remainingWalletBalance = wallet.remaining_wallet_balance || 0;


                const couponAmountElements = document.getElementsByClassName('couponAmount');
                const walletAppliedElements = document.getElementsByClassName('wallet_applied');
                const remainingWalletBalanceElements = document.getElementsByClassName('remaining_wallet_balance');

                if (couponAmount > 0) {
                    document.getElementById('applyCouponDiv').classList.add('hidden'); // Hide Apply Coupon
                    document.getElementById('couponAppliedDiv').classList.remove('hidden'); // Show Coupon Applied
                } else {
                    document.getElementById('applyCouponDiv').classList.remove('hidden'); // Show Apply Coupon
                    document.getElementById('couponAppliedDiv').classList.add('hidden'); // Hide Coupon Applied
                }

                Array.from(couponAmountElements).forEach(element => {
                    element.innerText = couponAmount; // Set the coupon amount to each element
                });


                if (wallet && wallet.wallet_applied) {
                    Array.from(walletAppliedElements).forEach(element => {
                        element.innerText = walletApplied; // Wallet applied with 2 decimals
                    });

                    Array.from(remainingWalletBalanceElements).forEach(element => {
                        element.innerHTML = remainingWalletBalance >= 0 ?
                            `(${remainingWalletBalance} remaining) <i class="fi fi-rr-trash text-red-500 text-sm" onclick="event.stopPropagation(); removeWallet()"></i>` :
                            ""; // Only show remaining if > 0
                    });
                }


            } else {

            }
        } catch (error) {
            console.log(error)
        }
    }

    async function setActiveAddress(card, address_id) {

        removeCoupon(1);
        removeWallet(1);

        localStorage.setItem('wallet', JSON.stringify({
            wallet_applied: 0,
            remaining_wallet_balance: 0
        }));

        localStorage.setItem('appliedCoupon', JSON.stringify({
            coupon_id: 0,
            coupon_code: '',
            coupon_amount: 0,
            coupon_minOrderAmount: 0,
            coupon_type: 0
        }));

        // Remove 'active' class from all address cards
        const allAddressCards = document.querySelectorAll('.address-card');
        allAddressCards.forEach((addressCard) => {
            addressCard.classList.remove('bg-[#FFF4F1]', 'border-red-400'); // Deactivate previous address
        });

        // Add 'active' class to the clicked card
        card.classList.add('bg-[#FFF4F1]', 'border-red-400'); // Activate clicked address
        try {
            const response = await fetch('/activeAddress', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    address_id
                }),
            });

            const result = await response.json();
            console.log(result)

            // Handle success or error response
            if (result.status === 'success') {
                calculateOrderSummery()

                showToast(result.message, "success");
            } else {
                showToast(result.message, "error");
            }
        } catch (error) {
            console.log(error)
        }

    }

    async function deleteAddress(address_id) {
        // Logic to handle the deletion of the address
        try {
            const response = await fetch('/deleteAddress', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    address_id
                }),
            });

            const result = await response.json();

            // Handle success or error response
            if (result.status === 'success') {
                console.log(result)

                fetchAddressList();
                showToast(result.message, "success");

            } else {
                console.log(result)
                showToast(result.message, "error");

            }
        } catch (error) {
            console.log(error)
        }
    }

    function selectDeliveryMethod(method) {
        // Save the selected delivery method in localStorage
        localStorage.setItem("deliveryMethod", method);

        // Select all delivery method cards
        const allCards = document.querySelectorAll('.deliveryMethod');

        // Loop through each card and remove the 'active' styles
        allCards.forEach(card => {
            card.classList.remove('bg-green-50', 'border-green-700', 'text-green-700'); // Remove active styles
            card.classList.add('bg-white', 'border-green-700', 'text-green-700'); // Add default styles
        });

        // Select the clicked card and apply the active styles
        const selectedCard = document.getElementById(method);
        selectedCard.classList.add('bg-green-50', 'border-green-700', 'text-green-700'); // Add active styles
        selectedCard.classList.remove('bg-white'); // Remove default styles

        if (method == 'scheduledDelivery') {
            // Show the delivery date and time divs
            document.getElementById('deliveryDateDiv').classList.remove('hidden');
            document.getElementById('deliveryDateDiv').classList.add('block');

            document.getElementById('deliveryTimeDiv').classList.remove('hidden');
            document.getElementById('deliveryTimeDiv').classList.add('block');
        } else {
            // Hide the delivery date and time divs
            document.getElementById('deliveryDateDiv').classList.remove('block');
            document.getElementById('deliveryDateDiv').classList.add('hidden');

            document.getElementById('deliveryTimeDiv').classList.remove('block');
            document.getElementById('deliveryTimeDiv').classList.add('hidden');
        }
    }

    function loadDeliveryMethod() {
        // Get the stored delivery method from localStorage
        const storedMethod = localStorage.getItem("deliveryMethod");

        // If a delivery method is stored, update the UI accordingly
        if (storedMethod) {
            // Select all delivery method cards
            const allCards = document.querySelectorAll('.deliveryMethod');

            // Loop through each card and reset their styles
            allCards.forEach(card => {
                card.classList.remove('bg-green-50', 'border-green-700', 'text-green-700'); // Remove active styles
                card.classList.add('bg-white', 'border-green-700', 'text-green-700'); // Reset to default styles
            });

            // Apply active styles to the stored method card
            const selectedCard = document.getElementById(storedMethod);
            selectedCard.classList.add('bg-green-50', 'border-green-700', 'text-green-700'); // Active styles for the selected card
            selectedCard.classList.remove('bg-white'); // Remove default styles

            if (storedMethod == 'scheduledDelivery') {
                // Show the delivery date and time divs
                document.getElementById('deliveryDateDiv').classList.remove('hidden');
                document.getElementById('deliveryDateDiv').classList.add('block');

                document.getElementById('deliveryTimeDiv').classList.remove('hidden');
                document.getElementById('deliveryTimeDiv').classList.add('block');
            } else {
                // Hide the delivery date and time divs
                document.getElementById('deliveryDateDiv').classList.remove('block');
                document.getElementById('deliveryDateDiv').classList.add('hidden');

                document.getElementById('deliveryTimeDiv').classList.remove('block');
                document.getElementById('deliveryTimeDiv').classList.add('hidden');
            }

        }
    }

    async function setActiveDate(button) {
        // Remove 'active' class from all buttons
        const allButtons = document.querySelectorAll('.swiper-slide button.date');
        allButtons.forEach(btn => btn.classList.remove('bg-green-50', 'border-green-500', 'text-green-700'));

        // Add 'active' class to the clicked button
        button.classList.add('bg-green-50', 'border-green-500', 'text-green-700');

        const selectedDate = button.getAttribute("data-date");
        const currentYear = new Date().getFullYear();
        const formattedDate = formatDate(selectedDate, currentYear); // Convert to yyyy-mm-dd
        localStorage.setItem("activeDate", formattedDate);

        // Clear the timeslot div and active time in localStorage
        document.getElementById('timeslotDiv').innerHTML = '';
        localStorage.removeItem('activeTime');

        // Fetch time slots for the selected date
        try {
            const response = await fetch('/getTimeSlot', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    date: formattedDate
                })
            });

            const result = await response.json();

            if (result.status === 'success') {
                appendTimeSlots(result.data);
            } else {
                console.error(result.message);
                appendTimeSlots([]); // Clear slots if no data is available
            }
        } catch (error) {
            console.error('Error fetching time slots:', error);
            appendTimeSlots([]); // Clear slots on error
        }
    }

    // Function to check if a date is active when the page loads
    function checkActiveDateOnLoad() {
        const activeDate = localStorage.getItem("activeDate");
        if (activeDate) {
            const allButtons = document.querySelectorAll('.swiper-slide button.date');
            allButtons.forEach(button => {
                const buttonDate = button.getAttribute("data-date");
                if (activeDate === formatDate(buttonDate, new Date().getFullYear())) {
                    // Apply active styles to the button that matches the active date
                    button.classList.add('bg-green-50', 'border-green-500', 'text-green-700');
                }
            });
        }
    }

    function formatDate(shortDate, year) {
        const months = {
            Jan: "01",
            Feb: "02",
            Mar: "03",
            Apr: "04",
            May: "05",
            Jun: "06",
            Jul: "07",
            Aug: "08",
            Sep: "09",
            Oct: "10",
            Nov: "11",
            Dec: "12"
        };

        const [month, day] = shortDate.split(" "); // e.g., "Nov 17"
        const monthNumber = months[month];
        const dayNumber = day.padStart(2, "0"); // Ensure 2-digit day
        return `${year}-${monthNumber}-${dayNumber}`;
    }

    function appendTimeSlots(timeslots) {
        const parentDiv = document.getElementById('timeslotDiv');

        // Clear existing time slots in the parent div
        parentDiv.innerHTML = '';

        if (timeslots.length === 0) {
            parentDiv.innerHTML = '<p class="text-gray-500">No available time slots for the selected date.</p>';
            return;
        }

        // Iterate over time slots and append them to the parent div
        timeslots.forEach(slot => {
            const mintime = formatTime(slot.mintime);
            const maxtime = formatTime(slot.maxtime);

            const slotElement = document.createElement('div');
            slotElement.className = 'swiper-slide';

            slotElement.innerHTML = `
                    <button
                        class="flex flex-col text-sm whitespace-nowrap border py-2 px-6 rounded-lg bg-gray-100 border-gray-500 text-gray-700 time"
                        onclick="setActiveTime(this)" data-time="${mintime} - ${maxtime}" >
                        ${mintime} to ${maxtime}
                    </button>
                `;

            parentDiv.appendChild(slotElement);
        });
    }

    // Utility function to format time from 24-hour to 12-hour format
    function formatTime(time) {
        const [hour, minute] = time.split('.');
        const hourInt = parseInt(hour);
        const minuteInt = parseInt((minute || '0').padEnd(2, '0'));

        const ampm = hourInt >= 12 ? 'PM' : 'AM';
        const formattedHour = hourInt % 12 || 12; // Convert to 12-hour format

        return `${formattedHour}.${minuteInt.toString().padStart(2, '0')} ${ampm}`;
    }

    function setActiveTime(button) {
        // Remove 'active' class from all time buttons
        const allButtons = document.querySelectorAll('.swiper-slide button.time');
        allButtons.forEach(btn => btn.classList.remove('bg-green-50', 'border-green-500', 'text-green-700')); // Remove previously active styling

        // Add 'active' class to the clicked button
        button.classList.add('bg-green-50', 'border-green-500', 'text-green-700'); // Add active styles

        const selectedTime = button.getAttribute("data-time");
        localStorage.setItem("activeTime", selectedTime);
    }

    const couponModal = document.getElementById('couponModal');

    async function openCouponPopup() {
        const couponModal = document.getElementById('couponModal');
        const couponListDiv = document.getElementById('couponListDiv');
        const noCouponAvialbleDiv = document.getElementById('noCouponAvialbleDiv');

        if (!couponModal || !couponListDiv) {
            console.error("Modal element or coupon list container not found.");
            return;
        }

        // Show the modal
        couponModal.classList.remove('hidden');
        document.body.classList.add('modal-open');

        try {
            const response = await fetch('/getCouponList', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();

            if (result.status === 'success') {
                // Clear existing children
                couponListDiv.innerHTML = '';

                console.log('couponlist', result.data.length)

                if (result.data.length == 0) {
                    noCouponAvialbleDiv.classList.remove('hidden')
                } else {
                    // Loop through the fetched coupons and append to the container
                    result.data.forEach(coupon => {
                        const couponCard = document.createElement('div');
                        couponCard.innerHTML = `
                            <div class="flex flex-col bg-white rounded-lg p-4 w-full relative" data-coupon-id="${coupon.coupon_id}">
                                <div class="flex items-center">
                                    <img src="${coupon.coupon_img}" alt="Image" class="w-8 h-8 rounded-lg object-cover mr-4" />
                                    <h3 class="py-1 px-2 rounded font-medium text-xs bg-[#FFDB1F] text-black">Code: ${coupon.code}</h3>
                                </div>
                                <div class="flex flex-col mt-4 flex-grow">
                                    <h1 class="text-lg font-semibold mb-2">${coupon.title}</h1>
                                    <p class="text-sm text-gray-500 mb-4">Valid till: ${coupon.validTill}</p>
                                    <button id="coupon_btn_id_${coupon.coupon_id}" onclick="applyCoupon(${coupon.coupon_id}, '${coupon.code}', ${coupon.value}, ${coupon.min_order_amount}, ${coupon.coupon_type}, ${coupon.coupon_value})"
                                            class="mt-auto self-end bg-green-700 text-white py-2 px-4 rounded-lg text-sm hover:bg-green-900 transition-all">
                                        Apply
                                    </button>
                                </div>
                            </div>
                        `;

                        // Check if this coupon is already in localStorage and mark as applied
                        const appliedCoupon = JSON.parse(localStorage.getItem('appliedCoupon'));
                        if (appliedCoupon && +appliedCoupon.coupon_id === +coupon.id) {
                            const couponCardElement = couponCard.querySelector(`[data-coupon-id="${coupon.id}"]`);
                            if (couponCardElement) {
                                couponCardElement.classList.add('bg-green-50', 'border', 'border-green-500'); // Applied styles
                                const couponBtn = couponCard.querySelector(`#coupon_btn_id_${coupon.id}`);
                                couponBtn.innerText = 'Applied'; // Update button text to "Applied"
                                couponBtn.disabled = true; // Optionally, disable the button once applied

                                document.getElementById('applyCouponDiv').classList.add('hidden'); // Hide Apply Coupon
                                document.getElementById('couponAppliedDiv').classList.remove('hidden');

                                const couponAmountElements = document.getElementsByClassName('couponAmount');

                                Array.from(couponAmountElements).forEach(element => {
                                    element.innerText = coupon.amount; // Set the amount to each element
                                });
                            }
                        }

                        couponListDiv.appendChild(couponCard);
                    });
                }


            } else {
                noCouponAvialbleDiv.classList.remove('hidden')
                console.error(result.message);
            }
        } catch (error) {
            console.error('Error fetching coupons:', error);
        }
    }

    function closeCouponPopup() {
        couponModal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    async function applyCoupon(coupon_id, coupon_code, coupon_amount, coupon_minOrderAmount, coupon_type, coupon_value) {
        const wallet = JSON.parse(localStorage.getItem('wallet')) || {
            wallet_applied: 0,
            remaining_wallet_balance: 0
        };

        try {
            const response = await fetch('/applyCoupon', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    coupon_id,
                    coupon_code,
                    coupon_amount,
                    coupon_minOrderAmount,
                    wallet,
                    coupon_type
                }),
            });

            const result = await response.json();

            console.log(result)

            if (result.status === 'success') {
                // Store coupon details in localStorage as JSON
                const couponData = {
                    coupon_id,
                    coupon_code,
                    coupon_amount,
                    coupon_minOrderAmount,
                    coupon_type
                };
                localStorage.setItem('appliedCoupon', JSON.stringify(couponData));

                // Find the coupon card by its ID or unique identifier
                const couponCard = document.querySelector(`[data-coupon-id="${coupon_id}"]`);

                if (couponCard) {
                    // Change the background color of the entire coupon card to show it's applied
                    couponCard.classList.add('bg-green-50', 'border', 'border-green-500'); // Applied styles   

                    // Change the button text to "Applied"
                    const couponBtn = couponCard.querySelector(`#coupon_btn_id_${coupon_id}`);
                    couponBtn.innerText = 'Applied';
                    couponBtn.disabled = true; // Disable the button once applied

                    document.getElementById('applyCouponDiv').classList.add('hidden'); // Hide Apply Coupon
                    document.getElementById('couponAppliedDiv').classList.remove('hidden');
                    const couponAmountElements = document.getElementsByClassName('couponAmount');

                    Array.from(couponAmountElements).forEach(element => {
                        element.innerText = coupon_amount; // Set the amount to each element
                    });

                    if (coupon_type == 1) {
                        const discountInPercIfApplicableElements = document.getElementsByClassName('discountInPercIfApplicable');

                        Array.from(discountInPercIfApplicableElements).forEach(element => {
                            element.innerText = '('+coupon_value+'%)'; // Set the amount to each element
                        });
                    }



                    calculateOrderSummery()

                }
                showToast(result.message, "success");
                closeCouponPopup()

            } else {
                showToast(result.message, "error");
                console.error(result.message);
                closeCouponPopup()

            }
        } catch (error) {
            console.error('Error fetching coupons:', error);
        }


    }

    async function removeCoupon(isAddressChange = 0) {

        try {
            const response = await fetch('/removeCoupon', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
            });

            const result = await response.json();

            console.log(result)

            if (result.status === 'success') {
                // Remove the coupon state and reset
                document.getElementById('applyCouponDiv').classList.remove('hidden'); // Hide Apply Coupon
                document.getElementById('couponAppliedDiv').classList.add('hidden');

                // Optionally, clear any stored coupon data
                localStorage.removeItem("appliedCoupon");
                const couponAmount = document.getElementsByClassName('couponAmount')

                Array.from(couponAmount).forEach(element => {
                    element.innerText = 0;
                });

                        const discountInPercIfApplicableElements = document.getElementsByClassName('discountInPercIfApplicable');
Array.from(discountInPercIfApplicableElements).forEach(element => {
                    element.innerText = '';
                });

                calculateOrderSummery()

                if (isAddressChange == 0) {
                    showToast(result.message, "success");
                }

            } else {
                showToast(result.message, "error");

            }
        } catch {

        }

    }

    window.addEventListener('DOMContentLoaded', () => {
        checkActiveDateOnLoad();
        fetchAddressList();
        loadDeliveryMethod();
        calculateOrderSummery();
    });

    async function applyWallet() {
        const appliedCoupon = JSON.parse(localStorage.getItem('appliedCoupon'));
        let coupon_id = 0;
        if (appliedCoupon && appliedCoupon.coupon_amount) {
            coupon_id = appliedCoupon.coupon_id
        }
        try {
            const response = await fetch('/applyWallet', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    appliedCoupon
                }),
            });

            const result = await response.json();

            console.log(result)

            if (result.status === 'success') {
                const wallet_applied = document.getElementsByClassName('wallet_applied')
                const remaining_wallet_balance = document.getElementsByClassName('remaining_wallet_balance')

                Array.from(wallet_applied).forEach(element => {
                    element.innerText = result.data.wallet_applied; // Set the amount to each element
                });

                Array.from(remaining_wallet_balance).forEach(element => {
                    element.innerHTML = `(${result.data.remaining_wallet_balance} remaining) <i class="fi fi-rr-trash text-red-500 text-sm" onclick="event.stopPropagation(); removeWallet()"></i>`; // Set the amount to each element
                });

                localStorage.setItem('wallet', JSON.stringify({
                    wallet_applied: result.data.wallet_applied,
                    remaining_wallet_balance: result.data.remaining_wallet_balance
                }));

                calculateOrderSummery()

                showToast(result.message, "success");

            } else {
                console.error(result.message);
                showToast(result.message, "error");

            }
        } catch (error) {
            console.error('Error fetching wallet:', error);
        }

    }

    async function removeWallet(isAddressChange = 0) {
        const appliedCoupon = JSON.parse(localStorage.getItem('appliedCoupon'));
        let coupon_id = 0;
        if (appliedCoupon && appliedCoupon.coupon_amount) {
            coupon_id = appliedCoupon.coupon_id
        }

        try {
            const response = await fetch('/removeWallet', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    coupon_id
                }),
            });

            const result = await response.json();

            console.log(result)

            if (result.status === 'success') {

                localStorage.removeItem('wallet')

                const remaining_wallet_balance = document.getElementsByClassName('remaining_wallet_balance')
                const wallet_applied = document.getElementsByClassName('wallet_applied')

                Array.from(remaining_wallet_balance).forEach(element => {
                    element.innerHTML = `(${result.data.remaining_wallet_balance} apply)`; // Set the amount to each element
                });
                Array.from(wallet_applied).forEach(element => {
                    element.innerHTML = 0; // Set the amount to each element
                });

                calculateOrderSummery()

                if (isAddressChange == 0) {
                    showToast(result.message, "success");
                }

            } else {
                console.error(result.message);
                showToast(result.message, "error");

            }
        } catch (error) {
            console.error('Error fetching coupons:', error);
        }

    }

    localStorage.setItem('paymentMethode', 0)

    function setPaymentMethode(id) {
        localStorage.setItem('paymentMethode', id);
        const paymentElements = document.querySelectorAll('[id*="paymentMethod_"]');

        paymentElements.forEach(element => {
            element.classList.remove('border-red-400', 'bg-[#FFF4F1]');
        });

        const selectedElement = document.getElementById('paymentMethod_' + id);
        if (selectedElement) {
            selectedElement.classList.add('border-red-400', 'bg-[#FFF4F1]');
        }

        if (id == 3) {
            document.getElementById('paypal-button-container').classList.remove('hidden')
            document.getElementById('verifyOrderDetails').classList.add('hidden')

        } else {
            document.getElementById('paypal-button-container').classList.add('hidden')
            document.getElementById('verifyOrderDetails').classList.remove('hidden')
        }

    }

    async function verifyOrderDetails() {
        let paymentMethode = localStorage.getItem('paymentMethode');
        let deliveryMethod = localStorage.getItem('deliveryMethod');

        const appliedCoupon = JSON.parse(localStorage.getItem('appliedCoupon'))
        const wallet = JSON.parse(localStorage.getItem('wallet'))
        const activeDate = localStorage.getItem('activeDate')
        const activeTime = localStorage.getItem('activeTime')

        if (activeDate == null && deliveryMethod == 'scheduledDelivery') {
            showToast('Select Date', "error");
            return false
        }
        if (activeTime == null && deliveryMethod == 'scheduledDelivery') {
            showToast('Select Time', "error");
            return false
        }

        try {
            const response = await fetch('/verifyOrderDetails', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    appliedCoupon,
                    wallet,
                    activeDate,
                    activeTime,
                    paymentMethode,
                    deliveryMethod
                }),
            });

            const result = await response.json();

            if (result.status === 'success') {
                const subtotal = document.getElementsByClassName('subtotal')
                const taxTotal = document.getElementsByClassName('taxTotal')
                const deliveryCharge = document.getElementsByClassName('deliveryCharge')
                const grand_total = document.getElementsByClassName('grand_total')
                const couponAmount = document.getElementsByClassName('couponAmount')

                const remaining_wallet_balance = document.getElementsByClassName('remaining_wallet_balance')
                const wallet_applied = document.getElementsByClassName('wallet_applied')

                if (wallet !== null) {
                    Array.from(remaining_wallet_balance).forEach(element => {
                        element.innerHTML = `(${result.remaining_wallet_balance} remaining) <i class="fi fi-rr-trash text-red-500 text-sm" onclick="event.stopPropagation(); removeWallet()"></i>`; // Set the amount to each element
                    });
                    Array.from(wallet_applied).forEach(element => {
                        element.innerHTML = result.wallet_applied; // Set the amount to each element
                    });
                    localStorage.setItem('wallet', JSON.stringify({
                        wallet_applied: result.wallet_applied,
                        remaining_wallet_balance: result.remaining_wallet_balance
                    }));
                    const grandTotal =
                        (parseFloat(result.subTotal) || 0) +
                        (parseFloat(result.taxTotal) || 0) +
                        (parseFloat(result.additional_charge) || 0) +
                        (parseFloat(result.deliveryCharge) || 0) -
                        (parseFloat(result.coupon_amount) || 0) -
                        (parseFloat(result.wallet_applied) || 0);

                    Array.from(grand_total).forEach(element => {
                        element.innerText = grandTotal.toFixed(2);
                    });

                    if (grandTotal == 0) {
                        try {
                            const response = await fetch('/placeCODOrder', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    appliedCoupon,
                                    wallet,
                                    activeDate,
                                    activeTime,
                                    deliveryMethod
                                }),
                            });
                            const result = await response.json();
                            if (result.status === 'success') {
                                localStorage.removeItem('wallet')
                                localStorage.removeItem('activeDate')
                                localStorage.removeItem('activeTime')
                                localStorage.removeItem('appliedCoupon')

                                const popupContent = `
                                        <div class="bg-white w-11/12 sm:w-96 rounded-lg p-6 shadow-lg">
                                            <div class="flex items-center justify-center text-green-500">
                                                <img src="${result.base_url}/assets/dist/img/success-animation.gif" alt="Success Animation"/>
                                            </div>
                                            <div class="text-center mt-4">
                                                <h2 class="text-lg font-bold text-gray-800"><?php echo lang('website.order_placed_successfully'); ?>!</h2>
                                                <p class="text-sm text-gray-600 mt-2">
                                                <?php echo lang('website.thank_you_for_your_order_we_will_notify_you_when_its_ready_for_delivery'); ?> 
                                                </p>
                                            </div>
                                            <div class="flex items-center justify-center mt-6">
                                                <a href="${result.base_url}/order-details/${result.order_id}" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                                                    <?php echo lang('website.see_your_order_details'); ?> 
                                                </a>
                                            </div>
                                        </div>
                                    `;
                                const popupContainer = document.getElementById('orderResponsePopup');
                                popupContainer.innerHTML = popupContent;
                                popupContainer.classList.remove('hidden');

                                showToast(result.message, "success");
                                return true;

                            } else {
                                showToast(result.message, "error");
                                return true;

                            }
                        } catch (e) {
                            console.log(e)
                        }
                    }
                } else {

                    Array.from(grand_total).forEach(element => {
                        const grandTotal =
                            (parseFloat(result.subTotal) || 0) +
                            (parseFloat(result.taxTotal) || 0) +
                            (parseFloat(result.additional_charge) || 0) +
                            (parseFloat(result.deliveryCharge) || 0) -
                            (parseFloat(result.coupon_amount) || 0);

                        // Round to 2 decimal places
                        element.innerText = grandTotal.toFixed(2);
                    });

                    localStorage.removeItem('wallet');
                }

                Array.from(subtotal).forEach(element => {
                    element.innerText = result.subTotal;
                });
                Array.from(taxTotal).forEach(element => {
                    element.innerText = result.taxTotal;
                });
                Array.from(deliveryCharge).forEach(element => {
                    element.innerText = result.deliveryCharge;
                });

                if (paymentMethode == 0) {
                    showToast('Select Payment Methode', "error");
                    return false
                }
                if (paymentMethode == 1) {

                    try {
                        const response = await fetch('/placeCODOrder', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                appliedCoupon,
                                wallet,
                                activeDate,
                                activeTime,
                                paymentMethode,
                                deliveryMethod
                            }),
                        });
                        const result = await response.json();
                        if (result.status === 'success') {
                            localStorage.removeItem('wallet')
                            localStorage.removeItem('activeDate')
                            localStorage.removeItem('activeTime')
                            localStorage.removeItem('appliedCoupon')
                            localStorage.setItem('paymentMethode', 0)

                            const popupContent = `
                                <div class="bg-white w-11/12 sm:w-96 rounded-lg p-6 shadow-lg">
                                    <div class="flex items-center justify-center text-green-500">
                                        <img src="${result.base_url}/assets/dist/img/success-animation.gif" alt="Success Animation"/>
                                    </div>
                                    <div class="text-center mt-4">
                                        <h2 class="text-lg font-bold text-gray-800"><?php echo lang('website.order_placed_successfully'); ?>!</h2>
                                        <p class="text-sm text-gray-600 mt-2">
                                            <?php echo lang('website.thank_you_for_your_order_we_will_notify_you_when_its_ready_for_delivery'); ?>
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-center mt-6">
                                        <a href="${result.base_url}/order-details/${result.order_id}" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                                            <?php echo lang('website.see_your_order_details'); ?> 
                                        </a>
                                    </div>
                                </div>
                            `;
                            const popupContainer = document.getElementById('orderResponsePopup');
                            popupContainer.innerHTML = popupContent;
                            popupContainer.classList.remove('hidden');

                            showToast(result.message, "success");

                        } else {
                            showToast(result.message, "error");

                        }
                    } catch (e) {
                        console.log(e)
                    }

                } else if (paymentMethode == 2) {
                    <?php foreach ($paymentMethods as $paymentMethod):
                        if ($paymentMethod['id'] == 2):
                    ?>
                            try {
                                const response = await fetch('/createRazorpayOrder', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        appliedCoupon,
                                        wallet,
                                        activeDate,
                                        activeTime,
                                        paymentMethode,
                                        deliveryMethod
                                    }),
                                });
                                const result = await response.json();
                                console.log(result)

                                if (result.status === 'success') {

                                    let razorpay_order_id = result.razorpay_order_id;
                                    let amount = result.amount;
                                    let order_id = result.order_id;

                                    var options = {
                                        "key": "<?= $paymentMethod['api_key'] ?>",
                                        "amount": amount,
                                        "currency": '<?= $country['currency_shortcut'] ?>',
                                        "name": "<?= $settings['business_name'] ?>",
                                        "description": "Purchased from <?= $settings['business_name'] ?>",
                                        "image": "<?= base_url($settings['logo']) ?>",
                                        "prefill": {
                                            "name": '<?= $user_name ?>',
                                            "email": '<?= $user_email ?>',
                                            "contact": '<?= $user_mobile ?>'
                                        },
                                        "theme": {
                                            "color": "#2B4CAB"
                                        },
                                        "order_id": razorpay_order_id,
                                        "handler": async function(res) {
                                            const response = await fetch('/verifyRazorpayPayment', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json'
                                                },
                                                body: JSON.stringify({
                                                    razorpay_payment_id: res.razorpay_payment_id,
                                                    razorpay_order_id: res.razorpay_order_id,
                                                    razorpay_signature: res.razorpay_signature,
                                                    order_id: order_id
                                                }),
                                            });
                                            const result = await response.json();

                                            if (result.status === 'success') {
                                                localStorage.removeItem('wallet')
                                                localStorage.removeItem('activeDate')
                                                localStorage.removeItem('activeTime')
                                                localStorage.removeItem('appliedCoupon')
                                                localStorage.setItem('paymentMethode', 0)

                                                const popupContent = `
                                                        <div class="bg-white w-11/12 sm:w-96 rounded-lg p-6 shadow-lg">
                                                            <div class="flex items-center justify-center text-green-500">
                                                                <img src="${result.base_url}/assets/dist/img/success-animation.gif" alt="Success Animation"/>
                                                            </div>
                                                            <div class="text-center mt-4">
                                                                <h2 class="text-lg font-bold text-gray-800"><?php echo lang('website.order_placed_successfully'); ?>!</h2>
                                                                <p class="text-sm text-gray-600 mt-2">
                                                                    <?php echo lang('website.thank_you_for_your_order_we_will_notify_you_when_its_ready_for_delivery'); ?>
                                                                </p>
                                                            </div>
                                                            <div class="flex items-center justify-center mt-6">
                                                                <a href="${result.base_url}/order-details/${order_id}" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                                                                    <?php echo lang('website.see_your_order_details'); ?> 
                                                                </a>
                                                            </div>
                                                        </div>
                                                    `;

                                                const popupContainer = document.getElementById('orderResponsePopup');
                                                popupContainer.innerHTML = popupContent;
                                                popupContainer.classList.remove('hidden');
                                                showToast(result.message, "success");
                                            } else {
                                                showToast(result.message, "error");
                                            }
                                        }
                                    }

                                    var rzp1 = new Razorpay(options);
                                    rzp1.open();

                                } else {
                                    showToast(result.message, "error");
                                }
                            } catch (e) {
                                console.log(e)
                            }
                    <?php endif;
                    endforeach; ?>

                } else if (paymentMethode == 4) {
                    <?php foreach ($paymentMethods as $paymentMethod):
                        if ($paymentMethod['id'] == 4):
                    ?>
                            // paystack integration
                            try {
                                const response = await fetch('/createPaystackOrder', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        appliedCoupon,
                                        wallet,
                                        activeDate,
                                        activeTime,
                                        paymentMethode,
                                        deliveryMethod
                                    }),
                                });
                                const result = await response.json();
                                console.log(result)

                                if (result.status === 'success') {
                                    let amount = result.amount;
                                    let order_id = result.order_id;
                                    const paystack = new PaystackPop();
                                    paystack.newTransaction({
                                        key: '<?= $paymentMethod['api_key'] ?>',
                                        email: '<?= $user_email ?>',
                                        amount: amount * 100,
                                        currency: '<?= $country['currency_shortcut'] ?>',
                                        onSuccess: function(transaction) {
                                            // Send the reference to your server for verification
                                            fetch('<?= base_url("verifyPaystackOrder") ?>', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                    },
                                                    body: JSON.stringify({
                                                        reference: transaction.reference,
                                                        transaction: transaction.transaction,
                                                        amount,
                                                        order_id
                                                    })
                                                })
                                                .then(response => response.json())
                                                .then(data => {
                                                    if (data.status === 'success') {
                                                        localStorage.removeItem('wallet')
                                                        localStorage.removeItem('activeDate')
                                                        localStorage.removeItem('activeTime')
                                                        localStorage.removeItem('appliedCoupon')
                                                        localStorage.setItem('paymentMethode', 0)

                                                        const popupContent = `
                                                        <div class="bg-white w-11/12 sm:w-96 rounded-lg p-6 shadow-lg">
                                                            <div class="flex items-center justify-center text-green-500">
                                                                <img src="${data.base_url}/assets/dist/img/success-animation.gif" alt="Success Animation"/>
                                                            </div>
                                                            <div class="text-center mt-4">
                                                                <h2 class="text-lg font-bold text-gray-800"><?php echo lang('website.order_placed_successfully'); ?>!</h2>
                                                                <p class="text-sm text-gray-600 mt-2">
                                                                    <?php echo lang('website.thank_you_for_your_order_we_will_notify_you_when_its_ready_for_delivery'); ?>
                                                                </p>
                                                            </div>
                                                            <div class="flex items-center justify-center mt-6">
                                                                <a href="${data.base_url}/order-details/${order_id}" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                                                                    <?php echo lang('website.see_your_order_details'); ?> 
                                                                </a>
                                                            </div>
                                                        </div>
                                                    `;

                                                        const popupContainer = document.getElementById('orderResponsePopup');
                                                        popupContainer.innerHTML = popupContent;
                                                        popupContainer.classList.remove('hidden');
                                                        showToast(data.message, "success");
                                                    } else {
                                                        showToast(data.message, "error");
                                                    }
                                                })
                                                .catch(error => console.error('Error verifying payment:', error));
                                        },
                                        onCancel: function() {
                                            // Payment cancelled
                                            showToast('Transaction cancelled.', "error")
                                        }
                                    });
                                } else {
                                    showToast(result.message, "error");
                                }
                            } catch (e) {
                                console.log(e)
                            }
                    <?php endif;
                    endforeach; ?>

                } else if (paymentMethode == 5) {
                    <?php foreach ($paymentMethods as $paymentMethod):
                        if ($paymentMethod['id'] == 5):
                    ?>
                            //cashfree integration
                            try {
                                const response = await fetch('/createCashFreeOrder', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        appliedCoupon,
                                        wallet,
                                        activeDate,
                                        activeTime,
                                        paymentMethode,
                                        deliveryMethod
                                    }),
                                });

                                const result = await response.json();
                                console.log(result);

                                if (result.status === 'success') {
                                    const cashfree_order_id = result.cashfree_order_id;
                                    const order_id = result.order_id;
                                    const payment_session_id = result.payment_session_id;

                                    const cashfree = new Cashfree({
                                        mode: "sandbox", // Use "sandbox" for testing or "production" for live
                                    });

                                    const checkoutOptions = {
                                        paymentSessionId: payment_session_id,
                                        redirectTarget: "_modal", // Use "_modal" for a popup modal
                                    };

                                    try {
                                        const cashFreeResult = await cashfree.checkout(checkoutOptions);

                                        if (cashFreeResult.error) {
                                            console.error("Payment error or user closed the modal:", cashFreeResult.error);
                                            return; // Stop execution if there's an error
                                        }

                                        if (cashFreeResult.paymentDetails) {
                                            // Payment completed, irrespective of success or failure
                                            console.log("Payment completed, verifying payment...");
                                            console.log(cashFreeResult.paymentDetails.paymentMessage);

                                            // Call confirmCashFreeOrder to verify the payment
                                            const confirmResponse = await fetch('/confirmCashFreeOrder', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json'
                                                },
                                                body: JSON.stringify({
                                                    order_id,
                                                    payment_session_id,
                                                    cashfree_order_id
                                                }),
                                            });

                                            const confirmResult = await confirmResponse.json();

                                            if (confirmResult.status === 'success') {
                                                // Clear local storage and reset values
                                                localStorage.removeItem('wallet');
                                                localStorage.removeItem('activeDate');
                                                localStorage.removeItem('activeTime');
                                                localStorage.removeItem('appliedCoupon');
                                                localStorage.setItem('paymentMethode', 0);

                                                // Generate success popup
                                                const popupContent = `
                                                        <div class="bg-white w-11/12 sm:w-96 rounded-lg p-6 shadow-lg">
                                                            <div class="flex items-center justify-center text-green-500">
                                                                <img src="${confirmResult.base_url}/assets/dist/img/success-animation.gif" alt="Success Animation"/>
                                                            </div>
                                                            <div class="text-center mt-4">
                                                                <h2 class="text-lg font-bold text-gray-800"><?php echo lang('website.order_placed_successfully'); ?>!</h2>
                                                                <p class="text-sm text-gray-600 mt-2">
                                                                    <?php echo lang('website.thank_you_for_your_order_we_will_notify_you_when_its_ready_for_delivery'); ?>
                                                                </p>
                                                            </div>
                                                            <div class="flex items-center justify-center mt-6">
                                                                <a href="${confirmResult.base_url}/order-details/${order_id}" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                                                                    <?php echo lang('website.see_your_order_details'); ?> 
                                                                </a>
                                                            </div>
                                                        </div>
                                                    `;

                                                const popupContainer = document.getElementById('orderResponsePopup');
                                                popupContainer.innerHTML = popupContent;
                                                popupContainer.classList.remove('hidden');

                                                showToast(confirmResult.message, "success")
                                            } else {
                                                showToast(confirmResult.message, "error")
                                            }
                                        }
                                    } catch (error) {
                                        console.error("Error during Cashfree checkout:", error);
                                    }
                                } else {
                                    showToast(result.message, "error")
                                }
                            } catch (error) {
                                console.error("Error during payment process:", error);
                            }

                    <?php endif;
                    endforeach; ?>
                }

            } else {
                showToast(result.message, "error");
            }
        } catch (e) {
            console.log(e)
        }

    }

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
</script>
<?php foreach ($paymentMethods as $paymentMethod):
    if ($paymentMethod['id'] == 3):
?>
        <script src="https://www.paypal.com/sdk/js?client-id=<?= $paymentMethod['api_key'] ?>&currency=USD"></script>
        <script>
            paypal.Buttons({

                createOrder: function(data, actions) {

                    const appliedCoupon = JSON.parse(localStorage.getItem('appliedCoupon'))
                    const wallet = JSON.parse(localStorage.getItem('wallet'))
                    const activeDate = localStorage.getItem('activeDate')
                    const activeTime = localStorage.getItem('activeTime')
                    const deliveryMethod = localStorage.getItem('deliveryMethod')
                    let paymentMethode = localStorage.getItem('paymentMethode');

                    if (activeDate == null && deliveryMethod == 'scheduledDelivery') {
                        showToast('Select Date', "error");
                        return false
                    }
                    if (activeTime == null && deliveryMethod == 'scheduledDelivery') {
                        showToast('Select Time', "error");
                        return false
                    }
                    return fetch('/createPaypalOrder', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                appliedCoupon,
                                wallet,
                                activeDate,
                                activeTime,
                                deliveryMethod,
                                paymentMethode
                            }),
                        })
                        .then((res) => {
                            console.log(res)
                            if (!res.ok) {
                                throw new Error('Failed to create order');
                            }
                            return res.json();
                        })
                        .then((orderData) => {
                            if (!orderData.order_id) {
                                throw new Error('Order ID is missing in the response');
                            }
                            console.log('Order Data:', orderData);
                            return orderData.order_id;
                        })
                        .catch((err) => {
                            showToast('Error in createOrder', "error");

                            console.error('Error in createOrder:', err);
                            throw err;
                        });
                },
                onApprove: function(data, actions) {
                    // Capture the payment on your backend
                    return fetch('/capturePaypalOrder', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                orderID: data.orderID,
                            }),
                        })
                        .then((res) => res.json())
                        .then((captureData) => {

                            localStorage.removeItem('wallet')
                            localStorage.removeItem('activeDate')
                            localStorage.removeItem('activeTime')
                            localStorage.removeItem('appliedCoupon')
                            localStorage.setItem('paymentMethode', 0)

                            const popupContent = `
                                                        <div class="bg-white w-11/12 sm:w-96 rounded-lg p-6 shadow-lg">
                                                            <div class="flex items-center justify-center text-green-500">
                                                                <img src="${captureData.base_url}/assets/dist/img/success-animation.gif" alt="Success Animation"/>
                                                            </div>
                                                            <div class="text-center mt-4">
                                                                <h2 class="text-lg font-bold text-gray-800"><?php echo lang('website.order_placed_successfully'); ?>!</h2>
                                                                <p class="text-sm text-gray-600 mt-2">
                                                                <?php echo lang('website.thank_you_for_your_order_we_will_notify_you_when_its_ready_for_delivery'); ?>
                                                                </p>
                                                            </div>
                                                            <div class="flex items-center justify-center mt-6">
                                                                <a href="${captureData.base_url}/order-details/${captureData.order_id}" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                                                                <?php echo lang('website.see_your_order_details'); ?> 
                                                                </a>
                                                            </div>
                                                        </div>
                                                    `;

                            const popupContainer = document.getElementById('orderResponsePopup');
                            popupContainer.innerHTML = popupContent;
                            popupContainer.classList.remove('hidden');
                            showToast(captureData.message, "success");

                        });
                },
                onCancel: function(data) {
                    showToast('Payment cancelled!', "error");
                },
                onError: function(err) {
                    console.error('Error:', err);
                },
                style: {
                    layout: 'horizontal',
                    color: 'gold',
                    shape: 'rect',
                    label: 'pay',
                    tagline: false
                },
                message: {
                    amount: 100,
                    align: 'center',
                    color: 'black',
                    position: 'top',
                }
            }).render('#paypal-button-container'); // Render the PayPal button
        </script>
<?php endif;
endforeach; ?>