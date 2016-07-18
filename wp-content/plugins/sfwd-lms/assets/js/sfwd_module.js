if ( typeof sfwd_data.json != 'undefined' ) {
	sfwd_data = sfwd_data.json.replace(/&quot;/g, '"');
	sfwd_data = jQuery.parseJSON( sfwd_data );
}


function toggleVisibility(id) {
	var e = document.getElementById(id);
	if (e.style.display == 'block')
		e.style.display = 'none';
	else
		e.style.display = 'block';
}

function countChars(field,cntfield) {
	cntfield.value = field.value.length;
}

jQuery('.sfwd_datepicker').each(function () {
	jQuery('#' + jQuery(this).attr('id')).datepicker();
});

function sfwd_do_condshow_match( index, value ) {
	if ( typeof value != 'undefined' ) {
		matches = true;
		jQuery.each(value, function(subopt, setting) {
			cur = jQuery('[name=' + subopt + ']');
			type = cur.attr('type');
			if ( type == "checkbox" || type == "radio" )
				cur = jQuery('input[name=' + subopt + ']:checked');
			cur = cur.val();
			if ( cur != setting ) {
				matches = false;
				return false;
			}
		});
		if ( matches ) {
			jQuery('#' + index ).show();
		} else {
			jQuery('#' + index ).hide();					
		}
		return matches;
	}
	return false;
}

function sfwd_add_condshow_handlers( index, value ) {
	if ( typeof value != 'undefined' ) {
		jQuery.each(value, function(subopt, setting) {
			jQuery('[name=' + subopt + ']').change(function() {
				sfwd_do_condshow_match( index, value );
			});
		});
	}
}

function sfwd_do_condshow( condshow ) {
	if ( typeof sfwd_data.condshow != 'undefined' ) {
		jQuery.each(sfwd_data.condshow, function(index, value) {
			sfwd_do_condshow_match( index, value );
			sfwd_add_condshow_handlers( index, value );
		});
	}
}

function sfwd_show_pointer( handle, value ) {
	if ( typeof( jQuery( value.pointer_target ).pointer) != 'undefined' ) {
		jQuery(value.pointer_target).pointer({
					content    : value.pointer_text,
					close  : function() {
						jQuery.post( ajaxurl, {
							pointer: handle,
							action: 'dismiss-wp-pointer'
						});
					}
				}).pointer('open');
	}
}

jQuery(document).ready(function(){
if (typeof sfwd_data != 'undefined') {
	if ( typeof sfwd_data.condshow != 'undefined' ) {
		sfwd_do_condshow( sfwd_data.condshow );
	}
}
});

jQuery(document).ready(function() {
	var image_field;
	jQuery('.sfwd_upload_image_button').click(function() {
		window.send_to_editor = newSendToEditor;
		image_field = jQuery(this).next();
		formfield = image_field.attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});

	storeSendToEditor 	= window.send_to_editor;
	newSendToEditor		= function(html) {
							imgurl = jQuery('img',html).attr('src');
							image_field.val(imgurl);
							tb_remove();
							window.send_to_editor = storeSendToEditor;
						};
});

// props to commentluv for this fix
// workaround for bug that causes radio inputs to lose settings when meta box is dragged.
// http://core.trac.wordpress.org/ticket/16972
jQuery(document).ready(function(){
    // listen for drag drop of metaboxes , bind mousedown to .hndle so it only fires when starting to drag
    jQuery('.hndle').mousedown(function(){                                                               
        // set live event listener for mouse up on the content .wrap and wait a tick to give the dragged div time to settle before firing the reclick function
        jQuery('.wrap').mouseup(function(){store_radio(); setTimeout('reclick_radio();',50);});
    })
});
/**
* stores object of all radio buttons that are checked for entire form
*/
if(typeof store_radio != 'function') {
	function store_radio(){
	    var radioshack = {};
	    jQuery('input[type="radio"]').each(function(){
	        if(jQuery(this).is(':checked')){
	            radioshack[jQuery(this).attr('name')] = jQuery(this).val();
	        }
	        jQuery(document).data('radioshack',radioshack);
	    });
	}
}
/**
* detect mouseup and restore all radio buttons that were checked
*/
if(typeof reclick_radio != 'function') {
	function reclick_radio(){
	    // get object of checked radio button names and values
	    var radios = jQuery(document).data('radioshack');
	    //step thru each object element and trigger a click on it's corresponding radio button
	    for(key in radios){
	        jQuery('input[name="'+key+'"]').filter('[value="'+radios[key]+'"]').trigger('click');
	    }            
	    // unbind the event listener on .wrap  (prevents clicks on inputs from triggering function)
	    jQuery('.wrap').unbind('mouseup');
	}
}

