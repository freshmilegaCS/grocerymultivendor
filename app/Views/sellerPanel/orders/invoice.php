<style>
    @page {
        margin: 0;
    }

    body {
        margin: 0;
    }
</style>
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
                <b style='font-size: 12px;'>From,</b><br>
                <address style='font-size: 12px;'>
                    <strong><?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></strong><br>
                    <? echo json_decode($settings['address'], true)['address']; ?><br>
                    Phone: <?= $settings['phone']; ?><br>
                    Email: <?= $settings['email']; ?><br>
                    Website: <?= base_url(); ?>
                </address>
            </td>
            <td style="width: 33%; padding: 10px;">
                <?php if ($sellerInfo['view_customer_details'] == 1) { ?>
                <b style='font-size: 12px;'>Shipping Address</b><br>
                
                    <address style='font-size: 12px;'>
                        <strong id="name" style='font-size: 12px;'><?= $orderDetails['user_name'] ?></strong><br>
                        <span id="address" style='font-size: 12px;'><?= $orderDetails['address'] . ", " . $orderDetails['area'] . ", " . $orderDetails['city'] . ", " . $orderDetails['state'] . "-" . $orderDetails['pincode'] ?></span><br>
                        Phone: <span id="phone" style='font-size: 12px;'><?= $orderDetails['user_mobile'] ?></span><br>
                        Email: <span id="mail_id" style='font-size: 12px;'><?= $orderDetails['user_email'] ?></span>
                    </address>
                <?php } ?>
            </td>
            <td style="width: 33%; padding: 10px;">
                <b style='font-size: 12px;'>Invoice #<span id="invoice_id" style='font-size: 12px;'><?= $orderDetails['order_id'] ?></span></b><br>
                <b style='font-size: 12px;'>Order ID:</b> <span id="order_id" style='font-size: 12px;'><?= $orderDetails['order_id'] ?></span><br>
                <b style='font-size: 12px;'>Order Date:</b> <span id="Order_date" style='font-size: 12px;'><?= date('jS M, Y', strtotime($orderDetails['order_date'])) ?></span><br>
                <b style='font-size: 12px;'>Delivery Date:</b> <span id="Delivery_date" style='font-size: 12px;'><?php  isset($orderDetails['delivery_date']) ? date('jS M, Y', strtotime($orderDetails['delivery_date'])) : '' ?></span><br>
                <b style='font-size: 12px;'>Time Slot:</b> <span id="time_slot" style='font-size: 12px;'><?= $orderDetails['timeslot'] ?></span><br>
                <b style='font-size: 12px;'>Order Status:</b> <span id="order_status" style='font-size: 10px;' class="badge badge-sm <?= $orderDetails['order_status_color'] ?>"><?= $orderDetails['order_status'] ?></span>
            </td>
        </tr>
    </table>
    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table " style="width: 100%;">
                <thead>
                    <tr>
                        <th style='font-size: 12px;'>Sr.&nbsp;No.</th>
                        <th style='font-size: 12px;'>Product</th>
                        <th style='font-size: 12px;'>Sold&nbsp;By</th>
                        <th style='font-size: 12px;'>Unit</th>
                        <th style='font-size: 12px;'>Price</th>
                        <th style='font-size: 12px;'>Tax&nbsp;<?= $country['currency_symbol'] ?>&nbsp;(%)</th>
                        <th style='font-size: 12px;'>Qty</th>
                        <th style='font-size: 12px;'>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orderProducts)) : ?>
                        <?php foreach ($orderProducts as $index => $product) : ?>
                            <tr>
                                <td style='font-size: 12px;'><?= $index + 1; ?></td>
                                <td style='font-size: 12px;'><a class="text-dark text-underline" href="/admin/product/view/<?= $product['product_id'] ?>"><?= htmlspecialchars($product['product_name'] . ' (' . $product['product_variant_name'] . ')'); ?></a></td>
                                <td style='font-size: 12px;'><?= htmlspecialchars($product['store_name']); ?></td>
                                <td style='font-size: 12px;'><?= htmlspecialchars($product['quantity']); ?></td>
                                <td style='font-size: 12px;'><?= number_format($product['discounted_price'] == 0 ? $product['price'] : $product['discounted_price'], 2); ?></td>
                                <td style='font-size: 12px;'><?= number_format($product['tax_amount'], 2); ?> (<?= $product['tax_percentage']; ?>%)</td>
                                <td style='font-size: 12px;'><?= htmlspecialchars($product['quantity']); ?></td>
                                <td style='font-size: 12px;'>
                                    <?php
                                    $subtotal = ($product['discounted_price'] == 0 ? $product['price'] : $product['discounted_price'] + ($product['discounted_price'] == 0 ? $product['price'] : $product['discounted_price'] * $product['tax_percentage'] / 100)) * $product['quantity'];
                                    echo number_format($subtotal, 2);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center" style='font-size: 12px;'>No products found for this order.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <center>Bill Generated by <?= $settings['business_name']?></center>
    <hr style="border: 1px dashed #000;"> 
</div>