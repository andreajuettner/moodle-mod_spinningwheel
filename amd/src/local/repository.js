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
 * Repository module for mod_spinningwheel AJAX calls.
 *
 * @module     mod_spinningwheel/local/repository
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call as fetchMany} from 'core/ajax';

/**
 * Spin the wheel.
 *
 * @param {number} cmid Course module ID.
 * @param {number} groupid Group ID.
 * @returns {Promise}
 */
export const spinWheel = (cmid, groupid = 0) =>
    fetchMany([{methodname: 'mod_spinningwheel_spin_wheel', args: {cmid, groupid}}])[0];

/**
 * Get active entries.
 *
 * @param {number} cmid Course module ID.
 * @param {number} groupid Group ID.
 * @returns {Promise}
 */
export const getEntries = (cmid, groupid = 0) =>
    fetchMany([{methodname: 'mod_spinningwheel_get_entries', args: {cmid, groupid}}])[0];

/**
 * Get spin history.
 *
 * @param {number} cmid Course module ID.
 * @returns {Promise}
 */
export const getHistory = (cmid) =>
    fetchMany([{methodname: 'mod_spinningwheel_get_history', args: {cmid}}])[0];

/**
 * Clear spin history.
 *
 * @param {number} cmid Course module ID.
 * @returns {Promise}
 */
export const clearHistory = (cmid) =>
    fetchMany([{methodname: 'mod_spinningwheel_clear_history', args: {cmid}}])[0];
