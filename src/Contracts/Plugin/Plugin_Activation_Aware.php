<?php
/**
 * Interface Plugin_Activation_Aware.
 *
 * @package Sherv\Foundation
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Contracts\Plugin;

/**
 * Provides a method to handle plugin activation.
 *
 * @since 1.0.0
 */
interface Plugin_Activation_Aware {

	/**
	 * Trigger on plugin activation.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $network_wide Determines if the plugin is activated network-wide.
	 * @return void
	 */
	public function on_plugin_activation( bool $network_wide ): void;
}
