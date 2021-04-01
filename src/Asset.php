<?php

namespace LearnyboxMap;

/**
 * Functionalities to manage the plugin assets.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @author     freepius
 */
class Asset {
	protected const URL    = LEARNYBOXMAP_URL . 'assets/';
	protected const CSS    = self::URL . 'css/';
	protected const JS     = self::URL . 'js/';
	protected const IMAGES = self::URL . 'images/';

	public static function enqueue_css( string $file ): void {
		wp_enqueue_style( $file, self::CSS . $file . '.css', array(), LEARNYBOXMAP_VERSION );
	}

	public static function enqueue_js( string $file ): void {
		wp_enqueue_script( $file, self::JS . $file . '.js', array(), LEARNYBOXMAP_VERSION, true );
	}

	public static function enqueue_css_js( string $file ): void {
		self::enqueue_css( $file );
		self::enqueue_js( $file );
	}
}
