<div class="p-3 mb-3 " id="invoice">
    <div class="row">
        <div class="col-12">
            <h4 style="display: flex; align-items: center;">
                <img src="<?= base_url($settings['logo']) ?>" alt="" style="width: 50px;  margin-right: 10px;">
                <span style="margin-bottom: 20px;"><?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></span>

            </h4>
        </div>
    </div>
    <table class="table ">
        <tr>
            <td style="width: 33%; padding: 10px;">
                <b style='font-size: 12px;'><?php echo lang('website.from'); ?>,</b><br>
                <address style='font-size: 12px;'>
                    <strong><?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></strong><br>
                    <? echo json_decode($settings['address'], true)['address']; ?><br>
                    <?php echo lang('website.phone'); ?>: <?= $settings['phone']; ?><br>
                    <?php echo lang('website.email'); ?>: <?= $settings['email']; ?><br>
                    <?php echo lang('website.website'); ?>: <?= base_url(); ?>
                </address>
            </td>
            <td style="width: 33%; padding: 10px;">
                <b style='font-size: 12px;'><?php echo lang('website.shipping_address'); ?></b><br>
                <address style='font-size: 12px;'>
                    <strong id="name" style='font-size: 12px;'><?= $orderDetails['user_name'] ?></strong><br>
                    <span id="address" style='font-size: 12px;'><?= $orderDetails['address'] . ", " . $orderDetails['area'] . ", " . $orderDetails['city'] . ", " . $orderDetails['state'] . "-" . $orderDetails['pincode'] ?></span><br>
                    <?php echo lang('website.phone'); ?>: <span id="phone" style='font-size: 12px;'><?= $orderDetails['user_mobile'] ?></span><br>
                    <?php echo lang('website.email'); ?>: <span id="mail_id" style='font-size: 12px;'><?= $orderDetails['user_email'] ?></span>
                </address>
            </td>
            <td style="width: 33%; padding: 10px;">
                <b style='font-size: 12px;'><?php echo lang('website.invoice'); ?> #<span id="invoice_id" style='font-size: 12px;'><?= $orderDetails['order_id'] ?></span></b><br>
                <b style='font-size: 12px;'><?php echo lang('website.order_id'); ?> :</b> <span id="order_id" style='font-size: 12px;'><?= $orderDetails['user_order_id'] ?></span><br>
                <b style='font-size: 12px;'><?php echo lang('website.order_date'); ?> :</b> <span id="Order_date" style='font-size: 12px;'><?= date('jS M, Y', strtotime($orderDetails['order_date'])) ?></span><br>
                <b style='font-size: 12px;'><?php echo lang('website.delivery_date'); ?> :</b> <span id="Delivery_date" style='font-size: 12px;'><?php  isset($orderDetails['delivery_date']) ? date('jS M, Y', strtotime($orderDetails['delivery_date'])) : '' ?></span><br>
                <b style='font-size: 12px;'><?php echo lang('website.time_slot'); ?> :</b> <span id="time_slot" style='font-size: 12px;'><?= $orderDetails['timeslot'] ?></span><br>
                <b style='font-size: 12px;'><?php echo lang('website.order_status'); ?> :</b> <span id="order_status" style='font-size: 10px;' class="badge badge-sm <?= $orderDetails['order_status_color'] ?>"><?= $orderDetails['order_status'] ?></span>
            </td>
        </tr>
    </table>
    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table " style="width: 100%;">
                <thead>
                    <tr>
                        <th style='font-size: 12px;'><?php echo lang('website.sr_no'); ?></th>
                        <th style='font-size: 12px;'><?php echo lang('website.product'); ?></th>
                        <th style='font-size: 12px;'><?php echo lang('website.sold_by'); ?></th>
                        <th style='font-size: 12px;'><?php echo lang('website.unit'); ?></th>
                        <th style='font-size: 12px;'><?php echo lang('website.price'); ?></th>
                        <th style='font-size: 12px;'><?php echo lang('website.tax'); ?>&nbsp;<?= $country['currency_symbol'] ?>&nbsp;(%)</th>
                        <th style='font-size: 12px;'><?php echo lang('website.quantity'); ?></th>
                        <th style='font-size: 12px;'><?php echo lang('website.subtotal'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalProductPrice = 0; // Initialize total price variable
                    $totalProductTax = 0;

                    if (!empty($orderProducts)) : ?>

                        <?php foreach ($orderProducts as $index => $product) :
                            $itemPrice = (($product['discounted_price'] > 0)
                                ? $product['discounted_price']
                                : $product['price']) * $product['quantity'];

                            // Add to total price
                            $totalProductPrice += $itemPrice;

                            // Add tax amount to total tax
                            $totalProductTax += ($product['tax_amount']);

                        ?>
                            <tr>
                                <td style='font-size: 12px;'><?= $index + 1; ?></td>
                                <td style='font-size: 12px;'><a class="text-dark text-underline" href="#"><?= htmlspecialchars($product['product_name'] . ' (' . $product['product_variant_name'] . ')'); ?></a></td>
                                <td style='font-size: 12px;'><?= htmlspecialchars($product['store_name']); ?></td>
                                <td style='font-size: 12px;'><?= htmlspecialchars($product['quantity']); ?></td>
                                <td style='font-size: 12px;'><?= $product['discounted_price'] > 0 ? number_format($product['discounted_price'], 2) : number_format($product['price'], 2)  ?></td>
                                <td style='font-size: 12px;'><?= number_format($product['tax_amount'], 2); ?> (<?= $product['tax_percentage']; ?>%)</td>
                                <td style='font-size: 12px;'><?= htmlspecialchars($product['quantity']); ?></td>
                                <td style='font-size: 12px;'>
                                    <?php
                                        if($product['discounted_price'] > 0){
                                            $subtotal = $product['discounted_price'] * $product['quantity'];
                                        }else{
                                            $subtotal = $product['price'] * $product['quantity'];
                                        }
                                    echo number_format($subtotal, 2);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center" style='font-size: 12px;'><?php echo lang('website.no_products_found_for_this_order'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <table class="table">
            <tr>
                <td colspan="4">
                    <b style='font-size: 12px;'><?php echo lang('website.payment_mode'); ?> :</b> <br>
                    <span id="method" style='font-size: 12px;'><?= $orderDetails['payment_method_title'] ?></span><br>
                </td>
                <td colspan="4">
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <td style='font-size: 12px; padding:5px; border:0; font-weight:600; width:50%'><?php echo lang('website.subtotal'); ?> : </td>
                                <td style='font-size: 12px; padding:5px; border:0;'>
                                    <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                        <?= $country['currency_symbol'] ?><span id="subtotal"><?= $totalProductPrice ?></span>
                                    <?php else: ?>
                                        <span id="subtotal"><?= $totalProductPrice ?></span><?= $country['currency_symbol'] ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style='font-size: 12px; padding:5px; border:0; font-weight:600;'><?php echo lang('website.tax'); ?> : </td>
                                <td style='font-size: 12px; padding:5px; border:0;'><?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                        <?= $country['currency_symbol'] ?><span id="tax_value"><?= $totalProductTax ?></span>
                                    <?php else: ?>
                                        <span id="tax_value"><?= $totalProductTax ?></span><?= $country['currency_symbol'] ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style='font-size: 12px; padding:5px; border:0; font-weight:600;'><?php echo lang('website.delivery_charge'); ?> :</td>
                                <td style='font-size: 12px; padding:5px; border:0;'>
                                    <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                        <?= $country['currency_symbol'] ?><span id="delivery_charge"><?= $orderDetails['delivery_charge'] ?></span>
                                    <?php else: ?>
                                        <span id="delivery_charge"><?= $orderDetails['delivery_charge'] ?></span><?= $country['currency_symbol'] ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style='font-size: 12px; padding:5px; border:0; font-weight:600;'><?= $settings['additional_charge_name'] ?></td>
                                <td style='font-size: 12px; padding:5px; border:0;'><?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                        <?= $country['currency_symbol'] ?><span id="additional_charge"><?= $orderDetails['additional_charge'] ?></span>
                                    <?php else: ?>
                                        <span id="additional_charge"><?= $orderDetails['additional_charge'] ?></span><?= $country['currency_symbol'] ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style='font-size: 12px; padding:5px; border:0; font-weight:600;'><?php echo lang('website.discount'); ?> <span id="coupon_code"> </span>:</td>
                                <td style='font-size: 12px; padding:5px; border:0;'>- <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                        <?= $country['currency_symbol'] ?><span id="total_discount"><?= $orderDetails['coupon_amount'] ?></span>
                                    <?php else: ?>
                                        <span id="total_discount"><?= $orderDetails['coupon_amount'] ?></span><?= $country['currency_symbol'] ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style='font-size: 12px; padding:5px; border:0; font-weight:600;'><?php echo lang('website.wallet'); ?> <span id="wallet"> </span>:</td>
                                <td style='font-size: 12px; padding:5px; border:0;'>- <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                        <?= $country['currency_symbol'] ?><span id="used_wallet_amount"><?= $orderDetails['used_wallet_amount'] ?></span>
                                    <?php else: ?>
                                        <span id="used_wallet_amount"><?= $orderDetails['used_wallet_amount'] ?></span><?= $country['currency_symbol'] ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style='font-size: 12px; padding:5px; border:0; font-weight:600;'><?php echo lang('website.total'); ?>:</td>
                                <td style='font-size: 12px; padding:5px; border:0;'>
                                    <?php if ($settings['currency_symbol_position'] == 'left'): ?>
                                        <?= $country['currency_symbol'] ?>
                                        <span id="total"><?= round($totalProductPrice + $totalProductTax  + $orderDetails['delivery_charge'] + $orderDetails['additional_charge'] - $orderDetails['coupon_amount'] - $orderDetails['used_wallet_amount'], 2) ?></span>
                                    <?php else: ?>
                                        <span id="total"><?= round($totalProductPrice + $totalProductTax  + $orderDetails['delivery_charge'] + $orderDetails['additional_charge'] - $orderDetails['coupon_amount'] - $orderDetails['used_wallet_amount'], 2) ?></span><?= $country['currency_symbol'] ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <?php if (!empty($returnedProducts)) : ?>
        <div class="row">
            <div class="col-12"><b style='font-size: 12px;'><?php echo lang('website.retuned_product_list'); ?></b></div>

            <div class="col-12 table-responsive">
                <table class="table " style="width: 100%;">
                    <thead>
                        <tr>
                            <th style='font-size: 12px;'><?php echo lang('website.sr_no'); ?></th>
                            <th style='font-size: 12px;'><?php echo lang('website.product'); ?></th>
                            <th style='font-size: 12px;'><?php echo lang('website.sold_by'); ?></th>
                            <th style='font-size: 12px;'><?php echo lang('website.unit'); ?></th>
                            <th style='font-size: 12px;'><?php echo lang('website.price'); ?></th>
                            <th style='font-size: 12px;'><?php echo lang('website.tax'); ?>&nbsp;<?= $country['currency_symbol'] ?>&nbsp;(%)</th>
                            <th style='font-size: 12px;'><?php echo lang('website.quantity'); ?></th>
                            <th style='font-size: 12px;'><?php echo lang('website.subtotal'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalReturnedProductPrice = 0; // Initialize total price variable
                        $totalReturnedProductTax = 0; ?>

                        <?php foreach ($returnedProducts as $index => $product) :
                            $itemPrice = (($product['discounted_price'] > 0)
                                ? $product['discounted_price']
                                : $product['price']) * $product['quantity'];

                            // Add to total price
                            $totalReturnedProductPrice += $itemPrice;

                            // Add tax amount to total tax
                            $totalReturnedProductTax += ($product['tax_amount']);

                        ?>
                            <tr>
                                <td style='font-size: 12px;'><?= $index + 1; ?></td>
                                <td style='font-size: 12px;'><a class="text-dark text-underline" href="#"><?= htmlspecialchars($product['product_name'] . ' (' . $product['product_variant_name'] . ')'); ?></a></td>
                                <td style='font-size: 12px;'><?= htmlspecialchars($product['store_name']); ?></td>
                                <td style='font-size: 12px;'><?= htmlspecialchars($product['quantity']); ?></td>
                                <td style='font-size: 12px;'><?= number_format($product['discounted_price'], 2); ?></td>
                                <td style='font-size: 12px;'><?= number_format($product['tax_amount'], 2); ?> (<?= $product['tax_percentage']; ?>%)</td>
                                <td style='font-size: 12px;'><?= htmlspecialchars($product['quantity']); ?></td>
                                <td style='font-size: 12px;'>
                                    <?php
                                    $subtotal = ($product['discounted_price'] + ($product['discounted_price'] * $product['tax_percentage'] / 100)) * $product['quantity'];
                                    echo number_format($subtotal, 2);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>


    <center><?php echo lang('website.bill_generated_by'); ?>  <?= $settings['business_name']?></center>
    <hr style="border: 1px dashed #000;">
</div>