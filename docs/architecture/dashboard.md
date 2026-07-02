# Dashboard Architecture

## Purpose

The dashboard gives administrators an operational view of inactive-user reminder processing. It is informational and action-oriented, but destructive account deletion is outside this sprint's architecture.

## Entry points

- Main dashboard: plugin `index.php` route.
- Candidate detail: route parameter for candidate id.
- Audit log: filtered dashboard tab.
- Template preview: protected route or form action.
- Export endpoint: capability-protected CSV download.

## Dashboard components

### Summary cards

- Total active candidates.
- Reminders due now.
- Reminders sent in the last 24 hours.
- Failed reminder attempts.
- Suppressed candidates.
- Deletion-ready candidates.
- Last successful scan task time.
- Last successful send task time.

### Candidate table

Columns:

- User identity display using Moodle user picture/name APIs.
- Email availability status without exposing email unnecessarily.
- Last access.
- Inactivity duration.
- Current status.
- Reminder stage/count.
- Next reminder time.
- Last reminder time.
- Last error summary.
- Available actions based on capability.

Filters:

- Status.
- Reminder stage.
- Date range.
- Failure state.
- Suppression state.
- Search by user identity fields supported by Moodle APIs.

### Candidate detail view

- Candidate state timeline.
- Reminder attempt history.
- Audit entries for the candidate.
- Current eligibility decision and reason.
- Preview of next reminder template.
- Administrative suppression controls.

### Audit log table

- Timestamp.
- Severity.
- Event type.
- Actor.
- Affected user.
- Summary.
- Details expansion.

## Admin UI rules

- Every page must call `require_login()` and `require_capability()`.
- Output should use Moodle renderers/templates, page URL APIs, and table helpers.
- Forms should use Moodle forms APIs and sesskey validation.
- Manual actions must write audit entries and emit events.
- The dashboard must avoid loading all candidates into memory.

## Export

CSV export should be explicitly capability protected. It should include operational state but avoid unnecessary personal data. Exports should be streamed in chunks for large sites.

## Performance considerations

- Use indexed status and due-date columns for candidate lists.
- Use paginated queries.
- Avoid N+1 user lookups by joining or bulk-loading required user fields.
- Cache configuration and template catalog during one request.
- Keep dashboard summary queries aggregate-only.
