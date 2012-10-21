jQuery( function( $ ) {
	var checkCWA = function( sidebar ) {
		/*console.log( 'Checking to see if any collapsible widgets are inside of any collapsible containers' );*/
		if ( $( '.sidebar-collapsible-widget-area .widget[id*="collapsible-widget"]' ).length <= 0 ) {
			/*console.log( 'Did not find any collapsible widgets inside of collapsible containers' );*/
			return;
		}
		
		$( '.sidebar-collapsible-widget-area .widget[id*="collapsible-widget"]' ).each( function() {
			$( this ).removeClass( 'closed' );
			$( this ).find( '.widget-inside' ).css( { 'display' : 'block', 'color' : 'red' } );
			$( this ).closest( '.sidebar-collapsible-widget-area' ).removeClass( 'closed' );
		} );
		$( '.sidebar-collapsible-widget-area .widget[id*="collapsible-widget"] .widget-content' ).each( function() {
			$( this ).html( '<p>You attempted to set up a collapsible widget inside of a collapsible widget area. This could cause an infinite recursion resulting in a tear in the space-time continuum.</p> <p><strong>Please remove this widget from this sidebar</strong> in order to avoid destroying the entire universe. Thank you.</p>' );
			$( this ).closest( '.widget-inside' ).find( '.widget-control-save' ).remove();
		} );
	}
	
	var realSidebars = $( '#widgets-right div.widgets-sortables' );
	
	realSidebars.map( function() { checkCWA( this ); } )
	realSidebars.bind( 'sortreceive sortremove', function( event, ui ) {
		/*console.log( 'Just fired sortreceive or sortremove event' );*/
		setTimeout( function() { checkCWA(); }, 10 );
	} );
	realSidebars.bind( 'sortstop', function( event, ui ) {
		/*console.log( 'Just fired sortstop event' );*/
		setTimeout( function() { checkCWA(); }, 10 );
	} );
} );