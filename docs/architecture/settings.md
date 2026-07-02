# Settings Architecture

## Admin settings page

The plugin should register settings under Moodle site administration for local plugins. The settings page should be accessible only to administrators with the plugin management capability.

## Capabilities

Recommended capabilities:

| Capability | Purpose |
| --- | --- |
| `local/deleteinactiveuserreminder:viewdashboard` | View dashboard, candidate state, and audit summaries. |
| `local/deleteinactiveuserreminder:manage` | Change plugin settings and templates. |
| `local/deleteinactiveuserreminder:previewemail` | Preview rendered email templates. |
| `local/deleteinactiveuserreminder:export` | Export dashboard/audit data. |
| `local/deleteinactiveuserreminder:suppresscandidate` | Manually suppress or unsuppress candidates. |

## Configuration groups

### General

- Enable plugin.
- Dry-run mode.
- Batch size.
- Time zone/display preferences for dashboard dates.

### Inactivity policy

- Inactivity threshold in days.
- Last-access fallback for never-logged-in users.
- Include or exclude suspended users.
- Excluded authentication methods.
- Excluded cohorts, roles, or user profile conditions.
- Protected user ids or usernames.

### Reminder policy

- Number of reminder stages.
- Interval between stages.
- Final grace period after last reminder.
- Maximum retry count for failed sends.
- Retry delay.
- Sender identity strategy: support user, noreply, or configured sender.

### Email templates

- Template enabled/disabled status by stage.
- Subject, HTML body, and plain-text body.
- Preview selected template with sample user.
- Restore default template.

### Dashboard and audit

- Audit retention days.
- Reminder history retention days.
- Default dashboard page size.
- Export enablement.

## Validation rules

- Batch size must be greater than zero and below a safe maximum.
- Inactivity threshold must be greater than final grace and reminder intervals where policy requires it.
- Reminder stages must be ordered and non-negative.
- At least one enabled template must exist when reminders are enabled.
- Template variables must be from the approved variable catalog.
- Dry-run mode should visibly warn administrators that no messages will be sent.

## Settings access pattern

Runtime code should not read raw config values throughout the codebase. Instead:

1. `config_repository` reads Moodle config.
2. `settings_provider` validates and maps values into `inactivity_policy`.
3. Services receive typed policy objects.
4. Managers report configuration errors clearly in task output and dashboard warnings.
