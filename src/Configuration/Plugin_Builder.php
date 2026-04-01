<?php
/**
 * Class Plugin_Builder.
 *
 * @package Sherv\Foundation
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Configuration;

use Sherv\Foundation\Contracts\Plugin\Plugin;
use Sherv\Foundation\Exception\Failed_Initialization_Exception;
use Sherv\Foundation\Provider\Provider_Registry;
use Sherv\Foundation\Support\Utilities;

/**
 * Configures and creates a plugin instance.
 *
 * @since X.X.X
 */
final readonly class Plugin_Builder {

	/**
	 * Create a new plugin builder instance.
	 *
	 * @since X.X.X
	 *
	 * @param Plugin $plugin The plugin instance.
	 */
	public function __construct( private Plugin $plugin ) {
	}

	/**
	 * Specify the providers directory path.
	 *
	 * @since X.X.X
	 *
	 * @param string $path Path to the providers directory.
	 * @return $this
	 *
	 * @throws Failed_Initialization_Exception When the path is invalid.
	 */
	public function use_providers_path( string $path ): self {
		if ( ! is_dir( $path ) ) {
			throw Failed_Initialization_Exception::for_invalid_providers_path( $path ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$this->plugin->container()->bind( 'path.providers', $path );

		return $this;
	}

	/**
	 * Register the properties of the plugin in the plugin container.
	 *
	 * @since X.X.X
	 *
	 * @return $this
	 *
	 * @throws Failed_Initialization_Exception When the plugin main file is not set.
	 */
	public function with_plugin_properties(): self {
		$plugin_main_file = $this->plugin->plugin_main_file();

		if ( is_null( $plugin_main_file ) ) {
			throw Failed_Initialization_Exception::for_missing_plugin_main_file( __METHOD__ ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$this->plugin->container()->bind( 'url', plugin_dir_url( $plugin_main_file ) );
		$this->plugin->container()->bind( 'wp.debug', $this->plugin->is_debug_mode_enabled() );
		$this->plugin->container()->bind( 'wp.env', $this->plugin->detect_environment() );

		$plugin_data = Utilities::get_plugin_data( $plugin_main_file );

		$this->plugin->container()->bind( 'author', $plugin_data['Author'] );
		$this->plugin->container()->bind( 'author_uri', $plugin_data['AuthorURI'] );
		$this->plugin->container()->bind( 'description', $plugin_data['Description'] );
		$this->plugin->container()->bind( 'name', $plugin_data['Name'] );
		$this->plugin->container()->bind( 'network', $plugin_data['Network'] );
		$this->plugin->container()->bind( 'domain_path', $plugin_data['DomainPath'] );
		$this->plugin->container()->bind( 'textdomain', $plugin_data['TextDomain'] );
		$this->plugin->container()->bind( 'uri', $plugin_data['PluginURI'] );
		$this->plugin->container()->bind( 'version', $plugin_data['Version'] );
		$this->plugin->container()->bind( 'requires_wp', $plugin_data['RequiresWP'] );
		$this->plugin->container()->bind( 'requires_php', $plugin_data['RequiresPHP'] );
		$this->plugin->container()->bind( 'requires_plugins', $plugin_data['RequiresPlugins'] );

		return $this;
	}

	/**
	 * Register service providers.
	 *
	 * @since X.X.X
	 *
	 * @param array $providers           List of service providers to register.
	 * @param bool  $with_providers_file Whether to include providers from the providers file.
	 * @return $this
	 */
	public function with_providers( array $providers = [], bool $with_providers_file = true ): self {
		$providers = array_filter(
			array_unique(
				array_merge(
					$providers,
					$with_providers_file
					? $this->get_providers_file_list()
					: []
				)
			)
		);

		$provider_registry = $this->plugin->container( Provider_Registry::class );

		foreach ( $providers as $provider ) {
			$provider_registry->register( $provider );
		}

		return $this;
	}

	/**
	 * Create and return the plugin instance.
	 *
	 * @since X.X.X
	 *
	 * @return Plugin
	 */
	public function build(): Plugin {
		return $this->plugin;
	}

	/**
	 * Retrieve providers from the providers directory.
	 *
	 * @since X.X.X
	 *
	 * @return array
	 */
	private function get_providers_file_list(): array {
		$providers_file_path = $this->get_providers_file_path();

		if ( ! file_exists( $providers_file_path ) ) {
			return [];
		}

		return array_filter(
			require $providers_file_path, // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
			static fn ( string $provider ): bool => class_exists( $provider )
		);
	}

	/**
	 * Get the path to the providers file.
	 *
	 * @since X.X.X
	 *
	 * @return string
	 */
	private function get_providers_file_path(): string {
		return path_join( $this->plugin->container( 'path.providers' ), 'providers.php' );
	}
}
