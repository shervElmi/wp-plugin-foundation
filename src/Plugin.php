<?php
/**
 * Class Plugin.
 *
 * @package Sherv\Foundation
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Foundation;

use Sherv\Container\Container as DefaultContainer;
use Sherv\Container\Contracts\Container;
use Sherv\Foundation\Configuration\Plugin_Builder;
use Sherv\Foundation\Contracts\Plugin\Plugin as Plugin_Contract;
use Sherv\Foundation\Contracts\Plugin\Plugin_Activation_Aware;
use Sherv\Foundation\Contracts\Plugin\Plugin_Deactivation_Aware;
use Sherv\Foundation\Exception\Failed_Initialization_Exception;
use Sherv\Foundation\Provider\Provider_Factory;
use Sherv\Foundation\Provider\Provider_Registry;
use Sherv\Foundation\Support\Utilities;

/**
 * A package for initializing, configuring, and managing the lifecycle of a WordPress Plugin.
 *
 * @since 1.0.0
 */
class Plugin implements Plugin_Contract {

	/**
	 * Path to the plugin base directory.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected string $base_path;

	/**
	 * Indicates if the plugin has "booted".
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected bool $booted = false;

	/**
	 * Create a new plugin instance.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $plugin_main_file The path to the main plugin file.
	 * @param Container   $container        The container instance.
	 */
	public function __construct(
		protected ?string $plugin_main_file = null,
		protected Container $container = new DefaultContainer(),
	) {
		$this->set_base_path( $plugin_main_file );
		$this->register_base_bindings();
	}

	/**
	 * {@inheritDoc}
	 */
	public static function new( ?string $plugin_main_file = null, ?Container $container = null ): Plugin_Builder {
		return new Plugin_Builder( new static( $plugin_main_file, $container ?? new DefaultContainer() ) );
	}

	/**
	 * Set the base path for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $plugin_main_file The path to the main plugin file.
	 * @return void
	 *
	 * @throws Failed_Initialization_Exception When the path is invalid.
	 */
	protected function set_base_path( ?string $plugin_main_file = null ): void {
		$this->base_path = Utilities::plugin_dir_path( $plugin_main_file );

		if ( ! is_dir( $this->base_path ) ) {
			throw Failed_Initialization_Exception::for_invalid_plugin_main_file( $plugin_main_file ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$this->bind_paths_in_container();
	}

	/**
	 * Bind the plugin paths in the container.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function bind_paths_in_container(): void {
		$this->container->bind( 'path', $this->base_path() );
		$this->container->bind( 'path.providers', $this->base_path( 'src/Providers/' ) );
	}

	/**
	 * Register the base bindings in the container.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function register_base_bindings(): void {
		$this->container->bind( Plugin_Contract::class, $this );
		$this->container->bind( self::class, $this );
		$this->container->bind( Container::class, $this->container );

		$this->container->singleton( Provider_Factory::class );
		$this->container->singleton( Provider_Registry::class );
	}

	/**
	 * {@inheritDoc}
	 */
	public function on_plugin_activation( bool $network_wide ): void {
		$provider_registry = $this->container->make( Provider_Registry::class );

		foreach ( $provider_registry->all() as $provider ) {
			if ( $provider instanceof Plugin_Activation_Aware ) {
				$provider->on_plugin_activation( $network_wide );
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function on_plugin_deactivation( bool $network_wide ): void {
		$provider_registry = $this->container->make( Provider_Registry::class );

		foreach ( $provider_registry->all() as $provider ) {
			if ( $provider instanceof Plugin_Deactivation_Aware ) {
				$provider->on_plugin_deactivation( $network_wide );
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot(): void {
		if ( $this->is_booted() ) {
			return;
		}

		$provider_registry = $this->container->make( Provider_Registry::class );

		foreach ( $provider_registry->all() as $provider ) {
			$provider_registry->boot( $provider );
		}

		$this->booted = true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_booted(): bool {
		return $this->booted;
	}

	/**
	 * {@inheritDoc}
	 */
	public function base_path( string $path = '' ): string {
		return path_join( $this->base_path, $path );
	}

	/**
	 * {@inheritDoc}
	 */
	public function plugin_main_file(): ?string {
		return $this->plugin_main_file;
	}

	/**
	 * {@inheritDoc}
	 */
	public function container( ?string $id = null, array $with = [] ): mixed {
		return is_null( $id ) ? $this->container : $this->container->make( $id, $with );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_debug_mode_enabled(): bool {
		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}

	/**
	 * {@inheritDoc}
	 */
	public function detect_environment(): string {
		return wp_get_environment_type();
	}
}
