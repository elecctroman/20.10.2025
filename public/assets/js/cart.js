(() => {
    const quantityInputs = document.querySelectorAll('input[name="quantity"]');
    quantityInputs.forEach(input => {
        input.addEventListener('change', () => {
            if (parseInt(input.value, 10) < 1) {
                input.value = '1';
            }
        });
    });
})();
