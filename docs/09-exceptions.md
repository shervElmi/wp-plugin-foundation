# Exceptions

WP Plugin Foundation provides two custom exception classes for clear, descriptive error handling during plugin initialization and service provider management.

## Failed_Initialization_Exception

Thrown when the plugin cannot be initialized due to invalid configuration. Extends `RuntimeException`.

### Factory Methods

| Method | When It's Thrown |
|---|---|
| `for_invalid_plugin_main_file( $path )` | The plugin main file path does not exist or its directory is inaccessible. |
| `for_invalid_providers_path( $path )` | The providers directory path does not exist. |
| `for_missing_plugin_main_file( $method )` | A method requiring the plugin main file is called before it is set. |

### When They Occur

**Invalid plugin main file**: Triggered during `Plugin` construction when the resolved directory does not exist:

```php
use Sherv\Foundation\Plugin;

new Plugin( '/invalid/path/to/plugin.php' );
// → Failed_Initialization_Exception:
//   The plugin main file path "/invalid/path/to/plugin.php" is invalid or inaccessible.
```

**Invalid providers path**: Triggered by `Plugin_Builder::use_providers_path()`:

```php
Plugin::new()
    ->use_providers_path( '/nonexistent/providers' );
// → Failed_Initialization_Exception:
//   The providers path "/nonexistent/providers" is invalid or inaccessible.
```

**Missing plugin main file**: Triggered by `Plugin_Builder::with_plugin_properties()` when no plugin file was provided:

```php
Plugin::new()
    ->with_plugin_properties();
// → Failed_Initialization_Exception:
//   The main plugin file is required for the
//   "Sherv\Foundation\Configuration\Plugin_Builder::with_plugin_properties" method.
//   Make sure to provide it using Plugin::new() or the Plugin constructor.
```

## Invalid_Provider_Exception

Thrown when a service provider class is invalid or cannot be instantiated. Extends `InvalidArgumentException`.

### Factory Methods

| Method | When It's Thrown |
|---|---|
| `for_non_existent_provider( $class )` | The provider class does not exist. |
| `for_invalid_provider( $class )` | The class exists but does not implement `Service_Provider`. |

### When They Occur

**Non-existent provider**: Triggered by `Provider_Factory::create()` when the class cannot be found:

```php
$factory = new Provider_Factory();
$factory::create( 'NonExistentProvider', $container );
// → Invalid_Provider_Exception:
//   Provider class "NonExistentProvider" does not exist.
```

**Invalid provider**: Triggered when the class exists but doesn't implement the `Service_Provider` interface:

```php
$factory::create( stdClass::class, $container );
// → Invalid_Provider_Exception:
//   The provider class "stdClass" must implement the Service_Provider interface.
```

This also applies when passing an invalid class name string to `Provider_Registry::get()` or `Provider_Registry::has()`:

```php
$registry->get( stdClass::class );
// → Invalid_Provider_Exception:
//   The provider class "stdClass" must implement the Service_Provider interface.
```

## Handling Exceptions

Both exception types use named constructors (factory methods) to produce descriptive messages. In production, these exceptions typically indicate a developer configuration error rather than a runtime issue.

```php
use Sherv\Foundation\Exception\Failed_Initialization_Exception;
use Sherv\Foundation\Exception\Invalid_Provider_Exception;

try {
    $plugin = Plugin::new( __FILE__ )
        ->use_providers_path( $custom_path )
        ->with_providers()
        ->build();
} catch ( Failed_Initialization_Exception $e ) {
    // Path configuration error.
    error_log( $e->getMessage() );
} catch ( Invalid_Provider_Exception $e ) {
    // Provider class error.
    error_log( $e->getMessage() );
}
```
