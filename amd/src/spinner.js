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
 * Spinner logic and animation module for mod_spinningwheel.
 *
 * @module     mod_spinningwheel/spinner
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {draw, getSelectedIndex, preloadImages} from 'mod_spinningwheel/wheel';
import {launch as launchConfetti} from 'mod_spinningwheel/local/confetti';
import {tick as playTick, celebrate as playCelebrate, init as initSound, preloadCelebrate} from 'mod_spinningwheel/local/sounds';
import {spinWheel, getHistory, clearHistory} from 'mod_spinningwheel/local/repository';
import Templates from 'core/templates';
import Notification from 'core/notification';
import Pending from 'core/pending';
import Modal from 'core/modal';
import {getString} from 'core/str';

const TAU = Math.PI * 2;

let spinning = false;
let currentRotation = 0;

/**
 * Initialize the spinner.
 *
 * @param {number} cmid Course module ID.
 * @param {number} wheelid Wheel instance ID.
 * @param {Array} entries Array of entry objects.
 * @param {Array} colors Array of hex color strings.
 * @param {Object} config Configuration object.
 */
export const init = async(cmid, wheelid, entries, colors, config) => {
    const container = document.getElementById('mod-spinningwheel-' + cmid);
    if (!container) {
        return;
    }

    const canvas = container.querySelector('[data-region="wheel-canvas"]');
    const spinBtn = container.querySelector('[data-action="spin"]');
    const resultRegion = container.querySelector('[data-region="result"]');

    // Preload profile pictures if needed.
    if (config.displaymode > 0) {
        await preloadImages(entries);
    }

    // Handle "go to activity" links — navigate parent window and close popup/iframe.
    container.addEventListener('click', (e) => {
        const link = e.target.closest('[data-action="goto-activity"]');
        if (!link) {
            return;
        }
        e.preventDefault();
        const url = link.href;
        if (window.opener) {
            // Popup window — navigate opener and close popup.
            window.opener.location.href = url;
            window.close();
        } else if (window.self !== window.top) {
            // Iframe — navigate parent window.
            window.top.location.href = url;
        } else {
            // Normal window.
            window.location.href = url;
        }
    });

    // Initial draw.
    draw(canvas, entries, colors, currentRotation, config);

    // Responsive resize.
    const resizeObserver = new ResizeObserver(() => {
        const parent = canvas.parentElement;
        const dim = Math.min(parent.clientWidth, 500);
        canvas.width = dim;
        canvas.height = dim;
        draw(canvas, entries, colors, currentRotation, config);
    });
    resizeObserver.observe(canvas.parentElement);

    if (!spinBtn) {
        return;
    }

    spinBtn.addEventListener('click', async() => {
        if (spinning || entries.length === 0) {
            return;
        }

        // Initialize audio on first user gesture.
        if (config.tickingsound || config.celebratesoundurl) {
            initSound();
            preloadCelebrate(config.celebratesoundurl);
        }

        spinning = true;
        spinBtn.disabled = true;
        resultRegion.classList.add('visually-hidden');

        const pendingPromise = new Pending('mod_spinningwheel/spinner:spin');

        try {
            const result = await spinWheel(cmid, config.groupid || 0);

            // Match by text to avoid index mismatch between server and client arrays.
            let targetIndex = entries.findIndex(e => e.text === result.selectedtext);
            if (targetIndex === -1) {
                targetIndex = result.selectedindex;
            }

            await animateToIndex(canvas, entries, colors, targetIndex, config.spintime, config);

            // Post-spin celebration effects.
            if (config.showconfetti) {
                launchConfetti();
            }
            if (config.celebratesoundurl) {
                playCelebrate();
            }

            // Show result modal.
            const title = config.activityname || await getString('pluginname', 'mod_spinningwheel');
            const activityurl = result.activityurl || '';
            const {html} = await Templates.renderForPromise('mod_spinningwheel/result_modal', {
                selectedtext: result.selectedtext,
                pictureurl: result.pictureurl || '',
                haspicture: !!(result.pictureurl),
                winnermessage: config.winnermessage || '',
                haswinnermessage: !!(config.winnermessage),
                activityurl: activityurl,
                hasactivityurl: !!(activityurl),
            });

            const modal = await Modal.create({
                title: title,
                body: html,
                large: false,
                isVerticallyCentered: true,
            });
            modal.show();

            // Remove the backdrop so the wheel stays visible behind the modal.
            modal.getRoot().siblings('.modal-backdrop').remove();

            // "Later" button dismisses modal and reloads page to reflect availability changes.
            const dismissBtn = modal.getRoot()[0].querySelector('[data-action="dismiss"]');
            if (dismissBtn) {
                dismissBtn.addEventListener('click', () => {
                    modal.destroy();
                    window.location.reload();
                });
            }

            // Update aria result (stays visually hidden, only for screen readers).
            resultRegion.textContent = result.selectedtext;

            // Remove entry if removeafter is enabled.
            if (config.removeafter) {
                const idx = entries.findIndex(e => e.text === result.selectedtext);
                if (idx !== -1) {
                    entries.splice(idx, 1);
                    draw(canvas, entries, colors, currentRotation, config);
                }
            }

            // Update history.
            await updateHistory(container, cmid);

        } catch (err) {
            Notification.exception(err);
        }

        spinning = false;
        spinBtn.disabled = entries.length === 0;
        pendingPromise.resolve();
    });

    // Clear history button handler.
    const clearBtn = container.querySelector('[data-action="clear-history"]');
    if (clearBtn) {
        clearBtn.addEventListener('click', async() => {
            try {
                const confirmMsg = await getString('clearhistoryconfirm', 'mod_spinningwheel');
                await Notification.saveCancelPromise(
                    await getString('clearhistory', 'mod_spinningwheel'),
                    confirmMsg,
                    await getString('yes'),
                );

                await clearHistory(cmid);
                await updateHistory(container, cmid);

                const successMsg = await getString('historycleared', 'mod_spinningwheel');
                Notification.addNotification({message: successMsg, type: 'success'});
            } catch (err) {
                // User cancelled or error — ignore cancel, show errors.
                if (err && err.message) {
                    Notification.exception(err);
                }
            }
        });
    }
};

