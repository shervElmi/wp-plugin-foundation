<?php
/**
 * Fixture: Dummy_Container_Injected_Service_Provider.
 *
 * @package Sherv\Foundation\Tests
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Tests\Fixtures;

use Sherv\Container\Contracts\Container;
use Sherv\Foundation\Contracts\Provider\Service_Provider;

/**
 * A service provider fixture that receives a container via constructor injection.
 *
 * @since 1.0.0
 */
class Dummy_Container_Injected_Service_Provider implements Service_Provider {

	/**
	 * Create a new instance.
	 *
	 * @param Container $container The container instance.
	 */
	public function __construct( protected Container $container ) {
	}
}
