'use strict';
//PROCESS FONTS
	
//load
jQuery(function(){
	//functions
	hmenu_process_font_pack();
});

//process the font pack
function hmenu_process_font_pack(callback){
	//load the html
	jQuery.ajax({
		url: hmenu_ajax_url,
		type: "POST",
		data: {
			'action': 'hmenu_process_file'
		},
		dataType: "json"
	}).done(function(response){	
		//check data
		if(typeof callback !== 'undefined'){
			eval(""+ callback +"("+response+");");
		}
	}).fail(function(){
		 //page error
	});		
}

