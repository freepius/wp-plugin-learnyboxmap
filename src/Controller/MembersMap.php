<?php

namespace LearnyboxMap\Controller;

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
	public function __construct() {
		// Add 2 query vars: 1-st to load page, 2-nd to pass it a LearnyBox member.
		add_filter( 'query_vars', array( $this, 'allow_query_vars' ) );

		// Load the page if requested. TODO: and if the http referer is the good one!
		add_action( 'parse_request', array( $this, 'load_page_if_requested' ) );
	}

	public function allow_query_vars( array $query_vars ): array {
		$query_vars[] = 'learnyboxmap_page_membersmap';

		if ( ! in_array( 'member', $query_vars, true ) ) {
			$query_vars[] = 'member';
		}

		return $query_vars;
	}

	public function load_page_if_requested( \WP &$wp ): void {
		if ( ! isset( $wp->query_vars['learnyboxmap_page_membersmap'] ) ) {
			return;
		}
		\LearnyboxMap\Asset::enqueue_css_js( 'members-map' );

		$repo = new \LearnyboxMap\Repository\PostType\Member();

		// Variables/data sent to template.
		$v                           = new \stdClass();
		$v->email                    = $wp->query_vars['member'] ?? null;
		$v->member                   = $v->email ? $repo->get_by_email( $v->email ) : null;
		$v->is_registration_complete = 'publish' === get_post_status( $v->member );

		\LearnyboxMap\Template::render( 'members_map/main', $v );
		exit;
	}
}
