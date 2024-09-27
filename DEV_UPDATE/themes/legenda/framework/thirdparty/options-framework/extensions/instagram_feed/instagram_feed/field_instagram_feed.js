/****************************************************/
/* Panel social functions
/****************************************************/
jQuery(document).on('click', '.etheme-user .user-remove', function(e) {
	e.preventDefault();
	if ( ! confirm( 'Are you sure' ) ) {
		return;
	}
	var user = jQuery(this).parents('.etheme-user');
	var data =  {
		'action':'et_instagram_user_remove',
		'token': user.find('.user-token').attr( 'data-token' )
	};
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		success: function(data){
			if ( data != 'success' ){
			} else {
				if ( jQuery( '.etheme-user' ).length < 2 ) {
					jQuery( '.etheme-no-users' ).removeClass( 'hidden' );
				}
					
				user.remove();
			}
		},
		error: function(){
			alert('Error while deleting');
		},
		complete: function(){

		}
	});
});


jQuery(document).on('click', '.etheme-instagram-settings .etheme-instagram-save', function(e) {
	e.preventDefault();
	if ( ! confirm( 'Are you sure ?' ) ) {
		return;
	}
	var data =  {
		'action':'et_instagram_save_settings',
		'time':jQuery('#instagram_time').attr('value'),
		'time_type': jQuery('#instagram_time_type').attr('value')
	};
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		success: function(data){
			console.log(data);
		},
		error: function(){
			alert('Error while deleting');
		},
		complete: function(){

		}
	});
});

jQuery(document).on('click', '.etheme-instagram-manual', function(e) {
	e.preventDefault();
	if ( jQuery( '.etheme-instagram-manual-form' ).hasClass( 'hidden' ) ) {
		jQuery( '.etheme-instagram-manual-form' ).removeClass( 'hidden' );
	} else {
		jQuery( '.etheme-instagram-manual-form' ).addClass( 'hidden' );
	}
});


jQuery(document).on('click', '.etheme-manual-btn', function(e) {
	e.preventDefault();
	if ( ! confirm( 'Are you sure' ) ) {
		return;
	}
	var parent = jQuery(this).parent();
	var data =  {
		'action': 'et_instagram_user_add',
		'token' : jQuery( '#etheme-manual-token' ).attr( 'value' )
	};

	if ( ! data['token'] ) {
		parent.find( '.etheme-form-error' ).removeClass( 'hidden' );
		return;
	} else {
		parent.find( '.etheme-form-error' ).addClass( 'hidden' );
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		success: function(data){
			if ( data != 'success' ){
				parent.find( '.etheme-form-error-holder' ).text( '' );
				parent.find( '.etheme-form-error-holder' ).text( data );
				parent.find( '.etheme-form-error-holder' ).removeClass( 'hidden' );
			} else {
				location.reload();
			}
		},
		error: function(){
			alert('Error while deleting');
		},
		complete: function(){

		}
	});
});