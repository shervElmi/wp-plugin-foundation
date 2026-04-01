<?php
/**
 * Interface Plugin.
 *
 * @package Sherv\Foundation
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Contracts\Plugin;

use Sherv\Container\Contracts\Container;
use Sherv\Foundation\Configuration\Plugin_Builder;

/**
 * Contract for defining a plugin.
 *
 * @since X.X.X
 */
interface Plugin extends Plugin_Activation_Aware, Plugin_Deactivation_Aware {

	/**
	 * Begin configuring a new plugin instance.
	 *
	 * @since X.X.X
	 *
	 * @param string|null $plugin_main_file The path to the main plugin file.
	 * @param Container   $container        Optional container instance.
	 * @return Plugin_Builder
	 */
	public static function new( ?string $plugin_main_file = null, ?Container $container = null ): Plugin_Builder;

	/**
	 * Boot the plugin.
	 *
	 * @since X.X.X
	 *
	 * @return void
	 */
	public function boot(): void;

	/**
	 * Determine if the plugin has booted.
	 *
	 * @since X.X.X
	 *
	 * @return bool
	 */
	public function is_booted(): bool;

	/**
	 * Get the container instance or a specific entry by ID or class name.
	 *
	 * @since X.X.X
	 *
	 * @param string|null $id   The entry ID or class name.
	 * @param array       $with Parameters to pass when getting the entry.
	 * @return mixed
	 *
	 * @throws \Sherv\Container\Exception\Failed_Resolution_Exception When the entry cannot be resolved.
	 * @throws \Psr\Container\ContainerExceptionInterface          General container error.
	 * @throws \Psr\Container\NotFoundExceptionInterface           Entry not found.
	 */
	public function container( ?string $id = null, array $with = [] ): mixed;

	/**
	 * Get the base path of the plugin.
	 *
	 * @since X.X.X
	 *
	 * @param string $path Additional path to append.
	 * @return string
	 */
	public function base_path( string $path = '' ): string;

	/**
	 * Get the plugin main file.
	 *
	 * @since X.X.X
	 *
	 * @return string|null
	 */
	public function plugin_main_file(): ?string;

	/**
	 * Determine if debug mode is enabled.
	 *
	 * @since X.X.X
	 *
	 * @return bool
	 */
	public function is_debug_mode_enabled(): bool;

	/**
	 * Detect the plugin's current environment.
	 *
	 * @since X.X.X
	 *
	 * @return string
	 */
	public function detect_environment(): string;
}
