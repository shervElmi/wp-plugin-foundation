# Requirements Validation

The `Requirements_Validator` ensures that critical files exist **before** the Composer autoloader is loaded. If validation fails, it displays a WordPress admin notice and prevents the plugin from booting.

## Why Validate?

When a plugin is installed without running `composer install`, the `vendor/autoload.php` file will be missing. Attempting to `require` it would cause a fatal error. The validator catches this gracefully and shows an actionable admin notice instead.

## Quick Usage

```php
use Sherv\Foundation\Support\Validations\Requirements_Validator;

// Load the validator manually (autoloader isn't available yet).
if ( ! class_exists( Requirements_Validator::class ) ) {
    require_once __DIR__ . '/vendor/sherv/wp-plugin-foundation/src/Support/Validations/Requirements_Validator.php';
}

// Validate. Returns false and shows an admin notice on failure.
if ( ! Requirements_Validator::check( __FILE__ ) ) {
    return;
}

// Safe to load the autoloader now.
require_once __DIR__ . '/vendor/autoload.php';
```

The `check()` method automatically adds `vendor/autoload.php` to the required files list based on the plugin's base path.

## Static `check()` Method

```php
Requirements_Validator::check( ?string $plugin_file = null, array $required_files = [] ): bool
```

| Parameter | Description |
|---|---|
| `$plugin_file` | Path to the main plugin file (`__FILE__`). Used to resolve the base path and plugin name. |
| `$required_files` | Additional files that must be readable (beyond `vendor/autoload.php`). |

Returns `true` if all requirements are met. Returns `false` and registers an admin notice if any file is missing.

## Checking Additional Files

Pass extra files that must exist for the plugin to work:

```php
if ( ! Requirements_Validator::check( __FILE__, [
    __DIR__ . '/assets/app.js',
    __DIR__ . '/assets/app.css',
] ) ) {
    return;
}
```

## Admin Notice

When validation fails, the validator displays an error notice in the WordPress admin:

> **My Plugin failed to start.**
> - Required files are missing. Please run the following command to complete the installation: `composer install & npm install & npm run build`

The plugin name is resolved automatically from the plugin header. If the header cannot be read, a generic "Plugin" label is used.

The notice is shown on both `admin_notices` and `network_admin_notices` (for multisite).

## Using the Validator Directly

For advanced use cases, you can instantiate the validator and call `validate()` yourself:

```php
use Sherv\Foundation\Support\Validations\Requirements_Validator;

$validator = new Requirements_Validator(
    plugin_main_file: __FILE__,
    required_files: [ __DIR__ . '/config.php' ],
);

if ( ! $validator->validate() ) {
    $errors = $validator->get_error(); // WP_Error instance
    // Handle errors manually.
}
```

### Instance Methods

| Method | Description |
|---|---|
| `validate(): bool` | Run all validation checks. Returns `false` if errors exist. |
| `get_error(): WP_Error` | Get the `WP_Error` object with recorded errors. |
| `get_required_files(): array` | Get the current required files list. |
| `set_required_files( array $files ): void` | Replace the required files list (autoload is re-prepended). |

## How It Works

1. The validator resolves the plugin base path from the provided `$plugin_main_file` (or falls back to the Composer autoloader location).
2. It prepends `vendor/autoload.php` to the required files list.
3. `check_required_files()` verifies each file is readable via `is_readable()`.
4. If any file is missing, an error is added to the internal `WP_Error` object.
5. If errors exist, `print_admin_notice()` hooks into `admin_notices` to display them.

## Real-World Example

From a production plugin's main file:

```php
declare( strict_types=1 );

namespace Sherv\MyPlugin;

use Sherv\Foundation\Plugin;
use Sherv\Foundation\Support\Validations\Requirements_Validator;

const PLUGIN_FILE = __FILE__;
const PLUGIN_PATH = __DIR__ . '/';

if ( ! class_exists( Requirements_Validator::class ) ) {
    require_once PLUGIN_PATH . 'vendor/sherv/wp-plugin-foundation/src/Support/Validations/Requirements_Validator.php';
}

if ( ! Requirements_Validator::check( PLUGIN_FILE ) ) {
    return;
}

require_once PLUGIN_PATH . 'vendor/autoload.php';

// Continue with plugin setup...
```
