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
 * English language strings for mod_spinningwheel.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['modulename'] = 'Spinning Wheel';
$string['modulename_help'] = 'Bring excitement and energy to your teaching with the Spinning Wheel! The animated wheel randomly selects from freely configurable entries — whether questions, topics, tasks or directly from your course participants. Complete with sound effects, confetti animation and customisable colours, every spin becomes an experience.

<strong>How to use the Spinning Wheel:</strong>
<ul>
<li><strong>Promote fair participation:</strong> Enter your participants\' names (or use the automatic participant list) and let the wheel decide who goes next. This ensures equal involvement and removes the pressure of volunteering — nobody feels singled out because "the wheel has decided". Tip: Enable "Remove after spin" so each person is only selected once.</li>
<li><strong>Distribute topics and tasks excitingly:</strong> Fill the wheel with project topics, presentation subjects or assignments and let your groups spin. This turns distribution into a fair, fun moment instead of an endless discussion. Tip: With the "Remove after spin" option, each topic is assigned only once.</li>
<li><strong>Liven up icebreakers and introductions:</strong> Start your course with creative questions on the wheel — from "What is your favourite dish?" to "What superpower would you like to have?". The element of chance makes introduction rounds more engaging and helps shy participants feel at ease, since everyone answers the same random question.</li>
</ul>';
$string['modulenameplural'] = 'Spinning Wheels';
$string['pluginname'] = 'Spinning Wheel';
$string['pluginadministration'] = 'Spinning Wheel administration';

// Capabilities.
$string['spinningwheel:addinstance'] = 'Add a new Spinning Wheel';
$string['spinningwheel:view'] = 'View Spinning Wheel';
$string['spinningwheel:spin'] = 'Spin the wheel';
$string['spinningwheel:viewhistory'] = 'View spin history';
$string['spinningwheel:clearhistory'] = 'Clear spin history';

// Form.
$string['entrysource'] = 'Entry source';
$string['entrysource_help'] = 'Choose whether to populate the wheel from enrolled course participants, manual entries, or course activities. The "Course activities" option requires the availability_spinningwheel plugin to control activity access.';
$string['entrysource_participants'] = 'Course participants';
$string['entrysource_manual'] = 'Manual entries';
$string['entrysource_activities'] = 'Course activities';
$string['entrysource_activities_notinstalled'] = '<small class="text-muted">ⓘ The "Course activities" entry source requires the <strong>availability_spinningwheel</strong> plugin. <a href="https://github.com/andreajuettner/moodle-availability_spinningwheel" target="_blank">Learn more</a></small>';
$string['rolefilter'] = 'Role filter';
$string['rolefilter_help'] = 'Only include users with the selected roles on the wheel. Leave empty to include all enrolled users.';
$string['manualentries'] = 'Manual entries';
$string['manualentries_help'] = 'Enter one name or item per line. These will appear as segments on the wheel.';
$string['removeafter'] = 'Remove after selection';
$string['removeafter_help'] = 'If enabled, selected entries are removed from the wheel for subsequent spins within this session.';
$string['spintime'] = 'Spin duration';
$string['maxspins'] = 'Maximum spins';
$string['maxspins_help'] = 'Maximum number of spins allowed per session. Set to 0 for unlimited.';
$string['allowstudentspin'] = 'Allow students to spin';
$string['allowstudentspin_help'] = 'If enabled, students with view permission can also spin the wheel.';
$string['color'] = 'Colour {$a}';
$string['colors'] = 'Segment colours';
$string['colors_help'] = 'Set up to 6 hex colours (e.g. #FF6384) for the wheel segments. Leave empty for default colours. The colours cycle through the segments.';
$string['behaviour'] = 'Behaviour';
$string['permissions'] = 'Permissions';

