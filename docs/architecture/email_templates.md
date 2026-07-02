# Email Template System

## Goals

The email template system allows administrators to configure reminder content while keeping variable substitution safe, predictable, and auditable.

## Template keys

Recommended stable keys:

- `reminder_stage_1`
- `reminder_stage_2`
- `reminder_stage_3`
- `final_notice`

Sites may configure fewer or more stages later, but stable keys should be preserved for upgrades and audit history.

## Template variable catalog

Allowed variables should be explicit. Recommended variables:

| Variable | Description |
| --- | --- |
| `{{user_fullname}}` | Recipient full name. |
| `{{user_firstname}}` | Recipient first name. |
| `{{site_name}}` | Moodle site full name. |
| `{{site_url}}` | Moodle site URL. |
| `{{login_url}}` | Login page URL. |
| `{{profile_url}}` | Recipient profile URL when allowed. |
| `{{last_access_date}}` | Formatted last access date or fallback text. |
| `{{inactivity_days}}` | Number of inactive days. |
| `{{reminder_stage}}` | Current reminder stage. |
| `{{final_action_date}}` | Date when account becomes deletion-ready. |
| `{{support_contact}}` | Configured support contact. |

Unsupported variables should fail validation before save or before send.

## Rendering workflow

1. `template_manager` retrieves the selected template.
2. `email_template_renderer` builds a `template_context` from user, site, and policy data.
3. Renderer validates every placeholder against `template_variable_catalog`.
4. Renderer produces `rendered_email` with subject, HTML body, and plain text.
5. `email_message_factory` maps rendered output into Moodle message fields.
6. `reminder_sender` sends via Moodle messaging API and records outcome.

## Safety and privacy

- Do not allow arbitrary PHP, JavaScript, or executable expressions in templates.
- Sanitize/format output according to Moodle output APIs.
- Do not store fully rendered message bodies in audit logs.
- Store subject snapshot only if needed for administrator audit.
- Respect user mail preferences and Moodle messaging configuration where possible.

## Defaults

Default templates should be provided by a `default_template_catalog` class or language strings during implementation. Database templates override defaults. A restore action should reset customized templates to current defaults while recording an audit entry.

## Preview

Template preview should:

- require `previewemail` or `manage` capability;
- render against a selected user or generated sample context;
- clearly mark output as preview;
- not create reminder attempt records;
- not send email.
