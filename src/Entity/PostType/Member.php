<?php

namespace LearnyboxMap\Entity\PostType;

/**
 * Functionalities for the CPT representing a LearnyBox Member.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @subpackage Entity
 * @author     freepius
 */
class Member {
	public function __construct() {
		register_post_type( $this->name(), $this->definition() );
	}

	/**
	 * Get the CPT full name (ie with the plugin prefix).
	 */
	public static function name(): string {
		return 'learnyboxmap_member';
	}

	/**
	 * Determine if a given post is of this custom type.
	 *
	 * Note: this function do a SQL transaction or a WordPress cache call.
	 *
	 * @param int|\Wp_Post $post Post ID or post object (must not be a PHP falsey value).
	 */
	public static function is( $post ): bool {
		return $post && static::name() === get_post_type( $post );
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
			'add_new_item'  => __( 'Add New LearnyBox Member', 'learnyboxmap' ),
			'edit_item'     => __( 'Edit LearnyBox Member', 'learnyboxmap' ),
			'search_items'  => __( 'Search members', 'learnyboxmap' ),
			'not_found'     => __( 'No members found.', 'learnyboxmap' ),
		);
	}

	/**
	 * Get the CPT definition as array.
	 */
	protected function definition(): array {
		return array(
			'description'       => __( 'Represents a LearnyBox Member', 'learnyboxmap' ),
			'labels'            => $this->labels(),
			'public'            => true,
			'show_in_menu'      => \LearnyboxMap\Admin::MENU,
			'show_in_admin_bar' => false,
			'menu_icon'         => 'dashicons-groups',
			'rewrite'           => array( 'slug' => $this->slug() ),
			'supports'          => array( 'title', 'editor', 'custom-fields' ),
		);
	}

	/**
	 * Delete all the posts of the custom type.
	 * This function should be called during plugin uninstallation.
	 */
	public static function delete_posts(): void {
		// We cannot use get_posts() because the type might not be registered when this function is called.
		$query = new \WP_Query(
			array(
				'post_type' => static::name(),
				'nopaging'  => true,
			)
		);

		foreach ( $query->posts as $post ) {
			wp_delete_post( $post->ID, true );
		}
	}
}
