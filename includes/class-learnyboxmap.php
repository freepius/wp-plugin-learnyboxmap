<?php
/**
 * The core plugin class.
 *
 * This is used to define activator, desactivator, internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @subpackage LearnyboxMap/includes
 * @author     freepius
 */
class LearnyboxMap {
	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->version     = '1.0.0';
		$this->plugin_name = 'learnyboxmap';

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'desactivate' ) );
	}

	public function activate(): void {

	}

	public function desactivate(): void {

	}

	public function run(): void {

	}
}
