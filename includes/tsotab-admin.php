<?php
/**
 * TSO Tabs Widget — admin notices and dismiss AJAX.
 *
 * @package TSO_Tabs_Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue admin assets (widget form + notice dismiss).
 *
 * @param string $hook_suffix Current admin screen.
 */
function tsotab_enqueue_admin_assets( $hook_suffix ) {
	$show_notice = is_admin()
		&& current_user_can( 'manage_options' )
		&& ! tsotab_get_user_meta( get_current_user_id(), TSOTAB_USER_META_IGNORE_NOTICE_2 )
		&& (int) tsotab_get_stored_option( TSOTAB_STORED_OPTION_NOTICE_VIEWS, 0 ) < 3
		&& (bool) tsotab_get_stored_option( TSOTAB_STORED_OPTION_ACTIVATED, 0 );

	if ( 'widgets.php' !== $hook_suffix && ! $show_notice ) {
		return;
	}

	wp_register_style(
		'tsotab-widget-admin',
		TSOTAB_URL . 'assets/css/admin.css',
		array(),
		TSOTAB_VERSION
	);
	if ( 'widgets.php' === $hook_suffix ) {
		wp_enqueue_style( 'tsotab-widget-admin' );
	}

	wp_register_script(
		'tsotab-widget-admin',
		TSOTAB_URL . 'assets/js/admin.js',
		array( 'jquery' ),
		TSOTAB_VERSION,
		true
	);
	wp_localize_script(
		'tsotab-widget-admin',
		'tsotabAdminConfig',
		array(
			'dismissNonce' => wp_create_nonce( TSOTAB_NONCE_DISMISS ),
		)
	);
	wp_enqueue_script( 'tsotab-widget-admin' );
}
add_action( 'admin_enqueue_scripts', 'tsotab_enqueue_admin_assets' );

/**
 * Show welcome notice (max 3 views).
 */
function tsotab_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	global $current_user;
	$user_id = $current_user->ID;

	if ( tsotab_get_user_meta( $user_id, TSOTAB_USER_META_IGNORE_NOTICE_2 )
		|| (int) tsotab_get_stored_option( TSOTAB_STORED_OPTION_NOTICE_VIEWS, 0 ) >= 3
		|| ! tsotab_get_stored_option( TSOTAB_STORED_OPTION_ACTIVATED, 0 ) ) {
		return;
	}

	$views = (int) tsotab_get_stored_option( TSOTAB_STORED_OPTION_NOTICE_VIEWS, 0 );
	tsotab_update_stored_option( TSOTAB_STORED_OPTION_NOTICE_VIEWS, $views + 1 );
	echo '<div class="updated notice-info tso-tabs-widget-notice" id="tso-tabs-widget-notice" style="position:relative;">';
	echo '<p>' . esc_html__( 'Thank you for using TSO Tabs Widget. We hope you enjoy it!', 'tso-tabs-widget' ) . '</p>';
	echo '<a class="notice-dismiss tabwidget-notice-dismiss" data-ignore="1" href="#"></a>';
	echo '</div>';
}
add_action( 'admin_notices', 'tsotab_admin_notice' );

/**
 * Dismiss admin notice via AJAX.
 */
function tsotab_ajax_dismiss_notice() {
	if ( ! current_user_can( 'manage_options' ) || ! tsotab_verify_dismiss_notice_nonce() ) {
		wp_die( '', '', array( 'response' => 403 ) );
	}

	global $current_user;
	$user_id = $current_user->ID;

	if ( tsotab_request_post_is_set( 'dismiss' ) ) {
		$dismiss = tsotab_get_ajax_post_text( 'dismiss' );
		if ( '0' === $dismiss ) {
			tsotab_add_user_meta( $user_id, TSOTAB_USER_META_IGNORE_NOTICE, '1', true );
		} elseif ( '1' === $dismiss ) {
			tsotab_add_user_meta( $user_id, TSOTAB_USER_META_IGNORE_NOTICE_2, '1', true );
		}
	}
	wp_die();
}

add_action( 'wp_ajax_tsotab_dismiss_notice', 'tsotab_ajax_dismiss_notice' );
add_action( 'wp_ajax_mts_dismiss_tabwidget_notice', 'tsotab_ajax_dismiss_notice' );
