<?php

namespace LearnyboxMap;

/**
 * The core plugin class.
 *
 * This is used to define activator, desactivator, internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @author     freepius
 */
class Main {
	public function __construct() {
		// Loads the internationalization files.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Admin-specific hooks.
		new Admin();
	}

	public static function activate(): void {
		( new Members() )->create_sql_table();
	}

	public static function desactivate(): void {
		( new Members() )->drop_sql_table();
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'learnyboxmap', false, 'learnyboxmap/languages/' );
	}
}
