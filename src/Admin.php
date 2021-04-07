<?php

namespace LearnyboxMap;

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @author     freepius
 */
class Admin {
	public const MENU = 'learnyboxmap';

	public function __construct() {
		// Add the plugin admin menu for administrators only.
		add_action( 'admin_menu', array( $this, 'menu_add' ) );

		// Add the missing menu highlight for Category custom taxonomy.
		add_filter( 'parent_file', array( $this, 'menu_highlight' ) );

		// Init hooks for settings adminstration.
		new Settings();
	}

	public function menu_add(): void {
		// Menu to manage the LearnyBox members displayed on the map.
		add_menu_page(
			'LearnyBox Map',
			'LearnyBox Map',
			'administrator',
			self::MENU,
			null,
			Asset::img( 'learnybox-icon-20x20.png' ),
			26 // Just after the Comments menu entry (order === 25).
		);

		// Submenu to manage the Category custom taxonomy.
		add_submenu_page(
			self::MENU,
			null,
			__( 'Categories', 'default' ),
			'administrator',
			sprintf( 'edit-tags.php?taxonomy=%s', Entity\Taxonomy\Category::name() )
		);
	}

	public function menu_highlight( string $parent_file ): string {
		global $current_screen;

		return Entity\Taxonomy\Category::name() === $current_screen->taxonomy
			? self::MENU
			: $parent_file;
	}
}
