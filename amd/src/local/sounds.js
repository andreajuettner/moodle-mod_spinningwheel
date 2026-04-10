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
 * Sound effects module using Web Audio API.
 *
 * Ticking sound is generated programmatically via Web Audio API.
 * Celebration sounds are MP3 files loaded from mod/spinningwheel/sounds/.
 *
 * Celebration sound effects sourced from Pixabay (Pixabay Content License):
 *   - celebrate1: "Applause Cheer" by Driken Stan (pixabay.com/users/driken5482-45721595)
 *   - celebrate2: "Applause" by freesound_community (pixabay.com/users/freesound_community-46691455)
 *   - celebrate3: "Crowd Applause" by u_1s41v2luip (pixabay.com/users/u_1s41v2luip-28204898)
 *
 * @module     mod_spinningwheel/local/sounds
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

let audioCtx = null;
let celebrateBuffer = null;

const getContext = () => {
    if (audioCtx) {
        return audioCtx;
    }
    try {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        return audioCtx;
    } catch (e) {
        return null;
    }
};

/**
 * Initialize audio context. Must be called from a user gesture.
 */
export const init = () => {
    getContext();
};

/**
 * Preload the celebration sound file into an AudioBuffer.
 *
 * @param {string} url URL to the MP3 sound file.
 */
export const preloadCelebrate = async(url) => {
    if (!url) {
        return;
    }
    const ctx = getContext();
    if (!ctx) {
        return;
    }
    try {
        const response = await fetch(url);
        const arrayBuffer = await response.arrayBuffer();
        celebrateBuffer = await ctx.decodeAudioData(arrayBuffer);
    } catch (e) {
        celebrateBuffer = null;
    }
};

/**
 * Play a short tick sound for segment boundary crossing.
 */
export const tick = () => {
    const ctx = getContext();
    if (!ctx) {
        return;
    }
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);

    osc.frequency.value = 800;
    osc.type = 'sine';
    gain.gain.setValueAtTime(0.15, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.05);

    osc.start(ctx.currentTime);
    osc.stop(ctx.currentTime + 0.05);
};

/**
 * Play the preloaded celebration sound.
 */
export const celebrate = () => {
    const ctx = getContext();
    if (!ctx || !celebrateBuffer) {
        return;
    }
    const source = ctx.createBufferSource();
    source.buffer = celebrateBuffer;

    const gain = ctx.createGain();
    gain.gain.value = 0.7;

    source.connect(gain);
    gain.connect(ctx.destination);
    source.start(0);
};
