<?php
// This file is part of Moodle - https://moodle.org/.

namespace local_deleteinactiveuserreminder\privacy;

/**
 * Privacy provider for the Delete Inactive User Reminder local plugin.
 *
 * @package    local_deleteinactiveuserreminder
 * @copyright  2026 Delete Inactive User Reminder contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Explain why the plugin stores no personal data in this foundation release.
     *
     * @return string
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
