# Spinning Wheel (Glücksrad) - Moodle Activity Module

A Moodle 5+ activity module that adds an animated spinning wheel for random selection in courses. Ideal for gamification, ice-breakers, random student picks, classroom decision-making, or guided learning paths with activity unlock.

## Features

- **Animated spinning wheel** with configurable spin duration and smooth easing
- **Three entry sources:** Course participants, manual text entries, or course activities
- **Activity unlock mode:** Students spin to randomly unlock course activities one at a time
- **Customisable colours:** Up to 6 segment colours via colour picker
- **Name formats:** Full name, first name, last name, or first name + initial
- **Display modes:** Name only, name + profile picture, or picture only
- **Group and role filter:** Filter wheel entries by Moodle groups or roles
- **Remove after selection:** Optionally remove selected entries from subsequent spins
- **Sound effects:** Ticking sound during spin + celebration sounds (3 options)
- **Confetti animation** after each spin result
- **Custom winner message** displayed in result popup
- **Spin history:** Full log of all spins with timestamps
- **Embed on course page:** Display wheel directly on course page
- **Moodle mobile app support**
- **Activity completion:** Custom completion rule (student must spin)
- **Backup & restore** support
- **Privacy API** compliant (GDPR)
- **Fully localised:** English and German language packs

## Activity Unlock Mode

Students spin the wheel to decide which course activity to work on next. The selected activity gets unlocked for that student only. Completed activities appear greyed out on the wheel.

This mode requires the companion plugin **availability_spinningwheel**:
- Repository: [moodle-availability_spinningwheel](https://github.com/andreajuettner/moodle-availability_spinningwheel)

### Setup

1. Install both plugins (see Installation below)
2. Create a Spinning Wheel with entry source **"Course activities"**
3. Add the availability restriction **"Spinning Wheel unlock"** to each target activity
4. Enable **activity completion** on target activities

## Requirements

- Moodle 5.0+ (version 2025092600 or later)
- PHP 8.1+

## Installation

1. Download or clone into `mod/spinningwheel/`:
   ```bash
   cd /path/to/moodle/mod
   git clone https://github.com/andreajuettner/moodle-mod_spinningwheel.git spinningwheel
   ```
2. Optional — for activity unlock mode, install the availability condition plugin:
   ```bash
   cd /path/to/moodle/availability/condition
   git clone https://github.com/andreajuettner/moodle-availability_spinningwheel.git spinningwheel
   ```
3. Log in as site administrator
4. Navigate to **Site administration > Notifications**
5. Follow the on-screen prompts to complete the installation

## Usage

1. Turn editing on in your course
2. Click **Add an activity or resource**
3. Select **Spinning Wheel**
4. Configure entry source, colours, sounds and effects
5. Save and display — the wheel is ready to spin!

## Testing

```bash
# PHPUnit
vendor/bin/phpunit --filter mod_spinningwheel

# Behat
vendor/bin/behat --tags=@mod_spinningwheel
```

## Sound Credits

Celebration sound effects sourced from [Pixabay](https://pixabay.com/) under the [Pixabay Content License](https://pixabay.com/service/license-summary/). See `sounds/CREDITS` for detailed attribution.

## License

GNU GPL v3 or later. See [COPYING](https://www.gnu.org/licenses/gpl-3.0.html) for details.

## Copyright

2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
