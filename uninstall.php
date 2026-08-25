<?php
/**
 * Uninstall TSO Tabs Widget
 *
 * @package TSO_Tabs_Widget
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_post_meta_by_key( '_tsotab_view_count' );

delete_option( 'tso_tabs_widget_activated' );
delete_option( 'tso_tabs_widget_notice_views' );
delete_option( 'tso_tabs_widget_db_schema' );
delete_option( 'tso_tab_widget_activated' );
delete_option( 'tso_tab_widget_notice_views' );

delete_option( 'widget_wpt_widget' );
delete_option( 'widget_tsotab_widget' );

// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- uninstall cleanup, intended full-table operation
delete_metadata( 'user', 0, 'tso_tabs_widget_ignore_notice', '', true );
// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- uninstall cleanup, intended full-table operation
delete_metadata( 'user', 0, 'tso_tabs_widget_ignore_notice_2', '', true );
// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- uninstall cleanup, intended full-table operation
delete_metadata( 'user', 0, 'tso_tab_widget_ignore_notice', '', true );
// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- uninstall cleanup, intended full-table operation
delete_metadata( 'user', 0, 'tso_tab_widget_ignore_notice_2', '', true );
