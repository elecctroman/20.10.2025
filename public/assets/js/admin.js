(() => {
    document.addEventListener('DOMContentLoaded', () => {
        const toggles = document.querySelectorAll('[data-toggle]');
        toggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                const target = document.getElementById(toggle.dataset.toggle);
                if (target) {
                    target.toggleAttribute('hidden');
                }
            });
        });
    });
})();
