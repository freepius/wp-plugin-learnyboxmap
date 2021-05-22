<?php

namespace LearnyboxMap\Repository\PostType;

use LearnyboxMap\Option;
use LearnyboxMap\Api\LearnyBox as LearnyBoxAPI;
use LearnyboxMap\Entity\PostType\Member as MemberPostType;
use LearnyboxMap\Entity\Taxonomy\Category;
use LearnyboxMap\Transformer\PostType\Member as MemberTransformer;

/**
 * Functionalities to manage the LearnyBox members, ie:
 * - Synchronize a member from LearnyBox to WordPress (through the LearnyBox API)
 * - Get, update or delete a member
 * - Get all members registered on the map.
 *
 * For performance purposes, LearnyBox Member data are stored in a WordPress custom post as follows:
 * - post `title` receives:     name to display on the map (commonly the first and last name, or nickname)
 * - post `name/slug` receives: slugify email
 * - post `parent ID` receives: LearnyBox *user_id* (misuse of parent field, but nice to quickly retrieve a member)
 * - post `content` receives:   text describing the member, his activities, contact details, etc.
 * - post `metadata` receive:   raw email, full address and geo coordinates.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @subpackage Repository
 * @author     freepius
 */
class Member {
	/**
	 * Get all members registered on the map, ie having a post status to *publish*.
	 *
	 * @param array $excepted  An array of Member post IDs to not return.
	 * @return \WP_Post[]
	 */
	public function get_all_registered( array $excepted = array() ) {
		return get_posts(
			array(
				'post_type'      => MemberPostType::name(),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'post__not_in'   => $excepted,
			)
		);
	}

	/**
	 * Update a LearnyBox Member from *HTML Form* data.
	 *
	 * @param \WP_Post  $member     Member post to update.
	 * @param \stdClass $form_data  *HTML Form* data.
	 * @return \WP_Post|null The updated Member post, or null in case of error.
	 */
	public function update_by_form_data( \WP_Post $member, \stdClass $form_data ): ?\WP_Post {
		$post_id = wp_update_post(
			array( 'ID' => $member->ID )
			+ sanitize_post( MemberTransformer::form_to_wp( $form_data ), 'db' )
		);

		// Cannot use the 'tax_input' arg of wp_update_post() to update a taxonomy
		// because we are potentially running this function without user context.
		// In that case: no user = no permissions = no terms being assigned.
		// But wp_set_object_terms() doesn't check permissions.
		if ( -1 !== $form_data->category ) {
			wp_set_object_terms( $post_id, (int) $form_data->category, Category::name() );
		}

		return get_post( $post_id );
	}

	/**
	 * Try to get a LearnyBox Member by its email address.
	 *
	 * The getting process follows these steps:
	 * 1. If he exists, get the member already stored in WordPress.
	 * 2. If not, synchronize the member from LearnyBox platform to WordPress.
	 * 3. If no member was found, return null.
	 *
	 * @param string $email Unsanitized email address with which search a LearnyBox Member.
	 * @return \WP_Post|null
	 */
	public function get_by_email( string $email ): ?\WP_Post {
		$email        = sanitize_email( $email );
		$hashed_email = wp_hash( $email );

		$member = new \WP_Query(
			array(
				'post_type'      => MemberPostType::name(),
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => 1,
				'name'           => $hashed_email,
			)
		);

		return $member->have_posts() ? $member->next_post() : $this->synchronize_by_email( sanitize_email( $email ) );
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
				$member = MemberTransformer::api_to_wp(
					$api->get_one_member_by_id( $partial_member->user->user_id )
				);

				return $this->update_if_exists_by_learnybox_user_id( $member )
					?? $this->create( $member );
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
			sanitize_post( array( 'ID' => $post->ID ) + $member, 'db' )
		);

		return get_post( $post_id );
	}
}
