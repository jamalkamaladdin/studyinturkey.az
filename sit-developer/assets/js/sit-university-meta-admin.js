( function ( $ ) {
	'use strict';

	function bindField( $wrap ) {
		var $input = $wrap.find( '.sit-univ-media-input' );
		var $preview = $wrap.find( '.sit-univ-media-preview' );
		var frame = null;

		$wrap.find( '.sit-univ-media-select' ).on( 'click', function ( e ) {
			e.preventDefault();
			if ( frame ) {
				frame.open();
				return;
			}
			frame = wp.media( {
				title: sitUniversityMeta.frameTitle,
				button: { text: sitUniversityMeta.frameButton },
				multiple: false,
				library: { type: 'image' }
			} );
			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				$input.val( attachment.id );
				if ( attachment.sizes && attachment.sizes.medium ) {
					$preview.html( '<img src="' + attachment.sizes.medium.url + '" alt="" style="max-width:160px;height:auto;display:block;margin-top:8px;" />' );
				} else if ( attachment.url ) {
					$preview.html( '<img src="' + attachment.url + '" alt="" style="max-width:160px;height:auto;display:block;margin-top:8px;" />' );
				}
			} );
			frame.open();
		} );

		$wrap.find( '.sit-univ-media-clear' ).on( 'click', function ( e ) {
			e.preventDefault();
			$input.val( '' );
			$preview.empty();
		} );
	}

	$( function () {
		$( '.sit-univ-media-field' ).each( function () {
			bindField( $( this ) );
		} );
	} );
}( jQuery ) );
