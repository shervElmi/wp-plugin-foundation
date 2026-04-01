<?php
/**
 * Tests for Plugin.
 *
 * @package Sherv\Foundation\Tests
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Tests;

use Brain\Monkey;
use Mockery;
use Sherv\Container\Contracts\Container;
use Sherv\Foundation\Contracts\Plugin\Plugin as Plugin_Contract;
use Sherv\Foundation\Contracts\Plugin\Plugin_Activation_Aware;
use Sherv\Foundation\Contracts\Plugin\Plugin_Deactivation_Aware;
use Sherv\Foundation\Contracts\Provider\Service_Provider;
use Sherv\Foundation\Provider\Provider_Factory;
use Sherv\Foundation\Provider\Provider_Registry;
use Sherv\Foundation\Exception\Failed_Initialization_Exception;
use Sherv\Foundation\Configuration\Plugin_Builder;
use Sherv\Foundation\Plugin;
use Sherv\Foundation\Tests\Fixtures\Dummy_Basic_Service_Provider;

class Plugin_Test extends Test_Case {

	public function test_initialization_with_plugin_main_file(): void {
		$plugin_path      = '/wp-content/plugins/plugin-dir/';
		$plugin_main_file = $plugin_path . 'plugin-name.php';

		$plugin = new Plugin( $plugin_main_file );

		$this->assertSame( $plugin_main_file, $plugin->plugin_main_file() );
		$this->assertSame( $plugin_path, $plugin->base_path() );
		$this->assertSame( $plugin_path, $plugin->container( 'path' ) );
		$this->assertSame( $plugin_path . 'src/Providers/', $plugin->container( 'path.providers' ) );
	}

	public function test_initialization_without_plugin_main_file(): void {
		$plugin      = new Plugin();
		$plugin_path = $this->get_resolved_base_path();

		$this->assertNull( $plugin->plugin_main_file() );
		$this->assertSame( $plugin_path, $plugin->base_path() );
		$this->assertSame( $plugin_path, $plugin->container( 'path' ) );
		$this->assertSame( $plugin_path . 'src/Providers/', $plugin->container( 'path.providers' ) );
	}

	public function test_invalid_plugin_main_file_throws_exception(): void {
		$this->expectException( Failed_Initialization_Exception::class );
		$this->expectExceptionMessage( 'The plugin main file path "/invalid/path/to/plugin.php" is invalid or inaccessible.' );

		Monkey\Functions\when( 'is_dir' )->justReturn( false );

		new Plugin( '/invalid/path/to/plugin.php' );
	}

	public function test_base_path_with_subpaths(): void {
		$plugin = new Plugin();

		$this->assertSame( $this->get_resolved_base_path() . 'custom/subpath/', $plugin->base_path( 'custom/subpath/' ) );
	}

	public function test_container_registers_base_bindings(): void {
		$plugin = new Plugin();

		$this->assertInstanceOf( Plugin_Contract::class, $plugin );
		$this->assertInstanceOf( Container::class, $plugin->container( Container::class ) );
		$this->assertInstanceOf( Provider_Factory::class, $plugin->container( Provider_Factory::class ) );
		$this->assertInstanceOf( Provider_Registry::class, $plugin->container( Provider_Registry::class ) );
	}

	public function test_on_plugin_activation_invokes_providers(): void {
		$plugin = new Plugin();

		$service_provider_spy = Mockery::spy(
			new class() implements Service_Provider, Plugin_Activation_Aware {

				public function on_plugin_activation( bool $_network_wide ): void { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
				}
			}
		);

		$plugin->container( Provider_Registry::class )->register( $service_provider_spy );
		$plugin->on_plugin_activation( network_wide: false );

		$service_provider_spy->shouldHaveReceived( 'on_plugin_activation' );
	}

	public function test_on_plugin_deactivation_invokes_providers(): void {
		$plugin = new Plugin();

		$service_provider_spy = Mockery::spy(
			new class() implements Service_Provider, Plugin_Deactivation_Aware {

				public function on_plugin_deactivation( bool $_network_wide ): void { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
				}
			}
		);

		$plugin->container( Provider_Registry::class )->register( $service_provider_spy );
		$plugin->on_plugin_deactivation( network_wide: true );

		$service_provider_spy->shouldHaveReceived( 'on_plugin_deactivation' );
	}

	public function test_container_returns_itself_when_no_id_provided(): void {
		$plugin = new Plugin();

		$this->assertInstanceOf( Container::class, $plugin->container() );
	}

	public function test_container_resolves_entry_when_id_is_provided(): void {
		$plugin = new Plugin();
		$plugin->container()->bind( 'name', static fn(): string => 'Sherv' );

		$this->assertSame( 'Sherv', $plugin->container( 'name' ) );
	}

	public function test_boot_invokes_service_providers_boot_methods(): void {
		$plugin               = new Plugin();
		$service_provider_spy = Mockery::spy( Dummy_Basic_Service_Provider::class );

		$plugin->container( Provider_Registry::class )->register( $service_provider_spy );
		$plugin->boot();

		$service_provider_spy->shouldHaveReceived( 'boot' );
		$this->assertTrue( $plugin->is_booted() );
	}

	public function test_boot_does_not_reinvoke_providers_when_already_booted(): void {
		$plugin = new Plugin();
		$plugin->boot();

		$provider_registry_mock = Mockery::mock( Provider_Registry::class );
		$provider_registry_mock->shouldReceive( 'boot' )->never();

		$plugin->container()->bind( Provider_Registry::class, $provider_registry_mock );
		$plugin->boot();

		$this->assertTrue( $plugin->is_booted() );
	}

	public function test_boot_without_registered_providers(): void {
		$plugin = new Plugin();
		$plugin->boot();

		$this->assertTrue( $plugin->is_booted() );
	}

	public function test_debug_mode_detection(): void {
		$plugin = new Plugin();

		$this->assertFalse( $plugin->is_debug_mode_enabled() );
	}

	public function test_environment_detection(): void {
		Monkey\Functions\expect( 'wp_get_environment_type' )->andReturn( 'production' );

		$this->assertSame( 'production', ( new Plugin() )->detect_environment() );
	}

	public function test_new_with_plugin_main_file(): void {
		$builder = Plugin::new( '/wp-content/plugins/plugin-dir/plugin-name.php' );

		$this->assertInstanceOf( Plugin_Builder::class, $builder );

		$plugin = $builder->build();

		$this->assertInstanceOf( Plugin::class, $plugin );
		$this->assertSame( '/wp-content/plugins/plugin-dir/plugin-name.php', $plugin->plugin_main_file() );
	}

	public function test_new_with_custom_container(): void {
		$container = new \Sherv\Container\Container();
		$builder   = Plugin::new( null, $container );
		$plugin    = $builder->build();

		$this->assertSame( $container, $plugin->container() );
	}
}
