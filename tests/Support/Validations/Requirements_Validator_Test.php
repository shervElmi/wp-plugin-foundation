<?php
/**
 * Tests for Requirements_Validator.
 *
 * @package Sherv\Foundation\Tests
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Tests\Support\Validations;

use Brain\Monkey\Functions;
use Mockery;
use Sherv\Foundation\Support\Validations\Requirements_Validator;
use Sherv\Foundation\Tests\Test_Case;
use WP_Error;

class Requirements_Validator_Test extends Test_Case {

	public function test_validate_returns_true_when_all_files_exist(): void {
		$error = Mockery::mock( 'overload:' . WP_Error::class );
		$error->shouldReceive( 'has_errors' )->andReturn( false );
		$error->shouldReceive( 'add' )->never();

		$validator = new Requirements_Validator( null, [], $error );
		$validator->set_required_files( [ __FILE__ ] );

		$this->assertTrue( $validator->validate() );
	}

	public function test_validate_returns_false_when_files_missing(): void {
		$error = Mockery::mock( 'overload:' . WP_Error::class );
		$error->shouldReceive( 'has_errors' )->andReturn( true );
		$error->shouldReceive( 'add' )->once();

		$validator = new Requirements_Validator( null, [], $error );
		$validator->set_required_files( [ '/non/existent/file.php' ] );

		$this->assertFalse( $validator->validate() );
	}

	public function test_get_required_files_returns_files_list(): void {
		$error = Mockery::mock( 'overload:' . WP_Error::class );

		$validator = new Requirements_Validator( null, [ '/custom/file.php' ], $error );

		$required_files = $validator->get_required_files();

		$this->assertIsArray( $required_files );
		$this->assertContains( '/custom/file.php', $required_files );
	}

	public function test_set_required_files_updates_files_list(): void {
		$error = Mockery::mock( 'overload:' . WP_Error::class );

		$validator = new Requirements_Validator( null, [], $error );
		$validator->set_required_files( [ '/new/file.php' ] );

		$required_files = $validator->get_required_files();

		$this->assertContains( '/new/file.php', $required_files );
	}

	public function test_get_error_returns_wp_error_instance(): void {
		$error = Mockery::mock( 'overload:' . WP_Error::class );

		$validator = new Requirements_Validator( null, [], $error );

		$this->assertSame( $error, $validator->get_error() );
	}

	public function test_constructor_adds_autoload_by_default(): void {
		$error = Mockery::mock( 'overload:' . WP_Error::class );

		$plugin_file = '/plugin/path/plugin.php';
		$validator   = new Requirements_Validator( $plugin_file, [], $error );

		$required_files = $validator->get_required_files();

		$this->assertContains( '/plugin/path/vendor/autoload.php', $required_files );
	}

	public function test_constructor_merges_additional_files(): void {
		$error = Mockery::mock( 'overload:' . WP_Error::class );

		$plugin_file = '/plugin/path/plugin.php';
		$validator   = new Requirements_Validator( $plugin_file, [ '/custom/file.php' ], $error );

		$required_files = $validator->get_required_files();

		$this->assertContains( '/plugin/path/vendor/autoload.php', $required_files );
		$this->assertContains( '/custom/file.php', $required_files );
	}

	public function test_constructor_without_plugin_file_uses_autoloader_path(): void {
		$error = Mockery::mock( 'overload:' . WP_Error::class );

		$validator = new Requirements_Validator( null, [], $error );

		$required_files = $validator->get_required_files();

		$this->assertContains( $this->get_resolved_base_path() . 'vendor/autoload.php', $required_files );
	}

	public function test_check_returns_true_when_all_requirements_met(): void {
		$error = Mockery::mock( 'overload:' . WP_Error::class );
		$error->shouldReceive( 'has_errors' )->andReturn( false );

		$project_root = dirname( __DIR__, 3 );

		$this->assertTrue( Requirements_Validator::check( $project_root . '/composer.json' ) );
	}

	public function test_check_returns_false_and_displays_admin_notice(): void {
		$error = Mockery::mock( 'overload:' . WP_Error::class );
		$error->shouldReceive( 'has_errors' )->andReturn( true );
		$error->shouldReceive( 'add' )->once();
		$error->shouldReceive( 'get_error_messages' )->andReturn( [ 'Error message' ] );

		Functions\expect( 'get_plugin_data' )->andReturn( [ 'Name' => '' ] );

		$captured_callback = null;
		Functions\expect( 'add_action' )
			->twice()
			->andReturnUsing(
				static function ( $hook, $callback ) use ( &$captured_callback ) {
					$captured_callback = $callback;
				}
			);

		$this->assertFalse( Requirements_Validator::check( '/non/existent/plugin.php' ) );
		$this->assertNotNull( $captured_callback );

		Functions\when( 'wp_kses_post' )->returnArg();

		$this->expectOutputRegex( '/Error message/' );
		$captured_callback();
	}

	public function test_check_without_plugin_file(): void {
		$error = Mockery::mock( 'overload:' . WP_Error::class );
		$error->shouldReceive( 'has_errors' )->andReturn( false );

		$this->assertTrue( Requirements_Validator::check() );
	}

	public function test_print_admin_notice_resolves_plugin_name(): void {
		$plugin_file = dirname( __DIR__, 3 ) . '/composer.json';

		$error = Mockery::mock( 'overload:' . WP_Error::class );
		$error->shouldReceive( 'get_error_messages' )->andReturn( [ 'Error' ] );

		Functions\expect( 'get_plugin_data' )->andReturn( [ 'Name' => 'My Custom Plugin' ] );

		$captured_callback = null;
		Functions\expect( 'add_action' )
			->twice()
			->andReturnUsing(
				static function ( $hook, $callback ) use ( &$captured_callback ) {
					$captured_callback = $callback;
				}
			);

		$validator = new Requirements_Validator( $plugin_file, [], $error );

		$method = new \ReflectionMethod( $validator, 'print_admin_notice' );
		$method->invoke( $validator );

		$this->assertNotNull( $captured_callback );

		Functions\when( 'wp_kses_post' )->returnArg();

		$this->expectOutputRegex( '/My Custom Plugin failed to start/' );
		$captured_callback();
	}

	public function test_print_admin_notice_uses_default_plugin_name(): void {
		$error = Mockery::mock( 'overload:' . WP_Error::class );
		$error->shouldReceive( 'get_error_messages' )->andReturn( [ 'Error' ] );

		$captured_callback = null;
		Functions\expect( 'add_action' )
			->twice()
			->andReturnUsing(
				static function ( $hook, $callback ) use ( &$captured_callback ) {
					$captured_callback = $callback;
				}
			);

		$validator = new Requirements_Validator( null, [], $error );

		$method = new \ReflectionMethod( $validator, 'print_admin_notice' );
		$method->invoke( $validator );

		$this->assertNotNull( $captured_callback );

		Functions\when( 'wp_kses_post' )->returnArg();

		$this->expectOutputRegex( '/Plugin failed to start/' );
		$captured_callback();
	}
}
