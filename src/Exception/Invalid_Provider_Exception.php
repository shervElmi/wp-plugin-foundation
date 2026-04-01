<?php
/**
 * Class Invalid_Provider_Exception.
 *
 * @package Sherv\Foundation
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when an invalid service provider is encountered.
 *
 * @since X.X.X
 */
final class Invalid_Provider_Exception extends InvalidArgumentException {

	/**
	 * Create a new exception for a non-existent provider class.
	 *
	 * @since X.X.X
	 *
	 * @param class-string $provider The provider class that does not exist.
	 * @return self
	 */
	public static function for_non_existent_provider( string $provider ): self {
		$message = sprintf(
			'Provider class "%s" does not exist.',
			esc_html( $provider )
		);

		return new self( $message );
	}

	/**
	 * Create a new exception for an invalid provider that does not implement the Service_Provider interface.
	 *
	 * @since X.X.X
	 *
	 * @param class-string $provider The provider class name.
	 * @return self
	 */
	public static function for_invalid_provider( string $provider ): self {
		$message = sprintf(
			'The provider class "%s" must implement the Service_Provider interface.',
			esc_html( $provider )
		);

		return new self( $message );
	}
}
