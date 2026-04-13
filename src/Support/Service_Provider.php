<?php
/**
 * Abstract Class Service_Provider.
 *
 * @package Sherv\Foundation
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Support;

use Sherv\Container\Contracts\Container;
use Sherv\Foundation\Contracts\Provider\Service_Provider as Service_Provider_Contract;

/**
 * Base service provider class.
 *
 * Service providers can extend this class for consistent patterns and improved maintainability.
 *
 * @since 1.0.0
 *
 * @const array<string, mixed> BINDINGS   Container bindings to register.
 * @const array<string, mixed> SINGLETONS Container singletons to register.
 */
abstract class Service_Provider implements Service_Provider_Contract {

	/**
	 * Create a new service provider instance.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container The container instance.
	 */
	public function __construct( protected readonly Container $container ) {
	}

	/**
	 * Register services with the container.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register(): void {
	}

	/**
	 * Boot services after registration.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function boot(): void {
	}
}
