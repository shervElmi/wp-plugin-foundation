<?php
/**
 * Tests for Utilities.
 *
 * @package Sherv\Foundation\Tests
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Tests\Support;

use Brain\Monkey\Functions;
use Sherv\Foundation\Support\Utilities;
use Sherv\Foundation\Tests\Test_Case;

class Utilities_Test extends Test_Case {

	public function test_plugin_dir_path_with_plugin_file(): void {
		$path = Utilities::plugin_dir_path( '/wp-content/plugins/my-plugin/plugin.php' );

		$this->assertSame( '/wp-content/plugins/my-plugin/', $path );
	}

	public function test_plugin_dir_path_without_plugin_file(): void {
		$path = Utilities::plugin_dir_path();

		$this->assertSame( $this->get_resolved_base_path(), $path );
	}

	public function test_plugin_dir_path_normalizes_backslashes(): void {
		Functions\when( 'plugin_dir_path' )->justReturn( 'C:\\wp-content\\plugins\\my-plugin\\' );

		$path = Utilities::plugin_dir_path( 'C:\\wp-content\\plugins\\my-plugin\\plugin.php' );

		$this->assertSame( 'C:/wp-content/plugins/my-plugin/', $path );
	}

	public function test_get_plugin_data_returns_plugin_headers(): void {
		$expected = [
			'Name'    => 'Test Plugin',
			'Version' => '1.0.0',
		];

		Functions\expect( 'get_plugin_data' )
			->once()
			->with( '/path/to/plugin.php', false, false )
			->andReturn( $expected );

		$result = Utilities::get_plugin_data( '/path/to/plugin.php' );

		$this->assertSame( $expected, $result );
	}
}
