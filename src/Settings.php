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
		add_action( 'admin_menu', array( $this, 'menu_add' ) );
		add_action( 'admin_init', array( $this, 'init' ) );
	}

	/**
	 * Add a submenu to administrate the settings.
	 */
	public function menu_add(): void {
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

	/**
	 * Print the settings page.
	 */
	public function page(): void {
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

	/**
	 * Initialize the settings, sections and fields.
	 */
	public function init(): void {
		// Register the plugin global option and its global sanitizing function.
		register_setting( self::PAGE, Option::NAME, array( 'sanitize_callback' => array( $this, 'sanitize' ) ) );

		// Section and fields for the LearnyBox options.
		add_settings_section(
			'learnybox',
			__( 'LearnyBox settings', 'learnyboxmap' ),
			array( $this, 'section_learnybox' ),
			self::PAGE,
		);

		$fields = array(
			'api_key'     => array( 'learnybox', __( 'Your LearnyBox API key', 'learnyboxmap' ) ),
			'api_url'     => array( 'learnybox', __( 'Your LearnyBox URL', 'learnyboxmap' ) ),
			// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			'training_id' => array( 'learnybox', __( 'ID of one of your LearnyBox trainings', 'learnyboxmap' ), array( 'type' => 'integer', 'min' => 0 ) ),
		);

		// Section and fields for the "Members Map" page options.
		add_settings_section(
			'members_map',
			__( 'Members Map page settings', 'learnyboxmap' ),
			array( $this, 'section_members_map' ),
			self::PAGE,
		);

		$fields['consent_text'] = array(
			'members_map',
			__( 'Consent text', 'learnyboxmap' ),
			array(
				'type'            => 'html_editor',
				// 'help'         => __( 'Consent text that your LearnyBox members have to accept to be registered and displayed on the Members Map', 'learnyboxmap' ),
				'help'            => __( 'admin.settings.field_consent_text_help', 'learnyboxmap' ),
				'default_content' => __( 'admin.settings.field_consent_text_default', 'learnyboxmap' ),
			),
		);

		// Render all previously registered fields.
		foreach ( $fields as $option => $attrs ) {
			list($section, $title, $field_attrs) = $attrs + array( 2 => array() );

			add_settings_field(
				$option,
				$title,
				fn () => $this->render_field( $option, $field_attrs ),
				self::PAGE,
				$section,
				array( 'label_for' => $option )
			);
		}
	}

	/**
	 * Print a help message for the 'learnybox' section.
	 */
	public function section_learnybox(): void {
		echo '<p>' . esc_html__( 'admin.settings.section_learnybox_help', 'learnyboxmap' ) . '</p>';
	}

	/**
	 * Print a help message for the 'members_map' section.
	 */
	public function section_members_map(): void {
		echo '<p>' . esc_html__( 'admin.settings.section_members_map_help', 'learnyboxmap' ) . '</p>';
	}

	/**
	 * Render a field for a given plugin option.
	 *
	 * @param string $option The plugin option for which to render the field.
	 * @param array  $attrs  The field attributes (including its type).
	 */
	public function render_field( string $option, array $attrs = array() ): void {
		// Merge some default attributes.
		$attrs += array( 'type' => 'text' );

		'html_editor' === $attrs['type']
			? $this->html_editor( $option, $attrs )
			: $this->input( $option, $attrs );
	}

	/**
	 * Print a html 'input' tag for a given plugin option.
	 *
	 * @param string $option The plugin option for which to display the 'input' tag.
	 * @param array  $attrs  The 'input' tag attributes.
	 */
	protected function input( string $option, array $attrs = array() ): void {
		// Merge some default attributes.
		$attrs += array( 'size' => 40 );

		// Inline the attributes.
		$inlined_attrs = '';
		foreach ( $attrs as $attr => $value ) {
			$inlined_attrs .= sprintf( ' %s="%s"', $attr, $value );
		}

		$input = sprintf(
			'<input id="%s" name="%s" value="%s" %s>',
			$option,
			Option::name( $option ),
			Option::get( $option ),
			$inlined_attrs
		);

		$allowed_input_attrs = array( 'id', 'name', 'value', 'type', 'size', 'min' );

		echo wp_kses( $input, array( 'input' => array_fill_keys( $allowed_input_attrs, array() ) ) );
	}
	/**
	 * Print a html editor for a given plugin option.
	 *
	 * @param string $option The plugin option for which to display the html editor.
	 * @param array  $attrs  The editor attributes.
	 *
	 * @todo Probably move the 'help' message in render_field().
	 */
	protected function html_editor( string $option, array $attrs = array() ): void {
		// Merge some default attributes.
		$attrs += array(
			'default_content' => '',
			'textarea_name'   => Option::name( $option ),
			'teeny'           => true,
			'textarea_rows'   => 10,
			'quicktags'       => false,
			'media_buttons'   => false,
			'wpautop'         => false,
		);

		if ( isset( $attrs['help'] ) ) {
			echo esc_html( $attrs['help'] );
		}

		wp_editor( Option::get( $option ) ?: $attrs['default_content'], $option, $attrs );
	}

	/**
	 * Sanitize each sub-option of the plugin global option.
	 * If some unfixable errors occur, add settings errors to be displayed to the user.
	 *
	 * @param array $input An array containing options (as keys) and their inputs (as values).
	 * @return array       Return an array of options (as keys) and their sanitazed inputs (as values).
	 */
	public function sanitize( array $input ): array {
		foreach ( $input as $option => &$value ) {
			try {
				$value = Option::sanitize( $option, $value );
			} catch ( \InvalidArgumentException $e ) {
				add_settings_error( $option, $option, __( 'Error:', 'learnyboxmap' ) . ' ' . $e->getMessage() );
			}
		}

		return $input;
	}
}
