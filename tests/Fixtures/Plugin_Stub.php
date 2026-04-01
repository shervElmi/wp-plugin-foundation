<?php
/**
 * Fixture: Plugin_Stub.
 *
 * @package Sherv\Foundation\Tests
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Tests\Fixtures;

use Sherv\Container\Contracts\Container;
use Sherv\Foundation\Configuration\Plugin_Builder;
use Sherv\Foundation\Contracts\Plugin\Plugin;

/**
 * A stub implementation of the Plugin interface for testing.
 *
 * @since X.X.X
 */
class Plugin_Stub implements Plugin {

	/**
	 * Create a new plugin stub instance.
	 *
	 * @param Container|null $container The container instance.
	 * @param bool           $booted    Whether the plugin is booted.
	 */
	public function __construct(
		protected ?Container $container = null,
		protected bool $booted = false,
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public static function new( ?string $plugin_main_file = null, ?Container $container = null ): Plugin_Builder {
		return new Plugin_Builder( new static( $container ) );
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot(): void {
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
	public function container( ?string $id = null, array $with = [] ): mixed {
		return is_null( $id ) ? $this->container : $this->container?->make( $id, $with );
	}

	/**
	 * {@inheritDoc}
	 */
	public function base_path( string $path = '' ): string {
		return '/stub/path/' . $path;
	}

	/**
	 * {@inheritDoc}
	 */
	public function plugin_main_file(): ?string {
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_debug_mode_enabled(): bool {
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function detect_environment(): string {
		return 'testing';
	}

	/**
	 * {@inheritDoc}
	 */
	public function on_plugin_activation( bool $_network_wide ): void { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	}

	/**
	 * {@inheritDoc}
	 */
	public function on_plugin_deactivation( bool $_network_wide ): void { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	}

	/**
	 * Set the booted state.
	 *
	 * @param bool $booted Whether the plugin is booted.
	 * @return void
	 */
	public function set_booted( bool $booted ): void {
		$this->booted = $booted;
	}
}
