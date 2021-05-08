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

	public static function help( string $message, string $tag = 'span' ): string {
		$message = 'div' === $tag ? wp_kses_post( $message ) : wp_kses_data( $message );
		return "<$tag class=\"help\">$message</$tag>";
	}

	public static function help_e( string $message, string $tag = 'span' ): string {
		echo self::help( $message, $tag ); // phpcs:ignore
		return self::class;
	}

	public static function label( string $for, string $text, bool $with_html = false ): string {
		return sprintf(
			'<label for="%s">%s</label>',
			esc_attr( $for ),
			$with_html ? wp_kses_data( $text ) : esc_html( $text )
		);
	}

	public static function label_e( string $for, string $text, bool $with_html = false ): string {
		echo self::label( $for, $text, $with_html ); // phpcs:ignore
		return self::class;
	}

	public static function input( string $name, string $value, string $type = 'text', array $attrs = array() ): string {
		$attrs += array(
			'required' => false,
			'readonly' => false,
		);

		return sprintf(
			'<input id="%1$s" name="%1$s" value="%2$s" type="%3$s" %4$s %5$s>',
			/* 1 */ esc_attr( $name ),
			/* 2 */ esc_attr( $value ),
			/* 3 */ esc_attr( $type ),
			/* 4 */ $attrs['required'] ? 'required' : '',
			/* 5 */ $attrs['readonly'] ? 'readonly' : '',
		);
	}

	public static function input_e( string $name, string $value, string $type = 'text', array $attrs = array() ): string {
		echo self::input( $name, $value, $type, $attrs ); // phpcs:ignore
		return self::class;
	}

	public static function field( \stdClass $form, string $name, $type = 'text', array $attrs = array() ): string {
		$attrs += array(
			'label'    => '',
			'help'     => '',
			'required' => false, // @fixme: keep it here, or just in input() ?
		);

		printf(
			'<div class="%1$s">
				%2$s
				%3$s
				%4$s
				%5$s
			</div>',
			/* 1 */ $attrs['required'] ? 'required' : '',                           // phpcs:ignore
			/* 2 */ $attrs['label'] ? self::label( $name, $attrs['label'] ) : '',   // phpcs:ignore
			/* 3 */ self::input( $name, $form->$name ?? '', 'text', $attrs ),       // phpcs:ignore
			/* 4 */ $attrs['help'] ? self::help( $attrs['help'] ) : '',             // phpcs:ignore
			/* 5 */ self::error( $form->errors ?? array(), $name ),                 // phpcs:ignore
		);

		return self::class;
	}
}
