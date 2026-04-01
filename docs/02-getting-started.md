# Getting Started

This guide walks you through setting up a WordPress plugin with WP Plugin Foundation, from a minimal setup to a production-ready structure.

## Minimal Setup

The simplest way to create a plugin, without providers or plugin properties:

```php
use Sherv\Foundation\Plugin;

$plugin = Plugin::new()->build();
```

This gives you a `Plugin` instance with a DI container, automatic path bindings, and lifecycle support. The base path is resolved automatically from the Composer autoloader.

## Recommended Setup

For most plugins, use the full builder chain in your main plugin file:

```php
// my-plugin.php
use Sherv\Foundation\Plugin;

$plugin = Plugin::new( __FILE__ )
    ->with_plugin_properties()
    ->with_providers()
    ->build();

register_activation_hook( __FILE__, [ $plugin, 'on_plugin_activation' ] );
register_deactivation_hook( __FILE__, [ $plugin, 'on_plugin_deactivation' ] );

add_action( 'plugins_loaded', [ $plugin, 'boot' ] );
```

| Method | Purpose |
|---|---|
| `Plugin::new( __FILE__ )` | Creates a `Plugin_Builder`. Passing `__FILE__` sets the plugin base path. |
| `with_plugin_properties()` | Reads the plugin header and binds metadata (`name`, `version`, `url`, etc.) to the container. |
| `with_providers()` | Discovers and registers service providers from `src/Providers/providers.php`. |
| `build()` | Returns the configured `Plugin` instance. |

## Production-Ready Setup

For a production-ready starting point, see the [WP Plugin Foundation Starter](https://github.com/shervElmi/wp-plugin-foundation-starter). It validates the environment before loading, uses a singleton accessor, and separates lifecycle hooks. Below is a complete main plugin file:

```php
<?php
/**
 * Plugin Name: My Plugin
 * Plugin URI:  https://example.com/my-plugin
 * Description: A WordPress plugin built with WP Plugin Foundation.
 * Version:     1.0.0
 * Requires at least: 6.6
 * Requires PHP: 8.2
 * Author:      Your Name
 * Author URI:  https://example.com
 * Text Domain: my-plugin
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 */

declare( strict_types=1 );

namespace Sherv\MyPlugin;

use Sherv\Foundation\Plugin;
use Sherv\Foundation\Support\Validations\Requirements_Validator;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const PLUGIN_FILE = __FILE__;
const PLUGIN_PATH = __DIR__ . '/';

// Validate requirements before loading the autoloader (since it also checks autoloader is available).
if ( ! class_exists( Requirements_Validator::class ) ) {
    require_once PLUGIN_PATH . 'vendor/sherv/wp-plugin-foundation/src/Support/Validations/Requirements_Validator.php';
}

// Returns false and shows an admin notice on failure.
if ( ! Requirements_Validator::check( PLUGIN_FILE ) ) {
    return;
}

require_once PLUGIN_PATH . 'vendor/autoload.php';

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

/**
 * Get the plugin instance or resolve a container entry.
 *
 * @param string|null $id   The entry ID or class name.
 * @param array       $with Parameters to pass when resolving the entry.
 * @return mixed
 */
function plugin( ?string $id = null, array $with = [] ): mixed {
    return is_null( $id )
        ? get_plugin_instance()
        : get_plugin_instance()->container( $id, $with );
}

/**
 * Handle plugin activation.
 *
 * @param bool $network_wide Whether the plugin is activated network-wide.
 */
function activate( bool $network_wide = false ): void {
    get_plugin_instance()->on_plugin_activation( $network_wide );

    /**
     * Fires when the plugin is activated.
     *
     * @param bool $network_wide Whether to activate network-wide.
     */
    do_action( 'my_plugin_activation', $network_wide );
}

register_activation_hook( PLUGIN_FILE, __NAMESPACE__ . '\activate' );

/**
 * Handle plugin deactivation.
 *
 * @param bool $network_wide Whether the plugin is deactivated network-wide.
 */
function deactivate( bool $network_wide = false ): void {
    get_plugin_instance()->on_plugin_deactivation( $network_wide );

    /**
     * Fires when the plugin is deactivated.
     *
     * @param bool $network_wide Whether to deactivate network-wide.
     */
    do_action( 'my_plugin_deactivation', $network_wide );
}

register_deactivation_hook( PLUGIN_FILE, __NAMESPACE__ . '\deactivate' );

/**
 * Bootstrap the plugin.
 *
 * @return void
 */
function bootstrap_plugin(): void {
    add_action( 'init', static fn() => load_plugin_textdomain( plugin( 'textdomain' ) ) );

    plugin()->boot();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\bootstrap_plugin' );
```

### What Each Step Does

1. **Requirements validation**: Checks that `vendor/autoload.php` exists _before_ trying to load it. If missing, an admin notice is displayed and the plugin stops.
2. **Autoloader**: Loads Composer dependencies.
3. **Singleton accessor**: Ensures only one `Plugin` instance exists.
4. **Helper function**: `plugin()` returns the Plugin instance, `plugin( 'version' )` resolves a container entry. Usable from any file after `plugins_loaded`.
5. **Activation**: Delegates to providers that implement `Plugin_Activation_Aware`.
6. **Deactivation**: Delegates to providers that implement `Plugin_Deactivation_Aware`.
7. **Bootstrap**: Loads text domain and boots all registered service providers.

## Directory Structure

A typical plugin using WP Plugin Foundation follows this layout:

```
my-plugin/
├── my-plugin.php              # Main plugin file (plugin header + bootstrap)
├── composer.json
├── src/
│   └── Providers/
│       ├── providers.php      # Provider auto-discovery list
│       └── Example_Service_Provider.php
```

The default path bindings match this structure:

| Container Key | Default Value | Description |
|---|---|---|
| `path` | Plugin root directory | `$plugin->base_path()` |
| `path.providers` | `src/Providers/` | Where `providers.php` is loaded from |

## Next Steps

- **[Plugin Builder](./04-plugin-builder.md)**: Customize paths and plugin properties.
- **[Service Providers](./05-service-providers.md)**: Create and register providers.
- **[Plugin Lifecycle](./06-plugin-lifecycle.md)**: Handle activation, deactivation, and boot.
- **[Container](./07-container.md)**: Access the DI container and resolve services.
