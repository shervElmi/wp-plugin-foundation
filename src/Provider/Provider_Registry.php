<?php
/**
 * Class Provider_Registry.
 *
 * @package Sherv\Foundation
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Provider;

use Sherv\Foundation\Contracts\Plugin\Plugin;
use Sherv\Foundation\Contracts\Provider\Service_Provider;
use Sherv\Foundation\Exception\Invalid_Provider_Exception;

/**
 * Central registry for registering and getting service providers.
 *
 * @since 1.0.0
 */
class Provider_Registry {

	/**
	 * All registered service providers.
	 *
	 * @since 1.0.0
	 *
	 * @var Service_Provider[]
	 */
	protected array $providers = [];

	/**
	 * Create a new provider registry instance.
	 *
	 * @since 1.0.0
	 *
	 * @param Plugin           $plugin           The plugin instance.
	 * @param Provider_Factory $provider_factory The provider factory instance.
	 */
	public function __construct(
		protected readonly Plugin $plugin,
		protected readonly Provider_Factory $provider_factory,
	) {
	}

	/**
	 * Register a service provider.
	 *
	 * @since 1.0.0
	 *
	 * @param Service_Provider|string $provider The service provider instance or class name.
	 * @param bool                    $force    Whether to force re-registration.
	 * @return Service_Provider
	 */
	public function register( Service_Provider|string $provider, bool $force = false ): Service_Provider {
		$registered = $this->get( $provider );

		if ( $registered && ! $force ) {
			return $registered;
		}

		// Create the provider if it's a class name.
		if ( is_string( $provider ) ) {
			$provider = $this->provider_factory::create( $provider, $this->plugin->container() );
		}

		// Register BINDINGS or SINGLETONS with the plugin's container if the provider has them.
		if ( defined( $provider::class . '::BINDINGS' ) ) {
			foreach ( $provider::BINDINGS as $id => $entry ) {
				$this->plugin->container()->bind(
					is_string( $id ) ? $id : $entry,
					$entry
				);
			}
		}

		if ( defined( $provider::class . '::SINGLETONS' ) ) {
			foreach ( $provider::SINGLETONS as $id => $entry ) {
				$this->plugin->container()->singleton(
					is_string( $id ) ? $id : $entry,
					$entry
				);
			}
		}

		// Call the register method if the provider has one.
		if ( method_exists( $provider, 'register' ) ) {
			$provider->register();
		}

		$this->set( $provider );

		// Call the provider boot method if the package is already booted to execute its boot logic.
		if ( $this->plugin->is_booted() ) {
			$this->boot( $provider );
		}

		return $provider;
	}

	/**
	 * Get a registered service provider.
	 *
	 * @since 1.0.0
	 *
	 * @param Service_Provider|string $provider The service provider instance or class name.
	 * @return Service_Provider|null
	 */
	public function get( Service_Provider|string $provider ): ?Service_Provider {
		return $this->providers[ $this->get_provider_class( $provider ) ] ?? null;
	}

	/**
	 * Set a service provider in the registry.
	 *
	 * @since 1.0.0
	 *
	 * @param Service_Provider|string $provider The service provider instance or class name.
	 * @return void
	 */
	public function set( Service_Provider|string $provider ): void {
		$this->providers[ $this->get_provider_class( $provider ) ] = $provider;
	}

	/**
	 * Determine if a service provider is registered.
	 *
	 * @since 1.0.0
	 *
	 * @param Service_Provider|string $provider The service provider instance or class name.
	 * @return bool
	 */
	public function has( Service_Provider|string $provider ): bool {
		return isset( $this->providers[ $this->get_provider_class( $provider ) ] );
	}

	/**
	 * Get all registered service providers.
	 *
	 * @since 1.0.0
	 *
	 * @return Service_Provider[]
	 */
	public function all(): array {
		return $this->providers;
	}

	/**
	 * Boot a service provider.
	 *
	 * @since 1.0.0
	 *
	 * @param Service_Provider $provider The service provider to boot.
	 * @return void
	 */
	public function boot( Service_Provider $provider ): void {
		if ( method_exists( $provider, 'boot' ) ) {
			$provider->boot();
		}
	}

	/**
	 * Get the provider class name.
	 *
	 * @since 1.0.0
	 *
	 * @param Service_Provider|class-string $provider The provider instance or class name.
	 * @return class-string
	 *
	 * @throws Invalid_Provider_Exception When the provider is invalid.
	 */
	protected function get_provider_class( Service_Provider|string $provider ): string {
		if ( is_string( $provider ) ) {
			if ( ! is_a( $provider, Service_Provider::class, allow_string: true ) ) {
				throw Invalid_Provider_Exception::for_invalid_provider( $provider ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			}

			return $provider;
		}

		return $provider::class;
	}
}
