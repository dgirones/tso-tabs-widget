<?php
/**
 * TSO Tabs Widget — text domain loading.
 *
 * @package TSO_Tabs_Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a locale string starts with a given prefix (PHP 7.4 compatible).
 *
 * @param string $locale Locale code.
 * @param string $prefix Prefix to match.
 * @return bool
 */
function tsotab_locale_starts_with( $locale, $prefix ) {
	return 0 === strpos( (string) $locale, (string) $prefix );
}

/**
 * Load bundled MO catalogs for Catalan and Spanish.
 */
function tsotab_load_textdomain() {
	$domain = 'tso-tabs-widget';
	$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();

	$candidates = array( $locale );
	if ( tsotab_locale_starts_with( $locale, 'ca' ) && 'ca' !== $locale ) {
		$candidates[] = 'ca';
	}
	if ( tsotab_locale_starts_with( $locale, 'es' ) && 'es_ES' !== $locale ) {
		$candidates[] = 'es_ES';
	}

	foreach ( array_unique( $candidates ) as $try_locale ) {
		$mofile = TSOTAB_PATH . 'languages/' . $domain . '-' . $try_locale . '.mo';
		if ( is_readable( $mofile ) ) {
			load_textdomain( $domain, $mofile );
			return;
		}
	}
}
add_action( 'init', 'tsotab_load_textdomain', 1 );
