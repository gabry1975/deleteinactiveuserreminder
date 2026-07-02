# Data Flow

## High-level flow

```text
Moodle user table
  -> scan_inactive_users_task
    -> reminder_manager
      -> inactivity_evaluator
      -> candidate_user_repository
      -> reminder_schedule_calculator
      -> audit_writer

Candidate table
  -> send_reminders_task
    -> reminder_manager
      -> template_repository
      -> email_template_renderer
      -> email_message_factory
      -> reminder_sender
      -> reminder_repository
      -> audit_writer
      -> Moodle messaging API

Candidate/reminder/audit tables
  -> dashboard_manager
    -> dashboard pages and exports
```

## Scan workflow states

1. User is read from Moodle core user data.
2. User is evaluated against inactivity policy.
3. If inactive and eligible, candidate is created or updated.
4. If excluded, candidate is suppressed with a reason.
5. If previously tracked user becomes active, candidate is closed.
6. Next reminder time is calculated.
7. Audit log captures state transitions.

## Reminder workflow states

1. Candidate reaches `nextremindertime`.
2. Send task locks or claims the candidate logically for processing.
3. Eligibility is rechecked to avoid sending to recently active or deleted users.
4. Reminder attempt row is created.
5. Template is rendered.
6. Moodle messaging API sends the reminder.
7. Attempt and candidate records are updated.
8. Audit log and Moodle event are emitted.
9. Candidate is scheduled for next stage or marked deletion-ready after final grace period.

## Failure flow

- Candidate-specific failures are captured in reminder attempts, candidate `lasterror`, and audit logs.
- The task continues processing remaining candidates.
- Retry policy determines whether failed candidates become due again.
- Repeated failures move candidates to `failed` for administrator attention.
- Systemic configuration failures stop the task and create a clear task log message.

## Dashboard read flow

1. Administrator requests dashboard.
2. Page checks login and capability.
3. Request filters are converted into `filter_state`.
4. `dashboard_manager` retrieves aggregate summaries and paged rows.
5. Renderer displays data using Moodle UI conventions.
6. Manual actions call managers, update data, write audit logs, and redirect with notifications.

## Extensibility points

- Additional inactivity strategies can be added behind `inactivity_evaluator`.
- Additional notification channels can be added beside `reminder_sender` while keeping reminder attempts channel-aware in a future schema version.
- Deletion execution can be implemented as a separate manager and scheduled task that consumes `deletion_ready` candidates.
- Template variables can be extended through a catalog while preserving validation.
- Dashboard exports can be expanded without changing core workflow services.
