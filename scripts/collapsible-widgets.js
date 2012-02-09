jQuery( function( $ ) {
	if ( typeof( collapsible_widget_area ) === 'undefined' ) {
		collapsible_widget_area = { 'type' : 'tabbed' };
	}
	
	if ( collapsible_widget_area.type == 'accordion' ) {
		$( '.collapsible-item .widgettitle' ).each( function() {
			$( this ).html( $( this ).text() );
			$( this ).wrapInner( '<a href="#' + $( this ).closest( '.collapsible-item' ).attr( 'id' ) + '"/>' );
			$( this ).insertBefore( $( this ).closest( '.collapsible-item' ) );
		} );
		$( '.collapsible-widget-container .widgettitle' ).first().addClass( 'first-tab' );
		$( '.collapsible-widget-container .widgettitle' ).last().addClass( 'last-tab' );
		
		var ciwa_accordion_opts = {};
		ciwa_accordion_opts.collapsible = collapsible_widget_area.collapsible;
		ciwa_accordion_opts.active = collapsible_widget_area.closed ? false : 0;
		
		$( '.collapsible-widget-container' ).accordion( ciwa_accordion_opts );
	} else {
		var collapsibleIDs = 0;
		$( '.collapsible-widget-container' ).prepend( '<ul class="tab-nav"/>' );
		$( '.collapsible-item .widgettitle' ).each( function() {
			$( this ).wrapInner( '<a href="#' + $( this ).closest( '.collapsible-item' ).attr( 'id' ) + '"/>' );
			$( this ).wrap( '<li/>' );
			var currentItem = $( this ).find( 'a' );
			$( currentItem ).unwrap();
			$( currentItem ).closest( 'li' ).appendTo( $( currentItem ).closest( '.collapsible-widget-container' ).find( '.tab-nav' ) );
		} );
		$( '.collapsible-widget-container .tab-nav li' ).first().addClass( 'first-tab' );
		$( '.collapsible-widget-container .tab-nav li' ).last().addClass( 'last-tab' );
		
		var ciwa_tab_opts = {};
		if ( collapsible_widget_area.cookie ) {
			ciwa_tab_opts.cookie = true;
		}
		
		$( '.collapsible-widget-container' ).tabs( ciwa_tab_opts );
	}
} );