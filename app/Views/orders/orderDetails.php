<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Details | <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></title>

    <?= $this->include('template/style') ?>
    <link rel="stylesheet" href="<?= base_url('/assets/plugins/daterangepicker/daterangepicker.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/assets/plugins/daterangepicker/daterangepicker.css') ?>">
</head>

<body class="sidebar-mini control-sidebar-slide-open text-sm  layout-fixed <?php echo  $settings['thememode'] == 'Light' ? '' : 'dark-mode'; ?> layout-navbar-fixed text-sm" id="body">
    <div class="wrapper">


        <?= $this->include('template/header') ?>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <?= $this->include('template/sidebar') ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <!-- Main content -->

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <!-- ./col -->
                        <div class="col-md-12">
                            <div class="card card-<?= $settings['primary_color'] ?>">
                                <div class="card-header">
                                    <h3 class="card-title"> Order Action Section</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class="d-flex ">
                                        <form action="">
                                            <div class="mr-2">

                                                <select id="delivery_boy_id" onchange="assignDeliveryBoy(this)" class=" form-control form-control-sm  primary-bprder filter-product">
                                                    <option value="" aria-readonly="true" readonly>Assign Delivery Boy</option>
                                                    <?php foreach ($delivery_boy_lists as $delivery_boy_list):
                                                    ?>
                                                        <option value="<?= esc($delivery_boy_list['id']);
                                                                        ?>" <?php echo $orderDetails['delivery_boy_id'] ==  $delivery_boy_list['id'] ? 'selected' : '' ?>><?= esc($delivery_boy_list['name']);
                                                                                                                                                                            ?></option>
                                                    <?php endforeach;
                                                    ?>
                                                </select>
                                            </div>
                                        </form>
                                        <div class="mx-2">
                                            <select id="status" onchange="updateStatus(this)" class="form-control form-control-sm primary-bprder filter-product">
                                                <option value="" disabled>Update Status</option>
                                                <?php foreach ($status_list as $orderStatusList):
                                                ?>
                                                    <option value="<?= esc($orderStatusList['id']);
                                                                    ?>" <?php echo $orderDetails['status'] ==  $orderStatusList['id'] ? 'selected' : '' ?>><?= esc($orderStatusList['status']);
                                                                                                                                                            ?></option>
                                                <?php endforeach;
                                                ?>
                                            </select>
                                        </div>
                                        <div class=" mx-2">
                                            <a id="download-invoice" class="btn btn-primary btn-sm">
                                                <i class="fi fi-tr-file-export"></i> Export Invoice PDF
                                            </a>
                                        </div>
                                        <div class=" mx-2">
                                            <a onclick="printDiv('invoice')" rel="noopener" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="fi fi-tr-print"></i> Print Invoice
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            
                            <?php 
                            if($orderDetails['status'] == 7){ ?>
                                <div class="alert alert-default-danger">
                                    <h6>Cancellation Note: <b><?= $orderDetails['note'] ?></b></h6>
                            </div>
                            <?php }
                            ?>
                            

                            <div class="card card-<?= $settings['primary_color'] ?>">
                                <div class="card-header">
                                    <h3 class="card-title">View Order Details</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class="invoice p-3 mb-3" id="invoice">
                                        <div class="row">
                                            <div class="col-12">
                                                <h4>
                                                    <img src="<?= base_url($settings['logo']) ?>" alt="" style="width: 50px;"> <?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?>
                                                    <small class="float-right" id="order_date">Date: <?= date('jS M, Y', strtotime($orderDetails['order_date'])) ?></small>
                                                </h4>
                                            </div>
                                        </div> 
                                        <div class="row invoice-info">
                                            <div class="col-sm-4 invoice-col">
                                                <b>From,</b>
                                                <address>
                                                    <strong><?= isset($settings['business_name']) ? esc($settings['business_name']) : '' ?></strong><br>
                                                    <? echo json_decode($settings['address'], true)['address']; ?><br>
                                                    Phone: <?= $settings['phone']; ?><br>
                                                    Email: <?= $settings['email']; ?><br>
                                                    Website: <?= base_url(); ?>
                                                </address>
                                            </div>
                                            <div class="col-sm-4 invoice-col">
                                                <b>Shipping Address</b>
                                                <address>
                                                    <strong id="name"><?= $orderDetails['user_name'] ?></strong><br>
                                                    <span id="address"><?= $orderDetails['address'] . ", " . $orderDetails['area'] . ", " . $orderDetails['city'] . ", " . $orderDetails['state'] . "-" . $orderDetails['pincode'] ?></span><br>
                                                    Phone: <span id="phone"><?= $orderDetails['user_mobile'] ?></span><br>
                                                    Email: <span id="mail_id"><?= $orderDetails['user_email'] ?></span>
                                                </address>
                                            </div>
                                            <div class="col-sm-4 invoice-col">
                                                <b>Invoice #<span id="invoice_id"><?= $orderDetails['order_id'] ?></span></b><br>
                                                <br>
                                                <b>Order ID:</b> <span id="order_id"><?= $orderDetails['user_order_id'] ?></span><br>
                                                <b>Delivery Date:</b> <span id="Delivery_date"><?= date('jS M, Y', strtotime($orderDetails['delivery_date'])) ?></span><br>
                                                <b>Time Slot:</b> <span id="time_slot"><?= $orderDetails['timeslot'] ?></span><br>
                                                <b>Order Status:</b> <span id="order_status" class="badge <?= $orderDetails['order_status_color'] ?>"><?= $orderDetails['order_status'] ?></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 table-responsive">
                                                <table class="table table-striped" id="view_order_list" style="width: 100%;" data-ordering="false">
                                                    <thead>
                                                        <tr>
                                                            <th>Sr.&nbsp;No.</th>
                                                            <th>Product</th>
                                                            <th>Sold By</th>
                                                            <th>Unit</th>
                                                            <th>Price</th>
                                                            <th>Tax <?= $country['currency_symbol'] ?> (%)</th>
                                                            <th>Qty</th>
                                                            <th>Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (!empty($orderProducts)) : ?>
                                                            <?php foreach ($orderProducts as $index => $product) : ?>
                                                                <tr>
                                                                    <td><?= $index + 1; ?></td>
                                                                    <td><a class="text-dark text-underline" href="/admin/product/view/<?= $product['product_id'] ?>"><?= htmlspecialchars($product['product_name'] . ' (' . $product['product_variant_name'] . ')'); ?></a></td>
                                                                    <td><?= htmlspecialchars($product['store_name']); ?></td>
                                                                    <td><?= htmlspecialchars($product['quantity']); ?></td>
                                                                    <td><?= number_format($product['discounted_price'] == 0 ?$product['price'] : $product['discounted_price'] , 2); ?></td>
                                                                    <td><?= number_format($product['tax_amount'], 2); ?> (<?= number_format($product['tax_percentage'], 2); ?>%)</td>
                                                                    <td><?= htmlspecialchars($product['quantity']); ?></td>
                                                                    <td>
                                                                        <?php
                                                                        $subtotal = ($product['discounted_price'] == 0 ?$product['price'] : $product['discounted_price'] + ($product['discounted_price'] == 0 ?$product['price'] : $product['discounted_price'] * $product['tax_percentage'] / 100)) * $product['quantity'];
                                                                        echo number_format($subtotal, 2);
                                                                        ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else : ?>
                                                            <tr>
                                                                <td colspan="8" class="text-center">No products found for this order.</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-6">
                                                <b>Payment Mode:</b> <br>
                                                <img src="<?= base_url().$orderDetails['payment_method_img'] ?>" id="payment_img" style="width: 70px;">
                                                <span id="method"><?= $orderDetails['payment_method_title'] ?></span><br>
                                                <b>Payment Id:</b> <span id="payment_id"><?= $orderDetails['transaction_id'] ?></span>

                                                <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">

                                                </p>
                                            </div>
                                            <div class="col-6">

                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <tr>
                                                            <th style="width:50%">Subtotal : </th>
                                                            <td><?php if ($settings['currency_symbol_position'] == 'left') {
                                                                    echo $country['currency_symbol'];
                                                                } ?> <span id="subtotal"><?= $subtotalOfOrder['subtotal'] ?></span> <?php if ($settings['currency_symbol_position'] == 'right') {
                                                                                                                                    echo $country['currency_symbol'];
                                                                                                                                } ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Tax : </th>
                                                            <td><?php if ($settings['currency_symbol_position'] == 'left') {
                                                                    echo $country['currency_symbol'];
                                                                } ?> <span id="tax_value"><?= $orderDetails['tax'] ?> </span> <?php if ($settings['currency_symbol_position'] == 'right') {
                                                                                                                                echo $country['currency_symbol'];
                                                                                                                            } ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Delivery :</th>
                                                            <td><?php if ($settings['currency_symbol_position'] == 'left') {
                                                                    echo $country['currency_symbol'];
                                                                } ?> <span id="delivery_charge"><?= $orderDetails['delivery_charge'] ?></span> <?php if ($settings['currency_symbol_position'] == 'right') {
                                                                                                                                                    echo $country['currency_symbol'];
                                                                                                                                                } ?> </td>
                                                        </tr>
                                                        <?php
                                                        if ($settings['additional_charge_status']):
                                                        ?>
                                                            <tr>
                                                                <th><?= $settings['additional_charge_name'] ?> :</th>
                                                                <td><?php if ($settings['currency_symbol_position'] == 'left') {
                                                                        echo $country['currency_symbol'];
                                                                    } ?> <span id="additional_charge"><?= $orderDetails['additional_charge'] ?> </span> <?php if ($settings['currency_symbol_position'] == 'right') {
                                                                                                                                                            echo $country['currency_symbol'];
                                                                                                                                                        } ?> </td>
                                                            </tr>
                                                        <?php endif ?>
                                                        <tr>
                                                            <th>Discount <span id="coupon_code"> </span>:</th>
                                                            <td>- <?php if ($settings['currency_symbol_position'] == 'left') {
                                                                        echo $country['currency_symbol'];
                                                                    } ?> <span id="total_discount"><?= $orderDetails['coupon_amount'] ?> </span> <?php if ($settings['currency_symbol_position'] == 'right') {
                                                                                                                                                    echo $country['currency_symbol'];
                                                                                                                                                } ?> </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Wallet <span id="wallet"> </span>:</th>
                                                            <td>- <?php if ($settings['currency_symbol_position'] == 'left') {
                                                                        echo $country['currency_symbol'];
                                                                    } ?> <span id="used_wallet_amount"><?= $orderDetails['used_wallet_amount'] ?></span> <?php if ($settings['currency_symbol_position'] == 'right') {
                                                                                                                                                            echo $country['currency_symbol'];
                                                                                                                                                        } ?> </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Total:</th>
                                                            <td><?php if ($settings['currency_symbol_position'] == 'left') {
                                                                    echo $country['currency_symbol'];
                                                                } ?> <span id="total"><?= round($subtotalOfOrder['subtotal'] + $orderDetails['additional_charge'] + $orderDetails['tax'] - $orderDetails['used_wallet_amount'] + $orderDetails['delivery_charge'] - $orderDetails['coupon_amount'], 2) ?> </span> <?php if ($settings['currency_symbol_position'] == 'right') {
                                                                    echo $country['currency_symbol'];
                                                                } ?></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <center>Bill Generated by <?= $settings['business_name']?></center>
                                        <hr style="border: 1px dashed #000;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


        </div>

        <!-- /.content-wrapper -->
        <?= $this->include('template/footer') ?>

        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->

    <?= $this->include('template/script') ?>
    <script src="<?= base_url('/assets/page-script/orders.js') ?>"></script>
    <script>
        let previousDeliveryBoyId = null;
        let previousStatus = null;

        previousDeliveryBoyId = document.getElementById('delivery_boy_id').value;
        previousStatus = document.getElementById('status').value;

        function printDiv(divName) {
            var printContents = document.getElementById(divName).innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;

            window.print();

            document.body.innerHTML = originalContents;
        }

        $("#download-invoice").on('click', function() {
            $.ajax({
                url: "/admin/orders/download_invoice",
                type: "POST",
                data: {
                    invoice: "<?= $orderDetails['order_id'] ?>",
                },
                xhrFields: {
                    responseType: 'blob' // Expect the response as a Blob
                },
                beforeSend: function() {
                    $("#download-invoice").html(`<i class="fi fi-tr-loading spin-icon"></i>  Downloading Invoice...`);
                    $("#download-invoice").attr(`disabled`, `disabled`);
                },
                success: function(blob) {
                    // Create a download link for the PDF
                    const link = document.createElement('a');
                    const url = window.URL.createObjectURL(blob);
                    link.href = url;
                    link.download = `order_invoice_<?= $orderDetails['order_id'] ?>.pdf`;
                    document.body.appendChild(link);
                    link.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(link);
                },
                complete: function() {
                    $("#download-invoice").html(' <i class="fi fi-tr-file-export"></i> Export Invoice PDF');
                    $("#download-invoice").removeAttr(`disabled`);

                },
                error: function(xhr) {
                    console.error("Error generating PDF:", xhr.responseText);
                    alert("Failed to download invoice. Please try again.");
                }
            });


        })

        function updateStatus(status) {
            const newValue = status.value;
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, confirm it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    previousStatus = newValue;

                    $.ajax({
                        url: "/admin/orders/update_status",
                        type: "POST",
                        data: {
                            order_id: "<?= $orderDetails['order_id'] ?>",
                            status: $("#status").val()
                        },
                        dataType: "json",
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message, 'Admin says');
                            } else {
                                toastr.error(response.message, 'Admin says');
                                Array.from(status.options).forEach(option => {
                                    if (option.value === previousStatus) {
                                        option.selected = true;
                                    } else {
                                        option.selected = false;
                                    }
                                });

                            }
                        },
                        complete: function() {},
                        error: function(xhr) {
                            console.error("Error generating PDF:", xhr.responseText);
                            Array.from(status.options).forEach(option => {
                                if (option.value === previousStatus) {
                                    option.selected = true;
                                } else {
                                    option.selected = false;
                                }
                            });
                            toastr.error('Something went wrong. Please try again.', 'Admin says');
                        }
                    });
                } else {
                    toastr.error('Your action has been cancelled.', 'Admin says');
                    Array.from(status.options).forEach(option => {
                        if (option.value === previousStatus) {
                            option.selected = true;
                        } else {
                            option.selected = false;
                        }
                    });

                }
            });
        }



        function assignDeliveryBoy(delivery_boy_id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, confirm it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/admin/orders/assignDeliveryBoy",
                        type: "POST",
                        data: {
                            order_id: "<?= $orderDetails['order_id'] ?>",
                            delivery_boy_id: $("#delivery_boy_id").val()
                        },
                        dataType: "json",
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message, 'Admin says');
                            } else {
                                delivery_boy_id.value = previousDeliveryBoyId;
                                toastr.error(response.message, 'Admin says');
                            }
                        },
                        complete: function() {},
                        error: function(xhr) {
                            delivery_boy_id.value = previousDeliveryBoyId;
                            console.error("Error generating PDF:", xhr.responseText);
                            toastr.error("Something went wrong. Please try again.", 'Admin says');
                        }
                    });
                } else {
                    delivery_boy_id.value = previousDeliveryBoyId;
                    toastr.error('Your action has been cancelled.', 'Admin says');
                }
            });
        }
    </script>


</body>

</html>