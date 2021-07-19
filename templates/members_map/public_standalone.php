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
 * @param string        $register_status           Was the member registration in 'error' or correctly 'created' or 'updated'?
 * @param string        $consent_text              The consent text for registration.
 * @param \WP_Term[]    $categories                The available member categories.
 * @param string        $members                   All the registered members (excepted the current one) encoded as javascript array.
 */

wp_enqueue_editor();
\LearnyboxMap\Controller\MembersMap::enqueue_scripts_and_styles( $members, $categories );

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
	</head>
	<body class="learnyboxmap-standalone">

	<?php
	$message = array(
		'error'   => __( 'There are errors in the form. Please correct them to continue. ', 'learnyboxmap' ),
		'created' => __( 'You have been successfully registered on tha map. ', 'learnyboxmap' ),
		'updated' => __( 'Your profile has been successfully updated. ', 'learnyboxmap' ),
	);

	if ( array_key_exists( $register_status, $message ) ) {
		printf(
			'<p class="notice notice-%s">%s</p>',
			esc_attr( $register_status ),
			esc_html( $message[ $register_status ] )
		);
	}
	?>

	<main id="map"></main>

	<?php
	\LearnyboxMap\Template::render( 'members_map/member_register', $vars );
	wp_print_footer_scripts();
	?>
	</body>
</html>
