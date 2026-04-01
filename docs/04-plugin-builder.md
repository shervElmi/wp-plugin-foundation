# Plugin Builder

The `Plugin_Builder` provides a fluent API for configuring a plugin instance before it is built. It is returned by `Plugin::new()` and allows you to set paths, bind plugin metadata, and register service providers.

## Creating a Builder

```php
use Sherv\Foundation\Plugin;

$builder = Plugin::new( __FILE__ );
```

Passing `__FILE__` sets the plugin base path from the main plugin file. You can also omit it, and the base path will be resolved from the Composer autoloader:

```php
$builder = Plugin::new();
```

## Builder Methods

### `with_plugin_properties()`

Reads the WordPress plugin header from the main plugin file and binds the metadata to the container. **Requires** that the plugin main file was passed to `Plugin::new()`.

```php
$plugin = Plugin::new( __FILE__ )
    ->with_plugin_properties()
    ->build();

// All plugin header values are now accessible:
$plugin->container( 'name' );        // 'My Plugin'
$plugin->container( 'version' );     // '1.0.0'
$plugin->container( 'textdomain' );  // 'my-plugin'
```

The following keys are bound to the container:

| Container Key | Source Header |
|---|---|
| `name` | Plugin Name |
| `version` | Version |
| `description` | Description |
| `uri` | Plugin URI |
| `author` | Author |
| `author_uri` | Author URI |
| `textdomain` | Text Domain |
| `domain_path` | Domain Path |
| `network` | Network |
| `requires_wp` | Requires at least |
| `requires_php` | Requires PHP |
| `requires_plugins` | Requires Plugins |
| `url` | Plugin directory URL (computed) |
| `wp.debug` | `true` when `WP_DEBUG` is enabled |
| `wp.env` | Result of `wp_get_environment_type()` |

If the plugin main file was not provided, a `Failed_Initialization_Exception` is thrown:

```php
// Throws: "The main plugin file is required for the
// Plugin_Builder::with_plugin_properties method."
Plugin::new()->with_plugin_properties();
```

### `with_providers()`

Registers service providers. By default, it also loads providers from the `providers.php` file in the providers directory.

```php
// Auto-discover providers from src/Providers/providers.php
$plugin = Plugin::new( __FILE__ )
    ->with_providers()
    ->build();
```

You can pass providers directly, skip the providers file, or combine both:

```php
use Sherv\MyPlugin\Providers\Custom_Provider;

// Only inline providers, no file discovery.
$plugin = Plugin::new( __FILE__ )
    ->with_providers( [ Custom_Provider::class ], with_providers_file: false )
    ->build();

// Both file-discovered and inline providers (merged, deduplicated).
$plugin = Plugin::new( __FILE__ )
    ->with_providers( [ Custom_Provider::class ] )
    ->build();
```

Duplicate providers are automatically removed.

### `use_providers_path()`

Overrides the default providers directory (`src/Providers/`). This affects where `with_providers()` looks for the `providers.php` file.

```php
$plugin = Plugin::new( __FILE__ )
    ->use_providers_path( __DIR__ . '/includes/Providers' )
    ->with_providers()
    ->build();

$plugin->container( 'path.providers' ); // '/path/to/plugin/includes/Providers'
```

Throws `Failed_Initialization_Exception` if the path does not exist.

### `build()`

Returns the configured `Plugin` instance. Always call this last in the builder chain.

```php
$plugin = Plugin::new( __FILE__ )
    ->with_plugin_properties()
    ->with_providers()
    ->build();
```

## Real-World Example

From the [WP Plugin Foundation Starter](https://github.com/shervElmi/wp-plugin-foundation-starter) that demonstrates a typical builder chain:

```php
namespace Sherv\MyPlugin;

use Sherv\Foundation\Plugin;

/**
 * Get the shared instance of the plugin,
 * creating it if it doesn't already exist.
 *
 * @return Plugin
 */
function get_plugin_instance(): Plugin {
    /**
     * Holds the shared instance of the plugin.
     *
     * @var Plugin|null
     */
    static $plugin;

    if ( null === $plugin ) {
        $plugin = Plugin::new( PLUGIN_FILE )
            ->with_plugin_properties()
            ->with_providers()
            ->build();
    }

    return $plugin;
}
```

The builder is typically called once and the result is cached in a static variable.
