<?php

namespace LearnyboxMap;

/**
 * Functionalities to manage the LearnyBox members, ie:
 * => Create and drop the WordPress SQL table containing the members
 * => @TODO: Synchronize members from LearnyBox to WordPress (through the LearnyBox API)
 * => @TODO: Retrieve all the members
 * => @TODO: Delete a particular member.
 *
 * @since      1.0.0
 * @package    LearnyboxMap
 * @author     freepius
 */
class Members {
	protected function get_sql_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'learnyboxmap_members';
	}

	public function create_sql_table() {
		global $wpdb;

		$sql = <<<SQL
            CREATE TABLE `{$this->get_sql_table_name()}` (
                `id`            INT UNSIGNED NOT NULL COMMENT 'LearnyBox member user_id',
                `email`         VARCHAR(255) NOT NULL COMMENT 'LearnyBox member email',
                `name`          VARCHAR(255) NOT NULL COMMENT 'Member name, computed from Learnybox member data',
                `address`       VARCHAR(255)     NULL COMMENT 'Member address, aggregation of several LearnyBox member data',
                `geo_coords`    VARCHAR(255)     NULL COMMENT 'Geo. coordinates following the GPS-WGS 84 format',
                `category`      VARCHAR(255)     NULL COMMENT 'Member category (@TODO comment better)',
                PRIMARY KEY(`id`),
                UNIQUE KEY(`email`)
            );
        SQL;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$result = $wpdb->query( $sql );

		true === $result || die(
			esc_html__( 'Impossible to create the SQL table for Learnybox Map plugin.', 'learnyboxmap' )
			. '<br>→ ' . esc_html( $wpdb->last_error )
		);

	}

	public function drop_sql_table() {
		global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$wpdb->query( "DROP TABLE `{$this->get_sql_table_name()}`;" );
	}
}