// View.
$string['spin'] = 'Spin!';
$string['history'] = 'Spin history';
$string['noentries'] = 'No entries available for the wheel.';
$string['result'] = 'Result: {$a}';
$string['spunby'] = 'Spun by';
$string['selectedentry'] = 'Selected';
$string['spinnedat'] = 'Time';
$string['nospinsyet'] = 'No spins recorded yet.';
$string['clearhistory'] = 'Clear history';
$string['clearhistoryconfirm'] = 'Are you sure you want to delete all spin records? This cannot be undone.';
$string['historycleared'] = 'Spin history has been cleared.';
$string['spincount'] = '{$a} spin(s)';
$string['maxspinsreached'] = 'Maximum number of spins reached.';
$string['removespins'] = 'Remove all spin records';
$string['startnow'] = 'Start now';
$string['later'] = 'Later';
$string['completed'] = '(completed)';
$string['lastactivity_notice'] = 'Last remaining activity: <strong>{$a}</strong>';
$string['allcompleted_notice'] = 'Congratulations! You have completed all activities.';
$string['pendingactivity'] = 'You must first complete \'{$a}\' before you can spin again.';
$string['pendingactivity_notice'] = 'Please complete <strong>{$a}</strong> first before spinning again.';
$string['page-mod-spinningwheel-x'] = 'Any Spinning Wheel module page';

// Completion.
$string['completionspin'] = 'Student must spin the wheel';
$string['completiondetail:spin'] = 'Spin the wheel';

// Events.
$string['eventwheel_spun'] = 'Wheel spun';

// Privacy.
$string['privacy:metadata:spinningwheel_entries'] = 'Wheel entries that reference enrolled users.';
$string['privacy:metadata:spinningwheel_entries:userid'] = 'The ID of the user listed as a wheel entry.';
$string['privacy:metadata:spinningwheel_entries:text'] = 'The display text of the entry.';
$string['privacy:metadata:spinningwheel_entries:timecreated'] = 'The time the entry was created.';
$string['privacy:metadata:spinningwheel_spins'] = 'Records of wheel spins performed by users.';
$string['privacy:metadata:spinningwheel_spins:userid'] = 'The ID of the user who spun the wheel.';
$string['privacy:metadata:spinningwheel_spins:selectedtext'] = 'The text of the selected entry.';
$string['privacy:metadata:spinningwheel_spins:selecteduserid'] = 'The ID of the user who was selected by the spin.';
$string['privacy:metadata:spinningwheel_spins:timecreated'] = 'The time the wheel was spun.';
$string['privacy:metadata:spinningwheel_spins:selectedentryid'] = 'The ID of the selected entry.';
$string['privacy:metadata:spinningwheel_spins:groupid'] = 'The group ID used when the wheel was spun.';
$string['deleteduser'] = 'Deleted User';

// Display & appearance.
$string['displaymode'] = 'Display mode';
$string['displaymode_help'] = 'Choose how participant entries appear on the wheel. Name only shows text, Name + picture shows both, Picture only shows profile pictures without names.';
$string['displaymode_name'] = 'Name only';
$string['displaymode_namepic'] = 'Name + profile picture';
$string['displaymode_pic'] = 'Profile picture only';
$string['maxvisible'] = 'Maximum visible entries';
$string['maxvisible_help'] = 'Limit how many entries appear on the wheel at once to keep it readable. Set to 0 for no limit. When limited, a random subset is shown but all entries remain eligible.';
$string['nameformat'] = 'Name format';
$string['nameformat_help'] = 'Choose how participant names are displayed on the wheel. This setting only applies when the entry source is set to course participants.';
$string['nameformat_full'] = 'Full name';
$string['nameformat_first'] = 'First name only';
$string['nameformat_last'] = 'Last name only';
$string['nameformat_firstinitial'] = 'First name + initial';
$string['showtitle'] = 'Show activity title';
$string['showtitle_help'] = 'Display the activity name above the wheel. This is mainly useful when the wheel is embedded on the course page, where Moodle\'s activity header is not shown.';
$string['showshadow'] = 'Show wheel shadow';
$string['embedoncourse'] = 'Display';
$string['embedoncourse_help'] = 'Choose how the Spinning Wheel is displayed on the course page:<br>
<strong>Automatic:</strong> Standard activity link (click opens the activity).<br>
<strong>Embed:</strong> The wheel is displayed directly on the course page.<br>
<strong>Open:</strong> Click opens the wheel full-screen in the same window (with back button).<br>
<strong>In a popup:</strong> Click opens the wheel in a small popup window.';
$string['embedoncourse_auto'] = 'Automatic';
$string['embedoncourse_embed'] = 'Embed';
$string['embedoncourse_open'] = 'Open';
$string['embedoncourse_popup'] = 'In a popup';
$string['backtocourse'] = 'Back to course';

