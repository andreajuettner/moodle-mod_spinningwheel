<?php
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
 * Spinning Wheel activity form definition.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/spinningwheel/lib.php');

class mod_spinningwheel_mod_form extends moodleform_mod {

    #[\Override]
    public function definition() {
        global $DB, $PAGE;

        $mform = &$this->_form;

        // General section.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        // Entry source section.
        $mform->addElement('header', 'entryhdr', get_string('entrysource', 'spinningwheel'));

        $mform->addElement('select', 'entrysource', get_string('entrysource', 'spinningwheel'), [
            SPINNINGWHEEL_SOURCE_PARTICIPANTS => get_string('entrysource_participants', 'spinningwheel'),
            SPINNINGWHEEL_SOURCE_MANUAL => get_string('entrysource_manual', 'spinningwheel'),
        ]);
        $mform->addHelpButton('entrysource', 'entrysource', 'spinningwheel');

        // Role filter (only for participant mode).
        $roles = role_get_names(context_system::instance(), ROLENAME_ORIGINAL);
        $roleoptions = [];
        foreach ($roles as $role) {
            $roleoptions[$role->id] = $role->localname;
        }
        $mform->addElement(
            'autocomplete',
            'rolefilter',
            get_string('rolefilter', 'spinningwheel'),
            $roleoptions,
            ['multiple' => true]
        );
        $mform->addHelpButton('rolefilter', 'rolefilter', 'spinningwheel');
        $mform->hideIf('rolefilter', 'entrysource', 'eq', SPINNINGWHEEL_SOURCE_MANUAL);

        // Manual entries textarea (only for manual mode).
        $mform->addElement(
            'textarea',
            'manualentries',
            get_string('manualentries', 'spinningwheel'),
            ['rows' => 10, 'cols' => 60]
        );
        $mform->addHelpButton('manualentries', 'manualentries', 'spinningwheel');
        $mform->setType('manualentries', PARAM_TEXT);
        $mform->hideIf('manualentries', 'entrysource', 'eq', SPINNINGWHEEL_SOURCE_PARTICIPANTS);

        // Behaviour section.
        $mform->addElement('header', 'behaviourhdr', get_string('behaviour', 'spinningwheel'));

        $mform->addElement('selectyesno', 'removeafter', get_string('removeafter', 'spinningwheel'));
        $mform->addHelpButton('removeafter', 'removeafter', 'spinningwheel');

        $spintimeoptions = [];
        for ($i = 1; $i <= 15; $i++) {
            $spintimeoptions[$i * 1000] = $i . 's';
        }
        $mform->addElement('select', 'spintime', get_string('spintime', 'spinningwheel'), $spintimeoptions);
        $mform->setDefault('spintime', 5000);

        $mform->addElement('text', 'maxspins', get_string('maxspins', 'spinningwheel'), ['size' => '5']);
        $mform->setType('maxspins', PARAM_INT);
        $mform->setDefault('maxspins', 0);
        $mform->addHelpButton('maxspins', 'maxspins', 'spinningwheel');

        // Permissions section.
        $mform->addElement('header', 'permissionshdr', get_string('permissions', 'spinningwheel'));

        $mform->addElement('selectyesno', 'allowstudentspin', get_string('allowstudentspin', 'spinningwheel'));
        $mform->addHelpButton('allowstudentspin', 'allowstudentspin', 'spinningwheel');

        // Appearance section.
        $mform->addElement('header', 'appearancehdr', get_string('appearance'));

        // Visual color picker container (populated by AMD JS).
        $mform->addElement(
            'static',
            'colorpickers',
            get_string('colors', 'spinningwheel'),
            '<div id="mod-spinningwheel-colorpickers" class="d-flex flex-wrap gap-2"></div>'
        );
        $mform->addHelpButton('colorpickers', 'colors', 'spinningwheel');

        // Hidden text fields for actual form data storage.
        for ($i = 1; $i <= 6; $i++) {
            $mform->addElement('text', 'color' . $i, get_string('color', 'spinningwheel', $i), [
                'size' => '7',
                'placeholder' => '#RRGGBB',
                'class' => 'mod-spinningwheel-colorfield',
            ]);
            $mform->setType('color' . $i, PARAM_TEXT);
        }
        $PAGE->requires->js_call_amd('mod_spinningwheel/colorpicker', 'init');

        $mform->addElement('select', 'displaymode', get_string('displaymode', 'spinningwheel'), [
            0 => get_string('displaymode_name', 'spinningwheel'),
            1 => get_string('displaymode_namepic', 'spinningwheel'),
            2 => get_string('displaymode_pic', 'spinningwheel'),
        ]);
        $mform->setDefault('displaymode', 0);
        $mform->addHelpButton('displaymode', 'displaymode', 'spinningwheel');
        $mform->hideIf('displaymode', 'entrysource', 'eq', SPINNINGWHEEL_SOURCE_MANUAL);

        $mform->addElement('select', 'nameformat', get_string('nameformat', 'spinningwheel'), [
            0 => get_string('nameformat_full', 'spinningwheel'),
            1 => get_string('nameformat_first', 'spinningwheel'),
            2 => get_string('nameformat_last', 'spinningwheel'),
            3 => get_string('nameformat_firstinitial', 'spinningwheel'),
        ]);
        $mform->setDefault('nameformat', 0);
        $mform->addHelpButton('nameformat', 'nameformat', 'spinningwheel');
        $mform->hideIf('nameformat', 'entrysource', 'eq', SPINNINGWHEEL_SOURCE_MANUAL);

        $mform->addElement('text', 'maxvisible', get_string('maxvisible', 'spinningwheel'), ['size' => '5']);
        $mform->setType('maxvisible', PARAM_INT);
        $mform->setDefault('maxvisible', 0);
        $mform->addHelpButton('maxvisible', 'maxvisible', 'spinningwheel');

        $mform->addElement('selectyesno', 'showtitle', get_string('showtitle', 'spinningwheel'));
        $mform->addHelpButton('showtitle', 'showtitle', 'spinningwheel');
        $mform->setDefault('showtitle', 0);

        $mform->addElement('selectyesno', 'showshadow', get_string('showshadow', 'spinningwheel'));
        $mform->setDefault('showshadow', 1);

        $mform->addElement('select', 'embedoncourse', get_string('embedoncourse', 'spinningwheel'), [
            0 => get_string('embedoncourse_auto', 'spinningwheel'),
            1 => get_string('embedoncourse_embed', 'spinningwheel'),
            2 => get_string('embedoncourse_open', 'spinningwheel'),
            3 => get_string('embedoncourse_popup', 'spinningwheel'),
        ]);
        $mform->setDefault('embedoncourse', 0);
        $mform->addHelpButton('embedoncourse', 'embedoncourse', 'spinningwheel');

        // During spin section.
        $mform->addElement('header', 'duringspinhdr', get_string('duringspin', 'spinningwheel'));

        $mform->addElement('selectyesno', 'tickingsound', get_string('tickingsound', 'spinningwheel'));
        $mform->setDefault('tickingsound', 1);
        $mform->addHelpButton('tickingsound', 'tickingsound', 'spinningwheel');

        // After spin section.
        $mform->addElement('header', 'afterspinhdr', get_string('afterspin', 'spinningwheel'));

        $mform->addElement('selectyesno', 'showconfetti', get_string('showconfetti', 'spinningwheel'));
        $mform->setDefault('showconfetti', 1);
        $mform->addHelpButton('showconfetti', 'showconfetti', 'spinningwheel');

        $mform->addElement(
            'textarea',
            'winnermessage',
            get_string('winnermessage', 'spinningwheel'),
            ['rows' => 3, 'cols' => 50]
        );
        $mform->addHelpButton('winnermessage', 'winnermessage', 'spinningwheel');
        $mform->setType('winnermessage', PARAM_TEXT);

        $mform->addElement('select', 'celebratesound', get_string('celebratesound', 'spinningwheel'), [
            0 => get_string('celebratesound_off', 'spinningwheel'),
            1 => get_string('celebratesound_1', 'spinningwheel'),
            2 => get_string('celebratesound_2', 'spinningwheel'),
            3 => get_string('celebratesound_3', 'spinningwheel'),
        ]);
        $mform->setDefault('celebratesound', 1);
        $mform->addHelpButton('celebratesound', 'celebratesound', 'spinningwheel');

        // Standard course module elements.
        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    #[\Override]
    public function data_preprocessing(&$defaultvalues) {
        global $DB;

        if (empty($this->_instance)) {
            return;
        }

        // Convert rolefilter from comma-separated to array for autocomplete.
        if (!empty($defaultvalues['rolefilter'])) {
            $defaultvalues['rolefilter'] = explode(',', $defaultvalues['rolefilter']);
        }

        // Split colors into 6 individual fields.
        if (!empty($defaultvalues['colors'])) {
            $colorlist = array_filter(array_map('trim', explode("\n", $defaultvalues['colors'])));
            $colorlist = array_values($colorlist);
            for ($i = 1; $i <= 6; $i++) {
                $defaultvalues['color' . $i] = $colorlist[$i - 1] ?? '';
            }
        }

        // Load manual entries into textarea.
        if (!empty($defaultvalues['entrysource']) && $defaultvalues['entrysource'] == SPINNINGWHEEL_SOURCE_MANUAL) {
            $entries = $DB->get_records('spinningwheel_entries', ['wheelid' => $this->_instance], 'sortorder ASC');
            $lines = [];
            foreach ($entries as $entry) {
                $lines[] = $entry->text;
            }
            $defaultvalues['manualentries'] = implode("\n", $lines);
        }
    }

    #[\Override]
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);

