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
 * Places the "back to wheel" button inside the activity content region.
 *
 * The button is rendered server-side (hidden) at the end of the page; this module
 * moves it to the top of the main content region, right-aligned, so it reads as
 * part of the activity rather than a floating page widget.
 *
 * @module     mod_spinningwheel/returnbutton
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Move the return button to the top of the activity content region and reveal it.
 */
export const init = () => {
    const bar = document.querySelector('.mod-spinningwheel-returnbar');
    const region = document.querySelector('#region-main');
    if (!bar || !region) {
        return;
    }
    const header = region.querySelector('.activity-header');
    if (header) {
        header.insertAdjacentElement('afterend', bar);
    } else {
        region.prepend(bar);
    }
    bar.classList.add('mod-spinningwheel-returnbar-inline');
};
