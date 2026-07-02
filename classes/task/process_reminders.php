<?php
// This file is part of Moodle - https://moodle.org/.

namespace local_deleteinactiveuserreminder\task;

/**
 * Foundation scheduled task for inactive user reminders.
 *
 * @package    local_deleteinactiveuserreminder
 * @copyright  2026 Delete Inactive User Reminder contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_reminders extends \core\task\scheduled_task {
    /**
     * Return the scheduled task display name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('process_reminders:name', 'local_deleteinactiveuserreminder');
    }

    /**
     * Execute the foundation task without processing any reminders.
     */
    public function execute(): void {
        mtrace(get_string('process_reminders:message', 'local_deleteinactiveuserreminder'));
    }
}
