<?php
/**
 * EN: The LearnyboxMap plugin allows your LearnyBox members to add themselves on an interactive map and see other members already added.
 * As a WordPress admin, you can configure the plugin and the map, as well as manage the displayed members.
 *
 * FR: Le plugin LearnyboxMap permet à vos membres LearnyBox de s'ajouter sur une carte interactive et de voir les autres membres déjà ajoutés.
 * En tant qu'administrateur WordPress, vous pouvez configurer le plugin et la carte, ainsi que gérer les membres qui y sont affichés.
 *
 * @since             1.0.0
 * @package           LearnyboxMap
 * @author            freepius
 *
 * @wordpress-plugin
 * Plugin Name:       LearnyboxMap
 * Plugin URI:        https://github.com/freepius/wp-plugin-learnybox-map
 * Description:       LearnyboxMap allows your LearnyBox members to be displayed on an interactive map and see other members there.
 * Version:           1.0.0
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            freepius
 * Author URI:        https://freepius.net
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       learnyboxmap
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || die;

require __DIR__ . '/vendor/autoload.php';

define( 'LEARNYBOXMAP_VERSION', '1.0.0' );

// Plugin activator and desactivator.
register_activation_hook( __FILE__, array( 'LearnyboxMap\Main', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'LearnyboxMap\Main', 'desactivate' ) );

new LearnyboxMap\Main();
