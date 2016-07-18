(function( $ ) {
	
    $(function() {
         
        // Add Color Picker to all inputs that have 'color-field' class
       $('.learndash-skin-color-picker').wpColorPicker();
         
    });
	
	
	// Show the current preview based on what's saved
	var current_lds_skin = $('#learndash-skin').val();
	$('#lds-'+current_lds_skin).show();
	
	
	$('#learndash-skin').change(function() { 
	
		var new_lds_skin = $(this).val();
				
		lds_load_defaults(new_lds_skin);
		
		$('.lds-theme-preview').hide();
		
		$('#lds-' + new_lds_skin).show();		
	
	});
	
	function lds_load_defaults(skin) { 
	
		if(skin == 'modern') { 
		
			$('.lds_heading_bg').wpColorPicker('color','#2f4050');
			$('.lds_heading_txt').wpColorPicker('color','#a7b1c2');
			$('.lds_row_bg').wpColorPicker('color','#f6f6f7');
			$('.lds_row_bg_alt').wpColorPicker('color','#efeff1');
			$('.lds_row_txt').wpColorPicker('color','#2f4050');
			$('.lds_sub_row_bg').wpColorPicker('color','#ffffff');
			$('.lds_sub_row_bg_alt').wpColorPicker('color','#ffffff');
			$('.lds_sub_row_txt').wpColorPicker('color','#2f4050');
			$('.lds_button_bg').wpColorPicker('color','#23c6c8');
			$('.lds_button_txt').wpColorPicker('color','#ffffff');
			$('.lds_complete_button_bg').wpColorPicker('color','#1ab394');
			$('.lds_complete_button_txt').wpColorPicker('color','#ffffff');
			$('.lds_progress').wpColorPicker('color','#1ab394');
			$('.lds_links').wpColorPicker('color','#2f4050');
			$('.lds_widget_bg').wpColorPicker('color','#f5f5f6');
			$('.lds_widget_header_bg').wpColorPicker('color','#2f4050');
			$('.lds_widget_header_txt').wpColorPicker('color','#a7b1c2');
			$('.lds_widget_txt').wpColorPicker('color','#444444');
			$('.lds_checkbox_complete').wpColorPicker('color','#1ab394');
			$('.lds_checkbox_incomplete').wpColorPicker('color','#2f4050');
			$('.lds_arrow_complete').wpColorPicker('color','#1ab394');
			$('.lds_arrow_incomplete').wpColorPicker('color','#2f4050');
			
			$('#lds_button_border_radius').val('0');
			$('#lds_icon_style').val('modern');
			
			
		}
		
		if(skin == 'rustic') { 
		
			$('.lds_heading_bg').wpColorPicker('color','#036564');
			$('.lds_heading_txt').wpColorPicker('color','#ffffff');
			$('.lds_row_bg').wpColorPicker('color','#f9f4ec');
			$('.lds_row_bg_alt').wpColorPicker('color','#f3efe9');
			$('.lds_row_txt').wpColorPicker('color','#333333');
			$('.lds_sub_row_bg').wpColorPicker('color','#fbfffd');
			$('.lds_sub_row_bg_alt').wpColorPicker('color','#fbfffd');
			$('.lds_sub_row_txt').wpColorPicker('color','#333');
			$('.lds_button_bg').wpColorPicker('color','#031634');
			$('.lds_button_txt').wpColorPicker('color','#ffffff');
			$('.lds_complete_button_bg').wpColorPicker('color','#036564');
			$('.lds_complete_button_txt').wpColorPicker('color','#fff');
			$('.lds_progress').wpColorPicker('color','#036564');
			$('.lds_links').wpColorPicker('color','#031634');
			$('.lds_widget_bg').wpColorPicker('color','#f9f4ec');
			$('.lds_widget_header_bg').wpColorPicker('color','#033649');
			$('.lds_widget_header_txt').wpColorPicker('color','#ffffff');
			$('.lds_widget_txt').wpColorPicker('color','#333333');
			$('.lds_checkbox_complete').wpColorPicker('color','#036564');
			$('.lds_checkbox_incomplete').wpColorPicker('color','#dddddd');
			$('.lds_arrow_complete').wpColorPicker('color','#85d18a');
			$('.lds_arrow_incomplete').wpColorPicker('color','#036564');
			
			$('#lds_button_border_radius').val('2');
			$('#lds_icon_style').val('chunky');
			
		}
		
		if(skin == 'classic') { 
		
			$('.lds_heading_bg').wpColorPicker('color','#efefef');
			$('.lds_heading_txt').wpColorPicker('color','#444');
			$('.lds_row_bg').wpColorPicker('color','#fafafa');
			$('.lds_row_bg_alt').wpColorPicker('color','#fcfcfc');
			$('.lds_row_txt').wpColorPicker('color','#444');
			$('.lds_sub_row_bg').wpColorPicker('color','#fff');
			$('.lds_sub_row_bg_alt').wpColorPicker('color','#fff');
			$('.lds_sub_row_txt').wpColorPicker('color','#444');
			$('.lds_button_bg').wpColorPicker('color','#556270');
			$('.lds_button_txt').wpColorPicker('color','#fff');
			$('.lds_complete_button_bg').wpColorPicker('color','#77CCA4');
			$('.lds_complete_button_txt').wpColorPicker('color','#fff');
			$('.lds_progress').wpColorPicker('color','#77CCA4');
			$('.lds_links').wpColorPicker('color','#556270');
			$('.lds_widget_bg').wpColorPicker('color','#fafafa');
			$('.lds_widget_header_bg').wpColorPicker('color','#efefef');
			$('.lds_widget_header_txt').wpColorPicker('color','#444');
			$('.lds_widget_txt').wpColorPicker('color','#222');
			$('.lds_checkbox_complete').wpColorPicker('color','#77CCA4');
			$('.lds_checkbox_incomplete').wpColorPicker('color','#dddddd');
			$('.lds_arrow_complete').wpColorPicker('color','#77CCA4');
			$('.lds_arrow_incomplete').wpColorPicker('color','#006198');
			
			$('#lds_button_border_radius').val('0');
			$('#lds_icon_style').val('minimal');
			
			
		}
		
		if(skin == 'playful') { 

			$('.lds_heading_bg').wpColorPicker('color','#01E0FE');
			$('.lds_heading_txt').wpColorPicker('color','#004852');
			$('.lds_row_bg').wpColorPicker('color','#f9f9f9');
			$('.lds_row_bg_alt').wpColorPicker('color','#f1f1f1');
			$('.lds_row_txt').wpColorPicker('color','#435447');
			$('.lds_sub_row_bg').wpColorPicker('color','#fff');
			$('.lds_sub_row_bg_alt').wpColorPicker('color','#fff');
			$('.lds_sub_row_txt').wpColorPicker('color','#005385');
			$('.lds_button_bg').wpColorPicker('color','#1E6962');
			$('.lds_button_txt').wpColorPicker('color','#fff');
			$('.lds_complete_button_bg').wpColorPicker('color','#BDD537');
			$('.lds_complete_button_txt').wpColorPicker('color','#fff');
			$('.lds_progress').wpColorPicker('color','#BDD537');
			$('.lds_links').wpColorPicker('color','#005385');
			$('.lds_widget_bg').wpColorPicker('color','#f9f9f9');
			$('.lds_widget_header_bg').wpColorPicker('color','#01E0FE');
			$('.lds_widget_header_txt').wpColorPicker('color','#004852');
			$('.lds_widget_txt').wpColorPicker('color','#473d13');
			$('.lds_checkbox_complete').wpColorPicker('color','#BDD537');
			$('.lds_checkbox_incomplete').wpColorPicker('color','#1E6962');
			$('.lds_arrow_complete').wpColorPicker('color','#BDD537');
			$('.lds_arrow_incomplete').wpColorPicker('color','#1E6962');	
			
			$('#lds_button_border_radius').val('12');	
			$('#lds_icon_style').val('playful');
			
		
		}
	
		if(skin == 'default') { 
			
			$('.lds_heading_bg').wpColorPicker('color','');
			$('.lds_heading_txt').wpColorPicker();
			$('.lds_row_bg').wpColorPicker();
			$('.lds_row_bg_alt').wpColorPicker();
			$('.lds_row_txt').wpColorPicker();
			$('.lds_sub_row_bg').wpColorPicker();
			$('.lds_sub_row_bg_alt').wpColorPicker();
			$('.lds_sub_row_txt').wpColorPicker();
			$('.lds_button_bg').wpColorPicker();
			$('.lds_button_txt').wpColorPicker();
			$('.lds_complete_button_bg').wpColorPicker();
			$('.lds_complete_button_txt').wpColorPicker();
			$('.lds_progress').wpColorPicker();
			$('.lds_links').wpColorPicker();
			$('.lds_widget_bg').wpColorPicker();
			$('.lds_widget_header_bg').wpColorPicker();
			$('.lds_widget_header_txt').wpColorPicker();
			$('.lds_widget_txt').wpColorPicker();
			$('.lds_checkbox_complete').wpColorPicker();
			$('.lds_checkbox_incomplete').wpColorPicker();
			$('.lds_arrow_complete').wpColorPicker();
			$('.lds_arrow_incomplete').wpColorPicker();		
			
			$('#lds_button_border_radius').val('0');
			$('#lds_icon_style').val('default');
			
			
		}
		
	
		if(skin == 'upscale') { 
			$('.lds_heading_bg').wpColorPicker('color','#69514D');
			$('.lds_heading_txt').wpColorPicker('color','#fff');
			$('.lds_row_bg').wpColorPicker('color','#333');
			$('.lds_row_bg_alt').wpColorPicker('color','#222');
			$('.lds_row_txt').wpColorPicker('color','#fff');
			$('.lds_sub_row_bg').wpColorPicker('color','#444');
			$('.lds_sub_row_bg_alt').wpColorPicker('color','#444');
			$('.lds_sub_row_txt').wpColorPicker('color','#fff');
			$('.lds_button_bg').wpColorPicker('color','#AD3223');
			$('.lds_button_txt').wpColorPicker('color','#fff');
			$('.lds_complete_button_bg').wpColorPicker('color','#BE652D');
			$('.lds_complete_button_txt').wpColorPicker('color','#fff');
			$('.lds_progress').wpColorPicker('color','#BE652D');
			$('.lds_links').wpColorPicker('color','#fff');
			$('.lds_widget_bg').wpColorPicker('color','#000');
			$('.lds_widget_header_bg').wpColorPicker('color','#69514D');
			$('.lds_widget_header_txt').wpColorPicker('color','#fff');
			$('.lds_widget_txt').wpColorPicker('color','#fff');
			$('.lds_checkbox_complete').wpColorPicker('color','#BE652D');
			$('.lds_checkbox_incomplete').wpColorPicker('color','#f1f1f1');
			$('.lds_arrow_complete').wpColorPicker('color','#BE652D');
			$('.lds_arrow_incomplete').wpColorPicker('color','#f1f1f1');
			
			$('#lds_button_border_radius').val('0');
			$('#lds_icon_style').val('minimal');
					
		}
	
	}
	
	
})( jQuery );