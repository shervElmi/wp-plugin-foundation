<?php
/**
 * Class Requirements_Validator.
 *
 * @package Sherv\Foundation
 * @since   X.X.X
 */

declare( strict_types=1 );

namespace Sherv\Foundation\Support\Validations;

use Sherv\Foundation\Support\Utilities;
use WP_Error;

// Load Utilities manually because this validator may run before Composer autoload is available.
if ( ! class_exists( Utilities::class ) ) {
	require_once dirname( __DIR__ ) . '/Utilities.php';
}

/**
 * Ensures all necessary conditions are met for proper functionality.
 *
 * @since X.X.X
 */
class Requirements_Validator {

	/**
	 * Files that must be present and readable.
	 *
	 * @since X.X.X
	 *
	 * @var string[]
	 */
	protected array $required_files = [];

	/**
	 * Create a new requirements validator instance.
	 *
	 * @since X.X.X
	 *
	 * @param string|null $plugin_main_file Absolute path to the main plugin file, or null
	 *                                      to resolve the base path from the autoloader.
	 * @param string[]    $required_files   Additional files that must be readable.
	 * @param WP_Error    $error            WP_Error object for tracking errors.
	 */
	public function __construct(
		protected ?string $plugin_main_file = null,
		array $required_files = [],
		protected WP_Error $error = new WP_Error(),
	) {
		$this->set_required_files( $required_files );
	}

	/**
	 * Validate requirements and display an admin notice on failure.
	 *
	 * @since X.X.X
	 *
	 * @param string|null $plugin_file    Absolute path to the main plugin file (__FILE__),
	 *                                    or null to resolve from the autoloader.
	 * @param string[]    $required_files Additional files that must be readable.
	 * @return bool True when all requirements are met, false otherwise.
	 */
	public static function check( ?string $plugin_file = null, array $required_files = [] ): bool {
		$validator = new self( $plugin_file, $required_files );

		if ( $validator->validate() ) {
			return true;
		}

		$validator->print_admin_notice();

		return false;
	}

	/**
	 * Runs all validation checks.
	 *
	 * @since X.X.X
	 *
	 * @return bool Returns false if there are errors, otherwise true.
	 */
	public function validate(): bool {
		$this->check_required_files();

		return ! $this->error->has_errors();
	}

	/**
	 * Checks if all required files are readable.
	 *
	 * @since X.X.X
	 *
	 * @return bool True if all required files are readable, false otherwise.
	 */
	protected function check_required_files(): bool {
		foreach ( $this->required_files as $required_file ) {
			if ( ! is_readable( $required_file ) ) {
				$message = sprintf(
					/* translators: %s: build commands. */
					__( 'Required files are missing. Please run the following command to complete the installation: %s', 'sherv-foundation' ),
					'<code>composer install & npm install & npm run build</code>'
				);
				$this->error->add( 'check_required_files_error', $message );

				return false;
			}
		}

		return true;
	}

	/**
	 * Display an admin notice for validation errors.
	 *
	 * @since X.X.X
	 *
	 * @return void
	 */
	protected function print_admin_notice(): void {
		$messages    = $this->error->get_error_messages();
		$plugin_name = $this->resolve_plugin_name();

		$callback = static function () use ( $messages, $plugin_name ): void {
			printf(
				'<div class="notice notice-error"><p><strong>%s</strong></p><ul>%s</ul></div>',
				esc_html(
					sprintf(
						/* translators: %s: plugin name. */
						__( '%s failed to start.', 'sherv-foundation' ),
						$plugin_name
					)
				),
				implode(
					'',
					array_map( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						static fn( string $msg ): string => sprintf( '<li>%s</li>', wp_kses_post( $msg ) ),
						$messages
					)
				)
			);
		};

		add_action( 'admin_notices', $callback );
		add_action( 'network_admin_notices', $callback );
	}

	/**
	 * Resolve the human-readable plugin name.
	 *
	 * @since X.X.X
	 *
	 * @return string
	 */
	protected function resolve_plugin_name(): string {
		if ( is_string( $this->plugin_main_file ) && is_readable( $this->plugin_main_file ) ) {
			$plugin_data = Utilities::get_plugin_data( $this->plugin_main_file );

			if ( ! empty( $plugin_data['Name'] ) ) {
				return $plugin_data['Name'];
			}
		}

		return __( 'Plugin', 'sherv-foundation' );
	}

	/**
	 * Build the required files list by prepending the autoload file.
	 *
	 * @since X.X.X
	 *
	 * @param string[] $required_files Additional required files.
	 * @return string[]
	 */
	protected function build_required_files( array $required_files ): array {
		return array_merge(
			[ Utilities::plugin_dir_path( $this->plugin_main_file ) . 'vendor/autoload.php' ],
			$required_files
		);
	}

	/**
	 * Get the WP_Error object containing any errors that were recorded.
	 *
	 * @since X.X.X
	 *
	 * @return WP_Error The error object.
	 */
	public function get_error(): WP_Error {
		return $this->error;
	}

	/**
	 * Sets the list of files that need to be checked for readability.
	 *
	 * @since X.X.X
	 *
	 * @param string[] $required_files Array of required files.
	 * @return void
	 */
	public function set_required_files( array $required_files ): void {
		$this->required_files = $this->build_required_files( $required_files );
	}

	/**
	 * Get the list of required files.
	 *
	 * @since X.X.X
	 *
	 * @return string[] List of required files.
	 */
	public function get_required_files(): array {
		return $this->required_files;
	}
}
