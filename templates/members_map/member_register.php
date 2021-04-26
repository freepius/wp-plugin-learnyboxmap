<?php
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

use \LearnyboxMap\Template as Tpl;

?>
<h2><?php esc_html_e( 'Register on the map', 'learnyboxmap' ); ?></h2>

<form action="?learnyboxmap_page_membersmap=1" method="post">
	<?php wp_nonce_field( $form->nonce, 'nonce', false ); ?>
	<input type="hidden" name="member" value="<?php echo esc_attr( $form->member ); ?>">

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
				Tpl::label_e( 'category', __( 'Your category', 'learnyboxmap' ) )
					::render( 'widget/dropdown_categories', array( 'selected' => $form->category ) )
					::help_e( __( 'members_map.field_category_help', 'learnyboxmap' ) )
					::error_e( $form->errors, 'category' );
			?>
		</div>
	<?php endif; ?>

	<!-- Member geo. coordinates: required but readonly -->
	<?php //phpcs:disable
	$form->geo_coordinates = '0, 0';
	Tpl::field( $form, 'geo_coordinates', 'text', array(
		'label'    => __( 'Geographical coordinates', 'learnyboxmap' ),
		'help'     => __( 'members_map.field_geo_coordinates_help', 'learnyboxmap' ),
		'required' => true, 'readonly' => true,
	) );
	// phpcs:enable ?>

	<!-- Member address: only used to help member to find a place on the map, will be deleted after member registration -->
	<div>
		<?php Tpl::label_e( 'address', __( 'Find a place / address on the map', 'learnyboxmap' ) ); ?>
		<input id="address" name="address" type="text" value="<?php echo esc_attr( $form->address ); ?>">
		<button id="search-address" title="<?php esc_attr_e( 'Search on the map', 'learnyboxmap' ); ?>">
			<img src="<?php echo esc_attr( \LearnyboxMap\Asset::img( 'icon-search-map-30x30.png' ) ); ?>" alt="">
		</button>
		<?php Tpl::help_e( __( 'members_map.field_address_help', 'learnyboxmap' ) )::error_e( $form->errors, 'address' ); ?>
	</div>

	<!-- Member description text -->
	<div class="block">
		<label for="description"><?php esc_html_e( 'What do you have to say to others?', 'learnyboxmap' ); ?></label>
		<div class="help"><?php echo wp_kses_post( __( 'members_map.field_description_help', 'learnyboxmap' ) ); ?></div>
		<textarea id="description" name="description" rows="20" cols="100"><?php echo esc_textarea( $form->description ); ?></textarea>
	</div>

	<!-- Consent text and checkbox that member has to accept to validate its registration. -->
	<div id="consent-field" class="required">
		<h2><?php esc_html_e( 'Your consent', 'learnyboxmap' ); ?></h2>
		<input id="consent" name="consent" type="checkbox" required>
		<label for="consent"><?php echo wp_kses_data( $consent_text ); ?></label>
	</div>

	<input type="submit" value="<?php esc_html_e( 'Validate', 'learnyboxmap' ); ?>"/>
</form>
