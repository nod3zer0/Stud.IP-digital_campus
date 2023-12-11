<template>
    <canvas v-show="showCanvas" ref="canvas"></canvas>
</template>

<script>
export default {
    name: 'studip-ident-image',
    props: {
        value: {
            type: String,
        },
        showCanvas: {
            type: Boolean,
            default: false,
        },
        baseColor: {
            type: String, // hex color
        },
        pattern: {
            type: String,
            required: true,
        },
        width: {
            type: Number,
            default: 270,
        },
        height: {
            type: Number,
            default: 180,
        },
        shapesMin: {
            type: Number,
            default: 5,
        },
        shapesMax: {
            type: Number,
            default: 8,
        },
    },
    data() {
        return {
            random: null,
            ellipse: null,
        };
    },
    methods: {
        randint(min, max) {
            return Math.floor(this.random() * (max - min) + min);
        },
        renderIdentimage() {
            let canvas = this.$refs.canvas;
            canvas.width = this.width;
            canvas.height = this.height;

            const minSize = Math.min(this.width, this.height) * 0.2;
            const ctx = canvas.getContext('2d');
            const backgroundHSL = this.hexToHSL(this.baseColor);
            const numShape = this.randint(this.shapesMin, this.shapesMax);
            const shapeSizes = [];

            ctx.fillStyle = this.hexToRgbA(this.baseColor, 0.8);
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            const curveStart = this.randint(10, 70)/100 * this.height;
            const curveEnd = this.randint(10, 70)/100 * this.height;
            ctx.strokeStyle = `rgba(255, 255, 255, ${this.randint(30, 40) / 100})`;
            const curvedistance = this.randint(10, 20);
            const xFactor = this.randint(10, 45) / 100;
            const yFactor = this.randint(10, 45) / 100;
            for (let c = 0; c < numShape * 2; c++) {
                ctx.beginPath();
                ctx.moveTo(0, curveStart + curvedistance * c);
                ctx.bezierCurveTo(this.width * xFactor, this.height * yFactor, this.width * (xFactor + 0.5), this.height * (yFactor + 0.5), this.width, curveEnd + curvedistance * c);
                ctx.stroke();
            }

            for (let i = 0; i < numShape; i++) {
                shapeSizes.push(this.randint(minSize*0.2, minSize*2) + minSize);
            }

            shapeSizes.sort((a, b) => {
                return a < b ? 1 : a > b ? -1 : 0;
            });

            shapeSizes.forEach((shapeSizes, index) => {
                const radius = shapeSizes / 2;
                const [x, y] = this.createPointInEllipse(ctx);
                const x_center = x * (this.width + radius / 2) - radius / 4;
                const y_center = y * (this.height + radius / 2) - radius / 4;

                ctx.fillStyle = `rgba(255, 255, 255, ${this.randint(10, 80) / 100})`;

                ctx.beginPath();

                if (index % 2 === 0) {
                    ctx.arc(x_center, y_center, radius, 0, 2 * Math.PI);
                } else {
                    const size = radius;
                    ctx.moveTo(x_center + size * Math.cos(0), y_center + size * Math.sin(0));

                    for (let side = 0; side < 7; side++) {
                        ctx.lineTo(
                            x_center + size * Math.cos((side * 2 * Math.PI) / 6),
                            y_center + size * Math.sin((side * 2 * Math.PI) / 6)
                        );
                    }
                }

                ctx.fill();
            });

            this.$emit('input', canvas.toDataURL());
        },
        createPointInEllipse(ctx) {
            const x = this.random();
            const y = this.random();

            if (ctx.isPointInPath(this.ellipse, x, y)) {
                return [x, y];
            }

            return this.createPointInEllipse(...arguments);
        },

        cyrb128(value) {
            let h1 = 1779033703,
                h2 = 3144134277,
                h3 = 1013904242,
                h4 = 2773480762;

            for (let i = 0, k; i < value.length; i++) {
                k = value.charCodeAt(i);
                h1 = h2 ^ Math.imul(h1 ^ k, 597399067);
                h2 = h3 ^ Math.imul(h2 ^ k, 2869860233);
                h3 = h4 ^ Math.imul(h3 ^ k, 951274213);
                h4 = h1 ^ Math.imul(h4 ^ k, 2716044179);
            }

            h1 = Math.imul(h3 ^ (h1 >>> 18), 597399067);
            h2 = Math.imul(h4 ^ (h2 >>> 22), 2869860233);
            h3 = Math.imul(h1 ^ (h3 >>> 17), 951274213);
            h4 = Math.imul(h2 ^ (h4 >>> 19), 2716044179);

            return [(h1 ^ h2 ^ h3 ^ h4) >>> 0, (h2 ^ h1) >>> 0, (h3 ^ h1) >>> 0, (h4 ^ h1) >>> 0];
        },
        sfc32(a, b, c, d) {
            return function () {
                a >>>= 0;
                b >>>= 0;
                c >>>= 0;
                d >>>= 0;
                var t = (a + b) | 0;
                a = b ^ (b >>> 9);
                b = (c + (c << 3)) | 0;
                c = (c << 21) | (c >>> 11);
                d = (d + 1) | 0;
                t = (t + d) | 0;
                c = (c + t) | 0;

                return (t >>> 0) / 4294967296;
            };
        },

        hexToRGB(color) {
            color = color.slice(1); // remove #
            let val = parseInt(color, 16);
            let r = val >> 16;
            let g = (val >> 8) & 0x00ff;
            let b = val & 0x0000ff;

            if (g > 255) {
                g = 255;
            } else if (g < 0) {
                g = 0;
            }
            if (b > 255) {
                b = 255;
            } else if (b < 0) {
                b = 0;
            }

            return { r: r, g: g, b: b };
        },
        RGBToHSL(r, g, b) {
            r /= 255;
            g /= 255;
            b /= 255;

            let cmin = Math.min(r, g, b),
                cmax = Math.max(r, g, b),
                delta = cmax - cmin,
                h = 0,
                s = 0,
                l = 0;
            if (delta == 0) h = 0;
            // Red is max
            else if (cmax == r) h = ((g - b) / delta) % 6;
            // Green is max
            else if (cmax == g) h = (b - r) / delta + 2;
            // Blue is max
            else h = (r - g) / delta + 4;

            h = Math.round(h * 60);

            if (h < 0) h += 360;
            l = (cmax + cmin) / 2;

            s = delta == 0 ? 0 : delta / (1 - Math.abs(2 * l - 1));

            s = +(s * 100).toFixed(1);
            l = +(l * 100).toFixed(1);

            return { h: h, s: s, l: l };
            // return 'hsl(' + h + ',' + s + '%,' + l + '%)';
        },
        hexToHSL(color) {
            const RGB = this.hexToRGB(color);
            return this.RGBToHSL(RGB.r, RGB.g, RGB.b);
        },
        hexToRgbA(hex, a){
            const RGB = this.hexToRGB(hex);

            return 'rgba(' + RGB.r + ',' + RGB.g + ',' + RGB.b + ',' + a +')';
        },
        init() {
            const seed = this.cyrb128(this.pattern);
            this.random = this.sfc32(...seed);
            this.ellipse = new Path2D();
            this.ellipse.ellipse(0.5, 0.5, 0.5, 0.5, 0, 0, Math.PI * 2);
            this.renderIdentimage();
        }
    },
    mounted() {
        this.init();
    },
    watch: {
        baseColor() {
            this.init();
        },
    },
};
</script>
<style scoped>
  canvas {
    background-color: #fff;
  }
</style>