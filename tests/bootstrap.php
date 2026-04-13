<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Sherv\Foundation\Tests
 * @since   1.0.0
 */

declare( strict_types=1 );

$autoload_file = dirname( __DIR__ ) . '/vendor/autoload.php';

if ( ! is_file( $autoload_file ) ) {
	die( 'Please ensure dependencies are installed with Composer before running tests.' );
}

error_reporting( E_ALL ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting

if ( ! defined( 'PHPUNIT_COMPOSER_INSTALL' ) ) {
	define( 'PHPUNIT_COMPOSER_INSTALL', $autoload_file );
	require_once $autoload_file;
}

unset( $autoload_file );
