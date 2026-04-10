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
 * Confetti celebration effect using Canvas API.
 *
 * Creates a temporary overlay canvas with falling confetti particles.
 * No external dependencies required.
 *
 * @module     mod_spinningwheel/local/confetti
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const PARTICLE_COUNT = 150;
const DURATION = 3000;
const GRAVITY = 0.12;
const COLORS = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#7BC67E', '#FF6B6B'];

/**
 * Launch a confetti burst animation.
 */
export const launch = () => {
    const canvas = document.createElement('canvas');
    canvas.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;' +
        'pointer-events:none;z-index:10000;';
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    document.body.appendChild(canvas);

    const ctx = canvas.getContext('2d');

    const particles = [];
    for (let i = 0; i < PARTICLE_COUNT; i++) {
        particles.push({
            x: canvas.width * Math.random(),
            y: canvas.height * Math.random() * -0.5 - 20,
            vx: (Math.random() - 0.5) * 6,
            vy: Math.random() * 3 + 2,
            w: Math.random() * 8 + 4,
            h: Math.random() * 6 + 2,
            color: COLORS[Math.floor(Math.random() * COLORS.length)],
            rotation: Math.random() * Math.PI * 2,
            rotationSpeed: (Math.random() - 0.5) * 0.2,
        });
    }

    const startTime = performance.now();

    const animate = (now) => {
        const elapsed = now - startTime;
        const progress = elapsed / DURATION;

        if (progress >= 1) {
            canvas.remove();
            return;
        }

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        const fadeStart = 0.7;
        const globalAlpha = progress > fadeStart ? 1 - ((progress - fadeStart) / (1 - fadeStart)) : 1;

        particles.forEach(p => {
            p.vy += GRAVITY;
            p.x += p.vx;
            p.y += p.vy;
            p.rotation += p.rotationSpeed;

            ctx.save();
            ctx.globalAlpha = globalAlpha;
            ctx.translate(p.x, p.y);
            ctx.rotate(p.rotation);
            ctx.fillStyle = p.color;
            ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
            ctx.restore();
        });

        requestAnimationFrame(animate);
    };

    requestAnimationFrame(animate);
};
