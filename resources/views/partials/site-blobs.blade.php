<div class="site-shell__background" aria-hidden="true">
    <canvas id="blobs"></canvas>
</div>
<div class="grain" aria-hidden="true"></div>

<script>
    (function () {
        const canvas = document.getElementById('blobs');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        let w, h;

        const blobs = [
            { x: 0.12, y: 0.18, r: 0.22, color: 'rgba(224, 64, 251, 0.55)', vx: 0.00018, vy: 0.00012 },
            { x: 0.55, y: 0.35, r: 0.28, color: 'rgba(255, 23, 68, 0.5)', vx: -0.00014, vy: 0.00016 },
            { x: 0.88, y: 0.12, r: 0.2, color: 'rgba(255, 23, 68, 0.45)', vx: -0.0001, vy: 0.00008 },
            { x: 0.78, y: 0.78, r: 0.32, color: 'rgba(124, 77, 255, 0.45)', vx: 0.0001, vy: -0.00012 },
            { x: 0.25, y: 0.72, r: 0.18, color: 'rgba(124, 77, 255, 0.35)', vx: 0.00012, vy: -0.0001 },
        ];

        function resize() {
            w = canvas.width = window.innerWidth;
            h = canvas.height = window.innerHeight;
        }

        function draw() {
            ctx.clearRect(0, 0, w, h);

            if ('filter' in ctx) {
                ctx.filter = 'blur(80px)';
            }

            blobs.forEach(function (b) {
                b.x += b.vx;
                b.y += b.vy;
                if (b.x < -0.1 || b.x > 1.1) b.vx *= -1;
                if (b.y < -0.1 || b.y > 1.1) b.vy *= -1;

                ctx.beginPath();
                ctx.arc(b.x * w, b.y * h, b.r * Math.min(w, h), 0, Math.PI * 2);
                ctx.fillStyle = b.color;
                ctx.fill();
            });

            if ('filter' in ctx) {
                ctx.filter = 'none';
            }

            requestAnimationFrame(draw);
        }

        resize();
        window.addEventListener('resize', resize);
        draw();
    })();
</script>
