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
	public function __construct() {
		// Add the plugin admin menu for administrators only.
		add_action( 'admin_menu', array( $this, 'menu_add' ) );

		// Settings management.
		new AdminSettings();
	}

	public function menu_add() {
		// Menu to manage the LearnyBox members displayed on the map.
		add_menu_page(
			'LearnyBox Map',
			'LearnyBox Map',
			'administrator',
			'learnyboxmap',
			array( $this, 'members_page' ),
			plugins_url( 'learnyboxmap/dist/images/learnybox-icon-20x20.png' ),
		);
	}
}
