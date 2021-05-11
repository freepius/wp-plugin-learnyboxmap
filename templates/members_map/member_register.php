<?php
namespace LearnyboxMap;

use \LearnyboxMap\Template as Tpl;

/**
 * Render a form allowing a LearnyBox Member to register on the Members Map.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @subpackage templates
 * @author     freepius
 * @see        *members_map/main* template
 *
 * @global array         $vars          All the below/template variables
 * @global \stdClass     $form          Form data of the current member
 * @global string        $consent_text  The consent text for registration.
 * @global \WP_Term[]    $categories    The available member categories.
 */
?>
<h2><?php esc_html_e( 'Register on the map', 'learnyboxmap' ); ?></h2>

<form action="?learnyboxmap_page_membersmap=1" method="post">
	<?php
		wp_nonce_field( $form->nonce, 'nonce', false );
		Tpl::input( 'member', $form->member, 'hidden' );
	?>

	<!-- Member name: required -->
	<?php //phpcs:disable
	Tpl::field( $form, 'name', 'text', array(
		'label'    => __( 'Name to display', 'learnyboxmap' ),
		'help'     => __( 'members_map.field_name_help', 'learnyboxmap' ),
		'required' => true,
	) );
	// phpcs:enable ?>

	<!-- Member category: required -->
	<?php if ( $categories ) : ?>
		<div class="required">
			<?php
				Tpl::label( 'category', __( 'My category', 'learnyboxmap' ) )
					::render( 'widget/dropdown_categories', array( 'selected' => $form->category ) )
					::help( __( 'members_map.field_category_help', 'learnyboxmap' ) )
					::error( $form->errors, 'category' );
			?>
		</div>
	<?php endif; ?>

	<!-- Member geo coordinates: required but readonly -->
	<div class="required">
	<?php
		Tpl::label( 'geo_coordinates', __( 'Geographical coordinates', 'learnyboxmap' ) )
			::input( 'geo_coordinates', $form->geo_coordinates, 'text', array( 'readonly' => true ) )
			::button(
				'member-marker',
				Tpl::img_( 'icon-member-marker-30x30.png' ),
				array( 'title' => __( 'See / place my marker on the map', 'learnyboxmap' ) )
			)
			::help( __( 'members_map.field_geo_coordinates_help', 'learnyboxmap' ) )
			::error( $form->errors, 'geo_coordinates' );
		?>
	</div>

	<!-- Member address: used to help member to find his geo coordinates -->
	<div>
	<?php
		Tpl::label( 'address', __( 'My place / address', 'learnyboxmap' ) )
			::input( 'address', $form->geo_coordinates, 'text' )
			::button(
				'search-address',
				Tpl::img_( 'icon-search-address-30x30.png' ),
				array( 'title' => __( 'Find this place / address on the map, then update my geographical coordinates accordingly', 'learnyboxmap' ) )
			)
			::help( __( 'members_map.field_address_help', 'learnyboxmap' ) )
			::error( array( 'address' => __( 'members_map.form_error.address.unknown', 'learnyboxmap' ) ), 'address' );
		?>
	</div>

	<!-- Member description text -->
	<div class="block">
		<?php
			Tpl::label( 'description', __( 'What do I have to say about myself?', 'learnyboxmap' ) )
				::help( __( 'members_map.field_description_help', 'learnyboxmap' ), 'div' );
		?>
		<textarea id="description" name="description" rows="20" cols="100"><?php echo esc_textarea( $form->description ); ?></textarea>
	</div>

	<!-- Consent text and checkbox that member has to accept to validate its registration. -->
	<div id="consent-field" class="required">
		<h2><?php esc_html_e( 'My consent', 'learnyboxmap' ); ?></h2>
		<?php
			Tpl::input( 'consent', 'on', 'checkbox', array( 'required' => true ) )
				::label( 'consent', $consent_text, true );
		?>
	</div>

	<input type="submit" value="<?php esc_attr_e( 'Validate', 'learnyboxmap' ); ?>"/>
</form>
