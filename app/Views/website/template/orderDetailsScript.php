<script>
    const cancelOrderModal = document.getElementById('cancelOrderModal');

    function openCancelOrderPopup() {
        cancelOrderModal.classList.remove('hidden');
        document.body.classList.add('modal-open');
    }

    function closeCancelOrderPopup() {
        cancelOrderModal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    const cancelOrderForm = document.querySelector('form.cancelOrderForm');

    if (cancelOrderForm) {
        cancelOrderForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const note = document.getElementById('note').value.trim();

            try {
                const response = await fetch('/cancelOrder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        note,
                        order_id: <?= $order['id'] ?>
                    }),
                });

                const result = await response.json();

                // Handle success or error response
                if (result.status === 'success') {
                    event.target.reset();
                    closeCancelOrderPopup()

                    document.getElementById('orderTrackingDiv').classList.add('hidden')
                    document.getElementById('openCancelOrderPopup').classList.add('hidden')
                    showToast(result.message, "success");

                } else {
                    showToast(result.message, "danger");
                }
            } catch (error) {

            }
        })
    }

    const returningItemModal = document.getElementById('returningItemModal');

    function openReturningItemPopup(order_id, order_product_id) {
        returningItemModal.classList.remove('hidden');
        document.body.classList.add('modal-open');

        document.getElementById('ri_order_id').value = order_id
        document.getElementById('ri_order_product_id').value = order_product_id
    }

    function closeReturningItemPopup() {
        returningItemModal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    document.querySelector('form.returningItemForm').addEventListener('submit', async (event) => {
        event.preventDefault();

        const note = document.getElementById('note').value.trim();
        const order_id = document.getElementById('ri_order_id').value.trim();
        const order_product_id = document.getElementById('ri_order_product_id').value.trim();

        try {
            const response = await fetch('/returningItemRequest', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    note,
                    order_id,
                    order_product_id
                }),
            });

            const result = await response.json();

            // Handle success or error response
            if (result.status === 'success') {
                event.target.reset();
                closeReturningItemPopup()

                document.getElementById('returningItem_' + order_id + '_' + order_product_id).innerHTML = '<span class="font-medium text-yellow-800 bg-yellow-200 px-2 py-1 rounded text-xs">Pending</span>';

                showToast(result.message, "success");

            } else {
                showToast(result.message, "danger");
            }
        } catch (error) {

        }

    })

    async function downloadInvoice(order_id, buttonElement) {
        try {
            // Disable the button and change text
            const originalContent = buttonElement.innerHTML;
            buttonElement.innerHTML = `
            <i class="fi fi-rr-cloud-download-alt"></i>
            <span class="text-sm font-medium capitalize whitespace-nowrap">Downloading...</span>
        `;
            buttonElement.disabled = true;

            // Fetch the PDF file
            const response = await fetch('/downloadInvoice', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id
                }),
            });

            if (!response.ok) {
                throw new Error('Failed to download invoice.');
            }

            // Convert response to a blob
            const blob = await response.blob();

            // Create a download link for the PDF
            const link = document.createElement('a');
            const url = window.URL.createObjectURL(blob);
            link.href = url;
            link.download = `order_invoice_${order_id}.pdf`;
            document.body.appendChild(link);
            link.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(link);


            buttonElement.disabled = false;
            buttonElement.innerHTML = originalContent;

        } catch (error) {
            alert('An error occurred while downloading the invoice. Please try again.');
            console.error(error);
        }
    }
</script>