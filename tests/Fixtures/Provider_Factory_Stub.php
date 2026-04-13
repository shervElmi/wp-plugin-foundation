<?php
/**
 * Fixture: Provider_Factory_Stub.
 *
 * @package Sherv\Foundation\Tests
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Tests\Fixtures;

use Sherv\Container\Contracts\Container;
use Sherv\Foundation\Contracts\Provider\Service_Provider;
use Sherv\Foundation\Provider\Provider_Factory;

/**
 * A stub implementation of Provider_Factory for testing.
 *
 * @since 1.0.0
 */
class Provider_Factory_Stub extends Provider_Factory {

	/**
	 * Create a new service provider instance without validation.
	 *
	 * @param string    $provider  The service provider class name.
	 * @param Container $container The container instance.
	 * @return Service_Provider
	 */
	public static function create( string $provider, Container $container ): Service_Provider {
		return new $provider( $container );
	}
}
