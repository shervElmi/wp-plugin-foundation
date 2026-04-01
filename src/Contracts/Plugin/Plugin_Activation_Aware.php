<?php
/**
 * Interface Plugin_Activation_Aware.
 *
 * @package Sherv\Foundation
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Contracts\Plugin;

/**
 * Provides a method to handle plugin activation.
 *
 * @since X.X.X
 */
interface Plugin_Activation_Aware {

	/**
	 * Trigger on plugin activation.
	 *
	 * @since X.X.X
	 *
	 * @param bool $network_wide Determines if the plugin is activated network-wide.
	 * @return void
	 */
	public function on_plugin_activation( bool $network_wide ): void;
}
