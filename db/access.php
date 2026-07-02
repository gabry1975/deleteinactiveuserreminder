<?php
// This file is part of Moodle - https://moodle.org/.

/**
 * Capability definitions for the Delete Inactive User Reminder local plugin.
 *
 * @package    local_deleteinactiveuserreminder
 * @copyright  2026 Delete Inactive User Reminder contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/deleteinactiveuserreminder:viewdashboard' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
    'local/deleteinactiveuserreminder:manage' => [
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
];
