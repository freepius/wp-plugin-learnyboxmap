<?php

namespace LearnyboxMap;

/**
 * The core plugin class.
 *
 * This is used to internationalization, admin-specific hooks, and public-facing site hooks.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @author     freepius
 */
class Main {
	public function __construct() {
		// Loads the internationalization files.
		add_action( 'init', array( $this, 'load_textdomains' ) );

		// Load the Custom Post Types.
		add_action( 'init', array( $this, 'load_custom_post_types' ) );

		// Admin-specific hooks.
		new Admin();
	}

	public function load_textdomains(): void {
		load_plugin_textdomain( 'learnyboxmap', false, 'learnyboxmap/languages/' );
	}

	public static function load_custom_post_types(): void {
		new PostType\Member();
	}
}
