<?php
/**
 * TSO Tabs Widget — stored options, user meta, and POST helpers.
 *
 * @package TSO_Tabs_Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'TSOTAB_DB_SCHEMA' ) ) {
	define( 'TSOTAB_DB_SCHEMA', 1 );
}

define( 'TSOTAB_STORED_OPTION_ACTIVATED', 'activated' );
define( 'TSOTAB_STORED_OPTION_NOTICE_VIEWS', 'notice_views' );
define( 'TSOTAB_STORED_OPTION_DB_SCHEMA', 'db_schema' );
define( 'TSOTAB_USER_META_IGNORE_NOTICE', 'ignore_notice' );
define( 'TSOTAB_USER_META_IGNORE_NOTICE_2', 'ignore_notice_2' );

/**
 * Canonical wp_options key for a stored option id.
 *
 * @param string $id Symbolic option id.
 * @return string
 */
function tsotab_stored_option_key( $id ) {
	$map = array(
		TSOTAB_STORED_OPTION_ACTIVATED    => 'tso_tabs_widget_activated',
		TSOTAB_STORED_OPTION_NOTICE_VIEWS => 'tso_tabs_widget_notice_views',
		TSOTAB_STORED_OPTION_DB_SCHEMA    => 'tso_tabs_widget_db_schema',
	);
	$id = (string) $id;
	return isset( $map[ $id ] ) ? $map[ $id ] : '';
}

/**
 * Legacy wp_options keys (dual-read during rollout).
 *
 * @param string $id Symbolic option id.
 * @return string[]
 */
function tsotab_stored_option_legacy_keys( $id ) {
	$map = array(
		TSOTAB_STORED_OPTION_ACTIVATED    => array( 'tso_tab_widget_activated' ),
		TSOTAB_STORED_OPTION_NOTICE_VIEWS => array( 'tso_tab_widget_notice_views' ),
	);
	$id = (string) $id;
	return isset( $map[ $id ] ) ? $map[ $id ] : array();
}

/**
 * Canonical user meta key.
 *
 * @param string $id Symbolic meta id.
 * @return string
 */
function tsotab_user_meta_key( $id ) {
	$map = array(
		TSOTAB_USER_META_IGNORE_NOTICE   => 'tso_tabs_widget_ignore_notice',
		TSOTAB_USER_META_IGNORE_NOTICE_2 => 'tso_tabs_widget_ignore_notice_2',
	);
	$id = (string) $id;
	return isset( $map[ $id ] ) ? $map[ $id ] : '';
}

/**
 * Legacy user meta keys.
 *
 * @param string $id Symbolic meta id.
 * @return string[]
 */
function tsotab_user_meta_legacy_keys( $id ) {
	$map = array(
		TSOTAB_USER_META_IGNORE_NOTICE   => array( 'tso_tab_widget_ignore_notice' ),
		TSOTAB_USER_META_IGNORE_NOTICE_2 => array( 'tso_tab_widget_ignore_notice_2' ),
	);
	$id = (string) $id;
	return isset( $map[ $id ] ) ? $map[ $id ] : array();
}

/**
 * Get stored option (canonical, then legacy).
 *
 * @param string $id     Symbolic option id.
 * @param mixed  $default Default value.
 * @return mixed
 */
function tsotab_get_stored_option( $id, $default = false ) {
	$key = tsotab_stored_option_key( $id );
	if ( '' === $key ) {
		return $default;
	}
	$value = get_option( $key, null );
	if ( null !== $value ) {
		return $value;
	}
	foreach ( tsotab_stored_option_legacy_keys( $id ) as $legacy ) {
		$value = get_option( $legacy, null );
		if ( null !== $value ) {
			update_option( $key, $value );
			delete_option( $legacy );
			return $value;
		}
	}
	return $default;
}

/**
 * Update stored option (canonical key only).
 *
 * @param string $id    Symbolic option id.
 * @param mixed  $value Value.
 * @return bool
 */
function tsotab_update_stored_option( $id, $value ) {
	$key = tsotab_stored_option_key( $id );
	if ( '' === $key ) {
		return false;
	}
	foreach ( tsotab_stored_option_legacy_keys( $id ) as $legacy ) {
		delete_option( $legacy );
	}
	return update_option( $key, $value );
}

/**
 * Delete stored option (canonical + legacy).
 *
 * @param string $id Symbolic option id.
 * @return bool
 */
function tsotab_delete_stored_option( $id ) {
	$key = tsotab_stored_option_key( $id );
	$ok  = ( '' !== $key ) ? delete_option( $key ) : false;
	foreach ( tsotab_stored_option_legacy_keys( $id ) as $legacy ) {
		delete_option( $legacy );
	}
	return $ok;
}

/**
 * Get user meta (canonical, then legacy).
 *
 * @param int    $user_id User ID.
 * @param string $id      Symbolic meta id.
 * @param bool   $single  Single value.
 * @return mixed
 */
function tsotab_get_user_meta( $user_id, $id, $single = true ) {
	$key = tsotab_user_meta_key( $id );
	if ( '' === $key ) {
		return $single ? '' : array();
	}
	$value = get_user_meta( $user_id, $key, $single );
	if ( ( $single && '' !== $value && false !== $value ) || ( ! $single && ! empty( $value ) ) ) {
		return $value;
	}
	foreach ( tsotab_user_meta_legacy_keys( $id ) as $legacy ) {
		$legacy_val = get_user_meta( $user_id, $legacy, $single );
		if ( ( $single && '' !== $legacy_val && false !== $legacy_val ) || ( ! $single && ! empty( $legacy_val ) ) ) {
			update_user_meta( $user_id, $key, $legacy_val );
			delete_user_meta( $user_id, $legacy );
			return $legacy_val;
		}
	}
	return $single ? '' : array();
}

