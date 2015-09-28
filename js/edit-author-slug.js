jQuery(document).ready(function($){

	// Make example nicenames clickable.
	$('input[name="ba_eas_author_slug"]').click(function(){
		if ( 'ba_eas_author_slug_custom_radio' !== $(this).attr('id') ) {
			$('input[name="ba_eas_author_slug_custom"]').val( $(this).val() ).text( $(this).siblings('span').text() );
		}
	});

	// If focus moves to the custom author slug input, select the radio.
	$('input[name="ba_eas_author_slug_custom"]').focus(function(){
		$('#ba_eas_author_slug_custom_radio').attr('checked', 'checked');
	});

	// Hide the slugs if we're not doing role-based.
	if ( ! $('input[name="_ba_eas_do_role_based"]').is(':checked') ) {
		$('input[name="_ba_eas_do_role_based"]').parents('tr').next('tr').addClass('hidden');
	}

	// Watch for clicks on the role-based option.
	$('input[name="_ba_eas_do_role_based"]').on('click', function(){
		if ( $(this).is(':checked') ) {
			$(this).parents('tr').next('tr').fadeIn('slow', function(){$(this).removeClass('hidden');});
		} else {
			$(this).parents('tr').next('tr').fadeOut('fast', function(){$(this).addClass('hidden');});
		}
	});

	// Hide the slugs if we're not doing auto-update.
	if ( ! $('input[name="_ba_eas_do_auto_update"]').is(':checked') ) {
		$('input[name="_ba_eas_do_auto_update"]').parents('tr').next('tr').addClass('hidden');
	}

	// Watch for clicks on the auto-update option.
	$('input[name="_ba_eas_do_auto_update"]').on('click', function(){
		if ( $(this).is(':checked') ) {
			$(this).parents('tr').next('tr').fadeIn('slow', function(){$(this).removeClass('hidden');});
		} else {
			$(this).parents('tr').next('tr').fadeOut('fast', function(){$(this).addClass('hidden');});
		}
	});

	// Hide the slugs if we're not doing bulk update.
	if ( ! $('input[name="_ba_eas_bulk_update"]').is(':checked') ) {
		$('input[name="_ba_eas_bulk_update"]').parents('tr').next('tr').addClass('hidden');
	}

	// Watch for clicks on the bulk update option.
	$('input[name="_ba_eas_bulk_update"]').on('click', function(){
		if ( $(this).is(':checked') ) {
			$(this).parents('tr').next('tr').fadeIn('slow', function(){$(this).removeClass('hidden');});
		} else {
			$(this).parents('tr').next('tr').fadeOut('fast', function(){$(this).addClass('hidden');});
		}
	});
});
