(() => {
    document.addEventListener('DOMContentLoaded', () => {
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        lazyImages.forEach(img => {
            img.addEventListener('error', () => {
                img.src = '/assets/img/placeholders/placeholder.png';
            });
        });
    });
})();
