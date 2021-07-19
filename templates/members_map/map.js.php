<?php
namespace LearnyboxMap;

/**
 * Generate javascript for the Members Map.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @subpackage templates
 * @author     freepius
 *
 * @param array         $vars        All the below/template variables
 * @param \WP_Term[]    $categories  The available member categories.
 * @param string        $members     All the registered members (excepted the current one) encoded as javascript array.
 */
?>
const LearnyboxMapPlugin = {
	buildUrl: '<?php echo esc_js( Asset::BUILD_URL ); ?>',
	t: {
		CurrentMemberMarkerTitle: '<?php echo esc_js( __( 'Your marker is here. Drag-drop it to change its position.', 'learnyboxmap' ) ); ?>',
	},
	members: <?php echo $members; // phpcs:ignore WordPress.Security.EscapeOutput, <= already escaped for a js usage ?>,
	categories: {
		<?php array_map( fn ( $c ) => printf( '"%s": "%s", ', (int) $c->term_id, esc_js( $c->name ) ), $categories ); ?>
	}
}
