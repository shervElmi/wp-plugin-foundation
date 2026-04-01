<?php
/**
 * Tests for ServiceProvider.
 *
 * @package Sherv\Foundation\Tests
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Tests\Support;

use ReflectionProperty;
use Sherv\Container\Contracts\Container;
use Sherv\Foundation\Contracts\Provider\Service_Provider as Service_Provider_Contract;
use Sherv\Foundation\Support\Service_Provider;
use Sherv\Foundation\Tests\Test_Case;

/**
 * Test case for ServiceProvider.
 *
 * @since X.X.X
 */
class Service_Provider_Test extends Test_Case {

	/**
	 * Service provider instance.
	 *
	 * @var Service_Provider_Contract
	 */
	protected Service_Provider_Contract $service_provider;

	/**
	 * Container mock.
	 *
	 * @var Container
	 */
	protected Container $container_mock;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->container_mock   = $this->createStub( Container::class );
		$this->service_provider = new class( $this->container_mock ) extends Service_Provider {
		};
	}

	public function testServiceProviderCanBeInstantiated(): void {
		$this->assertInstanceOf( Service_Provider::class, $this->service_provider );
	}

	public function testContainerIsPassedCorrectly(): void {
		$container = new ReflectionProperty( $this->service_provider, 'container' );
		$container = $container->getValue( $this->service_provider );

		$this->assertSame( $this->container_mock, $container );
	}

	public function testRegisterMethodIsCallable(): void {
		$this->assertIsCallable( [ $this->service_provider, 'register' ] );

		$this->service_provider->register();

		$this->assertTrue( true );
	}

	public function testBootMethodIsCallable(): void {
		$this->assertIsCallable( [ $this->service_provider, 'boot' ] );

		$this->service_provider->boot();

		$this->assertTrue( true );
	}
}
