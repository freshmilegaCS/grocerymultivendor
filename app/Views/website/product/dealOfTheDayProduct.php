<!doctype html>
<html lang="<?= session()->get('site_lang') ?? 'en' ?>" dir="<?= dir_attribute() ?>">

<head>
    <?= $this->include('website/template/style') ?>
    <title><?= $settings['business_name'] ?></title>
</head>

<body class="bg-gray-100">
    <?= $this->include('website/template/header') ?>
    <main class="max-w-7xl mx-auto">

        <div class="mt-2 md:mt-4 md:container md:mx-auto px-3">
            <div class="relative flex flex-col min-w-0 rounded-lg break-words bg-white p-4 mb-6">
                <div class="flex justify-between">
                    <h1 class="text-lg font-medium z-10">Deal of the Day Products</h1>
                </div>
            </div>

            <div class="container">
                <div class="flex lg:gap-8">
                    <aside class="lg:w-1/4 mb-6 md:">
                        <div class="md:hidden hidden lg:block">
                            <div class="flex flex-col gap-8 rounded-lg bg-white border border-white p-4">
                                <div class="flex flex-col gap-3">
                                    <h5>Stores</h5>
                                    <div class="flex flex-col gap-4">

                                        <!-- form check -->
                                        <div class="flex flex-col gap-2">
                                            <div class="relative flex gap-2 items-center">
                                                <!-- input -->
                                                <input class="w-4 h-4 text-green-600 bg-white border-gray-300 rounded focus:ring-green-600 focus:outline-none focus:ring-2" type="checkbox" value="" id="eGrocery" checked="">
                                                <label class="text-gray-800" for="eGrocery">E-Grocery</label>
                                            </div>
                                            <!-- form check -->
                                            <div class="relative flex gap-2 items-center">
                                                <!-- input -->
                                                <input class="w-4 h-4 text-green-600 bg-white border-gray-300 rounded focus:ring-green-600 focus:outline-none focus:ring-2" type="checkbox" value="" id="DealShare">
                                                <label class="text-gray-800" for="DealShare">DealShare</label>
                                            </div>
                                            <!-- form check -->
                                            <div class="relative flex gap-2 items-center">
                                                <!-- input -->
                                                <input class="w-4 h-4 text-green-600 bg-white border-gray-300 rounded focus:ring-green-600 focus:outline-none focus:ring-2" type="checkbox" value="" id="Dmart">
                                                <label class="text-gray-800" for="Dmart">DMart</label>
                                            </div>
                                            <!-- form check -->
                                            <div class="relative flex gap-2 items-center">
                                                <!-- input -->
                                                <input class="w-4 h-4 text-green-600 bg-white border-gray-300 rounded focus:ring-green-600 focus:outline-none focus:ring-2" type="checkbox" value="" id="Blinkit">
                                                <label class="text-gray-800" for="Blinkit">Blinkit</label>
                                            </div>
                                            <!-- form check -->
                                            <div class="relative flex gap-2 items-center">
                                                <!-- input -->
                                                <input class="w-4 h-4 text-green-600 bg-white border-gray-300 rounded focus:ring-green-600 focus:outline-none focus:ring-2" type="checkbox" value="" id="BigBasket">
                                                <label class="text-gray-800" for="BigBasket">BigBasket</label>
                                            </div>
                                            <!-- form check -->
                                            <div class="relative flex gap-2 items-center">
                                                <!-- input -->
                                                <input class="w-4 h-4 text-green-600 bg-white border-gray-300 rounded focus:ring-green-600 focus:outline-none focus:ring-2" type="checkbox" value="" id="StoreFront">
                                                <label class="text-gray-800" for="StoreFront">StoreFront</label>
                                            </div>
                                            <!-- form check -->
                                            <div class="relative flex gap-2 items-center">
                                                <!-- input -->
                                                <input class="w-4 h-4 text-green-600 bg-white border-gray-300 rounded focus:ring-green-600 focus:outline-none focus:ring-2" type="checkbox" value="" id="Spencers">
                                                <label class="text-gray-800" for="Spencers">Spencers</label>
                                            </div>
                                            <div class="relative flex gap-2 items-center">
                                                <!-- input -->
                                                <input class="w-4 h-4 text-green-600 bg-white border-gray-300 rounded focus:ring-green-600 focus:outline-none focus:ring-2" type="checkbox" value="" id="onlineGrocery">
                                                <label class="text-gray-800" for="onlineGrocery">Online Grocery</label>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- form check -->
                                </div>
                                <div class="flex flex-col gap-3">
                                    <h5 class="">Price</h5>
                                    <div class="flex justify-center items-center">
                                        <div id="sliderContainer" class="relative max-w-xl w-full">
                                            <div>
                                                <!-- Range Inputs -->
                                                <input
                                                    id="minRange"
                                                    type="range"
                                                    min="0"
                                                    max="10000"
                                                    step="100"
                                                    value="0"
                                                    class="absolute appearance-none z-20 h-2 w-full opacity-0 cursor-pointer">

                                                <input
                                                    id="maxRange"
                                                    type="range"
                                                    min="100"
                                                    max="10000"
                                                    step="100"
                                                    value="7000"
                                                    class="absolute appearance-none z-20 h-2 w-full opacity-0 cursor-pointer">

                                                <!-- Slider Track -->
                                                <div class="relative z-10 h-2">
                                                    <div class="absolute left-0 right-0 top-0 bottom-0 rounded bg-gray-200"></div>
                                                    <div id="rangeTrack" class="absolute top-0 bottom-0 rounded bg-green-700" style="left: 0%; right: 0%;"></div>

                                                    <!-- Handles -->
                                                    <div id="minHandle" class="absolute w-6 h-6 top-0 bg-green-700 rounded-full -mt-2 -ml-1" style="left: 0%;"></div>
                                                    <div id="maxHandle" class="absolute w-6 h-6 top-0 bg-green-700 rounded-full -mt-2 -mr-3" style="left: 100%;"></div>
                                                </div>
                                            </div>

                                            <!-- Input Fields -->
                                            <div class="flex flex-row items-center gap-2 mt-2">
                                                <span id="priceDisplay" class="text-sm"><span id="minPrice"></span> - <span id="maxPrice"></span></span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </aside>
                    <section class="w-full bg-white rounded-lg p-4">

                        <div class="flex flex-col md:flex-row justify-between lg:items-center mb-6 gap-3">
                            <div>
                                <p class="text-sm">
                                    <span class="text-gray-900"><?= count($dealOfTheDayProducts) ?></span>
                                    Products found
                                </p>
                            </div>
                            <div class="flex flex-col md:flex-row justify-between md:items-center gap-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <button id="listViewButton" class="text-gray-600" onclick="setProductListView()">
                                            <i class="fi fi-rr-list"></i>
                                        </button>
                                        <button id="appViewButton" class="text-gray-600" onclick="setProductAppView()">
                                            <i class="fi fi-rr-apps"></i>
                                        </button>
                                        <button id="gridViewButton" class="text-gray-600 hidden md:block" onclick="setProductGridView()">
                                            <i class="fi fi-rr-grid"></i>
                                        </button>
                                    </div>
                                    <div class="ml-3 lg:hidden">
                                        <a class="text-sm btn inline-flex p-2 items-center gap-x-2 bg-white text-gray-800 border-gray-300 border rounded-lg disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-gray-700 hover:border-gray-700 active:bg-gray-700 active:border-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300" data-bs-toggle="offcanvas" href="#offcanvasCategory" role="button" aria-controls="offcanvasCategory">
                                            <i class="fi fi-rr-filter"></i>
                                            Filters
                                        </a>
                                    </div>
                                </div>

                                <div class="flex">
                                    <select class="text-sm p-2 block w-full border text-gray-700 border-gray-300 rounded-lg focus:border-green-600 focus:ring-green-600 disabled:opacity-50 disabled:pointer-events-none">
                                        <option selected value="Relevance">Relevance</option>
                                        <option value="Price(Low to High)">Price(Low to High)</option>
                                        <option value="Price(High to Low)">Price(High to Low)</option>
                                        <option value="Discount(High to Low)">Discount(High to Low)</option>
                                        <option value="Name(A to Z)">Name(A to Z)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 hidden" id="productListView">
                            <?php foreach ($dealOfTheDayProducts as $product): ?>
                                <?php $firstVarient = $product['variants'][0]; ?>

                                <div class="rounded-lg bg-white border border-green-500" id="<?= $product['slug'] ?>">
                                    <div class="flex p-2">
                                        <!-- Image Section with Discount -->
                                        <div class="relative flex-shrink-0">
                                            <!-- Discount Badge -->
                                            <?php if ($firstVarient['discounted_price'] > 0):
                                                $discountPercentage = (($firstVarient['price'] - $firstVarient['discounted_price']) / $firstVarient['price']) * 100;
                                                $discountPercentage = round($discountPercentage);
                                            ?>
                                                <div class="absolute -top-2 left-1">
                                                    <svg width="29" height="28" viewBox="0 0 29 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M28.9499 0C28.3999 0 27.9361 1.44696 27.9361 2.60412V27.9718L24.5708 25.9718L21.2055 27.9718L17.8402 25.9718L14.4749 27.9718L11.1096 25.9718L7.74436 27.9718L4.37907 25.9718L1.01378 27.9718V2.6037C1.01378 1.44655 0.549931 0 0 0H28.9499Z" fill="#15803D"></path>
                                                    </svg>
                                                </div>
                                                <span class="absolute  text-xs text-white font-bold left-[6px] -top-2 break-words"><?= $discountPercentage ?>%</span>
                                                <span class="absolute  text-xs text-white font-bold left-[8px] top-1 break-words">off</span>
                                            <?php endif; ?>
                                            <!-- Product Image -->
                                            <a href="<?= base_url('product/' . $product['slug']) ?>">
                                                <img src="<?= base_url($product['main_img']) ?>"
                                                    alt="<?= $product['product_name'] ?>"
                                                    class="w-28 h-28 md:w-40 md:h-40 object-cover rounded-lg">
                                            </a>
                                        </div>

                                        <!-- Text and Details Section -->
                                        <div class="flex flex-col justify-center ml-1 w-full">
                                            <!-- Product Name and Quantity -->
                                            <div>
                                                <h3 class="text-sm font-semibold">
                                                    <a href="<?= base_url('product/' . $product['slug']) ?>"><?= $product['product_name'] ?></a>
                                                </h3>
                                                <span class="text-xs text-gray-500"><?php echo $firstVarient['title'] ?></span>
                                            </div>

                                            <!-- Price and Button -->
                                            <div class="flex justify-between items-center mt-2">
                                                <div class="flex flex-col">
                                                    <?php if ($firstVarient['discounted_price'] > 0): ?>
                                                        <span class="text-sm text-gray-900 font-semibold"><?= $firstVarient['discounted_price'] ?></span>
                                                        <span class="line-through text-gray-500 text-xs"><?= $firstVarient['price'] ?></span>
                                                    <?php else: ?>
                                                        <span class="text-sm text-gray-900 font-semibold"><?= $firstVarient['price'] ?></span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="<?= $product['slug'] . '-mainbtndiv-' . $firstVarient['id'] ?>">
                                                    <?php
                                                    if ($product['cart_quantity']):
                                                    ?>
                                                        <div class="flex items-center gap-1 p-1 rounded-lg bg-green-700 border border-green-700 shadow-md">
                                                            <button type="button" onclick="removeFromCart(<?= $product['id'] ?>, <?= $firstVarient['id'] ?>)"
                                                                class="text-lg leading-none hover:text-primary <?= $product['slug'] . '-removebtn-' . $firstVarient['id'] ?>">
                                                                <i class="fi fi-rr-minus-small text-white"></i>
                                                            </button>
                                                            <span class="text-center h-5 text-sm font-medium text-white <?= $product['slug'] . '-qty-' . $firstVarient['id'] ?>"><?= $product['cart_quantity'] ?></span>
                                                            <button type="button" onclick="addToCart(<?= $product['id'] ?>, <?= $firstVarient['id'] ?>)"
                                                                class="text-lg leading-none hover:text-primary <?= $product['slug'] . '-addbtn-' . $firstVarient['id'] ?>">
                                                                <i class="fi fi-rr-plus-small text-white"></i>
                                                            </button>
                                                        </div>
                                                    <?php else: ?>
                                                        <button type="button" onclick="openProductVariantPopup(<?= $product['id'] ?>, '<?= $product['slug'] ?>')"
                                                            class="text-sm px-2 py-1 rounded-lg items-center gap-x-1 bg-green-700 text-white border-green-700 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 btn-sm <?= $product['slug'] . '-' . $firstVarient['id'] ?>">
                                                            <i class="fi fi-rr-shopping-cart"></i>
                                                            <span>Add</span>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>


                            <?php endforeach; ?>
                        </div>

                        <div class="grid xl:grid-cols-4 lg:grid-cols-3 md:grid-cols-2 grid-cols-2 gap-4" id="productAppView">
                            <?php foreach ($dealOfTheDayProducts as $product): ?>
                                <?php $firstVarient = $product['variants'][0]; ?>
                                <div class="rounded-lg bg-white border border-green-500" id="<?= $product['slug'] ?>">
                                    <div class="flex-auto p-2">
                                        <div class="text-center relative flex justify-center">
                                            <?php if ($firstVarient['discounted_price'] > 0):
                                                $discountPercentage = (($firstVarient['price'] - $firstVarient['discounted_price']) / $firstVarient['price']) * 100;
                                                $discountPercentage = round($discountPercentage);
                                            ?>
                                                <div class="absolute -top-2 left-1">
                                                    <svg width="29" height="28" viewBox="0 0 29 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M28.9499 0C28.3999 0 27.9361 1.44696 27.9361 2.60412V27.9718L24.5708 25.9718L21.2055 27.9718L17.8402 25.9718L14.4749 27.9718L11.1096 25.9718L7.74436 27.9718L4.37907 25.9718L1.01378 27.9718V2.6037C1.01378 1.44655 0.549931 0 0 0H28.9499Z" fill="#15803D"></path>
                                                    </svg>
                                                </div>
                                                <span class="absolute  text-xs text-white font-bold left-[6px] -top-2 break-words"><?= $discountPercentage ?>%</span>
                                                <span class="absolute  text-xs text-white font-bold left-[8px] top-1 break-words">off</span>
                                            <?php endif; ?>
                                            <a href="<?= base_url('product/' . $product['slug']) ?>">
                                                <img src="<?= base_url($product['main_img']) ?>"
                                                    alt="<?= $product['product_name'] ?>" class="w-4/5 h-auto ml-auto mr-auto" />
                                            </a>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <h3 class="text-sm truncate font-semibold"><a href="<?= base_url('product/' . $product['slug']) ?>"><?= $product['product_name'] ?></a></h3>
                                            <span class="text-xs text-gray-500 "><?php echo $firstVarient['title'] ?></span>
                                            <div class="flex justify-between items-center mt-2">
                                                <div class="flex flex-col">
                                                    <?php if ($firstVarient['discounted_price'] > 0): ?>
                                                        <span class="text-sm text-gray-900 font-semibold"><?= $firstVarient['discounted_price'] ?></span>
                                                        <span class="line-through text-gray-500 text-xs"><?= $firstVarient['price'] ?></span>
                                                    <?php else: ?>
                                                        <span class="text-sm text-gray-900 font-semibold"><?= $firstVarient['price'] ?></span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="<?= $product['slug'] . '-mainbtndiv-' . $firstVarient['id'] ?>">
                                                    <?php
                                                    if ($product['cart_quantity']):
                                                    ?>
                                                        <div class="flex items-center gap-1 p-1 rounded-lg bg-green-700 border border-green-700 shadow-md">
                                                            <button type="button" onclick="removeFromCart(<?= $product['id'] ?>, <?= $firstVarient['id'] ?>)"
                                                                class="text-lg leading-none hover:text-primary <?= $product['slug'] . '-removebtn-' . $firstVarient['id'] ?>">
                                                                <i class="fi fi-rr-minus-small text-white"></i>
                                                            </button>
                                                            <span class="text-center h-5 text-sm font-medium text-white <?= $product['slug'] . '-qty-' . $firstVarient['id'] ?>"><?= $product['cart_quantity'] ?></span>
                                                            <button type="button" onclick="addToCart(<?= $product['id'] ?>, <?= $firstVarient['id'] ?>)"
                                                                class="text-lg leading-none hover:text-primary <?= $product['slug'] . '-addbtn-' . $firstVarient['id'] ?>">
                                                                <i class="fi fi-rr-plus-small text-white"></i>
                                                            </button>
                                                        </div>
                                                    <?php else: ?>
                                                        <button type="button" onclick="openProductVariantPopup(<?= $product['id'] ?>, '<?= $product['slug'] ?>')"
                                                            class="text-sm px-2 py-1 rounded-lg items-center gap-x-1 bg-green-700 text-white border-green-700 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 btn-sm <?= $product['slug'] . '-' . $firstVarient['id'] ?>">
                                                            <i class="fi fi-rr-shopping-cart"></i>
                                                            <span>Add</span>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="grid xl:grid-cols-5 lg:grid-cols-4 md:grid-cols-4 grid-cols-2 gap-4 hidden" id="productGridView">
                            <?php foreach ($dealOfTheDayProducts as $product): ?>
                                <?php $firstVarient = $product['variants'][0]; ?>
                                <div class="rounded-lg bg-white border border-green-500" id="<?= $product['slug'] ?>">
                                    <div class="flex-auto p-2">
                                        <div class="text-center relative flex justify-center">
                                            <?php if ($firstVarient['discounted_price'] > 0):
                                                $discountPercentage = (($firstVarient['price'] - $firstVarient['discounted_price']) / $firstVarient['price']) * 100;
                                                $discountPercentage = round($discountPercentage);
                                            ?>
                                                <div class="absolute -top-2 left-1">
                                                    <svg width="29" height="28" viewBox="0 0 29 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M28.9499 0C28.3999 0 27.9361 1.44696 27.9361 2.60412V27.9718L24.5708 25.9718L21.2055 27.9718L17.8402 25.9718L14.4749 27.9718L11.1096 25.9718L7.74436 27.9718L4.37907 25.9718L1.01378 27.9718V2.6037C1.01378 1.44655 0.549931 0 0 0H28.9499Z" fill="#15803D"></path>
                                                    </svg>
                                                </div>
                                                <span class="absolute  text-xs text-white font-bold left-[6px] -top-2 break-words"><?= $discountPercentage ?>%</span>
                                                <span class="absolute  text-xs text-white font-bold left-[8px] top-1 break-words">off</span>
                                            <?php endif; ?>
                                            <a href="<?= base_url('product/' . $product['slug']) ?>">
                                                <img src="<?= base_url($product['main_img']) ?>"
                                                    alt="<?= $product['product_name'] ?>" class="w-4/5 h-auto ml-auto mr-auto" />
                                            </a>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <h3 class="text-sm truncate font-semibold"><a href="<?= base_url('product/' . $product['slug']) ?>"><?= $product['product_name'] ?></a></h3>
                                            <span class="text-xs text-gray-500 "><?php echo $firstVarient['title'] ?></span>
                                            <div class="flex justify-between items-center mt-2">
                                                <div class="flex flex-col">
                                                    <?php if ($firstVarient['discounted_price'] > 0): ?>
                                                        <span class="text-sm text-gray-900 font-semibold"><?= $firstVarient['discounted_price'] ?></span>
                                                        <span class="line-through text-gray-500 text-xs"><?= $firstVarient['price'] ?></span>
                                                    <?php else: ?>
                                                        <span class="text-sm text-gray-900 font-semibold"><?= $firstVarient['price'] ?></span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="<?= $product['slug'] . '-mainbtndiv-' . $firstVarient['id'] ?>">
                                                    <?php
                                                    if ($product['cart_quantity']):
                                                    ?>
                                                        <div class="flex items-center gap-1 p-1 rounded-lg bg-green-700 border border-green-700 shadow-md">
                                                            <button type="button" onclick="removeFromCart(<?= $product['id'] ?>, <?= $firstVarient['id'] ?>)"
                                                                class="text-lg leading-none hover:text-primary <?= $product['slug'] . '-removebtn-' . $firstVarient['id'] ?>">
                                                                <i class="fi fi-rr-minus-small text-white"></i>
                                                            </button>
                                                            <span class="text-center h-5 text-sm font-medium text-white <?= $product['slug'] . '-qty-' . $firstVarient['id'] ?>"><?= $product['cart_quantity'] ?></span>
                                                            <button type="button" onclick="addToCart(<?= $product['id'] ?>, <?= $firstVarient['id'] ?>)"
                                                                class="text-lg leading-none hover:text-primary <?= $product['slug'] . '-addbtn-' . $firstVarient['id'] ?>">
                                                                <i class="fi fi-rr-plus-small text-white"></i>
                                                            </button>
                                                        </div>
                                                    <?php else: ?>
                                                        <button type="button" onclick="openProductVariantPopup(<?= $product['id'] ?>, '<?= $product['slug'] ?>')"
                                                            class="text-sm px-2 py-1 rounded-lg items-center gap-x-1 bg-green-700 text-white border-green-700 disabled:opacity-50 disabled:pointer-events-none hover:text-white hover:bg-green-700 hover:border-green-700 btn-sm <?= $product['slug'] . '-' . $firstVarient['id'] ?>">
                                                            <i class="fi fi-rr-shopping-cart"></i>
                                                            <span>Add</span>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    </section>
                </div>
            </div>


        </div>


        <?= $this->include('website/template/mobileBottomMenu') ?>
        <?= $this->include('website/template/productVarientPopup') ?>

    </main>
    <?= $this->include('website/template/shopCart') ?>
    <?= $this->include('website/template/footer') ?>
    <?= $this->include('website/template/script') ?>
    <script src="<?= base_url('/assets/page-script/website/dealOfThedayProduct.js') ?>"></script>

</body>

</html>