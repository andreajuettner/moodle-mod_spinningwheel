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
 * View page renderable for mod_spinningwheel.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_spinningwheel\output;

use renderable;
use templatable;
use renderer_base;
use stdClass;

class view_page implements renderable, templatable {

    private stdClass $spinningwheel;
    private array $entries;
    private bool $canspin;
    private bool $canviewhistory;
    private int $cmid;
    private int $groupid;
    private bool $isembed;

    /**
     * Constructor.
     *
     * @param stdClass $spinningwheel The spinningwheel instance record.
     * @param array $entries The active wheel entries.
     * @param bool $canspin Whether the current user can spin.
     * @param bool $canviewhistory Whether the current user can view history.
     * @param int $cmid The course module ID.
     * @param int $groupid The current group ID.
     * @param bool $isembed Whether this is rendered in the embed view.
     */
    public function __construct(
        stdClass $spinningwheel,
        array $entries,
        bool $canspin,
        bool $canviewhistory,
        int $cmid,
        int $groupid = 0,
        bool $isembed = false
    ) {
        $this->spinningwheel = $spinningwheel;
        $this->entries = $entries;
        $this->canspin = $canspin;
        $this->canviewhistory = $canviewhistory;
        $this->cmid = $cmid;
        $this->groupid = $groupid;
        $this->isembed = $isembed;
    }

    /**
     * Export the data for the mustache template.
     *
     * @param renderer_base $output The renderer.
     * @return stdClass The template data.
     */
    #[\Override]
    public function export_for_template(renderer_base $output): stdClass {
        $defaultcolors = [
            '#E8174A', '#3A86FF', '#F9C80E',
            '#1B998B', '#7B2FF7', '#FF6B35',
        ];

        $colors = $defaultcolors;
        if (!empty($this->spinningwheel->colors)) {
            $custom = array_filter(array_map('trim', explode("\n", $this->spinningwheel->colors)));
            if (!empty($custom)) {
                $colors = $custom;
            }
        }

        $displaymode = (int)($this->spinningwheel->displaymode ?? 0);
        $isparticipant = ($this->spinningwheel->entrysource == SPINNINGWHEEL_SOURCE_PARTICIPANTS);

        $entriesdata = [];
        foreach ($this->entries as $entry) {
            $entrydata = [
                'id' => $entry->id ?? 0,
                'text' => $entry->text,
                'picture' => '',
            ];

            if ($isparticipant && $displaymode > 0 && !empty($entry->userrecord)) {
                global $PAGE;
                $userpicture = new \core\output\user_picture($entry->userrecord);
                $userpicture->size = 100;
                $userpicture->link = false;
                $entrydata['picture'] = $userpicture->get_url($PAGE, $output)->out(false);
            }

            $entriesdata[] = $entrydata;
        }

        $data = new stdClass();
        $data->cmid = $this->cmid;
        $data->wheelid = $this->spinningwheel->id;
        $data->canspin = $this->canspin;
        $data->canviewhistory = $this->canviewhistory;
        $data->canclearhistory = has_capability('mod/spinningwheel:clearhistory',
            \context_module::instance($this->cmid));
        $data->hasentries = !empty($entriesdata);
        $data->activityname = format_string($this->spinningwheel->name);
        $data->showtitle = $this->isembed && !empty($this->spinningwheel->showtitle);
        $data->showshadow = !empty($this->spinningwheel->showshadow);
        $data->entriesjson = json_encode($entriesdata);
        $data->colorsjson = json_encode(array_values($colors));
        // Build celebration sound URL if configured.
        $celebratesoundurl = '';
        $celebratesound = (int)($this->spinningwheel->celebratesound ?? 1);
        if ($celebratesound > 0) {
            $soundfiles = [
                1 => 'driken5482-applause-cheer-236786.mp3',
                2 => 'freesound_community-applause-105579.mp3',
                3 => 'u_1s41v2luip-crowd-applause-113728.mp3',
            ];
            if (isset($soundfiles[$celebratesound])) {
                $celebratesoundurl = (new \moodle_url(
                    '/mod/spinningwheel/sounds/' . $soundfiles[$celebratesound]
                ))->out(false);
            }
        }

        $data->configjson = json_encode([
            'spintime' => (int)$this->spinningwheel->spintime,
            'removeafter' => (bool)$this->spinningwheel->removeafter,
            'groupid' => $this->groupid,
            'displaymode' => $displaymode,
            'showconfetti' => (bool)($this->spinningwheel->showconfetti ?? true),
            'showshadow' => (bool)($this->spinningwheel->showshadow ?? true),
            'tickingsound' => (bool)($this->spinningwheel->tickingsound ?? true),
            'winnermessage' => $this->spinningwheel->winnermessage ?? '',
            'celebratesoundurl' => $celebratesoundurl,
            'activityname' => format_string($this->spinningwheel->name),
        ]);

        return $data;
    }
}
