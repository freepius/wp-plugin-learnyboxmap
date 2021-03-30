<?php

namespace LearnyboxMap\Repository\PostType;

use LearnyboxMap\Option;
use LearnyboxMap\Api\LearnyBox as LearnyBoxAPI;
use LearnyboxMap\Entity\PostType\Member as MemberPostType;

/**
 * Functionalities to manage the LearnyBox members, ie:
 * - Synchronize a member from LearnyBox to WordPress (through the LearnyBox API)
 * - Get, update or delete a member
 * - Get all the members
 *
 * For performance purposes, LearnyBox Member data are stored in a WordPress custom post as follows:
 * - post `title` receives:     first and last name
 * - post `name/slug` receives: slugify email
 * - post `parent ID` receives: *user_id*
 * - post `metadata` receive:   raw email and full address
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @subpackage Repository
 * @author     freepius
 */
class Member {
	/**
	 * Try to get a LearnyBox Member by its email address.
	 *
	 * The getting process follows these steps:
	 * 1. If he exists, get the member already stored in WordPress.
	 * 2. If not, synchronize the member from LearnyBox platform to WordPress.
	 * 3. If no member was found, return null.
	 *
	 * @param string $email Unsanitized email address with which search a LearnyBox Member.
	 * @return WP_Post|null
	 */
	public function get_by_email( string $email ): ?\WP_Post {
		$email = sanitize_email( $email );

		$member = new \WP_Query(
			array(
				'post_type'      => MemberPostType::name(),
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => 1,
				'name'           => $email,
			)
		);

		return $member->have_posts() ? $member->next_post() : $this->synchronize_by_email( $email );
	}

	/**
	 * Get a LearnyBox Member post by its LearnyBox *user_id*.
	 *
	 * @param integer $user_id A LearnyBox *user_id*.
	 * @return \WP_Post|null Return the post or null if none was found.
	 */
	protected function get_by_learnybox_user_id( int $user_id ): ?\WP_Post {
		$member = new \WP_Query(
			array(
				'post_type'      => MemberPostType::name(),
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => 1,
				'post_parent'    => $user_id,
			)
		);

		return $member->have_posts() ? $member->next_post() : null;
	}

	/**
	 * Try to synchronize a member from LearnyBox platform to WordPress.
	 *
	 * The synchronization process follows these steps:
	 * 1. Retrieve all LearnyBox Members through LearnyBox API.
	 * 2. If `$email` matches with the one of a member,
	 *    retrieve data of this only member through LearnyBox API.
	 * 3. In WordPress, from this getted member data:
	 *    3-1. If a LearnyBox Member post already exists with the same LearnyBox *user_id*, update the post.
	 *    3-2. Otherwise, create a new LearnyBox Member post with Draft status.
	 * 4. Return the post or null if no member was found.
	 *
	 * @param string $email Email address to search in data returned by LearnyBox API.
	 * @return \WP_Post|null
	 */
	protected function synchronize_by_email( string $email ): ?\WP_Post {
		$api = new LearnyBoxAPI( Option::get( 'api_url' ), Option::get( 'api_key' ) );

		$members = $api->get_all_members_by_training_id( (int) Option::get( 'training_id' ) );

		foreach ( $members as $partial_member ) {
			// LearnyBox member found!
			if ( $email === $partial_member->user->email ) {
				$member = $this->transform_from_api_to_wp(
					$api->get_one_member_by_id( $partial_member->user->user_id )
				);

				$post = $this->update_if_exists_by_learnybox_user_id( $member )
						?? $this->create( $member );

				return get_post( $post, \OBJECT, 'display' );
			}
		}

		return null;
	}

	/**
	 * Create, fill and return a new LearnyBox Member post (having a Draft status).
	 *
	 * @param array $member Member data in a WordPress Post style.
	 * @return \WP_Post
	 *
	 * @todo Handle the case where wp_insert_post() return 0 or a \WP_Error.
	 */
	protected function create( array $member ): \WP_Post {
		return get_post( wp_insert_post( $member ) );
	}

	/**
	 * Check if the LearnyBox *user_id* matches to a LearnyBox Member post.\
	 * If so, update and return this post. Otherwise, return null.
	 *
	 * @param array $member   New member data in a WordPress Post style.
	 * @return \WP_Post|null
	 *
	 * @todo Handle the case where wp_update_post() return 0 or a \WP_Error.
	 */
	protected function update_if_exists_by_learnybox_user_id( array $member ): ?\WP_Post {
		$post = $this->get_by_learnybox_user_id( $member['post_parent'] );

		if ( null === $post ) {
			return null;
		}

		$post_id = wp_update_post(
			sanitize_post( $member + array( 'ID' => $post->ID ), 'db' )
		);

		return get_post( $post_id );
	}

	/**
	 * Transform member data from LearnyBox API style to WordPress Post one.
	 *
	 * @param \stdClass $api_data Member data sent by LearnyBox API and json-decoded as object.
	 * @return array Member data transformed as an array having a WordPress Post style.
	 */
	protected function transform_from_api_to_wp( \stdClass $api_data ): array {
		return array(
			'post_type'    => MemberPostType::name(),
			'post_title'   => $api_data->_string,
			'post_name'    => $api_data->email,
			'post_parent'  => (int) $api_data->user_id,
			'meta_input'   => array(
				'email'   => $api_data->email,
				'address' => sprintf(
					'%s, %s %s, %s',
					$api_data->user_configs->adresse->value,
					$api_data->user_configs->code_postal->value,
					$api_data->user_configs->ville->value,
					$api_data->user_configs->pays->value,
				),
			),
		);
	}
}
