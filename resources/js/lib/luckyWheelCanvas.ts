/**
 * HTML5 Canvas lucky wheel: equal slices, labels centered and rotated for readability.
 * Coordinate system: canvas default (x right, y down), angles clockwise from 3 o'clock.
 * Slice 0 starts at 12 o'clock (same as CSS conic-gradient from 0deg at top).
 */

export type LuckyWheelSegment = {
    color: string;
    label: string;
    /** If omitted, derived from segment background via luminance. */
    textColor?: string;
};

/** Middle of segment i in degrees, 0° = top, clockwise (matches CSS conic / existing game math). */
export function segmentMiddleDegCss(index: number, sliceDeg: number): number {
    return index * sliceDeg + sliceDeg / 2;
}

/**
 * Rotation (degrees) so label reads upright along the arc (not upside down).
 * Same rule as radial prize wheels: flip 180° for segments on the bottom half.
 */
export function textRotationDegReadable(midDegCss: number): number {
    const t = midDegCss + 90;

    if (midDegCss > 90 && midDegCss < 270) {
        return t + 180;
    }

    return t;
}

function luminanceIsLight(hex: string): boolean {
    const h = hex.replace('#', '').trim();

    if (h.length !== 3 && h.length !== 6) {
        return false;
    }

    const full =
        h.length === 3
            ? h
                .split('')
                .map((c) => c + c)
                .join('')
            : h;

    const n = Number.parseInt(full, 16);
    const r = (n >> 16) & 255;
    const g = (n >> 8) & 255;
    const b = n & 255;

    return (0.299 * r + 0.587 * g + 0.114 * b) / 255 > 0.72;
}

function defaultTextColor(segmentColor: string): string {
    return luminanceIsLight(segmentColor) ? '#3e2723' : '#ffffff';
}

export type DrawLuckyWheelOptions = {
    width: number;
    height: number;
    segments: LuckyWheelSegment[];
    /** Label distance from center as fraction of outer radius (default 0.42; higher = closer to rim). */
    labelRadiusRatio?: number;
    /** Outer radius as fraction of min(w,h)/2 (default 0.93) to leave margin for border. */
    outerRadiusRatio?: number;
    /** Bulb decoration on slice edges (default true). */
    drawBulbs?: boolean;
};

/**
 * Draws the full wheel. Call from a single animation frame / resize handler.
 * Uses ctx.save/restore around each slice label. Does not clear the canvas — clear before calling if needed.
 */
export function drawLuckyWheel(
    ctx: CanvasRenderingContext2D,
    options: DrawLuckyWheelOptions,
): void {
    const {
        width,
        height,
        segments,
        labelRadiusRatio = 0.42,
        outerRadiusRatio = 0.93,
        drawBulbs = true,
    } = options;

    const n = segments.length;
    const cx = width / 2;
    const cy = height / 2;
    const r = (Math.min(width, height) / 2) * outerRadiusRatio;

    if (n === 0) {
        return;
    }

    const sliceRad = (2 * Math.PI) / n;
    /** First slice starts at 12 o'clock → canvas angle -π/2. */
    const startOffsetRad = -Math.PI / 2;

    const fontSize = Math.max(
        8,
        Math.min(
            13,
            Math.floor(
                (r * 0.165) / Math.max(1, Math.sqrt(n / 7)),
            ),
        ),
    );

    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';

    for (let i = 0; i < n; i++) {
        const startRad = startOffsetRad + i * sliceRad;
        const endRad = startOffsetRad + (i + 1) * sliceRad;
        const seg = segments[i]!;

        ctx.save();
        ctx.beginPath();
        ctx.moveTo(cx, cy);
        ctx.arc(cx, cy, r, startRad, endRad);
        ctx.closePath();
        ctx.fillStyle = seg.color;
        ctx.fill();
        ctx.restore();
    }

    if (drawBulbs) {
        const bulbR = Math.max(3, r * 0.024);
        const bulbInset = r - bulbR * 2;

        for (let i = 0; i < n; i++) {
            const a = startOffsetRad + i * sliceRad;
            const bx = cx + Math.cos(a) * bulbInset;
            const by = cy + Math.sin(a) * bulbInset;

            ctx.save();
            ctx.beginPath();
            ctx.arc(bx, by, bulbR, 0, Math.PI * 2);
            ctx.fillStyle = '#ffeb3b';
            ctx.shadowColor = 'rgba(255, 235, 59, 0.95)';
            ctx.shadowBlur = 0;
            ctx.fill();
            ctx.restore();
        }
    }

    const labelR = r * labelRadiusRatio;

    for (let i = 0; i < n; i++) {
        const startRad = startOffsetRad + i * sliceRad;
        const endRad = startOffsetRad + (i + 1) * sliceRad;
        const midRad = (startRad + endRad) / 2;
        const sliceDeg = 360 / n;
        const midDegCss = segmentMiddleDegCss(i, sliceDeg);
        const seg = segments[i]!;
        const fill =
            seg.textColor ?? defaultTextColor(seg.color);

        const lx = cx + Math.cos(midRad) * labelR;
        const ly = cy + Math.sin(midRad) * labelR;
        const rotDeg = textRotationDegReadable(midDegCss);

        ctx.save();
        ctx.translate(lx, ly);
        ctx.rotate((rotDeg * Math.PI) / 180);
        ctx.font = `800 ${fontSize}px system-ui, -apple-system, sans-serif`;
        ctx.fillStyle = fill;
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;

        // Viền giúp chữ rõ hơn trên nền màu sắc khác nhau của từng ô.
        const isWhiteText = fill.toLowerCase() === '#ffffff';
        ctx.lineWidth = Math.max(1, fontSize * 0.2);
        ctx.strokeStyle = isWhiteText
            ? 'rgba(0,0,0,0.45)'
            : 'rgba(255,255,255,0.72)';
        ctx.lineJoin = 'round';
        ctx.miterLimit = 2;

        ctx.strokeText(seg.label, 0, 0);
        ctx.fillText(seg.label, 0, 0);
        ctx.restore();
    }
}
