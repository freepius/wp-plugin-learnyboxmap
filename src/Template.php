<?php

namespace LearnyboxMap;

/**
 * Functionalities to manage and render the plugin templates.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @author     freepius
 */
class Template {
	protected const PATH = LEARNYBOXMAP_PATH . 'templates/';

	/**
	 * Print a php template.
	 *
	 * @param string          $template  Template name, commonly the file name without extension.
	 * @param array|\stdClass $vars      Variables that the template has access.
	 */
	public static function render( string $template, $vars = array() ): string {
		$vars = (array) $vars;
		extract( $vars ); // phpcs:ignore WordPress.PHP.DontExtract
		require self::PATH . $template . '.php';
		return self::class;
	}

	/**
	 * Return `$one` error from an `$errors` array.
	 *
	 * @param array  $errors  Keys are error identifiers ; values are error messages.
	 * @param string $id      Id. of the error to return.
	 */
	public static function error( array $errors, string $id ): string {
		return empty( $errors[ $id ] )
			? ''
			: sprintf(
				'<span id="error-%s" class="error">%s</span>',
				esc_attr( $id ),
				wp_kses_data( $errors[ $id ] )
			);
	}

	public static function error_e( array $errors, string $id ): string {
		echo self::error( $errors, $id ); // phpcs:ignore
		return self::class;
	}

	public static function help( string $help_message ): string {
		return '<span class="help">' . wp_kses_data( $help_message ) . '</span>';
	}

	public static function help_e( string $help_message ): string {
		echo self::help( $help_message ); // phpcs:ignore
		return self::class;
	}

	public static function label( string $for, string $text ): string {
		return sprintf( '<label for="%s">%s</label>', esc_attr( $for ), esc_html( $text ) );
	}

	public static function label_e( string $for, string $text ): string {
		echo self::label( $for, $text ); // phpcs:ignore
		return self::class;
	}

	public static function field( \stdClass $form, string $name, $type = 'text', array $attrs = array() ): string {
		$attrs += array(
			'label'    => '',
			'help'     => '',
			'required' => false,
			'readonly' => false,
		);

		printf(
			'<div class="%1$s">
				%2$s
				<input type="text" id="%3$s" name="%3$s" value="%4$s" %1$s %5$s>
				%6$s
				%7$s
			</div>',
			/* 1 */ $attrs['required'] ? 'required' : '',                           // phpcs:ignore
			/* 2 */ $attrs['label'] ? self::label( $name, $attrs['label'] ) : '',   // phpcs:ignore
			/* 3 */ esc_attr( $name ),
			/* 4 */ esc_attr( $form->$name ?? '' ),
			/* 5 */ $attrs['readonly'] ? 'readonly' : '',                           // phpcs:ignore
			/* 6 */ $attrs['help'] ? self::help( $attrs['help'] ) : '',             // phpcs:ignore
			/* 7 */ self::error( $form->errors ?? array(), $name ),                 // phpcs:ignore
		);

		return self::class;
	}
}
