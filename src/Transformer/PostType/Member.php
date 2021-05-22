<?php

namespace LearnyboxMap\Transformer\PostType;

use LearnyboxMap\Entity\PostType\Member as MemberPostType;
use LearnyboxMap\Entity\Taxonomy\Category as CategoryTaxonomy;

/**
 * Transform a LearnyBox Member from one data representation to another.
 *
 * The different transformations are:
 * - from LearnyBox API to WordPress Post (as array)
 * - from WordPress Post (as object) to PHP minimalist array.
 * - from WordPress Post (as object) to HTML Form
 * - from HTML Form to WordPress Post (as array)
 * - from $_POST[] to HTML Form
 *
 * Which class generates/controls which representation?
 * - LearnyBox API    by \LearnyboxMap\Api\LearnyBox class
 * - WordPress Post   by \LearnyboxMap\Repository\PostType\Member class
 * - Minimalist array by \LearnyboxMap\Controller\MembersMap class
 * - HTML Form        by \LearnyboxMap\Controller\MembersMap class
 * - $_POST           by templates/members_map/member_register template
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @subpackage Transformer
 * @author     freepius
 */
class Member {
	/**
	 * Transform member data from *LearnyBox API* style to *WordPress Post* one.
	 *
	 * @param \stdClass $api_data Member data sent by LearnyBox API and json-decoded as object.
	 * @return array Member data transformed as an array having a *WordPress Post* style.
	 */
	public static function api_to_wp( \stdClass $api_data ): array {
		return array(
			'post_type'    => MemberPostType::name(),
			'post_title'   => $api_data->_string,
			'post_name'    => wp_hash( $api_data->email ),
			'post_parent'  => (int) $api_data->user_id,
			'meta_input'   => array(
				'email'       => $api_data->email,
				'geo_address' => sprintf(
					'%s, %s %s, %s',
					$api_data->user_configs->adresse->value,
					$api_data->user_configs->code_postal->value,
					$api_data->user_configs->ville->value,
					$api_data->user_configs->pays->value,
				),
			),
		);
	}

	/**
	 * Transform member data from *HTML Form* style to *WordPress Post* one.
	 *
	 * @param \stdClass $form_data Member data in *HTML Form* style.
	 * @return array Member data transformed as an array having a *WordPress Post* style.
	 */
	public static function form_to_wp( \stdClass $form_data ): array {
		list($latitude, $longitude) = explode( ',', $form_data->geo_coordinates );

		return array(
			'post_status'  => 'publish',
			'post_type'    => MemberPostType::name(),
			'post_title'   => $form_data->name,
			'post_name'    => wp_hash( $form_data->member ),
			'post_content' => $form_data->description,
			'meta_input'   => array(
				'email'         => $form_data->member,
				'geo_address'   => $form_data->address,
				'geo_latitude'  => $latitude,
				'geo_longitude' => ltrim( $longitude ),
			),
		);
	}

	/**
	 * Transform a *LearnyBox Member* WordPress post into a *minimalist array*.
	 *
	 * @param \WP_Post $post A *LearnyBox Member* WordPress post.
	 * @return array Member data in *minimalist array* style.
	 */
	public static function wp_to_min_array( \WP_Post $post ): array {
		return array(
			$post->post_title,
			get_the_terms( $post, CategoryTaxonomy::name() )[0]->term_id ?? '',
			$post->geo_latitude,
			$post->geo_longitude,
			nl2br( $post->post_content ),
		);
	}

	/**
	 * Transform a *LearnyBox Member* WordPress post into *HTML Form* data.
	 *
	 * @param \WP_Post $post A *LearnyBox Member* WordPress post.
	 * @return \stdClass Member data in *HTML Form* style.
	 */
	public static function wp_to_form( \WP_Post $post ): \stdClass {
		return (object) array(
			'errors'          => array(),
			'member'          => $post->email,
			'name'            => $post->post_title,
			'category'        => get_the_terms( $post, CategoryTaxonomy::name() )[0]->term_id ?? null,
			'geo_coordinates' => $post->geo_latitude && $post->geo_longitude
				? "$post->geo_latitude, $post->geo_longitude"
				: '',
			'address'         => $post->geo_address,
			'description'     => $post->post_content,
		);
	}

	/**
	 * Sanitize, validate and transform member data from *$_POST* array into *HTML Form* object.
	 *
	 * @param \WP_Term[] $categories  All the available categories for a LearnyBox Member.
	 * @return \stdClass Member data in *HTML Form* style.
	 */
	public static function post_to_form( array $categories ): \stdClass {
		$form         = new \stdClass();
		$errors       = array();
		$form->errors =& $errors;

		// No need to check *nonce* here (it's supposed to have already been done).
		// phpcs:disable WordPress.Security.NonceVerification.Missing

		$sanitize = fn ( string $field, string $fn = 'sanitize_text_field' ): string =>
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			call_user_func( $fn, wp_unslash( $_POST[ $field ] ?? '' ) );

		$form->member          = $sanitize( 'member', 'sanitize_email' );
		$form->name            = $sanitize( 'name' );
		$form->category        = intval( $_POST['category'] ?? -1 );
		$form->geo_coordinates = $sanitize( 'geo_coordinates' );
		$form->address         = $sanitize( 'address' );
		$form->description     = $sanitize( 'description', 'wp_kses_data' );

		// Check name.
		if ( empty( $form->name ) ) {
			$errors['name'] = __( 'members_map.form_error.required', 'learnyboxmap' );
		}

		// Check category, if any.
		if ( array() !== $categories ) {
			if ( -1 === $form->category ) {
				$errors['category'] = __( 'members_map.form_error.required', 'learnyboxmap' );
			} elseif ( array() === array_filter( $categories, fn ( \WP_Term $cat ): bool => $form->category === $cat->term_id ) ) {
				$errors['category'] = __( 'members_map.form_error.category.invalid', 'learnyboxmap' );
			}
		}

		// Check and sanitize geo. coordinates.
		if ( empty( $form->geo_coordinates ) ) {
			$errors['geo_coordinates'] = __( 'members_map.form_error.required', 'learnyboxmap' );
		} else {
			list($latitude, $longitude) = array_map(
				fn ( string $val ): float => floatval( trim( $val ) ),
				explode( ',', $form->geo_coordinates )
			) + array( 0, 0 );

			if ( 0 === $latitude || 0 === $longitude ) {
				$errors['geo_coordinates'] = __( 'members_map.form_error.geo_coordinates.invalid', 'learnyboxmap' );
			} else {
				$form->geo_coordinates = "$latitude, $longitude";
			}
		}

		return $form;
	}
}
