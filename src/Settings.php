<?php

namespace LearnyboxMap;

/**
 * Functionalities and hooks to administrate the plugin options (in combination with the Options class)
 * through the WordPress Settings API and one settings page.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @author     freepius
 */
class Settings {
	protected const MENU = 'learnyboxmap_settings';
	protected const PAGE = self::MENU;

	public function __construct() {
		// Add a submenu to administrate the settings.
		add_action( 'admin_menu', array( $this, 'menu_add' ) );

		// Init the settings, sections and fields.
		add_action( 'admin_init', array( $this, 'init' ) );
	}

	public function menu_add() {
		// This 'settings' submenu is placed inside the Admin::MENU.
		add_submenu_page(
			Admin::MENU,
			__( 'Settings for the LearnyboxMap plugin', 'learnyboxmap' ),
			__( 'Settings', 'learnyboxmap' ),
			'manage_options',
			self::MENU,
			array( $this, 'page' )
		);
	}

	public function page() {
		echo '
		<div class="wrap">
			<h1>' . esc_html( get_admin_page_title() ) . '</h1>
			<form action="options.php" method="post">';
				settings_errors();
				settings_fields( self::PAGE );
				do_settings_sections( self::PAGE );
				submit_button();
		echo '
			</form>
		</div>';
	}

	public function init() {
		register_setting( self::PAGE, Option::NAME, array( 'sanitize_callback' => array( $this, 'sanitize' ) ) );

		// Section and fields for the LearnyBox API options.
		add_settings_section(
			'api',
			__( 'LearnyBox API settings', 'learnyboxmap' ),
			array( $this, 'section_api' ),
			self::PAGE,
		);

		$fields = array(
			'api_key' => array( 'api', __( 'Your LearnyBox API key', 'learnyboxmap' ) ),
			'api_url' => array( 'api', __( 'Your LearnyBox URL', 'learnyboxmap' ) ),
		);

		foreach ( $fields as $option => list($section, $title) ) {
			add_settings_field(
				$option,
				$title,
				fn () => $this->input_text( $option ),
				self::PAGE,
				$section,
				array( 'label_for' => $option )
			);
		}
	}

	public function section_api() {
		echo '<p>' . esc_html__( 'section_api_help', 'learnyboxmap' ) . '</p>';
	}

	public function input_text( string $option ) {
		echo sprintf(
			'<input type="text" id="%s" name="%s" value="%s" size="40">',
			$option,                 //phpcs:ignore WordPress.Security.EscapeOutput
			Option::name( $option ), //phpcs:ignore WordPress.Security.EscapeOutput
			esc_attr( Option::get( $option ) )
		);
	}

	public function sanitize( array $input ) {
		foreach ( $input as $option => &$value ) {
			$msg = Option::sanitize( $option, $value );

			if ( is_string( $msg ) ) {
				add_settings_error( $option, $option, $msg );
			}
		}

		return $input;
	}
}
