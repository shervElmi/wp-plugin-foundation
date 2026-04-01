# WP Plugin Foundation

A foundation package for building structured, maintainable WordPress plugins using modern PHP practices.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.2-blue)](https://www.php.net/)

## Requirements

- PHP 8.2+
- Composer
- WordPress

## Installation

```bash
composer require sherv/wp-plugin-foundation
```

## Quick Start

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

## Documentation

1. **[Introduction](./docs/01-introduction.md)**: Overview, features, and quick start.
2. **[Getting Started](./docs/02-getting-started.md)**: Step-by-step setup for a new plugin.
3. **[Architecture](./docs/03-architecture.md)**: Components overview and UML diagram.
4. **[Plugin Builder](./docs/04-plugin-builder.md)**: Builder API, paths, and plugin properties.
5. **[Service Providers](./docs/05-service-providers.md)**: Creating, registering, and booting providers.
6. **[Plugin Lifecycle](./docs/06-plugin-lifecycle.md)**: Activation, deactivation, and boot process.
7. **[Container](./docs/07-container.md)**: Accessing the DI container and resolving services.
8. **[Requirements Validation](./docs/08-requirements-validation.md)**: Pre-boot environment checks.
9. **[Exceptions](./docs/09-exceptions.md)**: Error handling reference.

## Development

```bash
git clone https://github.com/shervElmi/wp-plugin-foundation.git
cd wp-plugin-foundation
composer install
```

### Scripts

| Command                  | Description                          |
| ------------------------ | ------------------------------------ |
| `composer test`          | Run the test suite                   |
| `composer test:coverage` | Run tests with code coverage         |
| `composer lint`          | Run PHP CodeSniffer                  |
| `composer format`        | Auto-fix coding standards violations |

## Contributing

Contributions are welcome. Please open an issue or pull request on [GitHub](https://github.com/shervElmi/wp-plugin-foundation).

## Security

To report a security vulnerability, please see [SECURITY.md](SECURITY.md).

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a history of notable changes.

## License

© Sherv Elmi. Licensed under the [MIT License](LICENSE). Distributed without any warranty. See the license for details.
