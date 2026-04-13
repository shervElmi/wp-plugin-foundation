<?php
/**
 * Tests for Provider_Factory.
 *
 * @package Sherv\Foundation\Tests
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Tests\Provider;

use ReflectionProperty;
use Sherv\Container\Contracts\Container;
use Sherv\Foundation\Contracts\Provider\Service_Provider;
use Sherv\Foundation\Exception\Invalid_Provider_Exception;
use Sherv\Foundation\Provider\Provider_Factory;
use Sherv\Foundation\Tests\Test_Case;
use Sherv\Foundation\Tests\Fixtures\Dummy_Basic_Service_Provider;
use Sherv\Foundation\Tests\Fixtures\Dummy_Container_Injected_Service_Provider;
use stdClass;

class Provider_Factory_Test extends Test_Case {

	private Container $container_mock;

	protected function setUp(): void {
		parent::setUp();

		$this->container_mock = $this->createStub( Container::class );
	}

	public function test_create_returns_service_provider_instance(): void {
		$provider_class_name = Dummy_Basic_Service_Provider::class;

		$service_provider = Provider_Factory::create( $provider_class_name, $this->container_mock );

		$this->assertInstanceOf( Service_Provider::class, $service_provider );
		$this->assertInstanceOf( $provider_class_name, $service_provider );
	}

	public function test_create_injects_container_into_service_provider(): void {
		$service_provider = Provider_Factory::create( Dummy_Container_Injected_Service_Provider::class, $this->container_mock );

		$container = new ReflectionProperty( $service_provider, 'container' );
		$container = $container->getValue( $service_provider );

		$this->assertSame( $this->container_mock, $container );
	}

	public function test_create_throws_exception_for_non_existent_provider(): void {
		$this->expectException( Invalid_Provider_Exception::class );
		$this->expectExceptionMessage( 'Provider class "NonExistentProvider" does not exist.' );

		Provider_Factory::create( 'NonExistentProvider', $this->container_mock );
	}

	public function test_create_throws_exception_for_invalid_provider(): void {
		$this->expectException( Invalid_Provider_Exception::class );
		$this->expectExceptionMessage( 'The provider class "stdClass" must implement the Service_Provider interface.' );

		Provider_Factory::create( stdClass::class, $this->container_mock );
	}
}
