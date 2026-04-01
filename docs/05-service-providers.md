# Service Providers

Service providers are the central place to organize your plugin's bindings, hooks, and boot logic. Each provider encapsulates a single feature or concern.

## Creating a Provider

### Extending the Base Class (Recommended)

Extend `Sherv\Foundation\Support\Service_Provider` for automatic container injection and default `register()` / `boot()` methods:

```php
declare( strict_types=1 );

namespace Sherv\MyPlugin\Providers;

use Sherv\Foundation\Support\Service_Provider;

/**
 * Registers and bootstraps plugin-wide services and dependencies.
 */
final class Plugin_Service_Provider extends Service_Provider {

    /**
     * {@inheritDoc}
     */
    public function register(): void {
        // Bind services to the container.
    }

    /**
     * {@inheritDoc}
     */
    public function boot(): void {
        // Register WordPress hooks, filters, etc.
    }
}
```

The base class provides:

- `$this->container`: The DI container instance, injected via the constructor.
- `register()`: Called when the provider is registered (before boot). Use it to bind services.
- `boot()`: Called when the plugin boots (on `plugins_loaded`). Use it to register hooks.

### Implementing the Interface Directly

For providers that don't need the base class, implement the `Service_Provider` interface directly:

```php
use Sherv\Foundation\Contracts\Provider\Service_Provider;

final class Minimal_Provider implements Service_Provider {
    // No required methods. Service_Provider is a marker interface.
    // Optionally add register() and/or boot() methods.
}
```

When using the interface directly, the `Provider_Factory` still injects the container via the constructor:

```php
use Sherv\Container\Contracts\Container;
use Sherv\Foundation\Contracts\Provider\Service_Provider;

/**
 * A custom service provider with manual container injection.
 */
final class Custom_Provider implements Service_Provider {

    /**
     * Create a new custom provider instance.
     *
     * @param Container $container The container instance.
     */
    public function __construct( protected Container $container ) {
    }

    /**
     * Register bindings into the container.
     *
     * @return void
     */
    public function register(): void {
        $this->container->bind( 'greeting', 'Hello, World!' );
    }
}
```

## The `register()` Method

Use `register()` to bind services into the container. This method is called **before** any provider's `boot()` method, so all bindings are available when boot runs.

```php
use Sherv\MyPlugin\Services\Cache_Manager;
use Sherv\MyPlugin\Services\Api_Client;
use Sherv\MyPlugin\Support\Remote_Request;
use Sherv\Foundation\Support\Service_Provider;

/**
 * Registers and bootstraps plugin-wide services and dependencies.
 */
final class Plugin_Service_Provider extends Service_Provider {

    /**
     * {@inheritDoc}
     */
    public function register(): void {
        $this->container->singleton( Remote_Request::class );
        $this->container->singleton( Api_Client::class );
        $this->container->singleton( Cache_Manager::class );
    }
}
```

## The `boot()` Method

Use `boot()` to register WordPress actions, filters, and any logic that depends on bindings being available.

```php
use Sherv\MyPlugin\Http\Controllers\Page_Controller;
use Sherv\MyPlugin\Http\Controllers\Rest_Controller;
use Sherv\Foundation\Support\Service_Provider;

/**
 * Sets up and bootstraps the plugin routes.
 */
final class Route_Service_Provider extends Service_Provider {

    /**
     * {@inheritDoc}
     */
    public function register(): void {
        $this->container->singleton( Rest_Controller::class );
        $this->container->singleton( Page_Controller::class );
    }

    /**
     * {@inheritDoc}
     */
    public function boot(): void {
        $controller = $this->container->make( Page_Controller::class );

        add_action( 'init', [ $controller, 'add_rewrite_rules' ] );
        add_action( 'template_redirect', [ $controller, 'handle_redirect' ] );

        add_action(
            'rest_api_init',
            [ $this->container->make( Rest_Controller::class ), 'register_routes' ]
        );
    }
}
```

