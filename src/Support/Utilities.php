<?php
/**
 * Class Utilities.
 *
 * @package Sherv\Foundation
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Support;

use Composer\Autoload\ClassLoader;

/**
 * Common utility methods shared across the foundation package.
 *
 * @since X.X.X
 */
final class Utilities {

	/**
	 * Resolve the plugin base directory path.
	 *
	 * When a plugin main file path is provided, delegates to the WordPress
	 * `plugin_dir_path()` function. Otherwise falls back to the Composer
	 * autoloader location.
	 *
	 * @since X.X.X
	 *
	 * @param string|null $plugin_main_file Absolute path to the main plugin file.
	 * @return string Trailing-slashed directory path with forward slashes.
	 */
	public static function plugin_dir_path( ?string $plugin_main_file = null ): string {
		$path = is_string( $plugin_main_file )
			? plugin_dir_path( $plugin_main_file )
			: dirname( array_key_first( ClassLoader::getRegisteredLoaders() ) ) . '/';

		return str_replace( '\\', '/', $path );
	}

	/**
	 * Retrieve plugin header data.
	 *
	 * Ensures the WordPress `get_plugin_data()` function is available before
	 * calling it, loading the required file from `wp-admin` when necessary.
	 *
	 * @since X.X.X
	 *
	 * @param string $plugin_main_file Absolute path to the main plugin file.
	 * @return array<string, mixed> Plugin header data.
	 */
	public static function get_plugin_data( string $plugin_main_file ): array {
		// @codeCoverageIgnoreStart
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
		}
		// @codeCoverageIgnoreEnd

		return get_plugin_data( $plugin_main_file, false, false );
	}
}
