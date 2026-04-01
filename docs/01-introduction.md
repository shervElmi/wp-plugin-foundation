# Introduction

**WP Plugin Foundation** is a foundation package for building structured, maintainable WordPress plugins with modern PHP. It provides plugin lifecycle management, a service provider architecture, and seamless integration with the [`sherv/wp-di-container`](https://github.com/shervElmi/wp-di-container) dependency injection container.

## Features

| Feature | Description |
|---|---|
| **Fluent Builder API** | Configure your plugin with a readable, chainable `Plugin_Builder`. |
| **Service Providers** | Organize features into discrete providers with `register()` and `boot()` methods. |
| **Plugin Lifecycle** | First-class activation, deactivation, and boot hooks via dedicated interfaces. |
| **DI Container** | Automatic dependency resolution powered by `sherv/wp-di-container`. |
| **Auto-Discovery** | Load service providers from a `providers.php` file automatically. |
| **Requirements Validation** | Verify required files and environment before the plugin boots. |

## Requirements

- PHP 8.2+
- Composer
- WordPress (used within a WordPress plugin)

## Installation

```bash
composer require sherv/wp-plugin-foundation
```

## Quick Start

In your main plugin file (the file containing the [plugin header comment](https://developer.wordpress.org/plugins/plugin-basics/header-requirements/)):

```php
use Sherv\Foundation\Plugin;

$plugin = Plugin::new( __FILE__ )
    ->with_plugin_properties()
    ->with_providers()
    ->build();

register_activation_hook( __FILE__, [ $plugin, 'on_plugin_activation' ] );
register_deactivation_hook( __FILE__, [ $plugin, 'on_plugin_deactivation' ] );

add_action( 'plugins_loaded', [ $plugin, 'boot' ] );
```

For a complete walkthrough, see the [Getting Started](./02-getting-started.md) guide.

## Documentation

1. **[Introduction](./01-introduction.md)**: Overview, features, and quick start.
2. **[Getting Started](./02-getting-started.md)**: Step-by-step setup for a new plugin.
3. **[Architecture](./03-architecture.md)**: Components overview and UML diagram.
4. **[Plugin Builder](./04-plugin-builder.md)**: Builder API, paths, and plugin properties.
5. **[Service Providers](./05-service-providers.md)**: Creating, registering, and booting providers.
6. **[Plugin Lifecycle](./06-plugin-lifecycle.md)**: Activation, deactivation, and boot process.
7. **[Container](./07-container.md)**: Accessing the DI container and resolving services.
8. **[Requirements Validation](./08-requirements-validation.md)**: Pre-boot environment checks.
9. **[Exceptions](./09-exceptions.md)**: Error handling reference.
