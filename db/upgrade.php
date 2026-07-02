<?php
// This file is part of Moodle - https://moodle.org/.

/**
 * Upgrade steps for the Delete Inactive User Reminder local plugin.
 *
 * @package    local_deleteinactiveuserreminder
 * @copyright  2026 Delete Inactive User Reminder contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade callback for local_deleteinactiveuserreminder.
 *
 * @param int $oldversion Previously installed plugin version.
 * @return bool
 */
function xmldb_local_deleteinactiveuserreminder_upgrade(int $oldversion): bool {
    return true;
}
