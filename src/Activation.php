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
		Main::load_custom_taxonomies();
		flush_rewrite_rules();
	}

	public function deactivate(): void {
	}

	/**
	 * Actions done during the plugin uninstallation:
	 * 1. Delete all the posts of custom post types
	 * 2. Delete all the terms of custom taxonomies
	 */
	public static function uninstall(): void {
		\LearnyboxMap\Entity\PostType\Member::delete_posts();
		\LearnyboxMap\Entity\Taxonomy\Category::delete_terms();

		WP_DEBUG && wp_die( 'Uninstallation deactivated for this plugin (because WP_DEBUG is true).' );
	}
}
