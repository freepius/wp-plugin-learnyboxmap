<?php
/**
 * Template of the public, stand-alone "Members Map" page.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @subpackage templates
 * @author     freepius
 * @see        \LearnyboxMap\Controller\MembersMap class
 *
 * @global array         $vars                      All the below/template variables
 * @global \stdClass     $form                      Form data of the current member
 * @global bool          $is_registration_complete  Has the current member completed his registration on Members Map?
 * @global string        $consent_text              The consent text for registration.
 * @global \WP_Term[]    $categories                The available member categories.
 */

\LearnyboxMap\Asset::enqueue_css_js( 'members-map' );
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
	<body>
	<?php
	if ( false === $is_registration_complete ) {
		// Case of a member whose registration on the LearnyBox Map is NOT complete yet.
		\LearnyboxMap\Template::render(
			'members_map/member_register',
			array(
				'form'         => $form,
				'consent_text' => $consent_text,
				'categories'   => $categories,
			)
		);
	} else {
		// Case of a member whose registration on the LearnyBox Map is complete.
		\LearnyboxMap\Template::render(
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
