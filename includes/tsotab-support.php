<?php
/**
 * TSO Tabs Widget — blog, donate links, localized Plugins screen text.
 *
 * @package TSO_Tabs_Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TSO blog URL.
 *
 * @return string
 */
function tsotab_get_blog_url() {
	$default = 'https://www.tusoporteonline.es/blog';

	/**
	 * Filter the TSO blog URL shown on the Plugins screen.
	 *
	 * @param string $url Blog URL.
	 */
	return (string) apply_filters( 'tsotab_blog_url', $default );
}

/**
 * Ko-fi donation URL.
 *
 * @return string
 */
function tsotab_get_kofi_donate_url() {
	$default = 'https://ko-fi.com/deadko_cat';

	/**
	 * Filter the Ko-fi donation URL for TSO Tabs Widget.
	 *
	 * @param string $url Donation page URL.
	 */
	return (string) apply_filters( 'tsotab_kofi_donate_url', $default );
}

/**
 * Use .mo translation when loaded; otherwise ca/es string fallbacks.
 *
 * @param string $english    English msgid.
 * @param string $translated Result of __() with the same literal msgid.
 * @param string $ca         Catalan fallback.
 * @param string $es         Spanish fallback.
 * @return string
 */
function tsotab_gettext_with_locale_fallback( $english, $translated, $ca, $es ) {
	if ( $translated !== $english ) {
		return $translated;
	}

	$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
	if ( str_starts_with( $locale, 'ca' ) ) {
		return $ca;
	}
	if ( str_starts_with( $locale, 'es' ) ) {
		return $es;
	}

	return $english;
}

/**
 * Localized description for the Plugins list (HTML-safe fragment).
 *
 * @return string
 */
function tsotab_get_plugin_list_description() {
	$english    = 'AJAX tabbed sidebar widget (Popular, Recent, Comments, Tags). PHP 7.4+, WP 6.1+, cache-plugin friendly.';
	$translated = __( 'AJAX tabbed sidebar widget (Popular, Recent, Comments, Tags). PHP 7.4+, WP 6.1+, cache-plugin friendly.', 'tso-tabs-widget' );

	return tsotab_gettext_with_locale_fallback(
		$english,
		$translated,
		'Widget de pestanyes AJAX per a la barra lateral (Popular, Recent, Comentaris, Etiquetes). PHP 7.4+, WP 6.1+, compatible amb plugins de caché.',
		'Widget de pestañas AJAX para la barra lateral (Popular, Reciente, Comentarios, Etiquetas). PHP 7.4+, WP 6.1+, compatible con plugins de caché.'
	);
}

/**
 * Blog link label for the Plugins screen.
 *
 * @return string
 */
function tsotab_get_blog_link_label() {
	$english    = 'Blog';
	$translated = __( 'Blog', 'tso-tabs-widget' );

	return tsotab_gettext_with_locale_fallback( $english, $translated, 'Blog', 'Blog' );
}

/**
 * Donate link label for the Plugins screen.
 *
 * @return string
 */
function tsotab_get_donate_link_label() {
	$english    = 'Donate';
	$translated = __( 'Donate', 'tso-tabs-widget' );

	return tsotab_gettext_with_locale_fallback( $english, $translated, 'Donar', 'Donar' );
}

/**
 * Replace plugin header description with the translated string.
 *
 * @param array<string, array<string, string>> $plugins Plugins list.
 * @return array<string, array<string, string>>
 */
function tsotab_filter_plugin_list_description( array $plugins ) {
	$basename = plugin_basename( TSOTAB_FILE );
	if ( isset( $plugins[ $basename ] ) ) {
		$plugins[ $basename ]['Description'] = tsotab_get_plugin_list_description();
	}
	return $plugins;
}
add_filter( 'all_plugins', 'tsotab_filter_plugin_list_description' );

/**
 * Blog and Donate links on the Plugins screen.
 *
 * @param string[] $links Plugin row meta links.
 * @param string   $file  Plugin basename.
 * @return string[]
 */
function tsotab_filter_plugin_row_meta( $links, $file ) {
	if ( plugin_basename( TSOTAB_FILE ) !== $file ) {
		return $links;
	}

	$links[] = sprintf(
		'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
		esc_url( tsotab_get_blog_url() ),
		esc_html( tsotab_get_blog_link_label() )
	);
	$links[] = sprintf(
		'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
		esc_url( tsotab_get_kofi_donate_url() ),
		esc_html( tsotab_get_donate_link_label() )
	);

	return $links;
}
add_filter( 'plugin_row_meta', 'tsotab_filter_plugin_row_meta', 10, 2 );
