<h2><?php esc_html_e( 'Register on the map', 'learnyboxmap' ); ?></h2>

<form>
	<input type="hidden" name="ID" value="<?php echo esc_attr( $member->ID ); ?>"/>

	<!-- Member name: required -->
	<div class="required">
		<label for="name"><?php esc_html_e( 'Name to display', 'learnyboxmap' ); ?></label>
		<input id="name" name="name" type="text" size="50" required
			value="<?php echo esc_attr( $member->post_title ); ?>">
		<span class="help"><?php echo wp_kses_data( __( 'template.members_map.field_name_help', 'learnyboxmap' ) ); ?></span>
	</div>

	<!-- Member description text -->
	<div>
		<label for="description"><?php esc_html_e( 'What do you have to say to others?', 'learnyboxmap' ); ?></label>
		<textarea id="description" name="description" rows="20" cols="100"><?php echo esc_textarea( $member->post_content ); ?></textarea>
		<div class="help"><?php echo wp_kses_post( __( 'template.members_map.field_description_help', 'learnyboxmap' ) ); ?></div>
	</div>

	<!-- Member geo. coordinates: required but readonly -->
	<div class="required">
		<label for="geo_coordinates"><?php esc_html_e( 'Geographical coordinates', 'learnyboxmap' ); ?></label>
		<input id="geo_coordinates" name="geo_coordinates" type="text" size="25" required readonly
		<?php
			echo $member->geo_latitude && $member->geo_longitude
				? 'value="' . esc_attr( $member->geo_latitude . ', ' . $member->geo_longitude ) . '"'
				: '';
		?>
		>
		<span class="help"><?php echo wp_kses_data( __( 'template.members_map.field_geo_coordinates_help', 'learnyboxmap' ) ); ?></span>
	</div>

	<!-- Member address: only used to help member to find a place on the map, will be deleted after member registration -->
	<div>
		<label for="address"><?php esc_html_e( 'Find a place / address on the map', 'learnyboxmap' ); ?></label>
		<input id="address" name="address" type="text" size="50" value="<?php echo esc_attr( $member->geo_address ); ?>">
		<span class="help"><?php echo wp_kses_data( __( 'template.members_map.field_address_help', 'learnyboxmap' ) ); ?></span>
		<button id="search-address"><?php esc_html_e( 'Search on the map', 'learnyboxmap' ); ?></button>
	</div>

	<input type="submit" value="<?php esc_html_e( 'Validate', 'learnyboxmap' ); ?>"/>
</form>
