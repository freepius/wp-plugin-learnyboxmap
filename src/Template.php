<?php

namespace LearnyboxMap;

/**
 * This class:
 * - allows to manage and render the plugin templates
 * - offers methods to display various html elements (form elements, img, etc.)
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
	 * Print the return value of Template static methods (like img_(), error_(), input_(), label_()...)
	 * and return the class name (to allow chaining of method calls).
	 */
	public static function __callStatic( string $name, array $arguments ): string {
		$name = $name . '_';
		echo self::$name( ...$arguments ); // phpcs:ignore WordPress.Security.EscapeOutput, <= the method is supposed to escape its ouput
		return self::class;
	}

	public static function img_( string $file ): string {
		return sprintf( '<img src="%s" alt="">', esc_attr( Asset::img( $file ) ) );
	}

	/**
	 * Return `$one` error from an `$errors` array.
	 *
	 * @param array  $errors  Keys are error identifiers ; values are error messages.
	 * @param string $id      Id. of the error to return.
	 */
	public static function error_( array $errors, string $id ): string {
		return empty( $errors[ $id ] )
			? ''
			: sprintf(
				'<span id="error-%s" class="error">%s</span>',
				esc_attr( $id ),
				wp_kses_data( $errors[ $id ] )
			);
	}

	public static function help_( string $message, string $tag = 'span' ): string {
		$tag     = in_array( $tag, array( 'span', 'div' ), true ) ? $tag : 'span';
		$message = 'div' === $tag ? wp_kses_post( $message ) : wp_kses_data( $message );
		return "<$tag class=\"help\">$message</$tag>";
	}

	public static function label_( string $for, string $text, bool $with_html = false ): string {
		return sprintf(
			'<label for="%s">%s</label>',
			esc_attr( $for ),
			$with_html ? wp_kses_data( $text ) : esc_html( $text )
		);
	}

	public static function input_( string $name, string $value, string $type = 'text', array $attrs = array() ): string {
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

	public static function button_( string $id, string $value, array $attrs = array() ) {
		$attrs += array(
			'title' => '',
		);

		return sprintf(
			'<button id="%1$s" %3$s>%2$s</button>',
			/* 1 */ esc_attr( $id ),
			/* 2 */ wp_kses( $value, 'learnyboxmap_button' ),
			/* 3 */ $attrs['title'] ? ( 'title="' . esc_attr( $attrs['title'] ) . '"' ) : ''
		);
	}

	public static function field_( \stdClass $form, string $name, $type = 'text', array $attrs = array() ): string {
		$attrs += array(
			'label'    => '',
			'help'     => '',
			'required' => false, // @fixme: keep it here, or just in input() ?
		);

		return sprintf(
			'<div class="%1$s">
				%2$s
				%3$s
				%4$s
				%5$s
			</div>',
			/* 1 */ $attrs['required'] ? 'required' : '',
			/* 2 */ $attrs['label'] ? self::label_( $name, $attrs['label'] ) : '',
			/* 3 */ self::input_( $name, $form->$name ?? '', 'text', $attrs ),
			/* 4 */ $attrs['help'] ? self::help_( $attrs['help'] ) : '',
			/* 5 */ self::error_( $form->errors ?? array(), $name ),
		);
	}
}
