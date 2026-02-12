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
 * Reveals the "back to course" link on the embed page.
 *
 * The link is hidden by default and only shown when the embed page is opened
 * directly in the browser, not when it is embedded as an iframe on the course page.
 *
 * @module     mod_spinningwheel/backbutton
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Show the back-to-course link when the page is not inside an iframe.
 */
export const init = () => {
    if (window.self !== window.top) {
        return;
    }
    const back = document.getElementById('spinningwheel-back');
    if (back) {
        back.classList.add('spinningwheel-back-visible');
    }
};
