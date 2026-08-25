<?php
/**
 * Plugin Name: TSO Tabs Widget
 * Description: AJAX tabbed sidebar widget (Popular, Recent, Comments, Tags). PHP 7.4+, WP 6.1+, cache-plugin friendly.
 * Author:      Tu Soporte Online
 * Author URI:  https://www.tusoporteonline.es/blog
 * Plugin URI:  https://www.tusoporteonline.es/blog
 * Version:     1.0.0
 * Text Domain: tso-tabs-widget
 * Domain Path: /languages
 * Requires at least: 6.1
 * Requires PHP: 7.4
 * Tested up to: 7.0
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package TSO_Tabs_Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TSOTAB_VERSION', '1.0.0' );
define( 'TSOTAB_FILE', __FILE__ );
define( 'TSOTAB_PATH', plugin_dir_path( TSOTAB_FILE ) );
define( 'TSOTAB_DIR', TSOTAB_PATH );
define( 'TSOTAB_URL', plugin_dir_url( TSOTAB_FILE ) );

define( 'TSOTAB_NONCE_AJAX', 'tsotab_tabs_widget_ajax' );
define( 'TSOTAB_NONCE_AJAX_LEGACY', 'wpt_nonce' );
define( 'TSOTAB_NONCE_DISMISS', 'tsotab_dismiss_notice' );
define( 'TSOTAB_NONCE_DISMISS_LEGACY', 'wpt_dismiss_notice_nonce' );
define( 'TSOTAB_NONCE_VIEW', 'tsotab_view_count_nonce' );
define( 'TSOTAB_NONCE_VIEW_LEGACY', 'tsotab_view_nonce' );

require_once TSOTAB_PATH . 'includes/tsotab-storage.php';
require_once TSOTAB_PATH . 'includes/tsotab-i18n.php';
require_once TSOTAB_PATH . 'includes/tsotab-support.php';
require_once TSOTAB_PATH . 'includes/class-tsotab-widget.php';
require_once TSOTAB_PATH . 'includes/tsotab-view-count.php';
require_once TSOTAB_PATH . 'includes/tsotab-admin.php';

add_action(
	'widgets_init',
	static function () {
		register_widget( 'TSOTAB_Widget' );
	}
);

add_action(
	'widgets_init',
	static function () {
		unregister_widget( 'mts_Widget_Tabs_2' );
		unregister_widget( 'mts_Widget_Tabs' );
	},
	100
);

add_action(
	'admin_init',
	static function () {
		tsotab_migrate_stored_keys();
	}
);