/**
 * Animate the wheel to stop at a specific segment index.
 *
 * @param {HTMLCanvasElement} canvas
 * @param {Array} entries
 * @param {Array} colors
 * @param {number} targetIndex
 * @param {number} duration Animation duration in ms.
 * @param {Object} config Configuration object.
 * @returns {Promise}
 */
const animateToIndex = (canvas, entries, colors, targetIndex, duration, config) => {
    return new Promise((resolve) => {
        const count = entries.length;
        const sliceAngle = TAU / count;

        // Normalize current rotation to avoid floating-point drift.
        currentRotation = currentRotation % TAU;
        if (currentRotation < 0) {
            currentRotation += TAU;
        }

        const targetSegmentCenter = targetIndex * sliceAngle + sliceAngle / 2;
        let targetRotation = (-Math.PI / 2 - targetSegmentCenter) % TAU;
        if (targetRotation < 0) {
            targetRotation += TAU;
        }

        let delta = targetRotation - currentRotation;
        if (delta < 0) {
            delta += TAU;
        }

        const fullSpins = 5 + Math.floor(Math.random() * 3);
        const totalRotation = fullSpins * TAU + delta;

        const startRotation = currentRotation;
        const startTime = performance.now();
        let lastSegment = -1;

        const animate = (now) => {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Cubic ease-out for realistic deceleration.
            const eased = 1 - Math.pow(1 - progress, 3);

            currentRotation = startRotation + totalRotation * eased;
            draw(canvas, entries, colors, currentRotation, config);

            // Ticking sound on segment boundary crossing.
            if (config.tickingsound) {
                const currentSegment = getSelectedIndex(currentRotation, count);
                if (currentSegment !== lastSegment && lastSegment !== -1) {
                    playTick();
                }
                lastSegment = currentSegment;
            }

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                resolve();
            }
        };

        requestAnimationFrame(animate);
    });
};

/**
 * Update the history section if visible.
 *
 * @param {HTMLElement} container
 * @param {number} cmid
 */
const updateHistory = async(container, cmid) => {
    const historyRegion = container.querySelector('[data-region="history-table"]');
    if (!historyRegion) {
        return;
    }

    try {
        const result = await getHistory(cmid);

        const {html} = await Templates.renderForPromise('mod_spinningwheel/history_table', {
            hasspins: result.spins.length > 0,
            spins: result.spins,
        });
        historyRegion.innerHTML = html;
    } catch (err) {
        // Silently fail for history update.
    }
};
