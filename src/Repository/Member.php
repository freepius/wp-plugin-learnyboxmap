<?php

namespace LearnyboxMap\Repository;

/**
 * Functionalities to manage the LearnyBox members, ie:
 * => @TODO: Synchronize members from LearnyBox to WordPress (through the LearnyBox API)
 * => @TODO: Get all the members
 * => @TODO: Get, update or delete a given member
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @subpackage Repository
 * @author     freepius
 */
class Member {
	/**
	 * Get a LearnyBox member by its email address.
	 *
	 * @param string $email Email address to search in metadata of LearnyBox Member posts.
	 * @return WP_Post|null
	 */
	public function get_by_email( string $email ): ?\WP_Post {
		$member = new \WP_Query(
			array(
				'post_type'      => \LearnyboxMap\PostType\Member::name(),
				'posts_per_page' => 1,
				'meta_key'       => 'email',
				'meta_value'     => $email,
			)
		);

		return $member->have_posts() ? $member->the_post() : null;
	}

	/**
	 * Try to synchronize a member from LearnyBox platform to WordPress, following these steps:
	 * 1) Retrieve all LearnyBox members by an API call.
	 * 2) If $email matches with the one of a member, get this only member.
	 * 3) In WordPress, from this getted member data, create a new LearnyBox member post with Draft status
	 * 4) Return this post or null if no member was found.
	 *
	 * @param string $email Email address to search in data returned by LearnyBox API.
	 * @return \WP_Post|null
	 */
	public function synchronize_by_email( string $email ): ?\WP_Post {
		return null;
	}
}
