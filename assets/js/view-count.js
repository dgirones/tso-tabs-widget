/*
 * TSO Tabs Widget — post view counter (cache-friendly AJAX).
 * Version: 1.0.0
 */

jQuery( function ( $ ) {
	if ( typeof tsotabViewCountConfig === 'undefined' ) {
		return;
	}
	$.post( tsotabViewCountConfig.ajaxUrl, {
		action: 'tsotab_view_count',
		id:     tsotabViewCountConfig.postId,
		nonce:  tsotabViewCountConfig.nonce
	} );
} );
