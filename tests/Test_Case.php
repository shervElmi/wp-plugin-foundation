<?php
/**
 * Base Test_Case.
 *
 * @package Sherv\Foundation\Tests
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Tests;

use Brain\Monkey;
use Composer\Autoload\ClassLoader;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

abstract class Test_Case extends TestCase {

	use MockeryPHPUnitIntegration;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Monkey\Functions\stubEscapeFunctions();
		Monkey\Functions\stubTranslationFunctions();

		Monkey\Functions\stubs(
			[
				'plugin_dir_path' => static fn ( string $file ): string => dirname( $file ) . '/',
				'plugin_dir_url',
				'path_join'       => static fn ( string $base, string $path ): string => rtrim( $base, '/' ) . '/' . $path,
				'is_dir',
			]
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Get the resolved base path from ClassLoader.
	 *
	 * @return string
	 */
	protected function get_resolved_base_path(): string {
		return dirname( array_key_first( ClassLoader::getRegisteredLoaders() ) ) . '/';
	}
}
