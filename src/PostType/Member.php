<?php

namespace LearnyboxMap\PostType;

/**
 * Functionalities for the CPT representing a LearnyBox Member.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @subpackage PostType
 * @author     freepius
 */
class Member {
	public function __construct() {
		register_post_type( $this->name(), $this->definition() );
	}

	/**
	 * Get the CPT full name (ie with the plugin prefix).
	 */
	protected function name(): string {
		return 'learnyboxmap_member';
	}

	/**
	 * Get the CPT slug (used to rewrite the CPT URLs).
	 */
	protected function slug(): string {
		return __( 'member', 'learnyboxmap' );
	}

	/**
	 * Get the CPT labels as array.
	 */
	protected function labels(): array {
		return array(
			'name'          => _x( 'LearnyBox Members', 'Post type general name', 'learnyboxmap' ),
			'singular_name' => _x( 'LearnyBox Member', 'Post type singular name', 'learnyboxmap' ),
			'menu_name'     => _x( 'Members', 'Admin Menu text', 'learnyboxmap' ),
			'add_new_item'  => __( 'Add a new LearnyBox Member', 'learnyboxmap' ),
			'search_items'  => __( 'Search members', 'learnyboxmap' ),
			'not_found'     => __( 'No members found.', 'learnyboxmap' ),
		);
	}

	/**
	 * Get the CPT definition as array.
	 */
	protected function definition(): array {
		return array(
			'description'      => __( 'Represents a LearnyBox Member', 'learnyboxmap' ),
			'labels'           => $this->labels(),
			'public'           => true,
			'show_in_menu'     => \LearnyboxMap\Admin::MENU,
			'menu_icon'        => 'dashicons-groups',
			'rewrite'          => array( 'slug' => $this->slug() ),
			'supports'         => array( 'title', 'editor' ),
		);
	}
}
