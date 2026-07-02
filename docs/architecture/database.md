# Database Architecture

## Principles

The database model records reminder workflow state, immutable audit facts, and administrator-managed email templates. Moodle core user data remains in core tables and is referenced by user id. The plugin should not duplicate user profile data except transient values needed in historical audit entries.

## Tables

### `local_dir_candidates`

Tracks the current reminder/deletion-readiness state for each inactive user candidate.

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `id` | integer primary key | yes | Surrogate key. |
| `userid` | integer foreign key reference to `user.id` | yes | Candidate Moodle user. Unique while active. |
| `status` | varchar | yes | `identified`, `reminder_due`, `reminded`, `suppressed`, `failed`, `deletion_ready`, `closed`. |
| `inactivitysince` | integer timestamp | yes | Timestamp used as the basis for inactivity. |
| `lastaccess` | integer timestamp | no | Snapshot of user last access when scanned. |
| `firstidentified` | integer timestamp | yes | First time the user was recorded as a candidate. |
| `lastscanned` | integer timestamp | yes | Last time scan evaluated this candidate. |
| `nextremindertime` | integer timestamp | no | Next reminder due time. |
| `lastremindertime` | integer timestamp | no | Last successful reminder send time. |
| `remindercount` | integer | yes | Number of successful reminders sent. |
| `failurecount` | integer | yes | Consecutive send or processing failures. |
| `lasterror` | text | no | Short diagnostic text for the latest failure. |
| `suppressionreason` | varchar | no | Reason a candidate is excluded from reminder workflow. |
| `timecreated` | integer timestamp | yes | Record creation time. |
| `timemodified` | integer timestamp | yes | Last modification time. |

Recommended indexes:

- Unique index on `userid`.
- Non-unique index on `status`.
- Non-unique compound index on `status, nextremindertime`.
- Non-unique index on `lastscanned`.
- Non-unique index on `inactivitysince`.

### `local_dir_reminders`

Stores each reminder attempt and result.

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `id` | integer primary key | yes | Surrogate key. |
| `candidateid` | integer foreign key reference to `local_dir_candidates.id` | yes | Candidate record. |
| `userid` | integer reference to `user.id` | yes | Denormalized for query convenience and historical clarity. |
| `stage` | integer | yes | Reminder stage number beginning at 1. |
| `templatekey` | varchar | yes | Template used for this attempt. |
| `status` | varchar | yes | `queued`, `sent`, `failed`, `skipped`. |
| `subjectsnapshot` | varchar | no | Subject rendered at send time for audit. |
| `recipientemailhash` | varchar | no | Optional hash of recipient email to avoid storing the address. |
| `attemptedat` | integer timestamp | yes | Attempt timestamp. |
| `sentat` | integer timestamp | no | Successful send timestamp. |
| `errorcode` | varchar | no | Machine-readable error category. |
| `errormessage` | text | no | Administrator-readable error summary. |
| `timecreated` | integer timestamp | yes | Record creation time. |
| `timemodified` | integer timestamp | yes | Last modification time. |

Recommended indexes:

- Non-unique index on `candidateid`.
- Non-unique index on `userid`.
- Non-unique compound index on `status, attemptedat`.
- Unique logical constraint to prevent duplicate successful sends for `candidateid, stage`.

### `local_dir_templates`

Stores administrator-customized email templates.

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `id` | integer primary key | yes | Surrogate key. |
| `templatekey` | varchar | yes | Stable key such as `reminder_stage_1`. |
| `name` | varchar | yes | Administrator-facing template name. |
| `subject` | text | yes | Subject template. |
| `bodyhtml` | text | yes | HTML body template. |
| `bodytext` | text | no | Plain-text body template. |
| `enabled` | integer boolean | yes | Whether this template is active. |
| `version` | integer | yes | Monotonic version for audit and future migration. |
| `timecreated` | integer timestamp | yes | Record creation time. |
| `timemodified` | integer timestamp | yes | Last modification time. |

Recommended indexes:

- Unique index on `templatekey`.
- Non-unique index on `enabled`.

### `local_dir_audit_log`

Append-only administrator-readable audit log.

| Field | Type | Required | Description |
| --- | --- | --- | --- |
| `id` | integer primary key | yes | Surrogate key. |
| `eventtype` | varchar | yes | Domain event name. |
| `userid` | integer reference to `user.id` | no | Affected Moodle user, if applicable. |
| `candidateid` | integer reference to candidate table | no | Related candidate, if applicable. |
| `actorid` | integer reference to `user.id` | no | Administrator or system actor. |
| `severity` | varchar | yes | `debug`, `info`, `warning`, `error`. |
| `summary` | varchar | yes | Short message for dashboard display. |
| `details` | text | no | JSON-encoded structured metadata. |
| `timecreated` | integer timestamp | yes | Event time. |

Recommended indexes:

- Non-unique index on `eventtype`.
- Non-unique index on `userid`.
- Non-unique index on `candidateid`.
- Non-unique compound index on `severity, timecreated`.
- Non-unique index on `timecreated`.

## Status model

Candidate status values should be centralized in a value object or constants class during implementation.

- `identified`: User meets inactivity criteria and has been recorded.
- `reminder_due`: A reminder should be sent when the send task processes the record.
- `reminded`: At least one reminder has been sent and a future action is pending.
- `suppressed`: User matched an exclusion rule or administrator suppression.
- `failed`: Processing failed and requires retry or administrator review.
- `deletion_ready`: Final reminder/grace period has elapsed.
- `closed`: User no longer needs tracking because the user became active, was deleted, or was otherwise resolved.

## Data retention

- Candidate records should be closed rather than deleted when the user becomes active, so administrators can understand why reminders stopped.
- Reminder records should be retained for a configurable retention window.
- Audit logs should support automatic cleanup via a scheduled task.
- The privacy provider must disclose all plugin-owned personal data references and support Moodle privacy export/deletion requirements where applicable.

## Transaction boundaries

- Candidate creation/update should occur in short transactions per batch or per user chunk.
- Reminder sending must not hold a database transaction while calling the mail subsystem.
- Send attempts should be recorded before and after delivery to support failure recovery.
- Idempotency checks should prevent duplicate reminders for the same candidate and stage.
