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
	public static function render( string $template, $vars = array() ): void {
		extract( (array) $vars ); // phpcs:ignore WordPress.PHP.DontExtract
		require self::PATH . $template . '.php';
	}
}
