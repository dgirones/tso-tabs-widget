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

/**
 * Removes the tsotab_img / tsotab_img_large image files generated for the
 * Media Library and their entries in the attachment metadata.
 * Original images and every other registered size are left untouched.
 *
 * @return void
 */
function tsotab_uninstall_remove_thumbnails() {
	$sizes   = array( 'tsotab_img', 'tsotab_img_large' );
	$basedir = wp_get_upload_dir()['basedir'];
	$paged   = 1;

	do {
		$ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image',
				'fields'         => 'ids',
				'posts_per_page' => 200,
				'paged'          => $paged,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		foreach ( $ids as $attachment_id ) {
			$meta = wp_get_attachment_metadata( $attachment_id );
			$file = get_attached_file( $attachment_id );

			if ( ! is_array( $meta ) || empty( $meta['sizes'] ) || ! is_array( $meta['sizes'] ) || ! $file ) {
				continue;
			}

			$to_remove = array();
			foreach ( $sizes as $size ) {
				if ( isset( $meta['sizes'][ $size ]['file'] ) ) {
					$to_remove[ $size ] = basename( $meta['sizes'][ $size ]['file'] );
					unset( $meta['sizes'][ $size ] );
				}
			}

			if ( empty( $to_remove ) ) {
				continue;
			}

			// Never delete a file still referenced by the original or by another size.
			$still_used = array( basename( $file ) );
			foreach ( $meta['sizes'] as $other ) {
				if ( isset( $other['file'] ) ) {
					$still_used[] = basename( $other['file'] );
				}
			}

			$dir = dirname( $file );
			foreach ( array_unique( $to_remove ) as $filename ) {
				if ( ! in_array( $filename, $still_used, true ) ) {
					wp_delete_file_from_directory( $dir . '/' . $filename, $basedir );
				}
			}

			wp_update_attachment_metadata( $attachment_id, $meta );
		}

		++$paged;
	} while ( ! empty( $ids ) );
}

tsotab_uninstall_remove_thumbnails();
