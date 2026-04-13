<?php
/**
 * Class Failed_Initialization_Exception.
 *
 * @package Sherv\Foundation
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Exception;

use RuntimeException;

/**
 * Exception thrown when a package fails to initialize.
 *
 * @since 1.0.0
 */
final class Failed_Initialization_Exception extends RuntimeException {

	/**
	 * Create a new exception for an invalid plugin main file path.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path The invalid plugin main file path.
	 * @return self
	 */
	public static function for_invalid_plugin_main_file( string $path ): self {
		return self::invalid_path_exception( 'plugin main file', $path );
	}

	/**
	 * Create a new exception for an invalid providers directory path.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path The invalid providers directory path.
	 * @return self
	 */
	public static function for_invalid_providers_path( string $path ): self {
		return self::invalid_path_exception( 'providers', $path );
	}

	/**
	 * Exception for missing plugin main file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $method The method where the plugin main file is required.
	 * @return self
	 */
	public static function for_missing_plugin_main_file( string $method ): self {
		$message = sprintf(
			'The main plugin file is required for the "%s" method. Make sure to provide it using Plugin::new() or the Plugin constructor.',
			esc_html( $method )
		);

		return new self( $message );
	}

	/**
	 * Helper method to create an exception for an invalid path.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The type of the path.
	 * @param string $path The invalid path.
	 * @return self
	 */
	private static function invalid_path_exception( string $type, string $path ): self {
		$message = sprintf(
			'The %s path "%s" is invalid or inaccessible.',
			esc_html( $type ),
			esc_html( $path )
		);

		return new self( $message );
	}
}
