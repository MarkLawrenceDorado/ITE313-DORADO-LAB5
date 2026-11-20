/* ==========================================================
   transaction-form.js
   Enhances the "Record Transaction" form: shows the selected
   product's current stock, and caps the quantity input at the
   available stock when the transaction type is Stock Out.
   ========================================================== */

document.addEventListener('DOMContentLoaded', function () {
    const productSelect = document.getElementById('product_id');
    const stockDisplay = document.getElementById('currentStockDisplay');
    const quantityInput = document.getElementById('quantity');
    const typeRadios = document.querySelectorAll('input[name="type"]');

    if (!productSelect || !quantityInput) return;

    function getSelectedProductQuantity() {
        const option = productSelect.options[productSelect.selectedIndex];
        if (!option || !option.value) return null;
        return parseInt(option.dataset.quantity, 10);
    }

    function getSelectedType() {
        const checked = document.querySelector('input[name="type"]:checked');
        return checked ? checked.value : null;
    }

    function updateStockDisplayAndLimits() {
        const quantity = getSelectedProductQuantity();
        const type = getSelectedType();

        if (quantity === null || isNaN(quantity)) {
            stockDisplay.textContent = '';
            quantityInput.removeAttribute('max');
            quantityInput.removeAttribute('min');
            return;
        }

        stockDisplay.textContent = `Current stock: ${quantity} unit(s).`;

        if (type === 'stock_out') {
            // Can't remove more than what's currently in stock.
            quantityInput.setAttribute('max', quantity);
            quantityInput.setAttribute('min', 1);
            stockDisplay.textContent += ` Maximum you can remove: ${quantity}.`;
        } else if (type === 'stock_in') {
            quantityInput.removeAttribute('max');
            quantityInput.setAttribute('min', 1);
        } else {
            // Adjustment: any signed value is allowed.
            quantityInput.removeAttribute('max');
            quantityInput.removeAttribute('min');
        }
    }

    productSelect.addEventListener('change', updateStockDisplayAndLimits);
    typeRadios.forEach(radio => radio.addEventListener('change', updateStockDisplayAndLimits));

    // Run once on load in case the browser restored a previous selection
    // (e.g. after a validation error sent the user back to this form).
    updateStockDisplayAndLimits();
});