// During spin.
$string['duringspin'] = 'During spin';
$string['tickingsound'] = 'Ticking sound during spin';
$string['tickingsound_help'] = 'When enabled, a ticking sound plays as the wheel passes each segment during the spin.';

// After spin.
$string['afterspin'] = 'After spin';
$string['showconfetti'] = 'Show confetti celebration';
$string['showconfetti_help'] = 'When enabled, a confetti animation plays after each spin result.';
$string['winnermessage'] = 'Custom winner message';
$string['winnermessage_help'] = 'Custom message displayed in the result popup after spinning. Leave empty to use the default message.';
$string['celebratesound'] = 'Celebration sound';
$string['celebratesound_help'] = 'Choose a celebration sound effect played after the spin result. Sound effects from Pixabay (Pixabay Content License).';
$string['celebratesound_off'] = 'Off';
$string['celebratesound_1'] = 'Applause & Cheer';
$string['celebratesound_2'] = 'Applause';
$string['celebratesound_3'] = 'Crowd Applause';

// Mobile.
$string['mobile:spinresult'] = 'Last spin result';
$string['mobile:taptoopen'] = 'Tap to open in browser';

// Didactic examples.
$string['spinningwheel:viewexamples'] = 'View didactic examples';
$string['examples'] = 'Didactic examples';
$string['examples_intro'] = 'The following scenarios illustrate how the Spinning Wheel can be used in teaching. Each example includes a starting situation, step-by-step implementation, didactic benefits and recommended plugin settings.';
$string['example_situation'] = 'Starting situation';
$string['example_implementation'] = 'Implementation';
$string['example_benefit'] = 'Didactic benefit';
$string['example_settings'] = 'Recommended settings';

// Example 1.
$string['example1_title'] = 'Fair participation in class discussions';
$string['example1_situation'] = 'In many classes the same learners always volunteer while others hold back. The teacher wants to encourage equal participation without singling anyone out.';
$string['example1_implementation'] = 'The Spinning Wheel is populated with all course participants. Before asking a question or assigning a task the teacher spins the wheel on the smartboard or shared screen. The selected person answers the question, solves a problem or summarises a text passage. By enabling "Remove after selection" every learner is called upon exactly once during the lesson — no hiding, but no embarrassment either, because chance decides.';
$string['example1_benefit'] = 'Random selection is perceived as fair and creates a positive sense of anticipation ("Am I next?") that increases attention. At the same time it removes the social pressure of having to raise one\'s hand.';
$string['example1_settings'] = 'Entry source: Course participants · Display: Name + profile picture · Remove after selection: enabled · Role filter: students only (exclude teachers)';

// Example 2.
$string['example2_title'] = 'Topic and task allocation for group work';
$string['example2_situation'] = 'In projects or presentations topics need to be assigned to groups or individuals. Discussions like "I don\'t want that topic!" cost time and cause frustration.';
$string['example2_implementation'] = 'The teacher enters all available topics, tasks or project areas as manual entries — e.g. "Climate change and oceans", "Renewable energy", "Rainforest biodiversity". The teacher spins the wheel on the smartboard for each group in turn. The assigned topic is removed from the wheel via "Remove after selection" so no topic is given twice. The spin history automatically documents the allocation.';
$string['example2_benefit'] = 'Random allocation is seen as neutral and impartial. There is no room for claims of favouritism. At the same time learners practise adapting to unexpected topics — an important skill for flexible working.';
$string['example2_settings'] = 'Entry source: Manual · Remove after selection: enabled · Confetti + celebration sound: enabled (turns the moment into a celebration rather than frustration)';

