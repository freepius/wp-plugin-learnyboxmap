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

		// Init hooks for the public, stand-alone "Members Map" page.
		new Controller\MembersMap();
	}

	public function load_textdomains(): void {
		load_plugin_textdomain( 'learnyboxmap', false, LEARNYBOXMAP_REL_PATH . 'languages/' );
	}

	public static function load_custom_post_types(): void {
		new Entity\PostType\Member();
	}
}