        // Convert rolefilter array back to comma-separated string.
        if (!empty($data->rolefilter) && is_array($data->rolefilter)) {
            $data->rolefilter = implode(',', $data->rolefilter);
        } else {
            $data->rolefilter = '';
        }

        // Merge 6 color fields back to newline-separated string.
        $colorlines = [];
        for ($i = 1; $i <= 6; $i++) {
            $field = 'color' . $i;
            if (!empty($data->$field)) {
                $val = trim($data->$field);
                if (preg_match('/^#[0-9A-Fa-f]{6}$/', $val)) {
                    $colorlines[] = $val;
                }
            }
            unset($data->$field);
        }
        $data->colors = implode("\n", $colorlines);

        if (!empty($data->completionunlocked)) {
            $suffix = $this->get_suffix();
            if (empty($data->{'completionspin' . $suffix})) {
                $data->{'completionspin' . $suffix} = 0;
            }
        }
    }

    #[\Override]
    public function add_completion_rules() {
        $mform = &$this->_form;

        $suffix = $this->get_suffix();
        $completionspinel = 'completionspin' . $suffix;
        $mform->addElement('checkbox', $completionspinel, '', get_string('completionspin', 'spinningwheel'));
        $mform->setDefault($completionspinel, 0);

        return [$completionspinel];
    }

    #[\Override]
    public function completion_rule_enabled($data) {
        $suffix = $this->get_suffix();
        return !empty($data['completionspin' . $suffix]);
    }
}
