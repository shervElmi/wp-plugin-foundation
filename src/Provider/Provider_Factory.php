<?php
/**
 * Class Provider_Factory.
 *
 * @package Sherv\Foundation
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Provider;

use Sherv\Container\Contracts\Container;
use Sherv\Foundation\Contracts\Provider\Service_Provider;
use Sherv\Foundation\Exception\Invalid_Provider_Exception;

/**
 * Factory class for resolving service providers.
 *
 * @since X.X.X
 */
class Provider_Factory {

	/**
	 * Create a new service provider instance.
	 *
	 * @since X.X.X
	 *
	 * @param string    $provider  The service provider class name.
	 * @param Container $container The container instance.
	 * @return Service_Provider
	 *
	 * @throws Invalid_Provider_Exception When the provider is invalid.
	 */
	public static function create( string $provider, Container $container ): Service_Provider {
		if ( ! class_exists( $provider ) ) {
			throw Invalid_Provider_Exception::for_non_existent_provider( $provider ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$instance = new $provider( $container );

		if ( ! $instance instanceof Service_Provider ) {
			throw Invalid_Provider_Exception::for_invalid_provider( $provider ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		return $instance;
	}
}
