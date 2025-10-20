(() => {
    window.renderMiniChart = function (canvasId, data = []) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const width = canvas.width;
        const height = canvas.height;
        ctx.clearRect(0, 0, width, height);
        ctx.beginPath();
        data.forEach((value, index) => {
            const x = (index / Math.max(data.length - 1, 1)) * width;
            const y = height - (value * height);
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        ctx.strokeStyle = '#34d399';
        ctx.lineWidth = 2;
        ctx.stroke();
    };
})();
