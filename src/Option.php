<?php

namespace LearnyboxMap;

/**
 * Functionalities to manage the plugin options (get, set and sanitize).
 *
 * In practice, only one global option (an array) is manage through the WordPress Options API.
 * In combination, the Settings class is used to administrate these options.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @author     freepius
 */
class Option {
	// Name of the global option.
	public const NAME = 'learnyboxmap_options';

	// List of all the available options.
	protected const AVAILABLE = array( 'api_key', 'api_url', 'training_id' );

	/**
	 * Check if a given option is available for this plugin. If not, throw an exception.
	 *
	 * @param string $option The option to check.
	 * @throws \InvalidArgumentException If $option is not a valid one for this plugin.
	 */
	protected static function check_option_exists( string $option ): void {
		if ( ! in_array( $option, self::AVAILABLE, true ) ) {
			throw new \InvalidArgumentException( "'$option' is not a valid LearnyboxMap plugin option." );
		}
	}

	/**
	 * Return the full name of an option (used by the Settings class).
	 *
	 * @param string $option Option name.
	 * @return string
	 */
	public static function name( string $option ): string {
		self::check_option_exists( $option );
		return sprintf( '%s[%s]', self::NAME, $option );
	}

	/**
	 * Return the value of an option.
	 *
	 * @param string $option Name of the option to get.
	 */
	public static function get( string $option ) {
		self::check_option_exists( $option );
		return get_option( self::NAME )[ $option ] ?? null;
	}

	/**
	 * Sanitize a given value for an option.
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  Value to sanitize (passed by reference and modified if necessary).
	 * @return true|string If any error/s occur, return the error message/s. Otherwise, return true.
	 */
	public static function sanitize( string $option, &$value ) {
		self::check_option_exists( $option );

		if ( method_exists( self::class, "sanitize_$option" ) ) {
			return self::{"sanitize_$option"}( $value );
		}

		return true;
	}

	protected static function sanitize_api_url( &$value ) {
		$value = trailingslashit( esc_url_raw( $value ) );

		if ( 'https://' !== substr( $value, 0, 8 ) || '.learnybox.com/' !== substr( $value, -15 ) || strlen( $value ) < 24 ) {
			return __( 'Error: the URL entered doesn\'t follow the expected pattern "https://{your-sub-domain}.learnybox.com/"', 'learnyboxmap' );
		}

		return true;
	}
}
