# ADR-0001: Plugin Structure

## Status

Accepted

## Context

The plugin must follow Moodle local plugin conventions while remaining maintainable as reminder, dashboard, template, logging, and future deletion features grow. A flat script-heavy plugin would be difficult to test and extend.

## Decision

Use Moodle's `local_deleteinactiveuserreminder` component with namespaced classes under `classes/`. Organize classes by architectural role: repositories, services, managers, scheduled tasks, dashboard, email, logging, events, exceptions, privacy, and value objects.

The intended dependency direction is:

```text
UI and scheduled tasks -> managers -> services -> repositories -> Moodle APIs
```

## Consequences

- Business logic is isolated from Moodle page scripts.
- Scheduled tasks and dashboard actions can share managers and services.
- Unit testing is easier because services can depend on repository interfaces or simple collaborators.
- More files are created than in a minimal plugin, but the organization reduces long-term coupling.

## Alternatives considered

- **Single procedural implementation:** rejected because reminder workflow and dashboard behavior require clear state management and testability.
- **One large service class:** rejected because email rendering, inactivity evaluation, scheduling, and persistence have separate reasons to change.
