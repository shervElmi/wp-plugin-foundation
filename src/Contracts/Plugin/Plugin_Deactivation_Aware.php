<?php
/**
 * Interface Plugin_Deactivation_Aware.
 *
 * @package Sherv\Foundation
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Contracts\Plugin;

/**
 * Provides a method to handle plugin deactivation.
 *
 * @since 1.0.0
 */
interface Plugin_Deactivation_Aware {

	/**
	 * Trigger on plugin deactivation.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $network_wide Determines if the plugin is deactivated network-wide.
	 * @return void
	 */
	public function on_plugin_deactivation( bool $network_wide ): void;
}
