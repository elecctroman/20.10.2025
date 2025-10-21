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
        const priceStack = document.querySelector('.product-price-stack');
        const currencySymbols = { TRY: '₺', USD: '$', EUR: '€' };

        const renderPriceStack = (prices) => {
            if (!priceStack) return;
            const entries = Object.entries(prices || {});
            if (!entries.length) {
                priceStack.innerHTML = '';
                return;
            }
            priceStack.innerHTML = '';
            entries.forEach(([code, value]) => {
                const chip = document.createElement('span');
                chip.className = `price-chip ${code === 'TRY' ? 'primary' : ''}`;
                chip.textContent = `${currencySymbols[code] || ''}${Number(value).toFixed(2)}${code !== 'TRY' ? ` ${code}` : ''}`;
                priceStack.appendChild(chip);
            });
        };

        if (priceStack) {
            try {
                const basePrices = JSON.parse(priceStack.dataset.prices || '{}');
                renderPriceStack(basePrices);
            } catch (error) {
                console.warn('Fiyat dönüştürme verisi okunamadı', error);
            }
        }

        if (variantSelect) {
            const handleVariantChange = () => {
                const selected = variantSelect.options[variantSelect.selectedIndex];
                if (!selected) return;
                try {
                    const prices = JSON.parse(selected.dataset.prices || '{}');
                    renderPriceStack(prices);
                } catch (error) {
                    console.warn('Varyant fiyatı okunamadı', error);
                }
            };

            variantSelect.addEventListener('change', handleVariantChange);
            handleVariantChange();
        }

        const toastStack = document.querySelector('[data-toast-stack]');
        const spawnToast = (toast, index = 0) => {
            if (!toastStack || !toast || !toast.message) return;
            const node = document.createElement('div');
            node.className = `toast ${toast.type || 'info'}`;
            node.setAttribute('role', 'status');

            const text = document.createElement('span');
            text.textContent = toast.message;

            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.setAttribute('aria-label', 'Bildirim kapat');
            closeBtn.textContent = '×';

            node.append(text, closeBtn);
            closeBtn.addEventListener('click', () => {
                node.classList.remove('show');
                setTimeout(() => node.remove(), 200);
            });
            toastStack.appendChild(node);
            requestAnimationFrame(() => node.classList.add('show'));
            const lifetime = 5000 + (index * 500);
            setTimeout(() => {
                node.classList.remove('show');
                setTimeout(() => node.remove(), 200);
            }, lifetime);
        };

        if (Array.isArray(window.__TOASTS)) {
            window.__TOASTS.forEach((toast, index) => spawnToast(toast, index));
        }

        window.showToast = (message, type = 'info') => spawnToast({ message, type });
    });
})();
