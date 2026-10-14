<?php
/**
 * TSO Tabs Widget — front AJAX nonces (cache-safe).
 *
 * Full-page caches bake wp_localize_script nonces into HTML. Those nonces expire
 * and are user-specific, which causes intermittent 403 on AJAX. These endpoints
 * return fresh nonces from admin-ajax.php (not page-cached).
 *
 * @package TSO_Tabs_Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return fresh front-end nonces as JSON (widget + view counter).
 */
function tsotab_ajax_get_front_nonce() {
	wp_send_json_success(
		array(
			'nonce'     => wp_create_nonce( TSOTAB_NONCE_AJAX ),
			'viewNonce' => wp_create_nonce( TSOTAB_NONCE_VIEW ),
		)
	);
}

add_action( 'wp_ajax_tsotab_get_front_nonce', 'tsotab_ajax_get_front_nonce' );
add_action( 'wp_ajax_nopriv_tsotab_get_front_nonce', 'tsotab_ajax_get_front_nonce' );
