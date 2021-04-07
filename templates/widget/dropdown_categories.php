<?php
/**
 * Render all the member categories as a html dropdown list (ie a html *select* tag).
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @subpackage templates
 * @author     freepius
 * @see        *members_map/member_register* template
 *
 * @global array    $vars       All the below/template variables
 * @global int      $selected   ID of the term to select.
 *
 * @todo Add somewhere a *title* containing the category description (note: not possible inside the *option* tag).
 */

wp_dropdown_categories(
	array(
		'taxonomy'         => \LearnyboxMap\Entity\Taxonomy\Category::name(),
		'name'             => 'category',
		'hide_empty'       => false,
		'hierarchical'     => true,
		'required'         => true,
		'show_option_none' => __( 'None', 'default' ),
		'selected'         => $selected,
	)
);