## Declarative Bindings with Constants

Instead of writing `register()` logic, you can declare bindings and singletons as class constants. The `Provider_Registry` reads these automatically during registration.

### `BINDINGS`

Each entry is passed to `$container->bind()`:

```php
use Sherv\Foundation\Contracts\Provider\Service_Provider;

final class App_Service_Provider implements Service_Provider {

    public const BINDINGS = [
        'logger' => \Monolog\Logger::class,
    ];
}
```

### `SINGLETONS`

Each entry is passed to `$container->singleton()`:

```php
use Sherv\MyPlugin\Services\Cache_Manager;
use Sherv\MyPlugin\Support\Remote_Request;
use Sherv\Foundation\Contracts\Provider\Service_Provider;

final class App_Service_Provider implements Service_Provider {

    public const SINGLETONS = [
        Remote_Request::class,
        Cache_Manager::class,
    ];
}
```

With numeric keys, the class name is used as both the ID and the entry. With string keys, the key is the ID:

```php
public const SINGLETONS = [
    'http.client'  => Remote_Request::class,   // bound as 'http.client'
    Cache_Manager::class,                       // bound as Cache_Manager::class
];
```

You can combine constants with `register()`. Both are processed.

## Accessing Container Properties

When `with_plugin_properties()` is used in the builder, plugin metadata is available as array offsets on the container:

```php
/**
 * Handles plugin asset loading and management.
 */
final class Asset_Service_Provider extends Service_Provider {

    /**
     * {@inheritDoc}
     */
    public function boot(): void {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Enqueue plugin scripts and styles.
     *
     * @return void
     */
    public function enqueue_scripts(): void {
        wp_enqueue_script(
            'my-plugin-app',
            $this->container['url'] . 'assets/app.js',
            [],
            $this->container['version'],
            true
        );

        wp_set_script_translations(
            'my-plugin-app',
            $this->container['textdomain']
        );
    }
}
```

## Auto-Discovery

Place a `providers.php` file in the providers directory (default: `src/Providers/`). It should return an array of provider class names:

```php
// src/Providers/providers.php

return [
    Sherv\MyPlugin\Providers\Asset_Service_Provider::class,
    Sherv\MyPlugin\Providers\Block_Service_Provider::class,
    Sherv\MyPlugin\Providers\Plugin_Service_Provider::class,
    Sherv\MyPlugin\Providers\Route_Service_Provider::class,
];
```

Then call `with_providers()` on the builder:

```php
$plugin = Plugin::new( __FILE__ )
    ->with_providers()
    ->build();
```

Non-existent classes in the providers file are silently filtered out.

## Force Re-Registration

By default, registering a provider that is already registered returns the existing instance. Pass `force: true` to re-register:

```php
$registry = $plugin->container( Provider_Registry::class );
$registry->register( My_Provider::class, force: true );
```

This calls `register()` again and, if the plugin is already booted, also calls `boot()`.

## Real-World Example: Block Registration

From a plugin that registers custom Gutenberg blocks:

```php
declare( strict_types=1 );

namespace Sherv\MyPlugin\Providers;

use Sherv\MyPlugin\Services\Block\Block;
use Sherv\MyPlugin\Services\Block\Block_Patterns;
use Sherv\Foundation\Support\Service_Provider;

/**
 * Registers and bootstraps block services.
 */
final class Block_Service_Provider extends Service_Provider {

    /**
     * {@inheritDoc}
     */
    public function register(): void {
        $this->container->singleton( Block::class );
        $this->container->singleton( Block_Patterns::class );
    }

    /**
     * {@inheritDoc}
     */
    public function boot(): void {
        add_action(
            'init',
            static function () {
                $this->container->make( Block::class )->register_blocks();
                $this->container->make( Block_Patterns::class )->register_patterns();
            }
        );
    }
}
```
