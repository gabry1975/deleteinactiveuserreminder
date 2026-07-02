# Services, Managers, and Repositories

## Dependency direction

```text
UI / Scheduled tasks
  -> Managers
    -> Services
      -> Repositories
        -> Moodle DB APIs
```

Dependencies must point inward toward lower-level infrastructure only through explicit constructor dependencies or Moodle dependency conventions. Business services should be straightforward to unit test with repository doubles.

## Repository layer

### `config_repository`

- Reads and writes plugin configuration through Moodle config APIs.
- Normalizes missing settings to documented defaults.
- Provides typed accessors for inactivity threshold, reminder schedule, batch size, exclusions, retention windows, and dry-run mode.

### `candidate_user_repository`

- Queries Moodle user records eligible for inactivity scanning.
- Excludes deleted users, guest users, configured auth methods, suspended users when configured, and protected system accounts.
- Reads and writes `local_dir_candidates`.
- Provides paged queries for dashboard filters and scheduled-task batches.

### `reminder_repository`

- Creates reminder attempt records.
- Finds due reminders by status and due timestamp.
- Marks reminders as sent, failed, or skipped.
- Provides historical reminder counts by candidate and stage.

### `template_repository`

- Retrieves active templates by stable key.
- Persists administrator-customized templates.
- Supports fallback to default template catalog when no database template exists.

### `audit_log_repository`

- Appends audit entries.
- Queries audit entries for dashboard display and export.
- Deletes audit entries older than configured retention.

## Service layer

### `settings_provider`

Builds an `inactivity_policy` value object from repository settings and validates it. It is the canonical source of runtime configuration.

### `inactivity_evaluator`

Determines whether a Moodle user is inactive. Inputs include last access, account creation time, manual exclusions, role/category exclusions, auth exclusions, and site policy settings. It returns a decision object with reason codes rather than a bare boolean.

### `reminder_schedule_calculator`

Calculates the next reminder stage and due timestamp. It owns rules such as:

- first reminder after configured inactivity threshold;
- subsequent reminders after configured intervals;
- final grace period before deletion-ready state;
- no reminder when user becomes active or is suppressed.

### `email_template_renderer`

Renders subject, HTML body, and plain-text body using an explicit allowlist of template variables. It validates missing variables and unsupported placeholders before sending.

### `email_message_factory`

Converts a rendered template and user record into the message object expected by Moodle's messaging API. It sets component, message name, recipient, sender, subject, HTML, plain text, and contextual URLs.

### `reminder_sender`

Sends one reminder attempt. It records attempt state, calls the messaging API, updates result state, and emits domain events. It must be idempotent for candidate/stage combinations.

### `account_deletion_preparer`

Marks candidates as deletion-ready after the final reminder window. It must not delete accounts in this sprint's architecture. Future deletion workflows should be separate and capability protected.

### `audit_writer`

Provides a small structured API for audit logging and Moodle event triggering. It maps domain actions to both plugin-owned audit rows and Moodle events where appropriate.

### `permission_checker`

Centralizes capability checks used by dashboard pages, settings forms, and future manual actions.

## Manager layer

### `reminder_manager`

Coordinates scan and send workflows.

Responsibilities:

- Process scan batches.
- Upsert candidate records.
- Recalculate candidate statuses and next reminder times.
- Process due reminders in send batches.
- Move completed final-stage candidates to `deletion_ready`.
- Enforce dry-run mode and idempotency.
- Return `processing_result` summaries for tasks and logs.

### `dashboard_manager`

Provides dashboard summary cards, filterable candidate lists, audit excerpts, and export datasets. It must not expose user records without checking capabilities.

### `settings_manager`

Validates setting combinations. Examples: final grace period must be positive, batch size must be bounded, reminder stages must be ordered, and template keys must exist for enabled stages.

### `template_manager`

Validates and saves templates, previews rendered output for a selected user, and restores defaults.

## Error handling

- Repositories wrap low-level database exceptions in `repository_exception` with safe messages.
- Template failures raise `template_render_exception` and mark reminder attempts as failed.
- Messaging failures raise or return `reminder_send_exception` details and increment failure counts.
- Managers catch expected domain exceptions per candidate, record audit errors, and continue with the next candidate.
- Scheduled tasks should fail only for systemic errors such as invalid global configuration or unavailable database access.

## Logging

Every significant state transition should produce an audit entry:

- candidate identified;
- candidate no longer inactive;
- reminder queued;
- reminder sent;
- reminder failed;
- candidate suppressed;
- deletion-ready transition;
- administrator setting or template change.

Sensitive values such as full email addresses and full rendered bodies should not be written to audit logs.
