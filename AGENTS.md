# Flux Press - Agent Coding Guidelines

This file provides guidelines and commands for AI agents working in this WordPress theme codebase (Sage-based with Livewire 4 and Flux UI).

## Project Overview

- **Theme Name**: Flux Press
- **Stack**: WordPress + Sage (Acorn 5) + Livewire 4 + Flux UI + Tailwind CSS 4
- **DDEV**: This project runs in a Docker environment via DDEV
- **PHP**: 8.3+ required

---

## Build/Lint/Test Commands

### Frontend (npm)

```bash
# Development server with hot reload
npm run dev

# Production build
npm run build

# Translation workflow
npm run translate           # Full translation pipeline
npm run translate:pot       # Generate .pot file
npm run translate:update    # Update existing .po files
npm run translate:compile   # Compile .mo and JSON files
```

### Backend (Composer/PHP)

```bash
# Always use ddev prefix for Acorn commands
ddev wp acorn --help

# Clear caches
ddev wp acorn config:clear
ddev wp acorn view:clear
ddev wp acorn route:clear
ddev wp acorn cache:clear

# Discover packages
ddev wp acorn package:discover

# Run tests (if configured)
ddev wp acorn test

# Validate composer
composer validate --strict

# Install dependencies
composer install --prefer-dist
```

### Code Formatting (Laravel Pint)

```bash
# Format all PHP files
ddev composer pint

# Format specific file
ddev vendor/bin/pint app/Services/MyService.php

# Dry run (preview changes)
ddev vendor/bin/pint --test
```

---

## Code Style Guidelines

### General Rules

- **Indentation**: Use tabs for PHP files (4 spaces), 2 spaces for Blade/JS/CSS files (per `.editorconfig`)
- **Quotes**: Single quotes for strings in PHP
- **Trailing commas**: Use trailing commas in multi-line arrays/functions
- **Line endings**: LF (Unix-style)
- **No comments**: Write self-documenting code. Do not include unnecessary comments or commented-out code
- **Final newline**: Always insert final newline

### PHP

1. **Strict Types**: Enable strict typing at the file level where appropriate
2. **Return Types**: Always declare return types on methods
3. **Typed Properties**: Use typed properties (`public string $name`, `public array $items`)
4. **PHPDoc**: Use `@param` and `@return` annotations for complex types
   ```php
   /**
    * @param array<int,array<string,mixed>> $items
    * @return array<string,mixed>
    */
   public function process(array $items): array { ... }
   ```
5. **Namespace**: PSR-4 autoloading, `App\` prefix
6. **Constants**: Use `const` for simple values, group related constants
7. **WordPress Sanitization**: Always sanitize user input:
   - `sanitize_text_field()` for text
   - `sanitize_textarea_field()` for textareas
   - `esc_url_raw()` for URLs
   - `sanitize_key()` for keys/slugs
   - `wp_kses_post()` for HTML content

### Blade Templates (.blade.php)

1. **Translations**: All user-facing text MUST use translation functions:
   ```blade
   {{ __('Button Text', 'sage') }}
   <flux:button>{{ __('Save', 'sage') }}</flux:button>
   ```
2. **Flux UI First**: Always prefer Flux UI components over native HTML
3. **Directive Spacing**: `@if`, `@foreach`, etc. with one space after
4. **Attribute Order**: `type`, `class`, `wire:`, `x-`, `:`, `@` bindings

### JavaScript

1. **ES Modules**: Use ES module syntax (`import`/`export`)
2. **Arrow Functions**: Prefer arrow functions for callbacks
3. **Const**: Use `const` by default, `let` only when reassignment needed
4. **Null Checks**: Always check for `null`/`undefined` before accessing properties
5. **Optional Chaining**: Use `?.` for safe property access

### CSS/Tailwind

1. **Tailwind 4**: Uses `@import 'tailwindcss'` syntax
2. **Dark Mode**: Use `dark:` variant, not separate dark stylesheets
3. **Custom Colors**: Define in `@theme {}` block
4. **Responsive**: Use `sm:`, `lg:`, `xl:` breakpoints

---

## Flux UI & Livewire 4 Guidelines

### Component Usage

- **Always use Flux UI**: `<flux:input>`, `<flux:button>`, `<flux:heading>`, etc.
- **SPA Navigation**: Use `wire:navigate` for internal links
  ```blade
  <a href="/page" wire:navigate>Link</a>
  <flux:navlist.item href="/page" wire:navigate>Item</flux:navlist.item>
  ```
- **Icons**: Import with `flux:icon.*` syntax

### Livewire Components

1. **Naming**: Use kebab-case for component names
2. **Computed Properties**: Use `#[Computed]` attribute
3. **Security**: Always check permissions in `mount()` and actions
4. **Dispatching**: Use `->dispatch()` for browser events

---

## DDEV Environment Rules

**CRITICAL**: Always use `ddev` prefix for CLI commands:

```bash
# Correct
ddev wp acorn make:livewire MyComponent
ddev composer pint
ddev wp cli cache flush

# Wrong
wp acorn make:livewire
composer pint
```

---

## Internationalization (i18n)

1. **Never hardcode strings**: All UI text must use translation functions
2. **Text Domain**: Always use `'sage'` as the text domain
3. **Functions**:
   - `__('Text', 'sage')` - translate
   - `esc_attr__()` - for HTML attributes
   - `esc_html__()` - for HTML content
   - `_x('text', 'context', 'sage')` - with context

---

## File Structure

```
app/
├── Providers/          # Service providers
├── Services/           # Business logic services
├── View/Composers/     # View composers
├── Customizer/         # Customizer controls
└── WooCommerce/        # WooCommerce integration

resources/
├── views/              # Blade templates
│   ├── components/     # Blade components
│   ├── layouts/       # Layout templates
│   └── woocommerce/   # WooCommerce templates
├── css/               # Stylesheets
└── js/                # JavaScript

config/
├── app.php            # Application config
├── theme-interface.php # Theme settings
└── theme-presets/      # Preset configurations
```

---

## Error Handling

1. **WordPress Errors**: Check with `is_wp_error()` after API calls
2. **Exceptions**: Catch with `try/catch`, use `\Throwable` for all types
3. **Logging**: Use `logger()->info()` or `logger()->error()` for debugging
4. **Null Checks**: Always verify arrays/objects exist before accessing

---

## Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Classes | PascalCase | `HomeEcommerceDataService` |
| Methods | camelCase | `getSettings()`, `processItems()` |
| Properties | camelCase | `$sectionOrder`, `$heroSlides` |
| Constants | SCREAMING_SNAKE | `CACHE_TTL`, `SECTION_KEYS` |
| Views | kebab-case | `home-visual-builder.blade.php` |
| Livewire | kebab-case | `<livewire:my-component />` |

---

## Git Workflow

1. Create feature branch: `git checkout -b feature/my-feature`
2. Follow code style (run `pint` before committing)
3. Commit with clear message describing the change
4. Push and create PR

---

## Testing

- No test framework currently configured
- If adding complex logic, consider adding PHPUnit tests
- Run `composer validate --strict` before commits
