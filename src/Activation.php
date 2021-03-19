<?php

namespace LearnyboxMap;

/**
 * Plugin activation, deactivation and uninstall handlers.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @author     freepius
 */
class Activation {
	public function __construct( string $file ) {
		register_activation_hook( $file, array( $this, 'activate' ) );
		register_deactivation_hook( $file, array( $this, 'deactivate' ) );
		register_uninstall_hook( $file, array( __CLASS__, 'uninstall' ) );
	}

	public function activate(): void {
		Main::load_custom_post_types();
		flush_rewrite_rules();
	}

	public function deactivate(): void {
	}

	public static function uninstall(): void {
		WP_DEBUG && wp_die( 'Uninstallation deactivated for this plugin (because WP_DEBUG is true).' );
	}
}
