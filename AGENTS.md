# AGENTS.md

## Project Overview

This is a Joomla! 6 module for product filtering (mod_ishop_filter).
Main stack: PHP 8.3+, Joomla 6, with optional JavaScript frontend.

## Architecture / Project structure

- `services/` — Service providers and dependency injection binding.
- `src/Dispatcher/` — Module dispatcher.
- `src/Helper/` — Helper classes containing business logic.
- `tmpl/` — View templates for the module layout.
- `media/` — Static assets (JavaScript, CSS, images).
- `language/en-GB/`, `language/ru-RU/` — Language files for English and Russian.
- `mod_ishop_filter.xml` — Extension manifest and configuration.

## Build/Lint/Test Commands

### General Commands

- `pnpm build` - Build the project (if applicable)
- `pnpm lint` - Run linting checks
- `pnpm test` - Run all tests
- `php vendor/bin/phpunit --filter testName path/to/test/file.php` - Run single PHP test
- `pnpm test -- --testNamePattern="test name"` - Run single JS/TS test

### Testing

- **Run full test suite**: `pnpm test`
- **Run single PHP test**: `php vendor/bin/phpunit --filter testName path/to/test/file.php`
- **Run single JS/TS test**: `pnpm test -- --testNamePattern="test name"`
- **Before committing**: run at least lint and tests: `pnpm lint && pnpm test`
- **Notes**:
  - No PHPUnit configuration currently present in the project root
  - Tests should follow Joomla! 6 testing conventions when implemented
  - Use `--filter` flag to target specific test methods in PHP

## Code Style Guidelines

### PHP (Joomla! Module)

This project is an extension for Joomla 6: product filtering module.
Before generating/refactoring the code, be sure to read docs https://manual.joomla.org/docs/ for version 6 using webfetch/websearch
All PHP code follows **Joomla! coding standards** and **PSR-12** guidelines:

#### Formatting

- Use 4 spaces for indentation
- Class opening brace on new line
- Function/method opening brace on same line as declaration
- No trailing whitespace
- Blank lines between methods and logical sections

#### Good example:

```php
class MyClass extends \Joomla\CMS\Module\Module
{
    protected function hello()
    {
        return 'Hello';
    }
}
```

#### Bad example:

```php
class MyClass extends \Joomla\CMS\Module\Module{protected function hello(){return'Hello';}}
```

### JavaScript/TypeScript (Frontend)

When implementing frontend JS:

#### Module System

- ES6 module syntax with explicit imports
- Use Joomla! web asset manager for script loading

#### Formatting

- 2 spaces for indentation (consistent with Prettier)
- Single quotes for strings
- Semicolons required

#### Naming Conventions

- Variables/Functions: camelCase (e.g., `prepareFilter()`, `getAjax`)
- Classes: PascalCase
- Constants: UPPER_CASE

#### Good example:

```javascript
import { Component } from '@joomla/component';

export class PrepareFilter {
    static const MAX_ITEMS = 100;

    getAjax() {
        // ...
    }
}
```

#### Bad example:

```javascript
import{Component}from'@joomla/component';exportclassPrepareFilter{staticconstMAX_ITEMS=100;getAjax(){}}
```

## AI Coding Agents Guidelines

Always:

- Respect existing Joomla 6 architecture and module structure.
- Read docs before making significant changes to business logic.
- Maintain compatibility with Joomla 6 and the existing module API.
- Write meaningful commit messages and comments when appropriate.

Ask first:

- Before adding new Composer/npm/pnpm dependencies.
- Before changing database structure, environment configurations, or CI/CD.
- Before performing large refactoring that touches multiple layers (dispatcher, helpers, templates).

Never:

- Commit secrets, passwords, or API keys.
- Edit vendor/ or core Joomla files.
- Break backward compatibility without explicit instruction.

## Documentation & References

Before generating or refactoring code, the agent must:
- If necessary, consult the official manual at https://manual.joomla.org/ for Joomla 6.

## Frontend Testing

The frontend is accessible at https://magazin-gefest-new.local

- The development server is always running and requires no additional setup
- Use this URL for manual testing of interface changes
- Responsive design should be tested across device size
