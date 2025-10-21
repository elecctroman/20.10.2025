(() => {
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.getElementById('trend-chart');
        if (typeof window.renderTrendChart === 'function') {
            window.renderTrendChart(canvas);
            window.addEventListener('resize', () => window.renderTrendChart(canvas));
        }

        document.body.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-action="toggle-code"]');
            if (toggle) {
                const pill = toggle.previousElementSibling?.classList.contains('code-pill')
                    ? toggle.previousElementSibling
                    : toggle.parentElement?.querySelector('.code-pill');
                if (pill) {
                    const isMasked = pill.getAttribute('data-visible') !== 'true';
                    pill.textContent = isMasked ? pill.getAttribute('data-code') : pill.getAttribute('data-mask');
                    pill.setAttribute('data-visible', isMasked ? 'true' : 'false');
                    toggle.textContent = isMasked ? 'Gizle' : 'Göster';
                }
            }

            const copy = event.target.closest('[data-action="copy-code"]');
            if (copy) {
                const pill = copy.parentElement?.querySelector('.code-pill');
                const value = pill?.getAttribute('data-code');
                if (value) {
                    navigator.clipboard?.writeText(value).then(() => {
                        if (typeof window.showToast === 'function') {
                            window.showToast('Kod panoya kopyalandı', 'success');
                        }
                    }).catch(() => {
                        if (typeof window.showToast === 'function') {
                            window.showToast('Kod kopyalanamadı', 'error');
                        }
                    });
                }
            }
        });
    });
})();
