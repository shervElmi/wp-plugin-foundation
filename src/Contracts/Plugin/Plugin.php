<?php
/**
 * Interface Plugin.
 *
 * @package Sherv\Foundation
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Contracts\Plugin;

use Sherv\Container\Contracts\Container;
use Sherv\Foundation\Configuration\Plugin_Builder;

/**
 * Contract for defining a plugin.
 *
 * @since 1.0.0
 */
interface Plugin extends Plugin_Activation_Aware, Plugin_Deactivation_Aware {

	/**
	 * Begin configuring a new plugin instance.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $plugin_main_file The path to the main plugin file.
	 * @param Container   $container        Optional container instance.
	 * @return Plugin_Builder
	 */
	public static function new( ?string $plugin_main_file = null, ?Container $container = null ): Plugin_Builder;

	/**
	 * Boot the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function boot(): void;

	/**
	 * Determine if the plugin has booted.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_booted(): bool;

	/**
	 * Get the container instance or a specific entry by ID or class name.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
	 *
	 * @param string $path Additional path to append.
	 * @return string
	 */
	public function base_path( string $path = '' ): string;

	/**
	 * Get the plugin main file.
	 *
	 * @since 1.0.0
	 *
	 * @return string|null
	 */
	public function plugin_main_file(): ?string;

	/**
	 * Determine if debug mode is enabled.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_debug_mode_enabled(): bool;

	/**
	 * Detect the plugin's current environment.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function detect_environment(): string;
}
