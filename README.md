# Spinning Wheel (Glücksrad) - Moodle Activity Module

A Moodle 5+ activity module that adds an animated spinning wheel for random selection in courses. Ideal for gamification, ice-breakers, random student picks, or classroom decision-making.

## Features

- **Animated spinning wheel** with configurable spin duration and smooth easing
- **Two entry sources:** Course participants or manual text entries
- **Customisable colours:** Up to 6 segment colours via colour picker
- **Name formats:** Full name, first name, last name, or first name + initial
- **Display modes:** Name only, name + profile picture, or picture only
- **Group support:** Filter wheel entries by Moodle groups
- **Role filter:** Limit participant entries to specific roles
- **Remove after selection:** Optionally remove selected entries from subsequent spins
- **Max spins:** Limit total number of spins per instance
- **Allow student spin:** Let students spin the wheel (not just teachers)
- **Sound effects:** Ticking sound during spin + celebration sounds (3 options)
- **Confetti animation** after each spin result
- **Custom winner message** displayed in result popup
- **Spin history:** Full log of all spins with timestamps, clear history button for teachers/managers
- **Full Moodle mobile app support:** Offscreen canvas rendering, profile pictures (base64 + SVG initials fallback), sounds, confetti, winner message, spin history with clear button
- **Activity completion:** Custom completion rule (student must spin)
- **Backup & restore** support
- **Privacy API** compliant (GDPR)
- **Fully localised:** English and German language packs included

## Requirements

- Moodle 5.0+ (version 2025092600 or later)
- PHP 8.1+

## Installation

1. Download or clone this repository into `mod/spinningwheel/` in your Moodle installation:
   ```bash
   cd /path/to/moodle/mod
   git clone https://github.com/andreajuettner/moodle-mod_spinningwheel.git spinningwheel
   ```
2. Log in as site administrator
3. Navigate to **Site administration > Notifications**
4. Follow the on-screen prompts to complete the installation

## Usage

1. Turn editing on in your course
2. Click **Add an activity or resource**
3. Select **Spinning Wheel**
4. Configure the wheel:
   - Choose **Entry source** (participants or manual entries)
   - Set colours, spin duration, display mode
   - Configure sound and visual effects
5. Save and display - the wheel is ready to spin!

## Configuration Options

| Setting | Description |
|---------|-------------|
| Entry source | Course participants or manual text entries |
| Role filter | Limit to specific roles (participants mode) |
| Name format | Full name / first name / last name / first + initial |
| Display mode | Name only / name + picture / picture only |
| Max visible | Limit entries shown on wheel (0 = unlimited) |
| Remove after | Remove selected entries from subsequent spins |
| Spin duration | Animation duration in milliseconds |
| Max spins | Maximum total spins (0 = unlimited) |
| Allow student spin | Let students spin the wheel |
| Segment colours | Up to 6 custom hex colours |
| Ticking sound | Sound during spin animation |
| Celebration sound | Sound after result (3 options) |
| Confetti | Confetti animation after spin |
| Winner message | Custom popup message |
| Completion | Require student to spin for completion |

## Capabilities

| Capability | Default roles |
|-----------|---------------|
| `mod/spinningwheel:addinstance` | Manager, Editing teacher |
| `mod/spinningwheel:view` | Student, Teacher, Editing teacher, Manager |
| `mod/spinningwheel:spin` | Editing teacher, Manager |
| `mod/spinningwheel:viewhistory` | Teacher, Editing teacher, Manager |
| `mod/spinningwheel:manageentries` | Editing teacher, Manager |
| `mod/spinningwheel:clearhistory` | Editing teacher, Manager |

## External API

Four web service functions are available for AJAX and mobile app integration:

- `mod_spinningwheel_spin_wheel` - Spin the wheel and get result
- `mod_spinningwheel_get_entries` - Get current wheel entries
- `mod_spinningwheel_get_history` - Get spin history
- `mod_spinningwheel_clear_history` - Clear all spin history

## Moodle Mobile App

The plugin has full support for the Moodle mobile app via the site plugin API (`CoreCourseModuleDelegate`). All features work natively in the app:

- **Wheel rendering:** Offscreen canvas with `toDataURL()` for Angular-compatible image binding
- **Profile pictures:** Base64-embedded from file storage (custom pictures) or SVG initials circles (fallback)
- **Spin animation:** Smooth rotation with ticking sound via Web Audio API
- **Celebration effects:** Confetti animation and applause sounds (base64-embedded MP3)
- **Result popup:** Overlay with winner message on the wheel
- **Spin history:** Collapsible accordion with clear history button (`core-site-plugins-call-ws`)

No additional configuration is needed — the mobile app detects the plugin automatically after cache purge and re-login.

## Testing

### PHPUnit (46 tests)

```bash
vendor/bin/phpunit mod/spinningwheel/tests/lib_test.php
vendor/bin/phpunit mod/spinningwheel/tests/external_test.php
vendor/bin/phpunit mod/spinningwheel/tests/privacy/provider_test.php
```

### Behat (9 scenarios)

```bash
vendor/bin/behat --tags=@mod_spinningwheel
```

## Sound Credits

Celebration sound effects sourced from [Pixabay](https://pixabay.com/) under the [Pixabay Content License](https://pixabay.com/service/license-summary/). See `sounds/CREDITS` for detailed attribution.

## License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

See [COPYING](https://www.gnu.org/licenses/gpl-3.0.html) for details.

## Copyright

2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
