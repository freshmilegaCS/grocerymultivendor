<script>
    const productFilter = {
        category: [],
        brand: [],
        seller: [],
        minPrice: 0,
        maxPrice: 0,
        fromPrice: 0,
        toPrice: 0,
        sort: 6
    };
    localStorage.setItem('productFilter', JSON.stringify(productFilter));

    // Function to set the active view
    function setActiveView(view) {
        // Hide all views
        document.getElementById('productListView').classList.add('hidden');
        document.getElementById('productAppView').classList.add('hidden');
        document.getElementById('productGridView').classList.add('hidden');

        // Reset button classes to inactive
        document.getElementById('listViewButton').classList.replace('text-green-600', 'text-gray-600');
        document.getElementById('appViewButton').classList.replace('text-green-600', 'text-gray-600');
        document.getElementById('gridViewButton').classList.replace('text-green-600', 'text-gray-600');

        // Show the selected view and set button as active
        if (view === 'list') {
            document.getElementById('productListView').classList.remove('hidden');
            document.getElementById('listViewButton').classList.replace('text-gray-600', 'text-green-600');
        } else if (view === 'app') {
            document.getElementById('productAppView').classList.remove('hidden');
            document.getElementById('appViewButton').classList.replace('text-gray-600', 'text-green-600');
        } else if (view === 'grid') {
            document.getElementById('productGridView').classList.remove('hidden');
            document.getElementById('gridViewButton').classList.replace('text-gray-600', 'text-green-600');
        }

        // Save the selected view to localStorage
        localStorage.setItem('productView', view);
    }

    const productView = localStorage.getItem('productView') || 'grid'; // Default to 'list' view
    setActiveView(productView);
    // Functions for each view
    function setProductListView() {
        setActiveView('list');
    }

    function setProductAppView() {
        setActiveView('app');
    }

    function setProductGridView() {
        setActiveView('grid');
    }

    function applyFilter(u) {
        let updatedFilter = JSON.parse(localStorage.getItem('productFilter'));

        // Function to update the array by adding or removing slugs
        const updateArray = (array, selectedSlugs) => {
            // Remove unchecked slugs
            const filteredArray = array.filter(slug => selectedSlugs.includes(slug)); // Remove unchecked slugs
            // Add new slugs
            const newArray = filteredArray.concat(selectedSlugs.filter(slug => !filteredArray.includes(slug))); // Add newly checked slugs
            return newArray;
        };

        if (u === 'sort') {
            const productSort = document.getElementById('productSort').value;
            updatedFilter.sort = +productSort;
        }

        localStorage.setItem('productFilter', JSON.stringify(updatedFilter));

        fetchProductList();
    }

    // fetch product
    async function fetchProductList() {
        const productSort = document.getElementById('productSort').value;

        let updatedFilter = JSON.parse(localStorage.getItem('productFilter'));

        try {
            const response = await fetch('/fetchSubcategoryProductList', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    productSort,
                    'subcategory_slug': '<?= $subcategorySlug ?>'
                }),
            });
            const result = await response.json();

            if (result.status === 'success') {
                const products = result.products;
                const currency_symbol = result.currency_symbol;
                const currency_symbol_position = result.currency_symbol_position;
                const productListView = document.getElementById('productListView');
                const productAppView = document.getElementById('productAppView');
                const productGridView = document.getElementById('productGridView');
                productListView.innerHTML = '';
                productAppView.innerHTML = '';
                productGridView.innerHTML = '';

                document.getElementById('product_count').innerText = result.products.length;

                if (!result.products.length) {
                    document.getElementById('noProductAvilable').classList.remove('hidden'); // Make visible
                    document.getElementById('noProductAvilable').classList.add('block'); // Ensure it's displayed
                } else {
                    document.getElementById('noProductAvilable').classList.remove('block'); // Hide
                    document.getElementById('noProductAvilable').classList.add('hidden'); // Ensure it's hidden
                }

                const formatPrice = (price) => {
                    return currency_symbol_position === 'left' ?
                        `${currency_symbol}${price}` :
                        `${price}${currency_symbol}`;
                };

                products.forEach(product => {
                    const firstVariant = product.variants[0];

                    // Calculate discount percentage
                    const discountPercentage = firstVariant.discounted_price > 0 ?
                        Math.round(((firstVariant.price - firstVariant.discounted_price) / firstVariant.price) * 100) :
                        0;

                    // Product container
                    const productDiv = document.createElement('div');
                    productDiv.className = 'rounded-lg bg-white border border-green-500';
                    productDiv.id = product.slug;

                    const isOutOfStock = firstVariant.is_unlimited_stock == 0 && firstVariant.stock == 0;

                    // Inner content
                    productDiv.innerHTML = `
                            <div class="flex p-2">
                                <div class="relative flex-shrink-0">
                                    ${firstVariant.discounted_price > 0 ? `
                                        <div class="absolute -top-2 left-1">
                                            <svg width="29" height="28" viewBox="0 0 29 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M28.9499 0C28.3999 0 27.9361 1.44696 27.9361 2.60412V27.9718L24.5708 25.9718L21.2055 27.9718L17.8402 25.9718L14.4749 27.9718L11.1096 25.9718L7.74436 27.9718L4.37907 25.9718L1.01378 27.9718V2.6037C1.01378 1.44655 0.549931 0 0 0H28.9499Z" fill="#15803D"></path>
                                            </svg>
                                        </div>
                                        <span class="absolute text-xs text-white font-bold left-[6px] -top-2">${discountPercentage}%</span>
                                        <span class="absolute text-xs text-white font-bold left-[8px] top-1">off</span>
                                    ` : ''}
 
                                    <a href="/product/${product.slug}">
                                        <img src="${result.base_url+product.main_img}" alt="${product.product_name}" class="w-28 h-28 md:w-40 md:h-40 object-cover rounded-lg">
                                    </a>
                                </div>

                                <div class="flex flex-col justify-center ml-1 w-full">
                                    <div>
                                        <h3 class="text-sm font-semibold">
                                            <a href="/product/${product.slug}">${product.product_name}</a>
                                        </h3>
                                        <span class="text-xs text-gray-500">${firstVariant.title}</span>
                                    </div>

                                    <div class="flex justify-between items-center mt-2">
                                        <div class="flex flex-col">
                                        ${firstVariant.discounted_price > 0
                                                    ? `<span class="text-sm text-gray-900 font-semibold">${formatPrice(firstVariant.discounted_price)}</span>
                                                    <span class="line-through text-gray-500 text-xs">${formatPrice(firstVariant.price)}</span>`
                                                    : `<span class="text-sm text-gray-900 font-semibold">${formatPrice(firstVariant.price)}</span>`}
                                        </div>

                                        <div class="${product.slug}-mainbtndiv-${firstVariant.id}">
                                                ${
                                                    isOutOfStock
                                                    ? `
                                                    <button type="button" class="text-xs px-2 py-1 rounded-lg items-center gap-x-1 bg-red-700 text-white border-red-700 hover:text-white hover:bg-red-700 btn-sm">
                                                        <span>Out Of Stock</span>
                                                    </button>
                                                    `
                                                    : product.cart_quantity > 0
                                                    ? `
                                                    <div class="flex items-center gap-1 p-1 rounded-lg bg-green-700 border border-green-700 shadow-md">
                                                                <button type="button" onclick="removeFromCart(${product.id}, ${firstVariant.id})" 
                                                                    class="text-lg leading-none hover:text-primary ${product.slug}-removebtn-${firstVariant.id}">
                                                                    <i class="fi fi-rr-minus-small text-white"></i>
                                                                </button>
                                                                <span class="text-center h-5 text-sm font-medium text-white ${product.slug}-qty-${firstVariant.id}">${product.cart_quantity}</span>
                                                                <button type="button" onclick="addToCart(${product.id}, ${firstVariant.id})" 
                                                                    class="text-lg leading-none hover:text-primary ${product.slug}-addbtn-${firstVariant.id}">
                                                                    <i class="fi fi-rr-plus-small text-white"></i>
                                                                </button>
                                                            </div>
                                                    `
                                                    : `
                                                    <button type="button" onclick="openProductVariantPopup(${product.id}, '${product.slug}')" 
                                                        class="text-sm px-2 py-1 rounded-lg items-center gap-x-1 bg-green-700 text-white border-green-700 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 btn-sm ${product.slug}-${firstVariant.id}">
                                                        <i class="fi fi-rr-shopping-cart"></i>
                                                        <span>Add</span>
                                                    </button>
                                                    `
                                                }
                                            </div>

                                    </div>
                                </div>
                            </div>
                        `;

                    productListView.appendChild(productDiv);
                });

                products.forEach(product => {
                    const firstVariant = product.variants[0];
                    const discountPercentage = firstVariant.discounted_price > 0 ?
                        Math.round(((firstVariant.price - firstVariant.discounted_price) / firstVariant.price) * 100) :
                        0;

                    const isOutOfStock = firstVariant.is_unlimited_stock == 0 && firstVariant.stock == 0;


                    const productCard = `
                            <div class="rounded-lg bg-white border border-green-500" id="${product.slug}">
                                <div class="flex-auto p-2">
                                    <div class="text-center relative flex justify-center">
                                        ${discountPercentage > 0 
                                            ? `<div class="absolute -top-2 left-1">
                                                    <svg width="29" height="28" viewBox="0 0 29 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M28.9499 0C28.3999 0 27.9361 1.44696 27.9361 2.60412V27.9718L24.5708 25.9718L21.2055 27.9718L17.8402 25.9718L14.4749 27.9718L11.1096 25.9718L7.74436 27.9718L4.37907 25.9718L1.01378 27.9718V2.6037C1.01378 1.44655 0.549931 0 0 0H28.9499Z" fill="#15803D"></path>
                                                    </svg>
                                                </div>
                                                <span class="absolute text-xs text-white font-bold left-[6px] -top-2 break-words">${discountPercentage}%</span>
                                                <span class="absolute text-xs text-white font-bold left-[8px] top-1 break-words">off</span>`
                                            : ''}
                                        <a href="/product/${product.slug}">
                                            <img src="${result.base_url+product.main_img}" alt="${product.product_name}" class="w-4/5 h-auto ml-auto mr-auto" />
                                        </a>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <h3 class="text-sm truncate font-semibold">
                                            <a href="/product/${product.slug}">${product.product_name}</a>
                                        </h3>
                                        <span class="text-xs text-gray-500">${firstVariant.title}</span>
                                        <div class="flex justify-between items-center mt-2">
                                            <div class="flex flex-col">
                                                ${firstVariant.discounted_price > 0
                                                    ? `<span class="text-sm text-gray-900 font-semibold">${formatPrice(firstVariant.discounted_price)}</span>
                                                    <span class="line-through text-gray-500 text-xs">${formatPrice(firstVariant.price)}</span>`
                                                    : `<span class="text-sm text-gray-900 font-semibold">${formatPrice(firstVariant.price)}</span>`}
                                            </div>
                                            

                                            <div class="${product.slug}-mainbtndiv-${firstVariant.id}">
                                                ${
                                                    isOutOfStock
                                                    ? `
                                                    <button type="button" class="text-xs px-2 py-1 rounded-lg items-center gap-x-1 bg-red-700 text-white border-red-700 hover:text-white hover:bg-red-700 btn-sm">
                                                        <span>Out Of Stock</span>
                                                    </button>
                                                    `
                                                    : product.cart_quantity > 0
                                                    ? `
                                                    <div class="flex items-center gap-1 p-1 rounded-lg bg-green-700 border border-green-700 shadow-md">
                                                                <button type="button" onclick="removeFromCart(${product.id}, ${firstVariant.id})" 
                                                                    class="text-lg leading-none hover:text-primary ${product.slug}-removebtn-${firstVariant.id}">
                                                                    <i class="fi fi-rr-minus-small text-white"></i>
                                                                </button>
                                                                <span class="text-center h-5 text-sm font-medium text-white ${product.slug}-qty-${firstVariant.id}">${product.cart_quantity}</span>
                                                                <button type="button" onclick="addToCart(${product.id}, ${firstVariant.id})" 
                                                                    class="text-lg leading-none hover:text-primary ${product.slug}-addbtn-${firstVariant.id}">
                                                                    <i class="fi fi-rr-plus-small text-white"></i>
                                                                </button>
                                                            </div>
                                                    `
                                                    : `
                                                    <button type="button" onclick="openProductVariantPopup(${product.id}, '${product.slug}')" 
                                                        class="text-sm px-2 py-1 rounded-lg items-center gap-x-1 bg-green-700 text-white border-green-700 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 btn-sm ${product.slug}-${firstVariant.id}">
                                                        <i class="fi fi-rr-shopping-cart"></i>
                                                        <span>Add</span>
                                                    </button>
                                                    `
                                                }
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </div>`;




                    productAppView.insertAdjacentHTML('beforeend', productCard);
                });

                products.forEach(product => {
                    const firstVariant = product.variants[0];
                    const discountPercentage = firstVariant.discounted_price > 0 ?
                        Math.round(((firstVariant.price - firstVariant.discounted_price) / firstVariant.price) * 100) :
                        0;

                    const isOutOfStock = firstVariant.is_unlimited_stock == 0 && firstVariant.stock == 0;


                    const productCard = `
                            <div class="rounded-lg bg-white border border-green-500" id="${product.slug}">
                                <div class="flex-auto p-2">
                                    <div class="text-center relative flex justify-center">
                                        ${discountPercentage > 0 
                                            ? `<div class="absolute -top-2 left-1">
                                                    <svg width="29" height="28" viewBox="0 0 29 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M28.9499 0C28.3999 0 27.9361 1.44696 27.9361 2.60412V27.9718L24.5708 25.9718L21.2055 27.9718L17.8402 25.9718L14.4749 27.9718L11.1096 25.9718L7.74436 27.9718L4.37907 25.9718L1.01378 27.9718V2.6037C1.01378 1.44655 0.549931 0 0 0H28.9499Z" fill="#15803D"></path>
                                                    </svg>
                                            </div>
                                            <span class="absolute text-xs text-white font-bold left-[6px] -top-2 break-words">${discountPercentage}%</span>
                                            <span class="absolute text-xs text-white font-bold left-[8px] top-1 break-words">off</span>`
                                            : ''}
                                        <a href="/product/${product.slug}">
                                            <img src="${result.base_url+product.main_img}" alt="${product.product_name}" class="w-4/5 h-auto ml-auto mr-auto" />
                                        </a>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <h3 class="text-sm truncate font-semibold">
                                            <a href="/product/${product.slug}">${product.product_name}</a>
                                        </h3>
                                        <span class="text-xs text-gray-500">${firstVariant.title}</span>
                                        <div class="flex justify-between items-center mt-2">
                                            <div class="flex flex-col">
                                            ${firstVariant.discounted_price > 0
                                                    ? `<span class="text-sm text-gray-900 font-semibold">${formatPrice(firstVariant.discounted_price)}</span>
                                                    <span class="line-through text-gray-500 text-xs">${formatPrice(firstVariant.price)}</span>`
                                                    : `<span class="text-sm text-gray-900 font-semibold">${formatPrice(firstVariant.price)}</span>`}
                                            </div>
                                            <div class="${product.slug}-mainbtndiv-${firstVariant.id}">
                                                ${
                                                    isOutOfStock
                                                    ? `
                                                    <button type="button" class="text-xs px-2 py-1 rounded-lg items-center gap-x-1 bg-red-700 text-white border-red-700 hover:text-white hover:bg-red-700 btn-sm">
                                                        <span>Out Of Stock</span>
                                                    </button>
                                                    `
                                                    : product.cart_quantity > 0
                                                    ? `
                                                    <div class="flex items-center gap-1 p-1 rounded-lg bg-green-700 border border-green-700 shadow-md">
                                                                <button type="button" onclick="removeFromCart(${product.id}, ${firstVariant.id})" 
                                                                    class="text-lg leading-none hover:text-primary ${product.slug}-removebtn-${firstVariant.id}">
                                                                    <i class="fi fi-rr-minus-small text-white"></i>
                                                                </button>
                                                                <span class="text-center h-5 text-sm font-medium text-white ${product.slug}-qty-${firstVariant.id}">${product.cart_quantity}</span>
                                                                <button type="button" onclick="addToCart(${product.id}, ${firstVariant.id})" 
                                                                    class="text-lg leading-none hover:text-primary ${product.slug}-addbtn-${firstVariant.id}">
                                                                    <i class="fi fi-rr-plus-small text-white"></i>
                                                                </button>
                                                            </div>
                                                    `
                                                    : `
                                                    <button type="button" onclick="openProductVariantPopup(${product.id}, '${product.slug}')" 
                                                        class="text-sm px-2 py-1 rounded-lg items-center gap-x-1 bg-green-700 text-white border-green-700 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 btn-sm ${product.slug}-${firstVariant.id}">
                                                        <i class="fi fi-rr-shopping-cart"></i>
                                                        <span>Add</span>
                                                    </button>
                                                    `
                                                }
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>`;

                    productGridView.insertAdjacentHTML('beforeend', productCard);
                });

            } else {
                console.error('Failed to load products');
            }
        } catch (error) {
            console.error('Error fetching products:', error);
        }
    }
    fetchProductList();
</script>