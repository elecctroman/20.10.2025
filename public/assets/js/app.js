(() => {
    document.addEventListener('DOMContentLoaded', () => {
        const slider = document.querySelector('[data-slider] .hero-slides');
        const controls = document.querySelectorAll('.hero-control');
        let active = 0;
        let timer;

        const setSlide = (index) => {
            if (!slider) return;
            active = (index + controls.length) % controls.length;
            slider.style.transform = `translateX(-${active * 100}%)`;
            controls.forEach((control, idx) => {
                control.setAttribute('aria-current', idx === active ? 'true' : 'false');
            });
        };

        const startAuto = () => {
            if (!slider || controls.length === 0) return;
            clearInterval(timer);
            timer = setInterval(() => setSlide(active + 1), 5000);
        };

        controls.forEach((button, idx) => {
            button.addEventListener('click', () => {
                setSlide(idx);
                startAuto();
            });
        });

        setSlide(0);
        startAuto();

        document.querySelectorAll('[data-quick-add]').forEach((overlay) => {
            const output = overlay.querySelector('output');
            let quantity = parseInt(output.textContent, 10) || 1;
            overlay.addEventListener('click', (event) => {
                if (event.target.dataset.step) {
                    event.stopPropagation();
                    const step = parseInt(event.target.dataset.step, 10);
                    quantity = Math.max(1, quantity + step);
                    output.textContent = quantity;
                }
            });
        });

        document.querySelectorAll('[data-quantity]').forEach((wrapper) => {
            const input = wrapper.querySelector('input[type="number"]');
            wrapper.addEventListener('click', (event) => {
                const step = parseInt(event.target.dataset.step || '0', 10);
                if (!step) return;
                event.preventDefault();
                const min = parseInt(input.getAttribute('min') || '1', 10);
                const max = parseInt(input.getAttribute('max') || '0', 10);
                let next = parseInt(input.value || '1', 10) + step;
                if (!Number.isFinite(next)) next = min;
                next = Math.max(min, next);
                if (max > 0) {
                    next = Math.min(max, next);
                }
                input.value = next;
            });
        });

        const variantSelect = document.getElementById('variant-select');
        if (variantSelect) {
            const priceTag = document.querySelector('.product-detail .product-price');
            variantSelect.addEventListener('change', () => {
                const selected = variantSelect.options[variantSelect.selectedIndex];
                const price = selected.dataset.price;
                if (priceTag && price) {
                    priceTag.textContent = `₺${price}`;
                }
            });
        }
    });
})();
