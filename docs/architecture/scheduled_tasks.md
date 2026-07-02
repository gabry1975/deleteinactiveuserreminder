# Scheduled Tasks

## Task classes

### `local_deleteinactiveuserreminder\task\scan_inactive_users_task`

Purpose: discover inactive users and maintain candidate state.

Recommended frequency: daily, with administrator override.

Workflow:

1. Load and validate settings through `settings_provider`.
2. Query candidate users in pages using `candidate_user_repository`.
3. Evaluate each user through `inactivity_evaluator`.
4. Upsert candidate state and calculate next reminder through `reminder_schedule_calculator`.
5. Suppress or close candidates that are no longer eligible.
6. Write audit entries and emit events for state transitions.
7. Return a processing summary for task output.

### `local_deleteinactiveuserreminder\task\send_reminders_task`

Purpose: send due reminder emails and advance reminder workflow.

Recommended frequency: hourly or several times per day depending on site size.

Workflow:

1. Load settings and check whether sending is enabled.
2. Fetch due candidates by `status` and `nextremindertime`.
3. For each candidate, re-check eligibility before sending.
4. Render the configured template for the current stage.
5. Send through `reminder_sender`.
6. Update candidate reminder counts and next due time.
7. Mark candidates `deletion_ready` after final grace period.
8. Record failures without stopping the whole batch.

### `local_deleteinactiveuserreminder\task\cleanup_audit_logs_task`

Purpose: remove old audit and reminder attempt records according to retention settings.

Recommended frequency: weekly.

Workflow:

1. Load retention settings.
2. Delete audit rows older than retention threshold in bounded chunks.
3. Optionally remove old reminder attempt rows for closed candidates.
4. Log cleanup counts.

## Batch processing

- All tasks must honor a configurable maximum batch size.
- Tasks should use stable pagination by id or timestamp, not offset pagination for large mutable datasets.
- Long tasks should periodically check time limits where Moodle APIs support it.
- Each candidate should be processed independently so one bad record does not stop the batch.

## Idempotency

- Scan task can safely re-run because it upserts candidate state.
- Send task must check that a successful reminder for the candidate/stage does not already exist.
- A failed send can be retried according to configured retry policy.
- State transitions should compare previous status with new status before writing audit entries.

## Task output

Each task should print concise summaries suitable for Moodle scheduled-task logs:

- records scanned;
- candidates created;
- candidates updated;
- reminders sent;
- reminders failed;
- candidates suppressed;
- candidates marked deletion-ready;
- elapsed time.
