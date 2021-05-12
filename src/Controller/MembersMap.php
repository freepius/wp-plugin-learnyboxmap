<?php

namespace LearnyboxMap\Controller;

use \LearnyboxMap\Option;
use \LearnyboxMap\Template;
use \LearnyboxMap\Entity\PostType\Member as MemberPostType;
use \LearnyboxMap\Entity\Taxonomy\Category as CategoryTaxonomy;
use \LearnyboxMap\Repository\PostType\Member as MemberRepository;
use \LearnyboxMap\Transformer\PostType\Member as MemberTransformer;

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

	public function __construct() {
		add_filter( 'query_vars', array( $this, 'allow_query_vars' ) );
		add_action( 'parse_request', array( $this, 'load_page_if' ) );
	}

	protected function exit_with_error(): void {
		wp_die( esc_html__( 'members_map.error', 'learnyboxmap' ) );
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
		if ( ! empty( $_POST ) ) {
			// The <form> must be secure and fresh (ie nonce =< 12 hours), and correct.
			1 === wp_verify_nonce( sanitize_key( $_POST['nonce'] ?? '' ), self::NONCE_KEY )
			&& isset( $_POST['member'] )
			&& isset( $_POST['name'] )
			&& isset( $_POST['geo_coordinates'] )
			&& isset( $_POST['address'] )
			&& isset( $_POST['description'] )
			|| $this->exit_with_error();
		}
	}

	/**
	 * Load the "Members Map" page only if all following conditions are true:
	 * 1. *learnyboxmap_page_membersmap* query var exists
	 * 2. @todo: http referer is the good one
	 * 3. if a form is submitted, it must be valid (ie, with the expected "shape")
	 * 4. *member* query var exists
	 * 5. *member* is a valid email address
	 * 6. *member* matches with an existing LearnyBox member.
	 *
	 * If first condition is false: continue the normal WordPress process (ie, the page has not been requested).
	 * If one of other conditions is false: exit with error (ie, the page has been requested, but with potentially bad intentions).
	 *
	 * @param \WP $wp Current WordPress environment instance (passed by reference).
	 */
	public function load_page_if( \WP &$wp ): void {
		// Condition 1.
		if ( ! isset( $wp->query_vars['learnyboxmap_page_membersmap'] ) ) {
			return;
		}

		// Conditions 2. and 3.
		$this->do_checks_before_load_page( $wp );

		// Condition 4.
		$email = $wp->query_vars['member'] ?? null;

		// Condition 5.
		if ( false === is_email( $email ) ) {
			$this->exit_with_error();
		}

		// Condition 6.
		$repo   = new MemberRepository();
		$member = $repo->get_by_email( $email );

		if ( false === MemberPostType::is( $member ) ) {
			$this->exit_with_error();
		}

		// Variables/data sent to template.
		$v                           = new \stdClass();
		$v->is_form_validation       = false === empty( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$v->is_registration_complete = 'publish' === get_post_status( $member );
		$v->consent_text             = Option::get( 'consent_text' );
		$v->members                  = wp_json_encode(
			array_map(
				array( MemberTransformer::class, 'wp_to_min_array' ),
				$repo->get_all_registered( array( $member->ID ) )
			)
		);

		$v->categories = get_terms(
			array(
				'taxonomy'   => CategoryTaxonomy::name(),
				'hide_empty' => false,
			)
		);

		// Prepare the Form data:
		// - either from Member post (case of a first request)
		// - either from $_POST (case of a submitted form).
		$v->form = $v->is_form_validation
			? MemberTransformer::post_to_form( $v->categories )
			: MemberTransformer::wp_to_form( $member );

		$v->form->nonce = self::NONCE_KEY;

		// If form has no error => update and publish the Member.
		if ( $v->is_form_validation && array() === $v->form->errors ) {
			$repo->update_by_form_data( $member, $v->form );

			$args = array(
				'learnyboxmap_page_membersmap' => 1,
				'member'                       => $v->form->member,
			);

			if ( wp_safe_redirect( add_query_arg( $args, '/' ) ) ) {
				exit;
			}
		}

		Template::render( 'members_map/main', $v );
		exit;
	}
}
