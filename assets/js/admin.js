/*
 * TSO Tabs Widget — widget form admin UI.
 * Version: 1.0.0
 */

jQuery( document ).on( 'click', function ( e ) {
	var $this = jQuery( e.target );
	var $form = $this.closest( '.wpt_options_form' );

	if ( $this.is( '.wpt_enable_comments' ) ) {
		var $related = $form.find( '.wpt_comment_options' );
		if ( $this.is( ':checked' ) ) {
			$related.slideDown();
		} else {
			$related.slideUp();
		}

	} else if ( $this.is( '.wpt_show_thumbnails' ) ) {
		var $related = $form.find( '.wpt_thumbnail_size' );
		if ( $this.is( ':checked' ) ) {
			$related.slideDown();
		} else {
			$related.slideUp();
		}

	} else if ( $this.is( '.wpt_show_excerpt' ) ) {
		var $related = $form.find( '.wpt_excerpt_length' );
		if ( $this.is( ':checked' ) ) {
			$related.slideDown();
		} else {
			$related.slideUp();
		}

	} else if ( $this.is( '.wpt_tab_order_header a' ) ) {
		e.preventDefault();
		$form.find( '.wpt_tab_order' ).slideToggle();

	} else if ( $this.is( '.wpt_advanced_options_header a' ) ) {
		e.preventDefault();
		$form.find( '.wpt_advanced_options' ).slideToggle();
	}
} );

jQuery( document ).on( 'click', '.tabwidget-notice-dismiss', function ( e ) {
	e.preventDefault();
	var dismiss = jQuery( this ).data( 'ignore' );
	jQuery( this ).closest( '.tso-tabs-widget-notice' ).remove();
	jQuery.ajax( {
		type: 'POST',
		url:  ajaxurl,
		data: {
			action:  'tsotab_dismiss_notice',
			dismiss: dismiss,
			nonce:   ( typeof tsotabAdminConfig !== 'undefined' ? tsotabAdminConfig.dismissNonce : '' ),
		},
	} );
	return false;
} );
