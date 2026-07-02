<?php
// This file is part of Moodle - https://moodle.org/.

/**
 * Admin settings for the Delete Inactive User Reminder local plugin.
 *
 * @package    local_deleteinactiveuserreminder
 * @copyright  2026 Delete Inactive User Reminder contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_deleteinactiveuserreminder',
        get_string('pluginname', 'local_deleteinactiveuserreminder')
    );

    $settings->add(new admin_setting_heading(
        'local_deleteinactiveuserreminder/foundation',
        get_string('settings:foundationheading', 'local_deleteinactiveuserreminder'),
        get_string('settings:foundationdescription', 'local_deleteinactiveuserreminder')
    ));

    $ADMIN->add('localplugins', $settings);
}
