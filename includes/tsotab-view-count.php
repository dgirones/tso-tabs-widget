<?php
/**
 * TSO Tabs Widget — post view counter (AJAX for cache plugins).
 *
 * @package TSO_Tabs_Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ensure new posts have view-count meta for the Popular tab.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 */
function tsotab_ensure_post_view_count_meta( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || ! ( $post instanceof WP_Post ) || 'post' !== $post->post_type ) {
		return;
	}
	if ( '' === get_post_meta( $post_id, '_tsotab_view_count', true ) ) {
		add_post_meta( $post_id, '_tsotab_view_count', 0, true );
	}
}
add_action( 'save_post', 'tsotab_ensure_post_view_count_meta', 10, 2 );

/**
 * Enqueue view-count script on single posts.
 */
function tsotab_enqueue_view_count_script() {
	if ( ! is_single() ) {
		return;
	}

	$exclude_admins = apply_filters( 'tsotab_view_count_exclude_admins', false );
	if ( true === $exclude_admins ) {
		$exclude_admins = 'edit_posts';
	}
	if ( $exclude_admins && current_user_can( $exclude_admins ) ) {
		return;
	}

	$use_ajax = apply_filters( 'tsotab_view_count_cache_support', true );
	if ( ! $use_ajax ) {
		tsotab_update_view_count( get_the_ID() );
		return;
	}

	wp_enqueue_script(
		'tsotab-view-count',
		TSOTAB_URL . 'assets/js/view-count.js',
		array( 'jquery' ),
		TSOTAB_VERSION,
		true
	);
	wp_localize_script(
		'tsotab-view-count',
		'tsotabViewCountConfig',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'postId'  => get_the_ID(),
			'nonce'   => wp_create_nonce( TSOTAB_NONCE_VIEW ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'tsotab_enqueue_view_count_script' );

/**
 * AJAX handler for view count.
 */
function tsotab_ajax_view_count() {
	if ( ! tsotab_verify_view_count_nonce() ) {
		wp_die();
	}
	$post_id = tsotab_get_ajax_post_int( 'id', 0 );
	if ( $post_id > 0 && 'publish' === get_post_status( $post_id ) ) {
		tsotab_update_view_count( $post_id );
	}
	wp_die();
}

add_action( 'wp_ajax_tsotab_view_count', 'tsotab_ajax_view_count' );
add_action( 'wp_ajax_nopriv_tsotab_view_count', 'tsotab_ajax_view_count' );

/**
 * Increment view count meta for a post.
 *
 * @param int $post_id Post ID.
 */
function tsotab_update_view_count( $post_id ) {
	$sample_rate = (int) apply_filters( 'tsotab_sampling_rate', 100 ) / 100;
	if ( ( wp_rand() / mt_getrandmax() ) <= $sample_rate ) {
		$count = (int) get_post_meta( $post_id, '_tsotab_view_count', true );
		update_post_meta( $post_id, '_tsotab_view_count', $count + 1 );
		do_action( 'tsotab_view_count_after_update', $post_id );
	}
}

/**
 * Seed view count meta on existing posts (activation).
 */
function tsotab_add_views_meta_for_posts() {
	$allposts = get_posts(
		array(
			'numberposts' => -1,
			'post_type'   => 'post',
			'post_status' => 'any',
			'fields'      => 'ids',
		)
	);
	foreach ( $allposts as $id ) {
		add_post_meta( $id, '_tsotab_view_count', 0, true );
	}
}

/**
 * Plugin activation.
 */
function tsotab_plugin_activation() {
	tsotab_add_views_meta_for_posts();
	tsotab_update_stored_option( TSOTAB_STORED_OPTION_ACTIVATED, time() );
	tsotab_migrate_stored_keys();
	tsotab_send_ping( 'activate' );
}
register_activation_hook( TSOTAB_FILE, 'tsotab_plugin_activation' );

register_deactivation_hook(
	TSOTAB_FILE,
	static function () {
		tsotab_send_ping( 'deactivate' );
	}
);

/**
 * Send anonymous activation ping to TSO.
 *
 * @param string $event activate|deactivate.
 */
function tsotab_send_ping( $event ) {
	wp_remote_post(
		'https://tusoporteonline.es/blog/pings/neteja.php',
		array(
			'body'     => array(
				'event'   => $event,
				'plugin'  => 'TSO Tabs Widget',
				'php_ver' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
				'site'    => wp_parse_url( home_url(), PHP_URL_HOST ),
			),
			'timeout'  => 5,
			'blocking' => false,
		)
	);
}