jQuery(document).ready(function() {
		if ( typeof sfwd_data.pointers != 'undefined' ) {
			jQuery.each(sfwd_data.pointers, function(index, value) {
				if ( value != 'undefined' && value.pointer_text != '' ) {
					sfwd_show_pointer( index, value );				
				}
			});
		}
	
        jQuery(".sfwd_tab:not(:first)").hide();
        jQuery(".sfwd_tab:first").show();
        jQuery(".sfwd_header_tabs a").click(function(){
                stringref = jQuery(this).attr("href").split('#')[1];
                jQuery('.sfwd_tab:not(#'+stringref+')').hide();
                jQuery('.sfwd_tab#' + stringref).show();
                jQuery('.sfwd_header_tab[href!=#'+stringref+']').removeClass('active');
                jQuery('.sfwd_header_tab#[href=#' + stringref+']').addClass('active');
                return false;
        });
        


	jQuery("body.post-type-sfwd-courses #categorydiv > h3 > span, body.post-type-sfwd-lessons #categorydiv > h3 > span, body.post-type-sfwd-topic #categorydiv > h3 > span, body.post-type-sfwd-courses #categorydiv > h3 > span").html(sfwd_data.learndash_categories_lang);

	if(jQuery(".sfwd-lessons_settings").length)
		learndash_lesson_edit_page_javascript();

	if(jQuery(".sfwd-courses_settings").length)
		learndash_course_edit_page_javascript();

	if(jQuery(".sfwd-topic_settings").length) {
		learndash_topic_edit_page_javascript();
	}
	if(jQuery(".sfwd-quiz_settings").length) {
		learndash_quiz_edit_page_javascript();
	}
});

