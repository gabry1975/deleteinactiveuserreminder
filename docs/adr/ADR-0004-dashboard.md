# ADR-0004: Dashboard

## Status

Accepted

## Context

Administrators need transparency into inactive-user reminder processing, including counts, failures, candidate details, and audit history. The dashboard must scale and protect personal data.

## Decision

Create a capability-protected dashboard backed by `dashboard_manager`. The dashboard reads aggregate summaries, paged candidate rows, candidate details, and audit logs from repositories. Manual actions such as suppression must go through managers and write audit entries.

## Consequences

- UI remains thin and testable.
- Administrators can diagnose failed reminders and suppression reasons.
- Dashboard queries need careful indexing and pagination.
- Export must be separately capability protected because it can expose operational user data.

## Alternatives considered

- **Rely only on Moodle scheduled-task output:** rejected because task logs are not enough for operational review.
- **Expose raw database tables:** rejected because it bypasses capabilities, formatting, and privacy controls.
