# ADR-0003: Reminder Engine

## Status

Accepted

## Context

Reminder processing must be reliable, repeatable, and safe on large sites. It must avoid duplicate reminders, continue past per-user failures, and re-check eligibility before sending.

## Decision

Implement reminder processing as manager-orchestrated scheduled workflows:

1. `scan_inactive_users_task` identifies and updates candidates.
2. `send_reminders_task` sends due reminders in batches.
3. `cleanup_audit_logs_task` enforces retention.

The `reminder_manager` coordinates `inactivity_evaluator`, `reminder_schedule_calculator`, `reminder_sender`, repositories, and audit writing. Sending must be idempotent by candidate and reminder stage.

## Consequences

- Scanning and sending can run at different frequencies.
- The plugin can process large sites incrementally.
- Failures can be recorded per candidate without aborting the whole task.
- More state transitions must be documented and tested.

## Alternatives considered

- **Send reminders directly during scanning:** rejected because scanning may be expensive and should not hold send-side work.
- **Cron-only logic without persisted candidates:** rejected because it prevents dashboard visibility and robust retry behavior.
- **Immediate deletion after final reminder:** rejected because deletion execution should be a separate explicit future capability.
