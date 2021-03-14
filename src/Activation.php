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
	protected function __construct( string $file ) {
		register_activation_hook( $file, array( $this, 'activate' ) );
		register_deactivation_hook( $file, array( $this, 'deactivate' ) );
		register_uninstall_hook( $file, array( __CLASS__, 'uninstall' ) );
	}

	public function activate() {
		( new Members() )->create_sql_table();
	}

	public function deactivate(): void {
	}

	public static function uninstall(): void {
		( new Members() )->drop_sql_table();

		WP_DEBUG && wp_die( 'Uninstallation deactivated for this plugin (because WP_DEBUG is true).' );
	}

	public static function init( string $file ): self {
		static $instance = null;

		if ( ! $instance ) {
			$instance = new self( $file );
		}

		return $instance;
	}
}
