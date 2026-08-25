<?php
/**
 * TSO Tabs Widget — WP_Widget implementation.
 *
 * @package TSO_Tabs_Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main widget class.
 */
class TSOTAB_Widget extends WP_Widget {

		/**
		 * Placeholder SVG inline (replaces smallthumb.png / largethumb.png).
		 *
		 * @var string
		 */
		const PLACEHOLDER_SVG = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='65' height='65'%3E%3Crect width='65' height='65' fill='%23f0f0f0'/%3E%3C/svg%3E";

		// ── Constructor ───────────────────────────────────────────────────────

		public function __construct() {
			add_action( 'init', array( $this, 'register_image_sizes' ) );

			add_action( 'wp_ajax_tsotab_widget_content',        array( $this, 'ajax_widget_content' ) );
			add_action( 'wp_ajax_nopriv_tsotab_widget_content',   array( $this, 'ajax_widget_content' ) );
			add_action( 'wp_ajax_wpt_widget_content',             array( $this, 'ajax_widget_content' ) );
			add_action( 'wp_ajax_nopriv_wpt_widget_content',      array( $this, 'ajax_widget_content' ) );

			add_action( 'wp_enqueue_scripts',    array( $this, 'register_front_assets' ) );

			$widget_ops = array(
				'classname'   => 'widget_wpt',
				'description' => __( 'Display popular posts, recent posts, comments, and tags in tabbed format.', 'tso-tabs-widget' ),
			);

			parent::__construct(
				'wpt_widget',
				__( 'TSO Tabs Widget', 'tso-tabs-widget' ),
				$widget_ops
			);
		}

		// ── Init ──────────────────────────────────────────────────────────────

		public function register_image_sizes() {
			add_image_size( 'wp_review_small', 65,  65,  true );
			add_image_size( 'wp_review_large', 320, 240, true );
		}

		// ── Scripts ───────────────────────────────────────────────────────────