// Example 3.
$string['example3_title'] = 'Gamified quiz and review sessions';
$string['example3_situation'] = 'Revision phases before exams feel monotonous to many learners. Motivation drops when the teacher simply works through questions in order.';
$string['example3_implementation'] = 'Topic categories are placed on the wheel — e.g. for an English class: "Vocabulary", "Grammar", "Reading Comprehension", "Listening", "Culture". The teacher (or a student) spins the wheel and the class works on a question from the selected category. Because categories are not removed, the same topic can come up more than once — just like real learning where repetition matters.';
$string['example3_benefit'] = 'The game character boosts intrinsic motivation. The unpredictability of which category comes next keeps attention high. The teacher can add competitive elements such as points per correct answer or team contests.';
$string['example3_settings'] = 'Entry source: Manual · Remove after selection: disabled (categories remain) · Custom colours: one colour per category for recognition · Ticking sound: enabled (builds suspense)';

// Example 4.
$string['example4_title'] = 'Ice-breakers and getting-to-know-you activities';
$string['example4_situation'] = 'At the start of a course, school year or workshop participants often don\'t know each other. Traditional introduction rounds ("Say your name and a hobby") are uncreative and boring.';
$string['example4_implementation'] = 'The wheel is loaded with creative getting-to-know-you questions: "What is your most unusual talent?", "Which book influenced you the most?", "If you could time-travel — where to?", "What is the best advice you ever received?". First a person is selected via the name wheel, then that person spins the question wheel. This creates two moments of surprise that spark conversation.';
$string['example4_benefit'] = 'Unusual questions lead to more personal answers than standard introductions. The playful setting lowers inhibitions. Random selection means nobody feels singled out.';
$string['example4_settings'] = 'Two separate Spinning Wheels in the course: one with names (participants), one with questions (manual) · Confetti + celebration sound: enabled (relaxed atmosphere) · Winner message: e.g. "Your turn!"';

// Example 5.
$string['example5_title'] = 'Creative writing prompts and story starters';
$string['example5_situation'] = 'Learners stare at a blank page with no idea what to write about. Free choice of topic overwhelms some while prescribed topics feel restrictive.';
$string['example5_implementation'] = 'The wheel contains creative prompts of various kinds — opening sentences ("It was the last day before the holidays when…"), settings ("An abandoned train station"), characters ("A talking dog"), moods ("Mysterious") or objects ("An old key"). Learners spin once or several times and combine the results into a story. The more elements combined the more creative and surprising the outcomes. Variant A (shared): the teacher spins on the screen — everyone writes on the same topic. Variant B (individual): "Allow students to spin" is enabled — each learner opens the activity on their own device (tablet, laptop, Moodle app) and spins individually for a personal prompt.';
$string['example5_benefit'] = 'The random prompt overcomes "blank page" paralysis and encourages creative thinking under constraints ("constraints-based creativity"). In group work the same prompts produce very different texts — a great starting point for text comparison and peer feedback.';
$string['example5_settings'] = 'Entry source: Manual · Remove after selection: disabled (prompts can be reused) · Allow students to spin: enabled (variant B)';

// Example 6.
$string['example6_title'] = 'Reward system and positive reinforcement';
$string['example6_situation'] = 'The teacher wants to visibly reward positive behaviour, good participation or outstanding work — without relying on material rewards.';
$string['example6_implementation'] = 'The wheel contains small privileges and rewards: "5 minutes early break", "Choose your own seat", "Music during quiet work", "Homework pass for tomorrow", "You pick the next game" or "A compliment for the whole class". Learners who have shown special effort or behaviour may spin the wheel as recognition. The element of surprise amplifies the positive emotion.';
$string['example6_benefit'] = 'The Spinning Wheel combines extrinsic motivation (reward) with a playful element. The uncertainty of which reward will come makes the system more exciting than a predictable reward. Important: all entries should be positive — there are no "blanks".';
$string['example6_settings'] = 'Entry source: Manual · Confetti + celebration sound: enabled (celebratory moment) · Winner message: e.g. "Congratulations!" · Remove after selection: disabled';

