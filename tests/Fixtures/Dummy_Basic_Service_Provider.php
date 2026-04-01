<?php
/**
 * Fixture: Dummy_Basic_Service_Provider.
 *
 * @package Sherv\Foundation\Tests
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Tests\Fixtures;

use Sherv\Foundation\Contracts\Provider\Service_Provider;

/**
 * A basic service provider fixture for testing.
 *
 * @since X.X.X
 */
class Dummy_Basic_Service_Provider implements Service_Provider {

	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register(): void {
	}

	/**
	 * Boot services.
	 *
	 * @return void
	 */
	public function boot(): void {
	}
}
