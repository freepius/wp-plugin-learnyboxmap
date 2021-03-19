<?php

namespace LearnyboxMap;

/**
 * Functionalities and hooks to administrate the plugin settings.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @author     freepius
 */
class AdminSettings {
	// Option names used to store the plugin settings through the WordPress Settings API.
	const API_KEY = 'learnyboxmap_api_key';
	const API_URL = 'learnyboxmap_api_url';

	public function __construct() {
		// Add a submenu to administrate the settings.
		add_action( 'admin_menu', array( $this, 'menu_add' ) );

		// Init the settings, sections and fields.
		add_action( 'admin_init', array( $this, 'init' ) );
	}

	public function menu_add() {
		// This 'settings' submenu is placed inside the 'learnyboxmap' menu ( see Admin::menu_add() ).
		add_submenu_page(
			'learnyboxmap',
			__( 'Settings for the LearnyboxMap plugin', 'learnyboxmap' ),
			__( 'Settings', 'learnyboxmap' ),
			'administrator',
			'learnyboxmap-settings',
			array( $this, 'page' )
		);
	}

	public function page() {
		echo '
		<div class="wrap">
			<h1>' . esc_html( get_admin_page_title() ) . '</h1>
			<form action="options.php" method="post">';
				settings_fields( 'learnyboxmap' );
				do_settings_sections( 'learnyboxmap-settings' );
				submit_button();
		echo '
			</form>
		</div>';
	}

	public function init() {
		register_setting( 'learnyboxmap', self::API_KEY );
		register_setting( 'learnyboxmap', self::API_URL, array( 'sanitize_callback' => array( $this, 'sanitize_api_url' ) ) );

		// Section and fields for the LearnyBox API calls.
		add_settings_section(
			'api',
			__( 'LearnyBox API settings', 'learnyboxmap' ),
			array( $this, 'section_api' ),
			'learnyboxmap-settings'
		);

		$fields = array(
			self::API_KEY => array( 'api', __( 'Your LearnyBox API key', 'learnyboxmap' ) ),
			self::API_URL => array( 'api', __( 'Your LearnyBox URL', 'learnyboxmap' ) ),
		);

		foreach ( $fields as $field => list($section, $title) ) {
			add_settings_field(
				$field,
				$title,
				fn () => $this->input_text( $field ),
				'learnyboxmap-settings',
				$section,
				array( 'label_for' => $field )
			);
		}
	}

	public function section_api() {
		echo '<p>' . esc_html__( 'section_api_help', 'learnyboxmap' ) . '</p>';
		settings_errors( self::API_KEY );
	}

	public function input_text( string $field ) {
		echo sprintf(
			'<input type="text" id="%s" name="%1$s" value="%s" size="40">',
			$field, // phpcs:disable WordPress.Security.EscapeOutput
			esc_attr( get_option( $field ) ?? '' )
		);
	}

	public function sanitize_api_url( string $input ) {
		$input = trailingslashit( esc_url_raw( $input ) );

		if ( 'https://' !== substr( $input, 0, 8 ) || '.learnybox.com/' !== substr( $input, -15 ) || strlen( $input ) < 24 ) {
			add_settings_error(
				self::API_KEY,
				self::API_KEY,
				__( 'Error: the URL entered doesn\'t follow the expected pattern "https://{your-sub-domain}.learnybox.com/"', 'learnyboxmap' )
			);
		}

		return $input;
	}
}
