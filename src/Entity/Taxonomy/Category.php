<?php

namespace LearnyboxMap\Entity\Taxonomy;

use \LearnyboxMap\Entity\PostType;

/**
 * Functionalities for the Custom Taxonomy representing the Category of LearnyBox Member.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @subpackage Entity
 * @author     freepius
 */
class Category {
	public function __construct() {
		register_taxonomy( $this->name(), PostType\Member::name(), $this->definition() );
		register_taxonomy_for_object_type( $this->name(), PostType\Member::name() );
	}

	/**
	 * Get the taxonomy full name (ie with the plugin prefix).
	 */
	public static function name(): string {
		return 'learnyboxmap_category';
	}

	/**
	 * Get the taxonomy definition as array.
	 */
	protected function definition(): array {
		return array(
			'description'        => __( 'Represents the category of a LearnyBox member', 'learnyboxmap' ),
			'hierarchical'       => true, // Hierarchy is not useful in our case, but we want the Category taxonomy labels.
			'public'             => true,
			'publicly_queryable' => false,
			'show_admin_column'  => true,
			'show_in_nav_menus'  => false,
		);
	}

	/**
	 * Delete all the taxonomy terms.
	 * This function should be called during plugin uninstallation.
	 *
	 * Note: could be inefficient on a large amount of terms.
	 */
	public static function delete_terms(): void {
		// We cannot use get_terms() because the taxonomy might not be registered when this function is called.
		$query = new \WP_Term_Query(
			array(
				'taxonomy'   => static::name(),
				'hide_empty' => false,
			)
		);

		foreach ( $query->get_terms() as $term ) {
			wp_delete_term( $term->term_id, static::name() );
		}
	}
}
