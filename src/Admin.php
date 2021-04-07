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
			array( $this, 'members_page' ),
			Asset::img( 'learnybox-icon-20x20.png' ),
			26 // Just after the Comments menu entry (order === 25).
		);
	}
}
