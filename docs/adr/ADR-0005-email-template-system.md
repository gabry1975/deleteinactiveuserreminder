# ADR-0005: Email Template System

## Status

Accepted

## Context

Reminder emails must be configurable by administrators but safe to render and send. Templates need predictable variables, preview capability, and default restoration.

## Decision

Use a database-backed template system with stable template keys and an explicit variable catalog. Rendering is handled by `email_template_renderer`, message construction by `email_message_factory`, and sending by `reminder_sender`. Unsupported placeholders fail validation before sending.

## Consequences

- Administrators can customize content without code changes.
- Template rendering remains safe because variables are allowlisted.
- Previews can use the same rendering path as real sends.
- Future template migrations must preserve stable keys and versions.

## Alternatives considered

- **Hard-code email text only in language strings:** rejected because sites need content customization.
- **Allow arbitrary template expressions:** rejected because executable or overly dynamic templates increase security risk.
- **Store rendered email bodies in logs:** rejected to minimize personal data retention.
