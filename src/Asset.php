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
	protected const URL        = LEARNYBOXMAP_URL . 'assets/';
	protected const PATH       = LEARNYBOXMAP_PATH . 'assets/';
	protected const IMAGES     = self::URL . 'images/';
	protected const BUILD_PATH = self::PATH . 'build/';
	public const BUILD_URL     = self::URL . 'build/';

	public static function img( string $file ): string {
		return self::IMAGES . $file . '?ver=' . LEARNYBOXMAP_VERSION;
	}

	public static function enqueue_css( string $file, array $deps = array() ): void {
		list(, $version, $url) = self::file_metadata( $file );
		wp_enqueue_style( $file, "$url.css", $deps, $version );
	}

	public static function enqueue_js( string $file, array $deps = array() ): void {
		list($more_deps, $version, $url) = self::file_metadata( $file );
		wp_enqueue_script( $file, "$url.js", array_merge( $deps, $more_deps ), $version, true );
	}

	public static function enqueue_css_js( string $file, array $css_deps = array(), array $js_deps = array() ): void {
		self::enqueue_css( $file, $css_deps );
		self::enqueue_js( $file, $js_deps );
	}

	protected static function file_metadata( string $file ): array {
		$asset_file = self::BUILD_PATH . $file . '.asset.php';

		return array_values(
			( file_exists( $asset_file ) ? (array) include $asset_file : array() )
			+ array(
				'dependencies' => array(),
				'version'      => LEARNYBOXMAP_VERSION,
				'url'          => self::BUILD_URL . $file,
			)
		);
	}
}
