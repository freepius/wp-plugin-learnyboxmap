<?php

namespace LearnyboxMap;

use LearnyboxMap\Controller\MembersMap;

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

		// Small hacks to properly highlight our menus and submenus.
		add_filter( 'parent_file', array( $this, 'menu_highlight' ) );
		add_filter(
			'submenu_file',
			function ( ?string $submenu_file, string $parent_file ) {
				return null === $submenu_file ? $parent_file : str_replace( '&amp;', '&', $submenu_file );
			},
			10,
			2
		);

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
			sprintf(
				'edit-tags.php?taxonomy=%s&post_type=%s',
				Entity\Taxonomy\Category::name(),
				Entity\PostType\Member::name()
			)
		);

		// Submenu to display the Map.
		add_submenu_page(
			self::MENU,
			__( 'The members map', 'learnyboxmap' ),
			__( 'The map', 'learnyboxmap' ),
			'administrator',
			self::MENU . '_map',
			array( $this, 'map_page' )
		);
	}

	public function menu_highlight( string $parent_file ): string {
		global $current_screen;

		return Entity\Taxonomy\Category::name() === $current_screen->taxonomy
			? self::MENU
			: $parent_file;
	}

	/**
	 * Display the Members Map in admin context.
	 */
	public function map_page(): void {
		MembersMap::enqueue_scripts_and_styles();
		echo '
		<div class="wrap">
			<h1>' . esc_html( get_admin_page_title() ) . '</h1>
			<main id="map"></main>
		</div>';
	}
}
