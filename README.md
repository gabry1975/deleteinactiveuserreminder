# Delete Inactive User Reminder

Professional Moodle local plugin under development.

## Sprint 0.2.0 installable foundation

This repository root is the Moodle local plugin directory for component `local_deleteinactiveuserreminder` and folder name `deleteinactiveuserreminder`.

The current release installs the foundation only. It provides plugin metadata, capabilities, an administrator-only placeholder page, an admin settings page, a scheduled task that safely does nothing, and a privacy provider declaring that the plugin does not yet store personal data.

Reminder scanning, email sending, dashboard statistics, templates, reports, and manual execution are intentionally not implemented yet.

## Compatibility

Target Moodle compatibility is Moodle 4.1 through Moodle 5.x.

## Installation

1. Copy or clone this repository into the Moodle local plugins directory as `local/deleteinactiveuserreminder`.
2. From the Moodle site root, run the upgrade process:
   ```bash
   php admin/cli/upgrade.php
   ```
3. Confirm the plugin appears in **Site administration > Plugins > Local plugins**.
4. Open the plugin settings page under local plugins to confirm the foundation status message.
5. Visit `/local/deleteinactiveuserreminder/index.php` as an administrator to see the placeholder page.
6. Confirm the scheduled task **Process inactive user reminders** is registered in Moodle scheduled tasks.