// Example 7.
$string['example7_title'] = 'Random team formation';
$string['example7_situation'] = 'When learners choose their own groups the same constellations emerge. Some individuals are excluded or performance-homogeneous groups form. The teacher wants mixed teams without having to assign them manually.';
$string['example7_implementation'] = 'All course participants are on the wheel. The teacher announces: "The first three people drawn form Team A." Three consecutive spins are made — "Remove after selection" prevents anyone being drawn twice. Then Team B is drawn, Team C and so on. Profile pictures on the wheel make the process visually engaging and personal.';
$string['example7_benefit'] = 'Random group composition promotes social skills: learners must collaborate with changing partners and adjust to different working styles. The transparent random process is accepted as fair and prevents social exclusion.';
$string['example7_settings'] = 'Entry source: Course participants · Display: Name + profile picture · Remove after selection: enabled · Role filter: students only';

// Example 8.
$string['example8_title'] = 'Speaking exercises and role plays in language classes';
$string['example8_situation'] = 'Oral exercises in language classes often suffer because learners choose situations they already master and avoid more challenging scenarios. At the same time there is no motivating framework for speaking practice.';
$string['example8_implementation'] = 'The wheel contains everyday communication situations: "Order in a restaurant", "Ask for directions", "Complain about a hotel room", "Describe symptoms at the doctor\'s", "Conduct a job interview", "Small talk at a party". Two learners are selected (optionally via a second name wheel) and spontaneously act out the drawn situation as a dialogue. The ticking sound during the spin builds suspense and marks the transition to the practice phase.';
$string['example8_benefit'] = 'Random situation selection forces spontaneous adaptation to new contexts — exactly what is required in real communication. The playful setting reduces speaking anxiety. The spin history documents which situations have been practised so the teacher keeps track.';
$string['example8_settings'] = 'Entry source: Manual · Remove after selection: optional (disabled = situations can be practised multiple times) · Ticking sound: enabled';

// Example 9.
$string['example9_title'] = 'Tie-breaker for decisions';
$string['example9_situation'] = 'After a vote (e.g. on the next project topic, trip destination or presentation format) there is a tie or an endless discussion without resolution. The group is going in circles.';
$string['example9_implementation'] = 'The remaining options (e.g. "Zoo" and "Climbing park" after a 50/50 vote) are placed on the wheel. The Spinning Wheel is used as a neutral arbiter to make the final decision. The teacher explains: "We have a tie — the wheel decides." The confetti effect after the result turns a potentially frustrating moment into a celebratory occasion that everyone can get behind.';
$string['example9_benefit'] = 'The Spinning Wheel is not a replacement for democratic processes but a supplement in deadlock situations. It ends unproductive discussions in a way that is accepted as neutral. At the same time learners practise accepting outcomes that are not their first choice — an important social skill.';
$string['example9_settings'] = 'Entry source: Manual (only the tied options) · Confetti + celebration sound: enabled · Remove after selection: disabled (re-spin if needed) · Max spins: 1 · Spin duration: 5000 ms (maximum suspense)';

// Example 10.
$string['example10_title'] = 'Mobile use in on-site and hybrid teaching';
$string['example10_situation'] = 'Not every classroom has a computer with a projector. During field trips, outdoor activities or hybrid lessons the teacher needs a mobile solution that works on smartphones and tablets.';
$string['example10_implementation'] = 'The Spinning Wheel plugin works fully in the Moodle mobile app — with animation, profile pictures, sounds and confetti. The teacher opens the activity in the Moodle app on a tablet and shows the wheel to the class. Alternatively the screen can be shared via a conferencing tool. Learners can spin on their own devices via the app when "Allow students to spin" is enabled. In hybrid lessons both on-site and remote participants can see and use the wheel simultaneously via the app or browser. The spin history documents all results for follow-up.';
$string['example10_benefit'] = 'Full app support makes the Spinning Wheel location-independent — in the classroom, the schoolyard, on field trips or in remote lessons. The consistent experience across all devices (desktop, tablet, smartphone) ensures equal functionality for all participants.';
$string['example10_settings'] = 'All settings as needed for the scenario (see examples 1–9) · Tip: Clear cache and re-login in the app after changes';
