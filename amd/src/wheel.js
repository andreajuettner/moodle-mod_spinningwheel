// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Wheel rendering module for mod_spinningwheel.
 *
 * Renders the spinning wheel on an HTML5 canvas with support for
 * profile pictures, radial text, and drop shadow.
 *
 * @module     mod_spinningwheel/wheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const TAU = Math.PI * 2;
const TEXT_RADIUS = 0.72;
const PIC_RADIUS_SMALL = 0.42;
const PIC_RADIUS_LARGE = 0.55;
const PIC_SIZE_SMALL = 28;
const PIC_SIZE_LARGE = 48;

/** @type {Map<string, HTMLImageElement>} */
const imageCache = new Map();

/**
 * Pre-load profile picture images for all entries.
 *
 * @param {Array} entries Array of {text, picture} objects.
 * @returns {Promise}
 */
export const preloadImages = (entries) => {
    const promises = entries
        .filter(e => e.picture)
        .map(e => {
            if (imageCache.has(e.picture)) {
                return Promise.resolve();
            }
            return new Promise((resolve) => {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = () => {
                    imageCache.set(e.picture, img);
                    resolve();
                };
                img.onerror = () => resolve();
                img.src = e.picture;
            });
        });
    return Promise.all(promises);
};

/**
 * Draw the wheel on the canvas.
 *
 * @param {HTMLCanvasElement} canvas
 * @param {Array} entries
 * @param {Array} colors
 * @param {number} rotation Current rotation in radians.
 * @param {Object} config Configuration object.
 */
export const draw = (canvas, entries, colors, rotation = 0, config = {}) => {
    const ctx = canvas.getContext('2d');
    const size = Math.min(canvas.width, canvas.height);
    const cx = size / 2;
    const cy = size / 2;
    const radius = size / 2 - 8;
    const count = entries.length;
    const displaymode = config.displaymode || 0;

    if (count === 0) {
        return;
    }

    const sliceAngle = TAU / count;

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Draw shadow beneath the wheel.
    if (config.showshadow !== false) {
        ctx.save();
        ctx.beginPath();
        ctx.arc(cx, cy + 4, radius, 0, TAU);
        ctx.fillStyle = 'rgba(0, 0, 0, 0.12)';
        ctx.filter = 'blur(8px)';
        ctx.fill();
        ctx.filter = 'none';
        ctx.restore();
    }

    ctx.save();
    ctx.translate(cx, cy);
    ctx.rotate(rotation);

    for (let i = 0; i < count; i++) {
        const startAngle = i * sliceAngle;
        const endAngle = startAngle + sliceAngle;
        const color = colors[i % colors.length];

        const isCompleted = entries[i].completed || false;

        // Draw segment.
        ctx.beginPath();
        ctx.moveTo(0, 0);
        ctx.arc(0, 0, radius, startAngle, endAngle);
        ctx.closePath();
        // Completed activities are greyed out.
        ctx.fillStyle = isCompleted ? '#cccccc' : color;
        ctx.fill();
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Draw content in segment.
        ctx.save();
        ctx.rotate(startAngle + sliceAngle / 2);

        const textColor = isCompleted ? '#666666' : getContrastColor(color);

        if (displaymode === 2 && entries[i].picture) {
            drawCircularImage(ctx, entries[i].picture, radius * PIC_RADIUS_LARGE, 0, PIC_SIZE_LARGE);
        } else if (displaymode === 1 && entries[i].picture) {
            drawCircularImage(ctx, entries[i].picture, radius * PIC_RADIUS_SMALL, 0, PIC_SIZE_SMALL);
            drawRadialText(ctx, entries[i].text, radius, count, textColor);
        } else {
            drawRadialText(ctx, entries[i].text, radius, count, textColor);
        }

        ctx.restore();
    }

    ctx.restore();

    // Outer ring.
    ctx.beginPath();
    ctx.arc(cx, cy, radius, 0, TAU);
    ctx.strokeStyle = '#dee2e6';
    ctx.lineWidth = 3;
    ctx.stroke();

    // Center circle.
    ctx.beginPath();
    ctx.arc(cx, cy, radius * 0.08, 0, TAU);
    ctx.fillStyle = '#ffffff';
    ctx.fill();
    ctx.strokeStyle = '#dee2e6';
    ctx.lineWidth = 2;
    ctx.stroke();
};

