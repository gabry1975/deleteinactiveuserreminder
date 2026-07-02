# ADR-0002: Database Model

## Status

Accepted

## Context

The plugin needs to track inactive-user candidates, reminder attempts, administrator-customized templates, and audit history. Moodle core user data should remain authoritative and should not be copied unnecessarily.

## Decision

Use four plugin-owned tables in the target architecture:

- `local_dir_candidates` for current candidate workflow state.
- `local_dir_reminders` for reminder attempts and outcomes.
- `local_dir_templates` for customized email templates.
- `local_dir_audit_log` for append-only operational audit entries.

Reference Moodle users by `userid` instead of duplicating profile data. Use indexed workflow fields for scalable scheduled tasks and dashboards.

## Consequences

- Candidate state and attempt history are separated, which supports auditability and idempotency.
- Dashboard queries can use purpose-built indexes.
- Privacy provider work is required because plugin tables reference users.
- The schema leaves room for future deletion execution and notification channels.

## Alternatives considered

- **Store only scheduled task logs:** rejected because administrators need queryable state and history.
- **Duplicate user profile snapshots:** rejected to reduce privacy risk and stale data.
- **Use config only for templates:** rejected because template versioning, enablement, and audit workflows need structured records.
