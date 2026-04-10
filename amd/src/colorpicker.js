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
 * Color picker enhancement for mod_spinningwheel form fields.
 *
 * Populates the static color picker container with native HTML5
 * color pickers paired with text displays, synced to the actual
 * form input fields (by Moodle element ID) for data persistence.
 *
 * @module     mod_spinningwheel/colorpicker
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const init = () => {
    const container = document.getElementById('mod-spinningwheel-colorpickers');
    if (!container) {
        return;
    }

    // Moodle puts the custom 'class' attribute on the wrapper div, not
    // on the <input>. Use Moodle's auto-generated element IDs instead.
    const inputs = [];
    for (let i = 1; i <= 6; i++) {
        const el = document.getElementById('id_color' + i);
        if (el) {
            inputs.push(el);
        }
    }

    if (!inputs.length) {
        return;
    }

    inputs.forEach((formInput) => {
        const pair = document.createElement('div');
        pair.className = 'd-inline-flex align-items-center gap-1 me-1';

        const picker = document.createElement('input');
        picker.type = 'color';
        picker.className = 'mod-spinningwheel-cpicker';
        picker.value = formInput.value || '#888888';

        const display = document.createElement('input');
        display.type = 'text';
        display.className = 'form-control mod-spinningwheel-colortext';
        display.size = 7;
        display.maxLength = 7;
        display.placeholder = '#RRGGBB';
        display.value = formInput.value || '';

        // Sync: picker -> display + form field.
        picker.addEventListener('input', () => {
            const val = picker.value.toUpperCase();
            display.value = val;
            formInput.value = val;
        });

        // Sync: display -> picker + form field.
        display.addEventListener('input', () => {
            formInput.value = display.value;
            if (/^#[0-9A-Fa-f]{6}$/.test(display.value)) {
                picker.value = display.value;
            }
        });

        pair.appendChild(picker);
        pair.appendChild(display);
        container.appendChild(pair);

        // Hide the original form row.
        const row = formInput.closest('.fitem');
        if (row) {
            row.style.display = 'none';
        }
    });
};
