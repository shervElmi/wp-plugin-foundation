# Container

WP Plugin Foundation integrates with [`sherv/wp-di-container`](https://github.com/shervElmi/wp-di-container) for dependency injection. The container is created automatically when you instantiate a `Plugin` and is available throughout the plugin's lifetime.

## Accessing the Container

### From the Plugin Instance

```php
// Get the container itself.
$container = $plugin->container();

// Resolve an entry by ID or class name.
$service = $plugin->container( My_Service::class );

// Resolve with parameters.
$service = $plugin->container( My_Service::class, [ 'api_url' => 'https://api.example.com' ] );
```

### From a Service Provider

Inside any provider that extends the base `Service_Provider`, the container is available as `$this->container`:

```php
use Sherv\Foundation\Support\Service_Provider;

/**
 * Sets up and bootstraps the plugin routes.
 */
final class Route_Service_Provider extends Service_Provider {

    /**
     * {@inheritDoc}
     */
    public function register(): void {
        $this->container->singleton( Api_Client::class );
    }

    /**
     * {@inheritDoc}
     */
    public function boot(): void {
        $api = $this->container->make( Api_Client::class, [
            'api_url' => 'https://api.example.com/v1',
        ] );

        add_action( 'rest_api_init', [
            $this->container->make( Rest_Controller::class ),
            'register_routes',
        ] );
    }
}
```

### Using a Helper Function

Define a `plugin()` helper in your main plugin file for convenient access anywhere:

```php
namespace Sherv\MyPlugin;

use Sherv\Foundation\Plugin;

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
```

Usage:

```php
use function Sherv\MyPlugin\plugin;

// Get the Plugin instance.
plugin()->boot();

// Resolve a container binding.
$version = plugin( 'version' );

// Resolve a class.
$api = plugin( Api_Client::class );

// Load text domain.
add_action( 'init', static fn() => load_plugin_textdomain( plugin( 'textdomain' ) ) );
```

## Binding Services

### In `register()`

```php
public function register(): void {
    // Transient binding, new instance each time.
    $this->container->bind( Logger::class );

    // Singleton, same instance every time.
    $this->container->singleton( Cache_Manager::class );

    // Bind an interface to a concrete class.
    $this->container->bind( LoggerInterface::class, FileLogger::class );

    // Bind a scalar value.
    $this->container->bind( 'api.url', 'https://api.example.com' );
}
```

### With Declarative Constants

```php
use Sherv\Foundation\Contracts\Provider\Service_Provider;

final class App_Provider implements Service_Provider {

    public const BINDINGS = [
        LoggerInterface::class => FileLogger::class,
    ];

    public const SINGLETONS = [
        Cache_Manager::class,
        'http.client' => Remote_Request::class,
    ];
}
```

See [Service Providers → Declarative Bindings](./05-service-providers.md#declarative-bindings-with-constants) for details.

## Resolving Services

```php
// By class name, auto-resolved with constructor injection.
$controller = $plugin->container( Rest_Controller::class );

// By string ID.
$version = $plugin->container( 'version' );
$url     = $plugin->container( 'url' );

// With runtime parameters.
$api = $plugin->container( Api_Client::class, [ 'api_url' => 'https://api.example.com' ] );
```

### Array Access

The container supports array access for bound values:

```php
$this->container['url'];        // Plugin directory URL
$this->container['version'];    // Plugin version
$this->container['textdomain']; // Text domain
$this->container['path'];       // Plugin base path
```

This is particularly useful inside service providers when enqueueing assets:

```php
/**
 * Enqueue plugin scripts.
 *
 * @return void
 */
public function enqueue_scripts(): void {
    $asset_url = "{$this->container['url']}assets/app";
    $manifest  = require "{$this->container['path']}assets/app.asset.php";

    wp_enqueue_script(
        'my-plugin-app',
        "{$asset_url}.js",
        $manifest['dependencies'],
        $manifest['version'],
        true
    );
}
```

## Default Path Bindings

These are bound automatically when the `Plugin` is created:

| Key | Value | Example |
|---|---|---|
| `path` | Plugin base directory | `/wp-content/plugins/my-plugin/` |
| `path.providers` | Providers directory | `/wp-content/plugins/my-plugin/src/Providers/` |

## Plugin Property Bindings

These are bound when `with_plugin_properties()` is called on the builder:

| Key | Type | Description |
|---|---|---|
| `name` | `string` | Plugin Name header |
| `version` | `string` | Version header |
| `description` | `string` | Description header |
| `uri` | `string` | Plugin URI header |
| `url` | `string` | Plugin directory URL |
| `author` | `string` | Author header |
| `author_uri` | `string` | Author URI header |
| `textdomain` | `string` | Text Domain header |
| `domain_path` | `string` | Domain Path header |
| `network` | `bool` | Network header |
| `requires_wp` | `string` | Requires at least header |
| `requires_php` | `string` | Requires PHP header |
| `requires_plugins` | `array` | Requires Plugins header |
| `wp.debug` | `bool` | `true` when `WP_DEBUG` is on |
| `wp.env` | `string` | `wp_get_environment_type()` result |

## Base Bindings

These are registered automatically during plugin construction:

| Key | Value |
|---|---|
| `Sherv\Foundation\Contracts\Plugin\Plugin` | The plugin instance |
| `Sherv\Foundation\Plugin` | The plugin instance |
| `Sherv\Container\Contracts\Container` | The container instance |
| `Sherv\Foundation\Provider\Provider_Factory` | Singleton |
| `Sherv\Foundation\Provider\Provider_Registry` | Singleton |

## Custom Container

You can provide your own container instance:

```php
use Sherv\Container\Container;
use Sherv\Foundation\Plugin;

$container = new Container();
$plugin    = Plugin::new( __FILE__, $container )->build();

// The same container instance is used.
assert( $container === $plugin->container() );
```

The container must implement `Sherv\Container\Contracts\Container`.
