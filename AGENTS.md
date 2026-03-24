# AGENTS.md

## Build/Lint/Test Commands

### General Commands
- `pnpm build` - Build the project (if applicable)
- `pnpm lint` - Run linting checks
- `pnpm test` - Run all tests
- `php vendor/bin/phpunit --filter testName path/to/test/file.php` - Run single PHP test
- `pnpm test -- --testNamePattern="test name"` - Run single JS/TS test

### Testing Notes
- No PHPUnit configuration currently present in the project root
- Tests should follow Joomla! 6 testing conventions when implemented
- Use `--filter` flag to target specific test methods in PHP

## Code Style Guidelines

### PHP (Joomla! Module)
This project is an extension for Joomla 6: product filtering module. 
Before generating/refactoring the code, be sure to read the local files in "C:\OSPanel\home\mod_ishop_filter\docs",
and, if necessary, look for details in https://manual.joomla.org/docs/ for version 6 using webfetch/websearch
All PHP code follows **Joomla! coding standards** and **PSR-12** guidelines:

#### Formatting
- Use 4 spaces for indentation
- Class opening brace on new line
- Function/method opening brace on same line as declaration
- No trailing whitespace
- Blank lines between methods and logical sections

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

## Project Structure Notes
This is a **Joomla! 6 Module** (mod_ishop_filter) with:

- **Service Provider**: `/services/`
- **Dispatcher**: `/src/Dispatcher/`
- **Helpers**: `/src/Helper/`
- **View templates**: `/tmpl/`
- **Assets**: `/media/`
- **Language files**: `/language/en-GB/`, `/language/ru-RU/`
- **Configuration**: `mod_ishop_filter.xml` (extension manifest)

All PHP code must follow Joomla! coding standards with PSR-12 compliance.