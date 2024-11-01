/**
 * Admin scripts.
 */
jQuery(function() {

	// Block Import form.
	const import_form   = jQuery( jQuery( '#easl-import-form' ).html() ).insertAfter( '.wp-header-end' ).hide();
	const import_toggle = jQuery( '<button class="page-title-action">' ).text( 'Import' ).insertBefore( '.post-type-ea_block_pattern .page-title-action' );

	import_toggle.on( 'click', function( e ) {
		e.preventDefault();
		import_form.toggle();
	} );

	/**
	 * Event to download block pattern json file.
	 *
	 * @version 0.0.1
	 */
	jQuery( '.easl-export-block-pattern' ).on( 'click', function() {

		jQuery.ajax({
			url: ea_block.ajax_url,
			type: 'POST',
			dataType: 'json',
			data: {
				'action'    : 'easl_export_block_pattern',
				'ajax_nonce': ea_block.ajax_nonce,
				'post_id'   : jQuery(this).data('post-id')
			},
			error: function( error ) {
				console.log( error.statusText );
			},
			success: function( response ) {
				if( response.success && response.data ) {
					download_file( response.data.contents , response.data.file_name );
				} else {
					console.log( response.data );
				}
			}
		});

	});

	/**
	 * Function to download the content as file.
	 *
	 * @since 0.0.1
	 *
	 * @param {String} content Contents for file
	 * @param {String} name    Name of the file.
	 */
	function download_file( content, file_name ) {
		const link = document.body.appendChild( document.createElement('a') );
		const file = new Blob([content], {
			type: 'application/json'
		});
		link.href = URL.createObjectURL(file);
		link.download = file_name;
		link.click();
	}

	// Ajax call to upload the pattern.
	jQuery( '#easl-block-pattern-import' ).on( 'click', ( e ) => {
		e.preventDefault();

		let formData = new FormData();
		let file = jQuery( '#easl_block_pattern_json' ).prop( 'files' )[0];
		formData.append( 'file', file );
		formData.append( 'action', 'easl_block_pattern_import' );
		formData.append( 'ajax_nonce', ea_block.ajax_nonce );

		jQuery.ajax( {
			url: ea_block.ajax_url,
			data: formData,
			type: 'POST',
			processData: false,
			contentType: false,
			dataType: 'json',
			success: ( response ) => {
				if ( response.success ) {
					jQuery( '#easl-pattern-library-import').hide();
					jQuery( '#easl_block_pattern_json' ).val('');
					jQuery( '#easl-import-success p' ).append(response.data);
					jQuery( '#easl-import-success').show();
					setTimeout(location.reload(), 2500);
				} else {
					jQuery( '#easl-import-error p' ).append(response.data)
					jQuery( '#easl-import-error').show();
				}
			},
			error: ( error ) => {
				jQuery( '#easl-import-error p' ).append(error.responseJSON.data)
				jQuery( '#easl-import-error').show();
			}
		} );
	} );

} );
