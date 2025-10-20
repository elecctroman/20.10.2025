(() => {
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.getElementById('trend-chart');
        if (typeof window.renderTrendChart === 'function') {
            window.renderTrendChart(canvas);
            window.addEventListener('resize', () => window.renderTrendChart(canvas));
        }
    });
})();
