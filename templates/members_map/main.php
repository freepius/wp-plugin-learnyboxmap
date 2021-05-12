<?php
namespace LearnyboxMap;

/**
 * Template of the public, stand-alone "Members Map" page.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @subpackage templates
 * @author     freepius
 * @see        \LearnyboxMap\Controller\MembersMap class
 *
 * @param array         $vars                      All the below/template variables
 * @param \stdClass     $form                      Form data of the current member
 * @param bool          $is_registration_complete  Has the current member completed his registration on Members Map?
 * @param string        $consent_text              The consent text for registration.
 * @param \WP_Term[]    $categories                The available member categories.
 * @param string        $members                   All the registered members (excepted the current one) encoded as javascript array.
 */

Asset::enqueue_css_js( 'members-map' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
	<head>
		<title><?php esc_html_e( 'The members map', 'learnyboxmap' ); ?></title>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
			wp_print_styles();
			wp_print_head_scripts();
		?>
		<script>
			const LearnyboxMapPlugin = {
				buildUrl: '<?php echo esc_js( Asset::BUILD_URL ); ?>',
				t: {
					CurrentMemberMarkerTitle: '<?php echo esc_js( __( 'Your marker is here. Drag-drop it to change its position.', 'learnyboxmap' ) ); ?>',
				},
				members: <?php echo $members; // phpcs:ignore WordPress.Security.EscapeOutput, <= already escaped for a js usage ?>,
				categories: {
					<?php array_map( fn ( $c ) => printf( '"%s": "%s", ', (int) $c->term_id, esc_js( $c->name ) ), $categories ); ?>
				}
			};
		</script>
	</head>
	<body>

	<main id="map"></main>

	<?php
	if ( true /*false === $is_registration_complete*/ ) {
		// Case of a member whose registration on the LearnyBox Map is NOT complete yet.
		\LearnyboxMap\Template::render( 'members_map/member_register', $vars );
	} else {
		// Case of a member whose registration on the LearnyBox Map is complete.
		Template::render(
			'members_map/member_manage',
			array(
				'form'         => $form,
				'consent_text' => $consent_text,
				'categories'   => $categories,
			)
		);
	}

	wp_print_footer_scripts();
	?>
	</body>
</html>