		public function register_front_assets() {
			wp_register_script(
				'tsotab-widget',
				TSOTAB_URL . 'assets/js/widget.js',
				array( 'jquery' ),
				TSOTAB_VERSION,
				true
			);
			wp_localize_script(
				'tsotab-widget',
				'tsotabWidgetConfig',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( TSOTAB_NONCE_AJAX ),
				)
			);
			wp_register_style(
				'tsotab-widget',
				TSOTAB_URL . 'assets/css/widget.css',
				array(),
				TSOTAB_VERSION
			);
		}

		// ── Form (panel widgets) ──────────────────────────────────────────────

		public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array(
				'tabs'             => array( 'recent' => 1, 'popular' => 1, 'comments' => 0, 'tags' => 0 ),
				'tab_order'        => array( 'popular' => 1, 'recent' => 2, 'comments' => 3, 'tags' => 4 ),
				'allow_pagination' => 1,
				'post_num'         => '5',
				'comment_num'      => '5',
				'show_thumb'       => 1,
				'thumb_size'       => 'small',
				'show_date'        => 1,
				'show_excerpt'     => 0,
				'excerpt_length'   => apply_filters( 'tsotab_excerpt_length_default', '15' ),
				'show_comment_num' => 0,
				'show_avatar'      => 1,
				'title_length'     => apply_filters( 'tsotab_title_length_default', '15' ),
			) );

			// phpcs:ignore WordPress.PHP.DontExtract -- comportament original del widget.
			extract( $instance );
			?>
			<div class="wpt_options_form">

				<h4><?php esc_html_e( 'Select Tabs', 'tso-tabs-widget' ); ?></h4>

				<div class="wpt_select_tabs">
					<label class="wpt-col-label" for="<?php echo esc_attr( $this->get_field_id( 'tabs' ) ); ?>_popular">
						<input type="checkbox" class="checkbox"
							id="<?php echo esc_attr( $this->get_field_id( 'tabs' ) ); ?>_popular"
							name="<?php echo esc_attr( $this->get_field_name( 'tabs' ) ); ?>[popular]"
							value="1" <?php checked( 1, ! empty( $tabs['popular'] ) ); ?> />
						<?php esc_html_e( 'Popular Tab', 'tso-tabs-widget' ); ?>
					</label>
					<label class="wpt-col-label" for="<?php echo esc_attr( $this->get_field_id( 'tabs' ) ); ?>_recent">
						<input type="checkbox" class="checkbox"
							id="<?php echo esc_attr( $this->get_field_id( 'tabs' ) ); ?>_recent"
							name="<?php echo esc_attr( $this->get_field_name( 'tabs' ) ); ?>[recent]"
							value="1" <?php checked( 1, ! empty( $tabs['recent'] ) ); ?> />
						<?php esc_html_e( 'Recent Tab', 'tso-tabs-widget' ); ?>
					</label>
					<label class="wpt-col-label" for="<?php echo esc_attr( $this->get_field_id( 'tabs' ) ); ?>_comments">
						<input type="checkbox" class="checkbox wpt_enable_comments"
							id="<?php echo esc_attr( $this->get_field_id( 'tabs' ) ); ?>_comments"
							name="<?php echo esc_attr( $this->get_field_name( 'tabs' ) ); ?>[comments]"
							value="1" <?php checked( 1, ! empty( $tabs['comments'] ) ); ?> />
						<?php esc_html_e( 'Comments Tab', 'tso-tabs-widget' ); ?>
					</label>
					<label class="wpt-col-label" for="<?php echo esc_attr( $this->get_field_id( 'tabs' ) ); ?>_tags">
						<input type="checkbox" class="checkbox"
							id="<?php echo esc_attr( $this->get_field_id( 'tabs' ) ); ?>_tags"
							name="<?php echo esc_attr( $this->get_field_name( 'tabs' ) ); ?>[tags]"
							value="1" <?php checked( 1, ! empty( $tabs['tags'] ) ); ?> />
						<?php esc_html_e( 'Tags Tab', 'tso-tabs-widget' ); ?>
					</label>
				</div>
				<div class="clear"></div>

				<h4 class="wpt_tab_order_header">
					<a href="#"><?php esc_html_e( 'Tab Order', 'tso-tabs-widget' ); ?></a>
				</h4>

				<div class="wpt_tab_order" style="display:none;">
					<label class="wpt-col-label" for="<?php echo esc_attr( $this->get_field_id( 'tab_order' ) ); ?>_popular">
						<input id="<?php echo esc_attr( $this->get_field_id( 'tab_order' ) ); ?>_popular"
							name="<?php echo esc_attr( $this->get_field_name( 'tab_order' ) ); ?>[popular]"
							type="number" min="1" step="1"
							value="<?php echo absint( $tab_order['popular'] ); ?>" style="width:48px;" />
						<?php esc_html_e( 'Popular', 'tso-tabs-widget' ); ?>
					</label>
					<label class="wpt-col-label" for="<?php echo esc_attr( $this->get_field_id( 'tab_order' ) ); ?>_recent">
						<input id="<?php echo esc_attr( $this->get_field_id( 'tab_order' ) ); ?>_recent"
							name="<?php echo esc_attr( $this->get_field_name( 'tab_order' ) ); ?>[recent]"
							type="number" min="1" step="1"
							value="<?php echo absint( $tab_order['recent'] ); ?>" style="width:48px;" />
						<?php esc_html_e( 'Recent', 'tso-tabs-widget' ); ?>
					</label>
					<label class="wpt-col-label" for="<?php echo esc_attr( $this->get_field_id( 'tab_order' ) ); ?>_comments">
						<input id="<?php echo esc_attr( $this->get_field_id( 'tab_order' ) ); ?>_comments"
							name="<?php echo esc_attr( $this->get_field_name( 'tab_order' ) ); ?>[comments]"
							type="number" min="1" step="1"
							value="<?php echo absint( $tab_order['comments'] ); ?>" style="width:48px;" />
						<?php esc_html_e( 'Comments', 'tso-tabs-widget' ); ?>
					</label>
					<label class="wpt-col-label" for="<?php echo esc_attr( $this->get_field_id( 'tab_order' ) ); ?>_tags">
						<input id="<?php echo esc_attr( $this->get_field_id( 'tab_order' ) ); ?>_tags"
							name="<?php echo esc_attr( $this->get_field_name( 'tab_order' ) ); ?>[tags]"
							type="number" min="1" step="1"
							value="<?php echo absint( $tab_order['tags'] ); ?>" style="width:48px;" />
						<?php esc_html_e( 'Tags', 'tso-tabs-widget' ); ?>
					</label>
				</div>
				<div class="clear"></div>

				<h4 class="wpt_advanced_options_header">
					<a href="#"><?php esc_html_e( 'Advanced Options', 'tso-tabs-widget' ); ?></a>
				</h4>

				<div class="wpt_advanced_options" style="display:none;">
					<p>
						<label for="<?php echo esc_attr( $this->get_field_id( 'allow_pagination' ) ); ?>">
							<input type="checkbox" class="checkbox"
								id="<?php echo esc_attr( $this->get_field_id( 'allow_pagination' ) ); ?>"
								name="<?php echo esc_attr( $this->get_field_name( 'allow_pagination' ) ); ?>"
								value="1" <?php checked( 1, $allow_pagination ); ?> />
							<?php esc_html_e( 'Allow pagination', 'tso-tabs-widget' ); ?>
						</label>
					</p>

					<div class="wpt_post_options">
						<p>
							<label for="<?php echo esc_attr( $this->get_field_id( 'post_num' ) ); ?>">
								<?php esc_html_e( 'Number of posts to show:', 'tso-tabs-widget' ); ?><br />
								<input id="<?php echo esc_attr( $this->get_field_id( 'post_num' ) ); ?>"
									name="<?php echo esc_attr( $this->get_field_name( 'post_num' ) ); ?>"
									type="number" min="1" step="1"
									value="<?php echo absint( $post_num ); ?>" />
							</label>
						</p>
						<p>
							<label for="<?php echo esc_attr( $this->get_field_id( 'title_length' ) ); ?>">
								<?php esc_html_e( 'Title length (words):', 'tso-tabs-widget' ); ?><br />
								<input id="<?php echo esc_attr( $this->get_field_id( 'title_length' ) ); ?>"
									name="<?php echo esc_attr( $this->get_field_name( 'title_length' ) ); ?>"
									type="number" min="1" step="1"
									value="<?php echo absint( $title_length ); ?>" />
							</label>
						</p>
						<p>
							<label for="<?php echo esc_attr( $this->get_field_id( 'show_thumb' ) ); ?>">
								<input type="checkbox" class="checkbox wpt_show_thumbnails"
									id="<?php echo esc_attr( $this->get_field_id( 'show_thumb' ) ); ?>"
									name="<?php echo esc_attr( $this->get_field_name( 'show_thumb' ) ); ?>"
									value="1" <?php checked( 1, $show_thumb ); ?> />
								<?php esc_html_e( 'Show post thumbnails', 'tso-tabs-widget' ); ?>
							</label>
						</p>
						<p class="wpt_thumbnail_size"<?php echo ( empty( $show_thumb ) ? ' style="display:none;"' : '' ); ?>>
							<label for="<?php echo esc_attr( $this->get_field_id( 'thumb_size' ) ); ?>">
								<?php esc_html_e( 'Thumbnail size:', 'tso-tabs-widget' ); ?>
							</label>
							<select id="<?php echo esc_attr( $this->get_field_id( 'thumb_size' ) ); ?>"
								name="<?php echo esc_attr( $this->get_field_name( 'thumb_size' ) ); ?>"
								style="margin-left:12px;">
								<option value="small" <?php selected( $thumb_size, 'small' ); ?>><?php esc_html_e( 'Small', 'tso-tabs-widget' ); ?></option>
								<option value="large" <?php selected( $thumb_size, 'large' ); ?>><?php esc_html_e( 'Large', 'tso-tabs-widget' ); ?></option>
							</select>
						</p>
						<p>
							<label for="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>">
								<input type="checkbox" class="checkbox"
									id="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>"
									name="<?php echo esc_attr( $this->get_field_name( 'show_date' ) ); ?>"
									value="1" <?php checked( 1, $show_date ); ?> />
								<?php esc_html_e( 'Show post date', 'tso-tabs-widget' ); ?>
							</label>
						</p>
						<p>
							<label for="<?php echo esc_attr( $this->get_field_id( 'show_comment_num' ) ); ?>">
								<input type="checkbox" class="checkbox"
									id="<?php echo esc_attr( $this->get_field_id( 'show_comment_num' ) ); ?>"
									name="<?php echo esc_attr( $this->get_field_name( 'show_comment_num' ) ); ?>"
									value="1" <?php checked( 1, $show_comment_num ); ?> />
								<?php esc_html_e( 'Show number of comments', 'tso-tabs-widget' ); ?>
							</label>
						</p>
						<p>
							<label for="<?php echo esc_attr( $this->get_field_id( 'show_excerpt' ) ); ?>">
								<input type="checkbox" class="checkbox wpt_show_excerpt"
									id="<?php echo esc_attr( $this->get_field_id( 'show_excerpt' ) ); ?>"
									name="<?php echo esc_attr( $this->get_field_name( 'show_excerpt' ) ); ?>"
									value="1" <?php checked( 1, $show_excerpt ); ?> />
								<?php esc_html_e( 'Show post excerpt', 'tso-tabs-widget' ); ?>
							</label>
						</p>
						<p class="wpt_excerpt_length"<?php echo ( empty( $show_excerpt ) ? ' style="display:none;"' : '' ); ?>>
							<label for="<?php echo esc_attr( $this->get_field_id( 'excerpt_length' ) ); ?>">
								<?php esc_html_e( 'Excerpt length (words):', 'tso-tabs-widget' ); ?><br />
								<input type="number" min="1" step="1"
									id="<?php echo esc_attr( $this->get_field_id( 'excerpt_length' ) ); ?>"
									name="<?php echo esc_attr( $this->get_field_name( 'excerpt_length' ) ); ?>"
									value="<?php echo absint( $excerpt_length ); ?>" />
							</label>
						</p>
					</div><!-- .wpt_post_options -->

					<div class="clear"></div>

					<div class="wpt_comment_options"<?php echo ( empty( $tabs['comments'] ) ? ' style="display:none;"' : '' ); ?>>
						<p>
							<label for="<?php echo esc_attr( $this->get_field_id( 'comment_num' ) ); ?>">
								<?php esc_html_e( 'Number of comments on Comments Tab:', 'tso-tabs-widget' ); ?><br />
								<input type="number" min="1" step="1"
									id="<?php echo esc_attr( $this->get_field_id( 'comment_num' ) ); ?>"
									name="<?php echo esc_attr( $this->get_field_name( 'comment_num' ) ); ?>"
									value="<?php echo absint( $comment_num ); ?>" />
							</label>
						</p>
						<p>
							<label for="<?php echo esc_attr( $this->get_field_id( 'show_avatar' ) ); ?>">
								<input type="checkbox" class="checkbox"
									id="<?php echo esc_attr( $this->get_field_id( 'show_avatar' ) ); ?>"
									name="<?php echo esc_attr( $this->get_field_name( 'show_avatar' ) ); ?>"
									value="1" <?php checked( 1, $show_avatar ); ?> />
								<?php esc_html_e( 'Show avatars on Comments Tab', 'tso-tabs-widget' ); ?>
							</label>
						</p>
					</div><!-- .wpt_comment_options -->

				</div><!-- .wpt_advanced_options -->

				<!-- Banner pro eliminat (fix: imatges externes eliminades) -->

			</div><!-- .wpt_options_form -->
			<?php
		}

		// ── Update (sanitització completa) ────────────────────────────────────

		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$allowed_tabs     = array( 'popular', 'recent', 'comments', 'tags' );
			$raw_tabs         = isset( $new_instance['tabs'] ) ? (array) $new_instance['tabs'] : array();
			$instance['tabs'] = array_intersect_key( $raw_tabs, array_flip( $allowed_tabs ) );

			$raw_order             = isset( $new_instance['tab_order'] ) ? (array) $new_instance['tab_order'] : array();
			$instance['tab_order'] = array();
			foreach ( $allowed_tabs as $t ) {
				$instance['tab_order'][ $t ] = max( 1, absint( $raw_order[ $t ] ?? 1 ) );
			}

			$instance['allow_pagination'] = isset( $new_instance['allow_pagination'] ) ? 1 : 0;
			$instance['post_num']         = max( 1, min( 20, absint( $new_instance['post_num']     ?? 5 ) ) );
			$instance['title_length']     = max( 1, absint( $new_instance['title_length']          ?? 15 ) );
			$instance['comment_num']      = max( 1, min( 20, absint( $new_instance['comment_num']  ?? 5 ) ) );
			$instance['show_thumb']       = isset( $new_instance['show_thumb'] )       ? 1 : 0;
			$instance['thumb_size']       = in_array( $new_instance['thumb_size'] ?? '', array( 'small', 'large' ), true )
				? $new_instance['thumb_size'] : 'small';
			$instance['show_date']        = isset( $new_instance['show_date'] )        ? 1 : 0;
			$instance['show_excerpt']     = isset( $new_instance['show_excerpt'] )     ? 1 : 0;
			$instance['excerpt_length']   = max( 1, min( 50, absint( $new_instance['excerpt_length'] ?? 15 ) ) );
			$instance['show_comment_num'] = isset( $new_instance['show_comment_num'] ) ? 1 : 0;
			$instance['show_avatar']      = isset( $new_instance['show_avatar'] )      ? 1 : 0;

			return $instance;
		}

		// ── Widget (frontend) ─────────────────────────────────────────────────

		public function widget( $args, $instance ) {
			$before_widget = $args['before_widget'];
			$after_widget  = $args['after_widget'];
			$widget_id     = ! empty( $args['widget_id'] ) ? $args['widget_id'] : $this->id;

			wp_enqueue_script( 'tsotab-widget' );
			wp_enqueue_style( 'tsotab-widget' );

			$tabs = ! empty( $instance['tabs'] ) ? $instance['tabs'] : array( 'recent' => 1, 'popular' => 1 );
			$tab_order = isset( $instance['tab_order'] ) ? $instance['tab_order']
				: array( 'popular' => 1, 'recent' => 2, 'comments' => 3, 'tags' => 4 );

			$tabs_count = count( $tabs );
			if ( $tabs_count <= 1 )    { $tabs_count = 1; }
			elseif ( $tabs_count > 3 ) { $tabs_count = 4; }

			$available_tabs = array(
				'popular'  => __( 'Popular',  'tso-tabs-widget' ),
				'recent'   => __( 'Recent',   'tso-tabs-widget' ),
				'comments' => __( 'Comments', 'tso-tabs-widget' ),
				'tags'     => __( 'Tags',     'tso-tabs-widget' ),
			);

			// Ordenar pestanyes per tab_order (mateix comportament que l'original).
			array_multisort( $tab_order, $available_tabs );

			// Args per passar al JS via data-attribute (evita inline script per CSP).
			$js_instance = $instance;
			unset( $js_instance['tabs'], $js_instance['tab_order'] );

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- generat per WP core.
			echo $before_widget;
			?>
			<div class="wpt_widget_content"
				id="<?php echo esc_attr( $widget_id ); ?>_content"
				data-widget-number="<?php echo esc_attr( $this->number ); ?>"
				data-args="<?php echo esc_attr( wp_json_encode( $js_instance ) ); ?>">
				<ul class="wpt-tabs has-<?php echo absint( $tabs_count ); ?>-tabs">
					<?php foreach ( $available_tabs as $tab => $label ) : ?>
						<?php if ( ! empty( $tabs[ $tab ] ) ) : ?>
							<li class="tab_title">
								<a href="#" id="<?php echo esc_attr( $tab ); ?>-tab">
									<?php echo esc_html( $label ); ?>
								</a>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
				<div class="clear"></div>
				<div class="inside">
					<?php if ( ! empty( $tabs['popular'] ) ) : ?>
						<div id="popular-tab-content"  class="tab-content"></div>
					<?php endif; ?>
					<?php if ( ! empty( $tabs['recent'] ) ) : ?>
						<div id="recent-tab-content"   class="tab-content"></div>
					<?php endif; ?>
					<?php if ( ! empty( $tabs['comments'] ) ) : ?>
						<div id="comments-tab-content" class="tab-content"><ul></ul></div>
					<?php endif; ?>
					<?php if ( ! empty( $tabs['tags'] ) ) : ?>
						<div id="tags-tab-content"     class="tab-content"><ul></ul></div>
					<?php endif; ?>
					<div class="clear"></div>
				</div>
				<div class="clear"></div>
			</div>
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- generat per WP core.
			echo $after_widget;
		}

		// ── AJAX handler ──────────────────────────────────────────────────────

		public function ajax_widget_content() {

			if ( ! tsotab_verify_widget_ajax_nonce() ) {
				wp_die( '', '', array( 'response' => 403 ) );
			}

			$tab    = tsotab_get_ajax_post_key( 'tab' );
			$number = tsotab_get_ajax_post_int( 'widget_number', 0 );
			$page   = max( 1, tsotab_get_ajax_post_int( 'page', 1 ) );

			$allowed_tabs = array( 'popular', 'recent', 'comments', 'tags' );
			if ( ! in_array( $tab, $allowed_tabs, true ) ) {
				wp_die( '', '', array( 'response' => 400 ) );
			}

			$raw_args = tsotab_get_ajax_post_args();
			$args             = array();
			$args['post_num']         = isset( $raw_args['post_num'] )         ? sanitize_text_field( $raw_args['post_num'] )         : '5';
			$args['comment_num']      = isset( $raw_args['comment_num'] )      ? sanitize_text_field( $raw_args['comment_num'] )      : '5';
			$args['show_thumb']       = isset( $raw_args['show_thumb'] )       ? sanitize_text_field( $raw_args['show_thumb'] )       : '0';
			$args['thumb_size']       = isset( $raw_args['thumb_size'] )       ? sanitize_key( $raw_args['thumb_size'] )              : 'small';
			$args['show_date']        = isset( $raw_args['show_date'] )        ? sanitize_text_field( $raw_args['show_date'] )        : '0';
			$args['show_excerpt']     = isset( $raw_args['show_excerpt'] )     ? sanitize_text_field( $raw_args['show_excerpt'] )     : '0';
			$args['excerpt_length']   = isset( $raw_args['excerpt_length'] )   ? sanitize_text_field( $raw_args['excerpt_length'] )   : '15';
			$args['show_comment_num'] = isset( $raw_args['show_comment_num'] ) ? sanitize_text_field( $raw_args['show_comment_num'] ) : '0';
			$args['show_avatar']      = isset( $raw_args['show_avatar'] )      ? sanitize_text_field( $raw_args['show_avatar'] )      : '0';
			$args['allow_pagination'] = isset( $raw_args['allow_pagination'] ) ? sanitize_text_field( $raw_args['allow_pagination'] ) : '0';
			$args['title_length']     = isset( $raw_args['title_length'] )     ? sanitize_text_field( $raw_args['title_length'] )     : '15';

			if ( empty( $raw_args ) ) {
				$settings = get_option( 'widget_wpt_widget', array() );
				if ( isset( $settings[ $number ] ) && is_array( $settings[ $number ] ) ) {
					$args = $settings[ $number ];
				} else {
					wp_die( esc_html__( 'Unable to load tab content', 'tso-tabs-widget' ) );
				}
			}

			$post_num = intval( $args['post_num'] ?? 5 );
			if ( $post_num > 20 || $post_num < 1 ) {
				$post_num = 5;
			}

			$comment_num = intval( $args['comment_num'] ?? 5 );
			if ( $comment_num > 20 || $comment_num < 1 ) {
				$comment_num = 5;
			}

			$show_thumb       = ! empty( $args['show_thumb'] )       ? 1 : 0;
			$thumb_size       = isset( $args['thumb_size'] ) && 'large' === $args['thumb_size'] ? 'large' : 'small';
			$show_date        = ! empty( $args['show_date'] )        ? 1 : 0;
			$show_excerpt     = ! empty( $args['show_excerpt'] )     ? 1 : 0;
			$excerpt_length   = intval( $args['excerpt_length']      ?? 15 );
			if ( $excerpt_length > 50 || $excerpt_length < 1 ) {
				$excerpt_length = 10;
			}
			$show_comment_num = ! empty( $args['show_comment_num'] ) ? 1 : 0;
			$show_avatar      = ! empty( $args['show_avatar'] )      ? 1 : 0;
			$allow_pagination = ! empty( $args['allow_pagination'] ) ? 1 : 0;
			$title_length     = max( 1, intval( $args['title_length'] ?? 15 ) );

			switch ( $tab ) {

				/* Popular Posts */
				case 'popular':
					?>
					<ul>
						<?php
						$popular = new WP_Query(
							array(
								'ignore_sticky_posts' => 1,
								'posts_per_page'      => $post_num,
								'post_status'         => 'publish',
								'orderby'             => 'meta_value_num',
								'meta_key'            => '_tsotab_view_count', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- required for popular sort.
								'order'               => 'DESC',
								'paged'               => $page,
							)
						);
						$last_page = $popular->max_num_pages;
						while ( $popular->have_posts() ) :
							$popular->the_post();
							$this->render_post_li( $show_thumb, $thumb_size, $title_length, $show_date, $show_comment_num, $show_excerpt, $excerpt_length );
						endwhile;
						wp_reset_postdata();
						?>
					</ul>
					<div class="clear"></div>
					<?php if ( $allow_pagination ) $this->tab_pagination( $page, $last_page ); ?>
					<?php
					break;

				/* Recent Posts */
				case 'recent':
					?>
					<ul>
						<?php
						$recent = new WP_Query( array(
							'posts_per_page' => $post_num,
							'orderby'        => 'post_date',
							'order'          => 'DESC',
							'post_status'    => 'publish',
							'paged'          => $page,
						) );
						$last_page = $recent->max_num_pages;
						while ( $recent->have_posts() ) :
							$recent->the_post();
							$this->render_post_li( $show_thumb, $thumb_size, $title_length, $show_date, $show_comment_num, $show_excerpt, $excerpt_length );
						endwhile;
						wp_reset_postdata();
						?>
					</ul>
					<div class="clear"></div>
					<?php if ( $allow_pagination ) $this->tab_pagination( $page, $last_page ); ?>
					<?php
					break;

				/* Comments */
				case 'comments':
					$avatar_size    = 65;
					$comment_length = 90;
					$comment_args = apply_filters(
						'tsotab_comments_tab_args',
						array(
							'type'   => 'comments',
							'status' => 'approve',
						)
					);
					$comments_total        = new WP_Comment_Query();
					$comments_total_number = $comments_total->query( array_merge( array( 'count' => 1 ), $comment_args ) );
					$last_page             = (int) ceil( $comments_total_number / $comment_num );
					$offset                = ( $page - 1 ) * $comment_num;
					$comments_query        = new WP_Comment_Query();
					$comments              = $comments_query->query( array_merge(
						array( 'number' => $comment_num, 'offset' => $offset ),
						$comment_args
					) );
					$no_comments = false;
					?>
					<ul>
						<?php if ( $comments ) : ?>
							<?php foreach ( $comments as $comment ) : ?>
							<li>
								<?php if ( $show_avatar ) : ?>
									<div class="wpt_avatar">
										<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
											<?php echo get_avatar( $comment->comment_author_email, $avatar_size ); ?>
										</a>
									</div>
								<?php endif; ?>
								<div class="wpt_comment_meta">
									<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
										<span class="wpt_comment_author"><?php echo esc_html( get_comment_author( $comment->comment_ID ) ); ?></span>
										- <span class="wpt_comment_post"><?php echo esc_html( get_the_title( $comment->comment_post_ID ) ); ?></span>
									</a>
								</div>
								<div class="wpt_comment_content">
									<p><?php echo esc_html( $this->truncate( wp_strip_all_tags( get_comment_text( $comment ) ), $comment_length ) ); ?></p>
								</div>
								<div class="clear"></div>
							</li>
							<?php endforeach; ?>
						<?php else : ?>
							<li>
								<div class="no-comments"><?php esc_html_e( 'No comments yet.', 'tso-tabs-widget' ); ?></div>
							</li>
							<?php $no_comments = true; ?>
						<?php endif; ?>
					</ul>
					<?php if ( $allow_pagination && ! $no_comments ) $this->tab_pagination( $page, $last_page ); ?>
					<?php
					break;

				/* Tags */
				case 'tags':
					$tags = get_tags( array( 'get' => 'all' ) );
					?>
					<ul>
						<?php if ( $tags ) : ?>
							<?php foreach ( $tags as $tag ) : ?>
								<?php
								$term_link = get_term_link( $tag );
								if ( is_wp_error( $term_link ) ) {
									continue;
								}
								?>
								<li>
									<a href="<?php echo esc_url( $term_link ); ?>">
										<?php echo esc_html( $tag->name ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						<?php else : ?>
							<?php esc_html_e( 'No tags created.', 'tso-tabs-widget' ); ?>
						<?php endif; ?>
					</ul>
					<?php
					break;
			}

			wp_die();
		}

		// ── Render d'un <li> de post ──────────────────────────────────────────

		private function render_post_li( $show_thumb, $thumb_size, $title_length, $show_date, $show_comment_num, $show_excerpt, $excerpt_length ) {
			?>
			<li class="wpt-post-item">
				<?php if ( 1 === $show_thumb ) : ?>
					<div class="wpt_thumbnail wpt_thumb_<?php echo esc_attr( $thumb_size ); ?>">
						<a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'wp_review_' . $thumb_size, array(
									'title'   => '',
									'loading' => 'lazy',
								) ); ?>
							<?php else : ?>
								<?php /* Placeholder SVG inline (sense fitxers d'imatge externs). */ ?>
								<img src="<?php echo esc_attr( self::PLACEHOLDER_SVG ); ?>"
									alt="<?php the_title_attribute(); ?>"
									class="wp-post-image wpt-placeholder"
									width="65" height="65"
									loading="lazy" />
							<?php endif; ?>
						</a>
					</div>
				<?php endif; ?>

				<div class="wpt-post-text">
					<div class="entry-title">
						<a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>">
							<?php echo esc_html( $this->post_title( $title_length ) ); ?>
						</a>
					</div>

					<?php if ( 1 === $show_date || 1 === $show_comment_num ) : ?>
						<div class="wpt-postmeta">
							<?php if ( 1 === $show_date ) the_time( 'j F Y' ); ?>
							<?php if ( 1 === $show_date && 1 === $show_comment_num ) echo ' &bull; '; ?>
							<?php if ( 1 === $show_comment_num ) : ?>
								<?php comments_number(
									__( 'No Comment',  'tso-tabs-widget' ),
									__( 'One Comment', 'tso-tabs-widget' ),
									// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
									'<span class="comments-number">%</span> ' . __( 'Comments', 'tso-tabs-widget' )
								); ?>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( 1 === $show_excerpt ) : ?>
						<div class="wpt_excerpt">
							<p><?php echo esc_html( $this->excerpt( $excerpt_length ) ); ?></p>
						</div>
					<?php endif; ?>
				</div><!-- .wpt-post-text -->
			</li>
			<?php
		}

		// ── Paginació ─────────────────────────────────────────────────────────

		public function tab_pagination( $page, $last_page ) {
			?>
			<div class="wpt-pagination">
				<?php if ( $page > 1 ) : ?>
					<a href="#" class="previous"><span><?php esc_html_e( '&laquo; Previous', 'tso-tabs-widget' ); ?></span></a>
				<?php endif; ?>
				<?php if ( $page !== $last_page ) : ?>
					<a href="#" class="next"><span><?php esc_html_e( 'Next &raquo;', 'tso-tabs-widget' ); ?></span></a>
				<?php endif; ?>
			</div>
			<div class="clear"></div>
			<input type="hidden" class="page_num" name="page_num" value="<?php echo absint( $page ); ?>" />
			<?php
		}

		// ── Helpers ───────────────────────────────────────────────────────────

		public function excerpt( $limit = 10 ) {
			$limit++;
			$excerpt = explode( ' ', get_the_excerpt(), $limit );
			if ( count( $excerpt ) >= $limit ) {
				array_pop( $excerpt );
				$excerpt = implode( ' ', $excerpt ) . '...';
			} else {
				$excerpt = implode( ' ', $excerpt );
			}
			$excerpt = preg_replace( '`\[[^\]]*\]`', '', $excerpt );
			return $excerpt;
		}

		public function post_title( $limit = 10 ) {
			$limit++;
			$title = explode( ' ', get_the_title(), $limit );
			if ( count( $title ) >= $limit ) {
				array_pop( $title );
				$title = implode( ' ', $title ) . '...';
			} else {
				$title = implode( ' ', $title );
			}
			return $title;
		}

		public function truncate( $str, $length = 24 ) {
			if ( mb_strlen( $str ) > $length ) {
				return mb_substr( $str, 0, $length ) . '...';
			}
			return $str;
		}

} // end class TSOTAB_Widget
