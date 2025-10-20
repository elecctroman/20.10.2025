(() => {
    function drawGradientLine(ctx, points, width, height) {
        ctx.clearRect(0, 0, width, height);
        if (!points.length) {
            return;
        }
        const max = Math.max(...points);
        const min = Math.min(...points);
        const range = max - min || 1;
        const step = width / Math.max(points.length - 1, 1);
        const gradient = ctx.createLinearGradient(0, 0, width, height);
        gradient.addColorStop(0, '#2ad0ff');
        gradient.addColorStop(0.5, '#7f5dff');
        gradient.addColorStop(1, '#c445ff');

        ctx.beginPath();
        points.forEach((value, index) => {
            const x = step * index;
            const normalized = (value - min) / range;
            const y = height - (normalized * (height - 20)) - 10;
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        ctx.strokeStyle = gradient;
        ctx.lineWidth = 3;
        ctx.lineJoin = 'round';
        ctx.lineCap = 'round';
        ctx.stroke();

        const fillGradient = ctx.createLinearGradient(0, 0, 0, height);
        fillGradient.addColorStop(0, 'rgba(42, 208, 255, 0.25)');
        fillGradient.addColorStop(1, 'rgba(12, 18, 30, 0.05)');

        ctx.lineTo(width, height);
        ctx.lineTo(0, height);
        ctx.closePath();
        ctx.fillStyle = fillGradient;
        ctx.fill();
    }

    window.renderTrendChart = function (canvas) {
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const width = canvas.width = canvas.offsetWidth;
        const height = canvas.height = canvas.offsetHeight;
        const data = JSON.parse(canvas.dataset.trend || '[]').map(Number);
        drawGradientLine(ctx, data, width, height);
    };
})();
