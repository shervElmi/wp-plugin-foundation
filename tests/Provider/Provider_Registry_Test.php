<?php
/**
 * Tests for Provider_Registry.
 *
 * @package Sherv\Foundation\Tests
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Tests\Provider;

use Mockery;
use Sherv\Container\Container;
use Sherv\Foundation\Contracts\Plugin\Plugin;
use Sherv\Foundation\Provider\Provider_Factory;
use Sherv\Foundation\Provider\Provider_Registry;
use Sherv\Foundation\Contracts\Provider\Service_Provider;
use Sherv\Foundation\Exception\Invalid_Provider_Exception;
use Sherv\Foundation\Tests\Fixtures\Dummy_Basic_Service_Provider;
use Sherv\Foundation\Tests\Fixtures\Plugin_Stub;
use Sherv\Foundation\Tests\Fixtures\Provider_Factory_Stub;
use Sherv\Foundation\Tests\Test_Case;
use stdClass;

class Provider_Registry_Test extends Test_Case {

	private Plugin $plugin;
	private Provider_Factory $provider_factory;

	protected function setUp(): void {
		parent::setUp();

		$this->plugin           = new Plugin_Stub( Mockery::mock( Container::class ) );
		$this->provider_factory = new Provider_Factory_Stub();
	}

	public function test_initialization(): void {
		$provider_registry = new Provider_Registry( $this->plugin, $this->provider_factory );

		$this->assertInstanceOf( Provider_Registry::class, $provider_registry );
	}

	public function test_register_adds_service_provider(): void {
		$provider_registry = new Provider_Registry( $this->plugin, $this->provider_factory );
		$service_provider  = new class() implements Service_Provider {
		};

		$provider_registry->register( $service_provider );

		$this->assertTrue( $provider_registry->has( $service_provider ) );
	}

	public function test_register_invokes_register_method(): void {
		$provider_registry    = new Provider_Registry( $this->plugin, $this->provider_factory );
		$service_provider_spy = Mockery::spy(
			new class() implements Service_Provider {

				public function register(): void {
				}
			}
		);

		$provider_registry->register( $service_provider_spy );

		$service_provider_spy->shouldHaveReceived( 'register' );
		$this->assertNotNull( $service_provider_spy );
	}

	public function test_register_binds_bindings_to_container(): void {
		$provider_registry = new Provider_Registry( $this->plugin, $this->provider_factory );

		$container_mock = $this->plugin->container();
		$container_mock->shouldReceive( 'bind' )->once()->with( 'example.binding', stdClass::class );

		$provider_registry->register(
			new class() implements Service_Provider {

				public const BINDINGS = [
					'example.binding' => stdClass::class,
				];
			}
		);

		$this->assertNotEmpty( $provider_registry->all() );
	}

	public function test_register_binds_singletons_to_container(): void {
		$provider_registry = new Provider_Registry( $this->plugin, $this->provider_factory );

		$container_mock = $this->plugin->container();
		$container_mock->shouldReceive( 'singleton' )->once()->with( 'example.singleton', stdClass::class );

		$provider_registry->register(
			new class() implements Service_Provider {

				public const SINGLETONS = [
					'example.singleton' => stdClass::class,
				];
			}
		);

		$this->assertNotEmpty( $provider_registry->all() );
	}

	public function test_register_boots_provider_when_plugin_is_booted(): void {
		$provider_registry    = new Provider_Registry( $this->plugin, $this->provider_factory );
		$service_provider_spy = Mockery::spy(
			new class() implements Service_Provider {

				public function boot(): void {
				}
			}
		);

		$provider_registry->register( $service_provider_spy );

		$service_provider_spy->shouldNotHaveReceived( 'boot' );

		$this->plugin->set_booted( true );
		$provider_registry->register( $service_provider_spy, force: true );

		$service_provider_spy->shouldHaveReceived( 'boot' );
		$this->assertTrue( $this->plugin->is_booted() );
	}

	public function test_register_creates_provider_from_class_name(): void {
		$provider_registry = new Provider_Registry( $this->plugin, $this->provider_factory );
		$class_name        = Dummy_Basic_Service_Provider::class;

		$this->assertInstanceOf( $class_name, $provider_registry->register( $class_name ) );
	}

	public function test_register_returns_same_provider_when_already_registered(): void {
		$provider_registry  = new Provider_Registry( $this->plugin, $this->provider_factory );
		$service_provider_1 = Mockery::mock( Service_Provider::class );
		$service_provider_2 = Mockery::mock( Service_Provider::class );

		$provider_registry->register( $service_provider_1 );
		$provider_registry->register( $service_provider_2 );

		$this->assertSame( $service_provider_1, $provider_registry->get( $service_provider_2 ) );
		$this->assertCount( 1, $provider_registry->all() );
	}

	public function test_register_with_force_re_registers_provider(): void {
		$provider_registry    = new Provider_Registry( $this->plugin, $this->provider_factory );
		$service_provider_spy = Mockery::spy( Dummy_Basic_Service_Provider::class );

		$provider_registry->register( $service_provider_spy );
		$provider_registry->register( $service_provider_spy, force: true );

		$service_provider_spy->shouldHaveReceived( 'register' )->twice();
		$this->assertTrue( $provider_registry->has( $service_provider_spy ) );
	}

	public function test_boot_invokes_boot_method(): void {
		$provider_registry    = new Provider_Registry( $this->plugin, $this->provider_factory );
		$service_provider_spy = Mockery::spy( Dummy_Basic_Service_Provider::class );

		$provider_registry->boot( $service_provider_spy );

		$service_provider_spy->shouldHaveReceived( 'boot' );
		$this->assertNotNull( $service_provider_spy );
	}

	public function test_get_returns_registered_provider(): void {
		$provider_registry  = new Provider_Registry( $this->plugin, $this->provider_factory );
		$service_provider_1 = Mockery::mock( 'Service_Provider1', Service_Provider::class );
		$service_provider_2 = Mockery::mock( 'Service_Provider2', Service_Provider::class );

		$provider_registry->register( $service_provider_1 );

		$this->assertSame( $service_provider_1, $provider_registry->get( $service_provider_1 ) );
		$this->assertNull( $provider_registry->get( $service_provider_2 ) );
	}

	public function test_has_identifies_registered_providers(): void {
		$provider_registry  = new Provider_Registry( $this->plugin, $this->provider_factory );
		$service_provider_1 = Mockery::mock( 'Service_Provider1', Service_Provider::class );
		$service_provider_2 = Mockery::mock( 'Service_Provider2', Service_Provider::class );

		$provider_registry->register( $service_provider_1 );

		$this->assertTrue( $provider_registry->has( $service_provider_1 ) );
		$this->assertFalse( $provider_registry->has( $service_provider_2 ) );
	}

	public function test_get_throws_exception_for_invalid_string_provider(): void {
		$provider_registry = new Provider_Registry( $this->plugin, $this->provider_factory );

		$this->expectException( Invalid_Provider_Exception::class );
		$this->expectExceptionMessage( 'The provider class "stdClass" must implement the Service_Provider interface.' );

		$provider_registry->get( stdClass::class );
	}

	public function test_all_returns_registered_providers(): void {
		$provider_registry = new Provider_Registry( $this->plugin, $this->provider_factory );
		$providers         = $provider_registry->all();

		$this->assertEmpty( $providers );

		$service_provider_1 = Mockery::mock( 'Service_Provider1', Service_Provider::class );
		$service_provider_2 = Mockery::mock( 'Service_Provider2', Service_Provider::class );

		$provider_registry->register( $service_provider_1 );
		$provider_registry->register( $service_provider_2 );

		$providers = $provider_registry->all();

		$this->assertCount( 2, $providers );
		$this->assertArrayHasKey( get_class( $service_provider_1 ), $providers );
		$this->assertArrayHasKey( get_class( $service_provider_2 ), $providers );
	}
}
