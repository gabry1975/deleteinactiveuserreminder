# Architecture Overview

## Purpose

`local_deleteinactiveuserreminder` is a Moodle local plugin that identifies inactive user accounts, sends configurable reminder emails before deletion, exposes an administrator dashboard for review and audit, and prepares accounts for deletion according to Moodle privacy, messaging, and scheduled task conventions.

This document describes the target architecture only. It intentionally does not define PHP implementation code or XMLDB install files.

## Architectural goals

- Keep deletion-reminder rules explicit, configurable, auditable, and testable.
- Separate persistence, business rules, orchestration, and presentation concerns.
- Use Moodle core APIs for users, configuration, messaging, scheduled tasks, logging, capabilities, admin pages, and output rendering.
- Support future extension points such as multiple reminder schedules, alternative inactivity criteria, preview-only mode, dry-run mode, and notification channels beyond email.
- Ensure large Moodle sites can process inactive users incrementally without long-running database locks or excessive memory usage.

## Plugin namespace and component

- Component name: `local_deleteinactiveuserreminder`
- PHP namespace: `local_deleteinactiveuserreminder`
- Language string component: `local_deleteinactiveuserreminder`
- Event component: `local_deleteinactiveuserreminder`
- Scheduled task component: `local_deleteinactiveuserreminder`

## Proposed directory structure

The implementation should follow Moodle local plugin conventions while keeping domain logic in namespaced classes.

```text
local/deleteinactiveuserreminder/
├── amd/
│   └── src/                         # Optional dashboard JS modules.
├── classes/
│   ├── admin/                       # Admin page controllers and form orchestration.
│   ├── dashboard/                   # Dashboard query DTOs, table builders, filters.
│   ├── email/                       # Email template rendering and message composition.
│   ├── event/                       # Moodle events emitted by the plugin.
│   ├── exception/                   # Domain-specific exception classes.
│   ├── logging/                     # Logging facade for plugin audit records and Moodle logs.
│   ├── manager/                     # Application-level orchestration managers.
│   ├── privacy/                     # Moodle privacy provider.
│   ├── repository/                  # Database access classes.
│   ├── service/                     # Business services.
│   ├── task/                        # Scheduled tasks.
│   └── value_object/                # Immutable domain values and status objects.
├── db/
│   ├── access.php                   # Capabilities.
│   ├── events.php                   # Event observers if needed.
│   ├── install.xml                  # Future XMLDB, not part of this sprint.
│   ├── tasks.php                    # Scheduled task definitions.
│   └── upgrade.php                  # Future upgrade steps.
├── lang/en/local_deleteinactiveuserreminder.php
├── settings.php                     # Admin settings registration.
├── index.php                        # Dashboard entry point.
└── version.php
```

## Complete class hierarchy

The exact class names are architectural targets for later implementation.

```text
local_deleteinactiveuserreminder\repository
├── config_repository
├── candidate_user_repository
├── reminder_repository
├── audit_log_repository
└── template_repository

local_deleteinactiveuserreminder\service
├── inactivity_evaluator
├── reminder_schedule_calculator
├── reminder_sender
├── account_deletion_preparer
├── email_template_renderer
├── email_message_factory
├── settings_provider
├── permission_checker
└── audit_writer

local_deleteinactiveuserreminder\manager
├── reminder_manager
├── dashboard_manager
├── settings_manager
└── template_manager

local_deleteinactiveuserreminder\dashboard
├── filter_state
├── dashboard_summary
├── inactive_user_row
├── inactive_user_table
└── export_builder

local_deleteinactiveuserreminder\email
├── template_context
├── rendered_email
├── template_variable_catalog
└── default_template_catalog

local_deleteinactiveuserreminder\task
├── scan_inactive_users_task
├── send_reminders_task
└── cleanup_audit_logs_task

local_deleteinactiveuserreminder\logging
├── logger
└── log_context

local_deleteinactiveuserreminder\value_object
├── inactivity_policy
├── reminder_stage
├── reminder_status
├── processing_result
└── date_window

local_deleteinactiveuserreminder\exception
├── plugin_exception
├── configuration_exception
├── template_render_exception
├── reminder_send_exception
└── repository_exception

local_deleteinactiveuserreminder\event
├── candidate_identified
├── reminder_queued
├── reminder_sent
├── reminder_failed
├── candidate_suppressed
├── deletion_ready
└── settings_changed
```

## Layer responsibilities

### Repository layer

Repository classes are the only plugin classes that perform direct CRUD operations on plugin-owned tables. They may read Moodle core user tables through Moodle DB APIs, but business decisions must remain outside repositories.

### Service layer

Service classes contain focused business operations: evaluating inactivity, calculating reminder due dates, rendering templates, sending emails, and writing audit records. Services are reusable from scheduled tasks, dashboard actions, and future CLI scripts.

### Manager layer

Managers coordinate multiple services and repositories for complete use cases. Managers define transaction boundaries, batch processing, idempotency rules, and user-facing workflows.

### Admin UI layer

Admin UI classes and dashboard pages should be thin. They validate request parameters, check capabilities, call managers, and render Moodle output components. They must not contain business rules.

## Primary workflows

1. Administrator configures inactivity and reminder settings.
2. Scan task evaluates Moodle user accounts and records reminder candidates.
3. Send task finds due reminder candidates and sends emails in controlled batches.
4. Reminder state, errors, and audit entries are persisted.
5. Dashboard displays candidate state, summary metrics, last reminder dates, errors, and available administrator actions.
6. Accounts that pass the final reminder grace period are marked deletion-ready for a later deletion workflow.

## Moodle APIs to use

- `$DB` DML API for all database access.
- `$CFG`, `get_config()`, `set_config()` for plugin configuration.
- Admin settings API for settings registration.
- Scheduled task API via `\core\task\scheduled_task`.
- Messaging API via `message_send()` for email delivery where possible.
- User API helpers for profile URLs, deleted/suspended filtering, and account state checks.
- Events API for significant domain events.
- Logging APIs and plugin-owned audit table for administrator-readable audit history.
- Capability API: `require_login()`, `require_capability()`, `has_capability()`.
- Output API: renderers, templates, `moodle_url`, `html_writer`, table classes.
- Privacy API via `\core_privacy\local\metadata\provider` and related interfaces.

## Non-goals for this sprint

- No PHP class implementation.
- No XMLDB schema files.
- No JavaScript, Mustache templates, or language strings.
- No destructive deletion workflow implementation.