function learndash_lesson_edit_page_javascript() {
	jQuery("[name='sfwd-lessons_lesson_assignment_upload']").change(function(){
		checked = jQuery("[name=sfwd-lessons_lesson_assignment_upload]:checked").length;
		if(checked) {
			jQuery("#sfwd-lessons_auto_approve_assignment").show();
		}
		else {
			jQuery("#sfwd-lessons_auto_approve_assignment").hide();
		}
	});
	if(jQuery("[name='sfwd-lessons_lesson_assignment_upload']"))
	jQuery("[name='sfwd-lessons_lesson_assignment_upload']").change();
    load_datepicker();	
}
function load_datepicker() {
    jQuery( "input[name='sfwd-lessons_visible_after_specific_date']" ).datepicker({
			changeMonth: true,
			changeYear: true,
            dateFormat : 'MM d, yy',
            onSelect: function(dateText, inst) {
                 jQuery("input[name='sfwd-lessons_visible_after']").val('0');
             jQuery("input[name='sfwd-lessons_visible_after']").prop('disabled', true);
             }
		});       
        
        jQuery("input[name='sfwd-lessons_visible_after_specific_date']").blur(function() {
            var specific_data = jQuery("input[name='sfwd-lessons_visible_after_specific_date']").val();
            if( specific_data != '') {
            jQuery("input[name='sfwd-lessons_visible_after']").val('0');
           jQuery("input[name='sfwd-lessons_visible_after']").attr("disabled", "disabled");
           }else {
             jQuery("input[name='sfwd-lessons_visible_after']").removeAttr("disabled");
           }
        });
        jQuery("input[name='sfwd-lessons_visible_after']").click(function() {
             var specific_data = jQuery("input[name='sfwd-lessons_visible_after_specific_date']").val();
            if( specific_data != '') {
            jQuery("input[name='sfwd-lessons_visible_after']").val('0');
           jQuery("input[name='sfwd-lessons_visible_after']").attr("disabled", "disabled");
           }else {
             jQuery("input[name='sfwd-lessons_visible_after']").removeAttr("disabled");
           }
            });
}
function learndash_course_edit_page_javascript() {
	jQuery("select[name=sfwd-courses_course_price_type]").change(function(){
		var price_type = 	jQuery("select[name=sfwd-courses_course_price_type]").val();
		if(price_type == "open" || price_type == "free") {
			jQuery("input[name=sfwd-courses_course_price]").val('');
			jQuery("#sfwd-courses_course_price").hide();
		}
		else
			jQuery("#sfwd-courses_course_price").show();

		if(price_type == "closed") 
			jQuery("#sfwd-courses_custom_button_url").show();
		else
			jQuery("#sfwd-courses_custom_button_url").hide();
			
		if(price_type == "subscribe") {
			jQuery("#sfwd-courses_course_price_billing_cycle").show();
			/*jQuery("#sfwd-courses_course_no_of_cycles").show();
			jQuery("#sfwd-courses_course_remove_access_on_subscription_end").show();*/
		}
		else {
			jQuery("#sfwd-courses_course_price_billing_cycle").hide();
			/*jQuery("#sfwd-courses_course_no_of_cycles").hide();
			jQuery("#sfwd-courses_course_remove_access_on_subscription_end").hide(); */
		}
	});
	jQuery("select[name=sfwd-courses_course_price_type]").change();
	jQuery("input[name=sfwd-courses_expire_access]").change( function() {
		if(jQuery("input[name=sfwd-courses_expire_access]:checked").val() == undefined) {
			jQuery("#sfwd-courses_expire_access_days").hide();
			jQuery("#sfwd-courses_expire_access_delete_progress").hide();
		}
		else
		{
			jQuery("#sfwd-courses_expire_access_days").show();
			jQuery("#sfwd-courses_expire_access_delete_progress").show();	
		}
	} );
	jQuery("input[name=sfwd-courses_expire_access]").change();


}
function learndash_quiz_edit_page_javascript() {
		jQuery("select[name=sfwd-quiz_quiz_pro]").change(function() {
			var quiz_pro = jQuery("select[name=sfwd-quiz_quiz_pro]").val();
			if(window['sfwd-quiz_quiz_pro'] != quiz_pro)
			{
				var html = jQuery("#sfwd-quiz_quiz_pro_html").html();
				if(html.length > 10)
					window['sfwd-quiz_quiz_pro_html'] = html;
				
				jQuery("#sfwd-quiz_quiz_pro_html").hide();
				jQuery("input[name=disable_advance_quiz_save]").val(1);


			}
			else
			{
				jQuery("#sfwd-quiz_quiz_pro_html").show();		
				jQuery("input[name=disable_advance_quiz_save]").val(0);
								
			}
			if(quiz_pro > 0)
			jQuery("#advanced_quiz_preview").attr("href",sfwd_data.advanced_quiz_preview_link + quiz_pro); 
			else
			jQuery("#advanced_quiz_preview").attr("href","#"); 
			
			jQuery.fn.wpProQuiz_preview();
		});
		var quiz_pro = jQuery("select[name=sfwd-quiz_quiz_pro]").val();
		window['sfwd-quiz_quiz_pro'] = sfwd_data.quiz_pro;
		jQuery("form#post").append("<div id='disable_advance_quiz_save'><input type='hidden' name='disable_advance_quiz_save' value='0'/></div>");
		jQuery("select[name=sfwd-quiz_quiz_pro]").change();

		jQuery("select[name=sfwd-quiz_course]").change(function() {
				if(window['sfwd_quiz_lesson'] == undefined)
				window['sfwd_quiz_lesson'] = jQuery("select[name=sfwd-quiz_lesson]").val();
				
				jQuery("select[name=sfwd-quiz_lesson]").html('<option>' + sfwd_data.loading_lang + '</option>');

				var data = {
					'action': 'select_a_lesson_or_topic',
					'course_id': jQuery(this).val()
				};

				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(json) {
					window['response'] = json;
					html  = '<option value="0">'+ sfwd_data.select_a_lesson_or_topic_lang + '</option>';
					jQuery.each(json.opt, function(i, opt) {
						if(opt.key != '' && opt.key != '0')
						{ 
							selected = (opt.key == window['sfwd_quiz_lesson'])? 'selected=selected': '';
							html += "<option value='" + opt.key + "' "+ selected +">" + opt.value + "</option>";				
						}
					});
					jQuery("select[name=sfwd-quiz_lesson]").html(html);
					//jQuery("select[name=sfwd-topic_lesson]").val(window['sfwd_topic_lesson']);
				}, "json");
		});	
		jQuery("#postimagediv").addClass("hidden_by_sfwd_lms_sfwd_module.js");
		jQuery("#postimagediv").hide(); //Hide the Featured Image Metabox
}
function learndash_topic_edit_page_javascript() {

	jQuery("[name='sfwd-topic_lesson_assignment_upload']").change(function(){
		checked = jQuery("[name=sfwd-topic_lesson_assignment_upload]:checked").length;
		if(checked) {
			jQuery("#sfwd-topic_auto_approve_assignment").show();
		}
		else {
			jQuery("#sfwd-topic_auto_approve_assignment").hide();
		}
	});
	if(jQuery("[name='sfwd-topic_lesson_assignment_upload']"))
	jQuery("[name='sfwd-topic_lesson_assignment_upload']").change();


		jQuery("select[name=sfwd-topic_course]").change(function() {
				if(window['sfwd_topic_lesson'] == undefined)
				window['sfwd_topic_lesson'] = jQuery("select[name=sfwd-topic_lesson]").val();
				
				jQuery("select[name=sfwd-topic_lesson]").html('<option>' + sfwd_data.loading_lang + '</option>');

				var data = {
					'action': 'select_a_lesson',
					'course_id': jQuery(this).val()
				};

				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(json) {
					window['response'] = json;
					html  = '<option value="0">'+ sfwd_data.select_a_lesson_lang + '</option>';
					jQuery.each(json, function(key, value) {
						if(key != '' && key != '0')
						{
							selected = (key == window['sfwd_topic_lesson'])? 'selected=selected': '';
							html += "<option value='" + key + "' "+ selected +">" + value + "</option>";				
						}
					});
					jQuery("select[name=sfwd-topic_lesson]").html(html);
					//jQuery("select[name=sfwd-topic_lesson]").val(window['sfwd_topic_lesson']);
				}, "json");
		});	
}

