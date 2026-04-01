# Plugin Lifecycle

WP Plugin Foundation manages three lifecycle phases: **activation**, **deactivation**, and **booting**. Each phase delegates to service providers that opt in via interfaces or methods.

## Activation

When WordPress activates your plugin, it fires the activation hook. Connect it to the plugin instance in your main plugin file:

```php
register_activation_hook( PLUGIN_FILE, [ $plugin, 'on_plugin_activation' ] );
```

Or with the namespaced function pattern:

```php
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
```

`on_plugin_activation()` iterates through all registered providers and calls `on_plugin_activation()` on every provider that implements `Plugin_Activation_Aware`.

### Making a Provider Activation-Aware

Implement the `Plugin_Activation_Aware` interface:

```php
use Sherv\Foundation\Contracts\Plugin\Plugin_Activation_Aware;
use Sherv\Foundation\Support\Service_Provider;

/**
 * Sets up and bootstraps the plugin routes.
 */
final class Route_Service_Provider extends Service_Provider implements Plugin_Activation_Aware {

    /**
     * {@inheritDoc}
     */
    public function register(): void {
        $this->container->singleton( Page_Controller::class );
    }

    /**
     * {@inheritDoc}
     */
    public function on_plugin_activation( bool $network_wide ): void {
        $this->container->make( Page_Controller::class )->add_rewrite_rules();

        flush_rewrite_rules();
    }
}
```

Common activation tasks:

- Creating database tables
- Setting default options
- Flushing rewrite rules
- Scheduling cron events

## Deactivation

Connect the deactivation hook similarly:

```php
register_deactivation_hook( PLUGIN_FILE, [ $plugin, 'on_plugin_deactivation' ] );
```

Or with the namespaced function pattern:

```php
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
```

### Making a Provider Deactivation-Aware

Implement the `Plugin_Deactivation_Aware` interface:

```php
use Sherv\Foundation\Contracts\Plugin\Plugin_Deactivation_Aware;
use Sherv\Foundation\Support\Service_Provider;

/**
 * Registers and bootstraps plugin-wide services and dependencies.
 */
final class Plugin_Service_Provider extends Service_Provider implements Plugin_Deactivation_Aware {

    /**
     * {@inheritDoc}
     */
    public function register(): void {
        $this->container->singleton( Remove_Transients::class );
    }

    /**
     * {@inheritDoc}
     */
    public function on_plugin_deactivation( bool $network_wide ): void {
        $this->container->make( Remove_Transients::class )->flush( $network_wide );
    }
}
```

Common deactivation tasks:

- Flushing rewrite rules
- Clearing transients and caches
- Unscheduling cron events

### Combining Both Interfaces

A single provider can handle both activation and deactivation:

```php
use Sherv\Foundation\Contracts\Plugin\Plugin_Activation_Aware;
use Sherv\Foundation\Contracts\Plugin\Plugin_Deactivation_Aware;
use Sherv\Foundation\Support\Service_Provider;

/**
 * Sets up and bootstraps the plugin routes.
 */
final class Route_Service_Provider extends Service_Provider
    implements Plugin_Activation_Aware, Plugin_Deactivation_Aware {

    /**
     * {@inheritDoc}
     */
    public function on_plugin_activation( bool $network_wide ): void {
        $this->container->make( Page_Controller::class )->add_rewrite_rules();
        flush_rewrite_rules();
    }

    /**
     * {@inheritDoc}
     */
    public function on_plugin_deactivation( bool $network_wide ): void {
        flush_rewrite_rules();
    }
}
```

## Booting

Booting should happen on `plugins_loaded` so all WordPress plugins are available:

```php
add_action( 'plugins_loaded', [ $plugin, 'boot' ] );
```

Or with the namespaced function pattern:

```php
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

### What `boot()` Does

1. Checks if the plugin is already booted. If so, returns immediately (no-op).
2. Iterates through all registered providers.
3. Calls `boot()` on each provider that defines a `boot()` method.
4. Sets `$plugin->is_booted()` to `true`.

### Boot State

```php
$plugin->is_booted(); // false before boot(), true after.
```

### Late Registration

If a provider is registered **after** the plugin has already booted, its `boot()` method is called immediately during registration. This ensures providers work correctly regardless of when they are registered.

## Lifecycle Order

```
1. Plugin::new()           → Plugin + builder created
2. with_providers()        → Providers registered (register() called)
3. build()                 → Plugin instance returned
4. Activation hook         → on_plugin_activation() on aware providers
5. plugins_loaded          → boot() on all providers
6. Deactivation hook       → on_plugin_deactivation() on aware providers
```

The `register()` → `boot()` ordering guarantees that all container bindings from every provider are available before any provider's `boot()` method runs.

## Environment and Debug

The `Plugin` instance provides environment detection methods:

```php
$plugin->is_debug_mode_enabled(); // true when WP_DEBUG is enabled
$plugin->detect_environment();    // 'production', 'staging', 'development', etc.
```

When `with_plugin_properties()` is used, these are also bound to the container:

```php
$plugin->container( 'wp.debug' ); // bool
$plugin->container( 'wp.env' );   // string
```
