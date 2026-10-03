# SCRIBE — PHP/SQLite Publishing Core

SCRIBE turns a static 2023 blog-template repository into a real server-rendered publishing system.

The original repository was named `Blog-0-with-PHP-1-SQL`, but the maintained `main` branch contained **no PHP files, no SQL files, no PDO/mysqli usage, and no database schema**. It consisted of unmodified Start Bootstrap blog/admin HTML templates, placeholder links/data, CDN dependencies, a 1.18 MB unrelated Jupyter notebook, and several multi-megabyte stock images.

SCRIBE makes the repository name truthful without inventing unnecessary framework complexity.

## Engineering identity

**SCRIBE — PHP/SQLite Publishing Core**

Focus:

- real PHP 8.3 server rendering
- SQLite through PDO
- prepared statements
- explicit schema
- published/draft boundary
- literal search semantics
- pagination
- output escaping
- security headers
- CLI-only authoring
- no exposed web admin surface

## Architecture

```text
database/schema.sql
        │
        ▼
src/Database.php
        │
        ▼
src/PostRepository.php
        ├─ create validation
        ├─ prepared INSERT
        ├─ published-only list/count
        ├─ prepared slug lookup
        ├─ LIKE wildcard escaping
        └─ bounded pagination
        │
        ├──────────────► bin/*.php
        │                CLI authoring / initialization
        │
        ▼
public/index.php + public/post.php
        │
        ▼
views/*.php
        │
        ▼
escaped server-rendered HTML
```

## Why there is no browser admin panel

The old repository included a static admin-dashboard template with login/register/password pages, but none had authentication or a server-side write path.

Rather than turning fake authentication into a larger security project, SCRIBE removes the public admin surface entirely.

Authoring occurs through local CLI commands. The browser-facing application is read-only.

That removes the need to pretend this repository securely implements:

- user accounts
- password storage
- password reset
- sessions
- CSRF-protected write forms
- role-based authorization

## Database model

`posts` contains:

- `id`
- `slug` — unique and validated
- `title`
- `excerpt`
- `body`
- `status` — `draft` or `published`
- `published_at`
- `created_at`
- `updated_at`

Public repository queries always require `status = 'published'`.

## Security

### SQL injection

All data-bearing SQL uses PDO prepared statements.

Search values are also escaped for SQL `LIKE` semantics so user-entered `%` and `_` remain literal characters instead of becoming wildcards.

### XSS

Database values are escaped through:

```php
htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
```

Article bodies are plain text. SCRIBE intentionally does not accept or render stored HTML.

### Security headers

The public entry points send:

- Content-Security-Policy
- Referrer-Policy
- X-Content-Type-Options
- X-Frame-Options
- Permissions-Policy

### Secrets

No passwords, API keys, access tokens, database credentials, or production connection strings are committed.

SQLite database files under `var/` are ignored.

## Local setup

Requirements:

- PHP 8.3+
- PDO SQLite extension

Initialize and seed:

```bash
php bin/init-db.php
php bin/seed.php
```

Run locally:

```bash
php -S 127.0.0.1:8080 -t public
```

Open:

```text
http://127.0.0.1:8080
```

## Create a post

Write the article body in a plain-text file, then run:

```bash
php bin/create-post.php \
  --title="A precise article title" \
  --slug="a-precise-article-title" \
  --excerpt="Short summary used in the public index." \
  --body-file="./article.txt" \
  --status=published
```

Omit `--status` to create a draft.

## Tests

Run:

```bash
php tests/run.php
```

The integration suite covers:

- schema creation
- slug validation
- draft visibility
- published timestamps
- published-only listing/counting
- published-only slug lookup
- injection-shaped slug rejection
- title search
- excerpt search
- literal `LIKE` percent handling
- literal `LIKE` underscore handling
- pagination bounds
- offset normalization
- unique-slug database constraint
- invalid status validation
- required title/body validation
- HTML escaping

## CI

GitHub Actions uses PHP 8.3 and validates:

1. PHP syntax
2. the integration/security test suite
3. database initialization and seed behavior

## Deployment

This project **does not use GitHub Pages** because GitHub Pages cannot execute PHP.

Deploy it to a PHP-capable environment with:

- PHP 8.3+
- PDO SQLite
- writable `var/` directory
- web document root set to `public/`

The SQLite file should remain outside the public document root.

## Modernization summary

Removed from the maintained tree:

- static Start Bootstrap Clean Blog template
- static Start Bootstrap admin dashboard
- fake login/register/password pages
- placeholder charts/tables
- fake social/contact links
- 1.18 MB unrelated Jupyter notebook
- editor-specific `.vscode` settings
- empty `text.txt`
- Bootstrap/Font Awesome/CDN dependencies
- approximately 6 MB of stock/template images
- ~400 KB generated Bootstrap CSS

Added:

- actual PHP application
- actual SQL schema
- PDO prepared statements
- SQLite persistence
- CLI publishing workflow
- security boundary documentation
- tests
- CI
- professional README

## Scope

SCRIBE is intentionally a small publishing core.

It is not presented as:

- a multi-user CMS
- an authentication system
- a WYSIWYG editor
- a comments platform
- a media manager
- a production-hosting stack

That limited scope is deliberate and testable.
