<?php
/**
 * Tests for Plugin_Builder.
 *
 * @package Sherv\Foundation\Tests
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Tests\Configuration;

use Brain\Monkey;
use Mockery;
use Sherv\Foundation\Provider\Provider_Registry;
use Sherv\Foundation\Contracts\Provider\Service_Provider;
use Sherv\Foundation\Exception\Failed_Initialization_Exception;
use Sherv\Foundation\Plugin;
use Sherv\Foundation\Tests\Test_Case;

class Plugin_Builder_Test extends Test_Case {

	public function test_build_returns_plugin_instance(): void {
		$plugin = Plugin::new()->build();

		$this->assertInstanceOf( Plugin::class, $plugin );
	}

	public function test_use_providers_path_with_valid_path(): void {
		$providers_path = '/wp-content/plugins/plugin-dir/src/Providers';

		$plugin = Plugin::new()->use_providers_path( $providers_path )->build();

		$this->assertSame( $providers_path, $plugin->container( 'path.providers' ) );
	}

	public function test_use_providers_path_with_invalid_path_throws_exception(): void {
		$this->expectException( Failed_Initialization_Exception::class );
		$this->expectExceptionMessage( 'The providers path "/invalid/path/" is invalid or inaccessible.' );

		$plugin_builder = Plugin::new();

		Monkey\Functions\when( 'is_dir' )->justReturn( false );

		$plugin_builder->use_providers_path( '/invalid/path/' );
	}

	public function test_with_plugin_properties_binds_plugin_data(): void {
		$plugin_headers = [
			'Author'          => 'Sherv Elmi',
			'AuthorURI'       => 'https://example.com',
			'Description'     => 'A sample plugin',
			'Name'            => 'Sample Plugin',
			'Network'         => true,
			'DomainPath'      => '/languages',
			'TextDomain'      => 'sample-plugin',
			'PluginURI'       => 'https://example.com/plugin',
			'Version'         => '1.0.0',
			'RequiresWP'      => '6.6.2',
			'RequiresPHP'     => '8.0',
			'RequiresPlugins' => [ 'another-plugin' ],
		];

		Monkey\Functions\expect( 'get_plugin_data' )->andReturn( $plugin_headers );
		Monkey\Functions\expect( 'wp_get_environment_type' )->andReturn( 'development' );

		$plugin = Plugin::new( '/wp-content/plugins/plugin-dir/plugin-name.php' )
					->with_plugin_properties()
					->build();

		$this->assertSame( $plugin_headers['Author'], $plugin->container( 'author' ) );
		$this->assertSame( $plugin_headers['AuthorURI'], $plugin->container( 'author_uri' ) );
		$this->assertSame( $plugin_headers['Description'], $plugin->container( 'description' ) );
		$this->assertSame( $plugin_headers['Name'], $plugin->container( 'name' ) );
		$this->assertSame( $plugin_headers['DomainPath'], $plugin->container( 'domain_path' ) );
		$this->assertSame( $plugin_headers['TextDomain'], $plugin->container( 'textdomain' ) );
		$this->assertSame( $plugin_headers['PluginURI'], $plugin->container( 'uri' ) );
		$this->assertSame( $plugin_headers['Version'], $plugin->container( 'version' ) );
		$this->assertSame( $plugin_headers['RequiresWP'], $plugin->container( 'requires_wp' ) );
		$this->assertSame( $plugin_headers['RequiresPHP'], $plugin->container( 'requires_php' ) );
		$this->assertSame( $plugin_headers['RequiresPlugins'], $plugin->container( 'requires_plugins' ) );

		$this->assertFalse( $plugin->container( 'wp.debug' ) );
		$this->assertSame( 'development', $plugin->container( 'wp.env' ) );
	}

	public function test_with_plugin_properties_without_plugin_main_file_throws_exception(): void {
		$this->expectException( Failed_Initialization_Exception::class );
		$this->expectExceptionMessage( 'The main plugin file is required for the "Sherv\Foundation\Configuration\Plugin_Builder::with_plugin_properties" method. Make sure to provide it using Plugin::new() or the Plugin constructor.' );

		Plugin::new()->with_plugin_properties()->build();
	}

	public function test_with_providers_includes_providers_file(): void {
		$providers_path = __DIR__ . '/../Fixtures/Providers/';

		$plugin = Plugin::new()
			->use_providers_path( $providers_path )
			->with_providers()
			->build();

		$this->assertNotEmpty( $plugin->container( Provider_Registry::class )->all() );
	}

	public function test_with_providers_returns_empty_when_providers_file_does_not_exist(): void {
		$providers_path = __DIR__ . '/../Fixtures/'; // No providers.php in this directory.

		$plugin = Plugin::new()
			->use_providers_path( $providers_path )
			->with_providers()
			->build();

		$this->assertEmpty( $plugin->container( Provider_Registry::class )->all() );
	}

	public function test_with_providers_registers_given_providers(): void {
		$service_provider = Mockery::mock( Service_Provider::class );

		$plugin = Plugin::new()
			->with_providers( [ $service_provider ], false )
			->build();

		$this->assertTrue( $plugin->container( Provider_Registry::class )->has( $service_provider ) );
	}

	public function test_with_providers_merges_given_providers_and_providers_file(): void {
		$providers_path = __DIR__ . '/../Fixtures/Providers/';
		$given_provider = Mockery::mock( Service_Provider::class );

		$plugin = Plugin::new()
			->use_providers_path( $providers_path )
			->with_providers( [ $given_provider ] )
			->build();

		$provider_registry = $plugin->container( Provider_Registry::class );

		$this->assertTrue( $provider_registry->has( $given_provider ) );

		$file_providers = require $providers_path . 'providers.php';

		foreach ( $file_providers as $file_provider ) {
			$this->assertTrue( $provider_registry->has( $file_provider ) );
		}
	}

	public function test_with_providers_removes_duplicate_providers(): void {
		$provider1 = Mockery::mock( Service_Provider::class );
		$provider2 = Mockery::mock( Service_Provider::class );

		$plugin = Plugin::new()
			->with_providers( [ $provider1, $provider2 ], false )
			->build();

		$provider_registry = $plugin->container( Provider_Registry::class );

		$this->assertCount( 1, $provider_registry->all() );
		$this->assertTrue( $provider_registry->has( $provider1 ) );
	}
}
