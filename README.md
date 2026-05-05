# psychedcms-message-template

Editable, localized message templates for PsychedCMS.

## Why

Cross-cutting infrastructure for any kind of message-based communication (push notifications, emails, SMS, in-app messages, …) where:

- The body needs to be localized (Gedmo translatable)
- Admins need to edit the body without a deploy
- The same template body is reused across multiple sends, with placeholders substituted at runtime

## Concepts

A `MessageTemplate` is a record identified by a namespaced `key` (e.g. `notification.set_modified`, `email.welcome`). It carries a translatable `body` containing `{placeholder}` markers, plus admin metadata (`description`, `placeholders`, `category`).

`MessageTemplateRenderer::render($key, $values, $locale)` substitutes the placeholders and returns the resolved body. The package does not own the semantics of any particular placeholder — the caller computes the values it needs.

## Status

Draft / WIP — bootstrap only.
