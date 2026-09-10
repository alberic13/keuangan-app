<script>
    document.addEventListener('DOMContentLoaded', () => {
        const syncRowState = (checkbox) => {
            const key = checkbox.dataset.key;
            const amountField = document.querySelector(`.payment-amount[data-key="${key}"]`);
            const invoiceField = checkbox.closest('tr')?.querySelector('.invoice-id-field');

            if (!amountField || !invoiceField) {
                return;
            }

            amountField.disabled = !checkbox.checked;
            amountField.required = checkbox.checked;
            invoiceField.disabled = !checkbox.checked;
        };

        document.querySelectorAll('.payment-toggle').forEach((checkbox) => {
            syncRowState(checkbox);
            checkbox.addEventListener('change', () => syncRowState(checkbox));
        });
    });
</script>
