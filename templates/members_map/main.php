<?php
/**
 * Template of the public, stand-alone "Members Map" page.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @subpackage templates
 * @author     freepius
 * @see        \LearnyboxMap\Controller\MembersMap class
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
	if ( $email && ! $member ) {
		// Case of an email entered but not found in LearnyBox members list.
		// translators: %s: The email address in error.
		echo esc_html( sprintf( __( 'No member found with email address: %s', 'learnyboxmap' ), $email ) );
	} elseif ( $member && false === $is_registration_complete ) {
		// Case of a member whose registration on the LearnyBox Map is NOT complete yet.
		\LearnyboxMap\Template::render(
			'members_map/member_register',
			array(
				'member'       => $member,
				'consent_text' => $consent_text,
			)
		);
	} elseif ( $member ) {
		// Case of a member whose registration on the LearnyBox Map is complete.
		\LearnyboxMap\Template::render(
			'members_map/member_manage',
			array(
				'member'       => $member,
				'consent_text' => $consent_text,
			)
		);
	}

	wp_print_footer_scripts();
	?>
	</body>
</html>
