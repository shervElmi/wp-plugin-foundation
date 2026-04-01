<?php
/**
 * Abstract Class Service_Provider.
 *
 * @package Sherv\Foundation
 * @since   X.X.X
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
 * @since X.X.X
 *
 * @const array<string, mixed> BINDINGS   Container bindings to register.
 * @const array<string, mixed> SINGLETONS Container singletons to register.
 */
abstract class Service_Provider implements Service_Provider_Contract {

	/**
	 * Create a new service provider instance.
	 *
	 * @since X.X.X
	 *
	 * @param Container $container The container instance.
	 */
	public function __construct( protected readonly Container $container ) {
	}

	/**
	 * Register services with the container.
	 *
	 * @since X.X.X
	 *
	 * @return void
	 */
	public function register(): void {
	}

	/**
	 * Boot services after registration.
	 *
	 * @since X.X.X
	 *
	 * @return void
	 */
	public function boot(): void {
	}
}