// The following functions are also found in /templates/learndash_template_script.js but as that is a template and the admin 
// can choose to remove them I copied them into this JS file loaded for admin. I couldn't take the chance the admin 
// would create a version in the theme and remove the functions. 
if (typeof flip_expand_collapse == 'undefined') {
	function flip_expand_collapse(what, id) {
	    //console.log(id + ':' + document.getElementById( 'list_arrow.flippable-'+id).className);
	    if (jQuery( what + '-' + id + ' .list_arrow.flippable' ).hasClass( 'expand' ) ) {
	        jQuery( what + '-' + id + ' .list_arrow.flippable' ).removeClass( 'expand' );
	        jQuery( what + '-' + id + ' .list_arrow.flippable' ).addClass( 'collapse' );
	        jQuery( what + '-' + id + ' .flip' ).slideUp();
	    } else {
	        jQuery( what + '-' + id + ' .list_arrow.flippable' ).removeClass( 'collapse' );
	        jQuery( what + '-' + id + ' .list_arrow.flippable' ).addClass( 'expand' );
	        jQuery( what + '-' + id + ' .flip' ).slideDown();
	    }
	    return false;
	}
}

if (typeof flip_expand_all == 'undefined') {
	function flip_expand_all(what) {
	    jQuery( what + ' .list_arrow.flippable' ).removeClass( 'collapse' );
	    jQuery( what + ' .list_arrow.flippable' ).addClass( 'expand' );
	    jQuery( what + ' .flip' ).slideDown();
	    return false;
	}
}

if (typeof flip_collapse_all == 'undefined') {
	function flip_collapse_all(what) {
	    jQuery( what + ' .list_arrow.flippable' ).removeClass( 'expand' );
	    jQuery( what + ' .list_arrow.flippable' ).addClass( 'collapse' );
	    jQuery( what + ' .flip' ).slideUp();
	    return false;
	}
}