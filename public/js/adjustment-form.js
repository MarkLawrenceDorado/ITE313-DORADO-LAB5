/* ==========================================================
   adjustment-form.js
   Shows the selected product's current system count, and
   live-computes the difference as the physical count is typed,
   before the form is even submitted.
   ========================================================== */

document.addEventListener('DOMContentLoaded', function () {
    const productSelect = document.getElementById('product_id');
    const systemCountDisplay = document.getElementById('systemCountDisplay');
    const physicalCountInput = document.getElementById('physical_count');
    const differenceDisplay = document.getElementById('differenceDisplay');

    if (!productSelect || !physicalCountInput) return;

    function getSystemQuantity() {
        const option = productSelect.options[productSelect.selectedIndex];
        if (!option || !option.value) return null;
        const qty = parseInt(option.dataset.quantity, 10);
        return isNaN(qty) ? null : qty;
    }

    function updateDisplay() {
        const systemQty = getSystemQuantity();

        if (systemQty === null) {
            systemCountDisplay.value = '—';
            differenceDisplay.value = '—';
            differenceDisplay.classList.remove('text-success', 'text-danger');
            return;
        }

        systemCountDisplay.value = systemQty;

        const physicalCount = parseInt(physicalCountInput.value, 10);
        if (isNaN(physicalCount)) {
            differenceDisplay.value = '—';
            differenceDisplay.classList.remove('text-success', 'text-danger');
            return;
        }

        const difference = physicalCount - systemQty;
        differenceDisplay.classList.remove('text-success', 'text-danger');

        if (difference === 0) {
            differenceDisplay.value = 'No change';
        } else if (difference > 0) {
            differenceDisplay.value = `+${difference}`;
            differenceDisplay.classList.add('text-success');
        } else {
            differenceDisplay.value = `${difference}`;
            differenceDisplay.classList.add('text-danger');
        }
    }

    productSelect.addEventListener('change', updateDisplay);
    physicalCountInput.addEventListener('input', updateDisplay);

    updateDisplay(); // run once in case of a restored selection after a validation error
});