/**
 * Draw radial text along the segment bisector.
 *
 * @param {CanvasRenderingContext2D} ctx
 * @param {string} text
 * @param {number} radius
 * @param {number} count
 * @param {string} color
 */
const drawRadialText = (ctx, text, radius, count, color) => {
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillStyle = color;
    const fontSize = Math.max(10, Math.min(16, Math.floor(radius / count * 1.5)));
    ctx.font = `600 ${fontSize}px system-ui, -apple-system, sans-serif`;

    const maxWidth = radius * 0.35;
    const lines = text.split('\n');
    const xPos = radius * TEXT_RADIUS;

    if (lines.length === 1) {
        // Single line — truncate if needed.
        let line = lines[0];
        if (ctx.measureText(line).width > maxWidth) {
            while (ctx.measureText(line + '\u2026').width > maxWidth && line.length > 0) {
                line = line.slice(0, -1);
            }
            line += '\u2026';
        }
        ctx.fillText(line, xPos, 0);
    } else {
        // Multi-line — draw first line above center, second below.
        const lineHeight = fontSize * 1.2;
        let line1 = lines[0];
        if (ctx.measureText(line1).width > maxWidth) {
            while (ctx.measureText(line1 + '\u2026').width > maxWidth && line1.length > 0) {
                line1 = line1.slice(0, -1);
            }
            line1 += '\u2026';
        }
        ctx.fillText(line1, xPos, -lineHeight / 2);

        // Second line smaller and lighter.
        const smallFontSize = Math.max(8, fontSize - 3);
        ctx.font = `400 ${smallFontSize}px system-ui, -apple-system, sans-serif`;
        ctx.fillText(lines[1], xPos, lineHeight / 2);
    }
};

/**
 * Draw a circular clipped profile picture.
 *
 * @param {CanvasRenderingContext2D} ctx
 * @param {string} pictureUrl
 * @param {number} x
 * @param {number} y
 * @param {number} diameter
 */
const drawCircularImage = (ctx, pictureUrl, x, y, diameter) => {
    const img = imageCache.get(pictureUrl);
    if (!img) {
        return;
    }
    const r = diameter / 2;
    ctx.save();
    ctx.beginPath();
    ctx.arc(x, y, r, 0, TAU);
    ctx.clip();
    ctx.drawImage(img, x - r, y - r, diameter, diameter);
    ctx.restore();

    ctx.beginPath();
    ctx.arc(x, y, r, 0, TAU);
    ctx.strokeStyle = 'rgba(255,255,255,0.5)';
    ctx.lineWidth = 1.5;
    ctx.stroke();
};

/**
 * Get a contrasting text color for a given background color.
 *
 * @param {string} hexColor
 * @returns {string}
 */
const getContrastColor = (hexColor) => {
    const hex = hexColor.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance > 0.5 ? '#1d2125' : '#ffffff';
};

/**
 * Calculate which segment the pointer (top center) is pointing at.
 *
 * @param {number} rotation Current rotation in radians.
 * @param {number} count Number of segments.
 * @returns {number} Segment index.
 */
export const getSelectedIndex = (rotation, count) => {
    if (count === 0) {
        return -1;
    }
    const sliceAngle = TAU / count;
    const pointerAngle = -Math.PI / 2;
    let effective = (pointerAngle - rotation) % TAU;
    if (effective < 0) {
        effective += TAU;
    }
    return Math.floor(effective / sliceAngle) % count;
};
