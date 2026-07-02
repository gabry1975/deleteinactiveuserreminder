<?php
// This file is part of Moodle - https://moodle.org/.

/**
 * Placeholder dashboard entry point for the Delete Inactive User Reminder plugin.
 *
 * @package    local_deleteinactiveuserreminder
 * @copyright  2026 Delete Inactive User Reminder contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/deleteinactiveuserreminder:viewdashboard', $context);

$url = new moodle_url('/local/deleteinactiveuserreminder/index.php');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'local_deleteinactiveuserreminder'));
$PAGE->set_heading(get_string('pluginname', 'local_deleteinactiveuserreminder'));

$output = $PAGE->get_renderer('core');

echo $output->header();
echo $output->heading(get_string('placeholder:title', 'local_deleteinactiveuserreminder'));
echo $output->notification(
    get_string('placeholder:message', 'local_deleteinactiveuserreminder'),
    \core\output\notification::NOTIFY_INFO
);
echo $output->footer();
