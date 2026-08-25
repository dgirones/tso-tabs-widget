/*
 * TSO Tabs Widget — front-end tab AJAX loader.
 * Version: 1.0.0
 */

function tsotabShowTabContent( $container, $tabContent ) {
	$container.find( '.inside > .tab-content' ).removeClass( 'tsotab-visible' );
	$tabContent.addClass( 'tsotab-visible' );
}

/**
 * Load tab content via AJAX.
 *
 * @param {string} tab_slug   Tab slug (popular|recent|comments|tags).
 * @param {number} page_num   Page number.
 * @param {jQuery} container  .wpt_widget_content element.
 * @param {Object} args_obj   Widget settings.
 */
function tsotabLoadTabContent( tab_slug, page_num, container, args_obj ) {

	var $container  = jQuery( container );
	var $tabContent = $container.find( '.tab-content[data-tab="' + tab_slug + '"]' );
	var isLoaded    = $tabContent.data( 'loaded' );

	if ( ! isLoaded || page_num !== 1 ) {
		if ( $container.hasClass( 'wpt-loading' ) ) {
			return;
		}

		$container.addClass( 'wpt-loading' );

		jQuery.ajax( {
			type:    'POST',
			url:     tsotabWidgetConfig.ajaxUrl,
			timeout: 15000,
			data:    {
				action:        'tsotab_widget_content',
				nonce:         tsotabWidgetConfig.nonce,
				tab:           tab_slug,
				page:          page_num,
				args:          args_obj,
				widget_number: $container.data( 'widget-number' )
			},
			success: function ( response ) {
				$container.removeClass( 'wpt-loading' );
				$tabContent
					.html( response )
					.data( 'loaded', 1 );
				tsotabShowTabContent( $container, $tabContent );
			},
			error: function ( jqXHR, textStatus ) {
				$container.removeClass( 'wpt-loading' );
				var i18n = ( tsotabWidgetConfig.i18n || {} );
				var msg = textStatus === 'timeout'
					? ( i18n.timeoutError || 'Timeout loading content.' )
					: ( i18n.loadError || 'Error loading content.' ) + ' (' + jqXHR.status + ')';
				$tabContent.html( '<p style="padding:10px;color:#999;font-size:12px;">' + msg + '</p>' );
				tsotabShowTabContent( $container, $tabContent );
				if ( window.console ) {
					console.error( 'TSO Tabs Widget AJAX error [' + tab_slug + ']:', textStatus, jqXHR.status, jqXHR.responseText );
				}
			}
		} );

	} else {
		tsotabShowTabContent( $container, $tabContent );
	}
}

jQuery( document ).ready( function () {
	if ( typeof tsotabWidgetConfig === 'undefined' ) {
		return;
	}

	jQuery( '.wpt_widget_content' ).each( function () {
		var $this = jQuery( this );
		var args  = $this.data( 'args' );

		$this.find( '.wpt-tabs a' ).click( function ( e ) {
			e.preventDefault();
			jQuery( this ).parent().addClass( 'selected' ).siblings().removeClass( 'selected' );
			var tab_slug = jQuery( this ).data( 'tab' );
			tsotabLoadTabContent( tab_slug, 1, $this, args );
		} );

		$this.on( 'click', '.wpt-pagination a', function ( e ) {
			e.preventDefault();
			var $a       = jQuery( this );
			var $pane    = $a.closest( '.tab-content' );
			var tab_slug = $pane.data( 'tab' );
			var page_num = parseInt( $pane.find( '.page_num' ).val(), 10 ) || 1;

			if ( $a.hasClass( 'next' ) ) {
				tsotabLoadTabContent( tab_slug, page_num + 1, $this, args );
			} else {
				$pane.data( 'loaded', 0 );
				tsotabLoadTabContent( tab_slug, page_num - 1, $this, args );
			}
		} );

		$this.find( '.wpt-tabs a' ).first().click();
	} );
} );