/**
 * Add user meta on canonical key.
 *
 * @param int    $user_id User ID.
 * @param string $id      Symbolic meta id.
 * @param mixed  $value   Value.
 * @param bool   $unique  Unique.
 * @return int|false
 */
function tsotab_add_user_meta( $user_id, $id, $value, $unique = false ) {
	$key = tsotab_user_meta_key( $id );
	if ( '' === $key ) {
		return false;
	}
	foreach ( tsotab_user_meta_legacy_keys( $id ) as $legacy ) {
		delete_user_meta( $user_id, $legacy );
	}
	return add_user_meta( $user_id, $key, $value, $unique );
}

/**
 * Migrate stored keys on activate / admin_init.
 */
function tsotab_migrate_stored_keys() {
	$schema = (int) tsotab_get_stored_option( TSOTAB_STORED_OPTION_DB_SCHEMA, 0 );
	if ( $schema >= TSOTAB_DB_SCHEMA ) {
		return;
	}
	tsotab_get_stored_option( TSOTAB_STORED_OPTION_ACTIVATED, 0 );
	tsotab_get_stored_option( TSOTAB_STORED_OPTION_NOTICE_VIEWS, 0 );
	tsotab_update_stored_option( TSOTAB_STORED_OPTION_DB_SCHEMA, TSOTAB_DB_SCHEMA );
}

/**
 * Verify widget AJAX nonce (canonical, then legacy).
 *
 * @return bool
 */
function tsotab_verify_widget_ajax_nonce() {
	if ( ! tsotab_request_post_is_set( 'nonce' ) ) {
		return false;
	}
	$nonce = tsotab_get_ajax_post_text( 'nonce' );
	if ( wp_verify_nonce( $nonce, TSOTAB_NONCE_AJAX ) ) {
		return true;
	}
	if ( defined( 'TSOTAB_NONCE_AJAX_LEGACY' ) && wp_verify_nonce( $nonce, TSOTAB_NONCE_AJAX_LEGACY ) ) {
		return true;
	}
	return false;
}

/**
 * Verify dismiss-notice AJAX nonce.
 *
 * @return bool
 */
function tsotab_verify_dismiss_notice_nonce() {
	if ( ! tsotab_request_post_is_set( 'nonce' ) ) {
		return false;
	}
	$nonce = tsotab_get_ajax_post_text( 'nonce' );
	if ( wp_verify_nonce( $nonce, TSOTAB_NONCE_DISMISS ) ) {
		return true;
	}
	if ( defined( 'TSOTAB_NONCE_DISMISS_LEGACY' ) && wp_verify_nonce( $nonce, TSOTAB_NONCE_DISMISS_LEGACY ) ) {
		return true;
	}
	return false;
}

/**
 * Verify view-count AJAX nonce.
 *
 * @return bool
 */
function tsotab_verify_view_count_nonce() {
	if ( ! tsotab_request_post_is_set( 'nonce' ) ) {
		return false;
	}
	$nonce = tsotab_get_ajax_post_text( 'nonce' );
	if ( wp_verify_nonce( $nonce, TSOTAB_NONCE_VIEW ) ) {
		return true;
	}
	if ( defined( 'TSOTAB_NONCE_VIEW_LEGACY' ) && wp_verify_nonce( $nonce, TSOTAB_NONCE_VIEW_LEGACY ) ) {
		return true;
	}
	return false;
}

/**
 * Whether a POST key is set (call only after nonce verification).
 *
 * @param string $key POST key.
 * @return bool
 */
function tsotab_request_post_is_set( $key ) {
	return isset( $_POST[ (string) $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Caller verified nonce before reading POST.
}

/**
 * Sanitized POST text (call only after nonce verification).
 *
 * @param string $key POST key.
 * @param string $default Default.
 * @return string
 */
function tsotab_get_ajax_post_text( $key, $default = '' ) {
	if ( ! isset( $_POST[ (string) $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Caller verified nonce before reading POST.
		return $default;
	}
	return sanitize_text_field( wp_unslash( $_POST[ (string) $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Caller verified nonce before reading POST.
}

/**
 * Sanitized POST key (call only after nonce verification).
 *
 * @param string $key POST key.
 * @param string $default Default.
 * @return string
 */
function tsotab_get_ajax_post_key( $key, $default = '' ) {
	if ( ! isset( $_POST[ (string) $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Caller verified nonce before reading POST.
		return $default;
	}
	return sanitize_key( wp_unslash( $_POST[ (string) $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Caller verified nonce before reading POST.
}

/**
 * POST integer (call only after nonce verification).
 *
 * @param string $key POST key.
 * @param int    $default Default.
 * @return int
 */
function tsotab_get_ajax_post_int( $key, $default = 0 ) {
	if ( ! isset( $_POST[ (string) $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Caller verified nonce before reading POST.
		return $default;
	}
	return (int) wp_unslash( $_POST[ (string) $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Caller verified nonce; value cast to int.
}

/**
 * POST args array (call only after nonce verification).
 *
 * @return array
 */
function tsotab_get_ajax_post_args() {
	if ( ! isset( $_POST['args'] ) || ! is_array( $_POST['args'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Caller verified nonce before reading POST.
		return array();
	}
	return (array) wp_unslash( $_POST['args'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Caller verified nonce; sanitized per field in caller.
}
