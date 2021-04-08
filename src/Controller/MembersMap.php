<?php

namespace LearnyboxMap\Controller;

use \LearnyboxMap\Option;
use \LearnyboxMap\Template;
use \LearnyboxMap\Entity\PostType\Member as MemberPostType;
use \LearnyboxMap\Entity\Taxonomy\Category as CategoryTaxonomy;
use \LearnyboxMap\Repository\PostType\Member as MemberRepository;

/**
 * Functionalities and hooks to manage the "Members Map" page.
 *
 * This page is PUBLIC, STAND-ALONE (ie with its own html) and contains:
 * 1) A Leaflet map showing all LearnyBox members already registered on it.
 * 2) If a member is passed to the URL (through a custom query var):
 *   2.1) If he is not yet registered on the map: allow him to register (with forms and messages on purpose).
 *   2.2) If he is already registered on the map: allow him to manage his registration (update or delete his data, etc.)
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @subpackage Controller
 * @author     freepius
 */
class MembersMap {
	protected const NONCE_KEY = 'learnyboxmap_members_map_nonce';

	protected MemberRepository $member_repo;

	public function __construct() {
		add_filter( 'query_vars', array( $this, 'allow_query_vars' ) );
		add_action( 'parse_request', array( $this, 'load_page_if' ) );
	}

	/**
	 * Add 2 query vars: 1-st to load page, 2-nd to pass it a LearnyBox member.
	 *
	 * @param array $query_vars List of allowed query variables (GET and POST).
	 */
	public function allow_query_vars( array $query_vars ): array {
		$query_vars[] = 'learnyboxmap_page_membersmap';

		if ( ! in_array( 'member', $query_vars, true ) ) {
			$query_vars[] = 'member';
		}

		return $query_vars;
	}

	/**
	 * Check that everything looks good before loading the page.
	 * Otherwise, exit with an error.
	 *
	 * @param \WP $wp Current WordPress environment instance (passed by reference).
	 *
	 * @todo check http referer is the good one
	 */
	protected function do_checks_before_load_page( \WP &$wp ): void {
		$exit_with_error = fn () => wp_die( esc_html__( 'members_map.error', 'learnyboxmap' ) );

		$member = $wp->query_vars['member'] ?? null;

		if ( empty( $_POST ) ) {
			// In a NOT <form> context, *member* query var must be a valid email address.
			$member = is_email( $member ) ? $this->member_repo->get_by_email( $member ) : $exit_with_error();
		} else {
			// The <form> must be secure and fresh (ie nonce =< 12 hours), and correct.
			1 === wp_verify_nonce( sanitize_key( $_POST['nonce'] ?? '' ), self::NONCE_KEY )
			&& isset( $_POST['name'] )
			&& isset( $_POST['geo_coordinates'] )
			&& isset( $_POST['address'] )
			&& isset( $_POST['description'] )
			|| $exit_with_error();
		}

		// *member* query var (ID or email) must match with an existing LearnyBox member.
		MemberPostType::is( $member ) || $exit_with_error();
	}

	/**
	 * Load the "Members Map" page only if all following conditions are true:
	 * 1. *learnyboxmap_page_membersmap* query var exists
	 * 2. *member* query var exists
	 * 3. @todo: http referer is the good one
	 * 4. *member* is either an ID or a valid email address, both matching with an existing LearnyBox member.
	 *
	 * If first condition is false: continue the normal WordPress process (ie, the page has not been requested).
	 * If one of other conditions is false: exit with error (ie, the page has been requested, but with potentially bad intentions).
	 *
	 * @param \WP $wp Current WordPress environment instance (passed by reference).
	 */
	public function load_page_if( \WP &$wp ): void {
		if ( ! isset( $wp->query_vars['learnyboxmap_page_membersmap'] ) ) {
			return;
		}

		$this->member_repo = new MemberRepository();

		$this->do_checks_before_load_page( $wp );

		// Variables/data sent to template.
		$v                           = new \stdClass();
		$v->email                    = $wp->query_vars['member'] ?? null;
		$v->member                   = $v->email ? $this->member_repo->get_by_email( $v->email ) : null;
		$v->is_registration_complete = 'publish' === get_post_status( $v->member );
		$v->consent_text             = Option::get( 'consent_text' );
		$v->categories               = get_terms(
			array(
				'taxonomy'   => CategoryTaxonomy::name(),
				'hide_empty' => false,
			)
		);

		Template::render( 'members_map/main', $v );
		exit;
	}
}
