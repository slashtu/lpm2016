//jQuery(document).ready(function() {
//	jQuery('.learndash-binary-selector').each(function() {
//		learndash_binary_selector.init();
//	});
//});


jQuery(document).ready(function() {
	var selectors_array = [];
	jQuery('.learndash-binary-selector').each(function() {
		var selector_id = jQuery(this).prop('id');
		selectors_array[selector_id] = new learndash_binary_selector(this);
		selectors_array[selector_id].init();
	});
});


function learndash_binary_selector(selector_div) {
	var self = this;
	this.selector_div = selector_div;
	this.selector_id = jQuery(selector_div).prop('id');
	
	this.show_id = function() {
		console.log('id[%o]', self.selector_id);
	}
	
	this.init = function() {
		self.init_vars();
		self.init_actions();
	}
	
	this.init_vars = function() {
	}
	
	this.init_actions = function() {
		if (jQuery('.learndash-binary-selector-button-add', self.selector_div).length) {
			jQuery('.learndash-binary-selector-button-add', self.selector_div).click(function(e){
				e.preventDefault();
				self.move_items_left2right();
			});
		}

		if (jQuery('.learndash-binary-selector-button-remove', self.selector_div).length) {
			jQuery('.learndash-binary-selector-button-remove', self.selector_div).click(function(e){
				e.preventDefault();
				self.move_items_right2left();
			});
		}
		
		
		if (jQuery('.learndash-binary-selector-left ul.learndash-binary-selector-pager a', self.selector_div).length) {
			jQuery('.learndash-binary-selector-left ul.learndash-binary-selector-pager a', self.selector_div).click(function(e){
				e.preventDefault();
				self.handle_pager(this);
			});
		}
	}

	this.move_items_left2right = function() {
		var added2right = false;
		if ((jQuery('.learndash-binary-selector-left select', self.selector_div).length) && (jQuery('.learndash-binary-selector-right select', self.selector_div).length)) {
			jQuery('.learndash-binary-selector-left select', self.selector_div).find('option:selected').each(function(){
				
				var option_left_el = jQuery(this);
				if (!option_left_el.hasClass('selector-option-disabled')) {
					self.prepare_option_for_move('right', option_left_el);
					jQuery('.learndash-binary-selector-right select', self.selector_div).append(jQuery("<option></option>").attr("value", option_left_el.val()).text(option_left_el.text())); 
					added2right = true;
				}
			});
		}	
		
		if (added2right == true) {
			self.sort_right_options();
		}
	}

	this.move_items_right2left = function() {

		if ((jQuery('.learndash-binary-selector-left select', self.selector_div).length) && (jQuery('.learndash-binary-selector-right select', self.selector_div).length)) {
			jQuery('.learndash-binary-selector-right select', self.selector_div).find('option:selected').each(function() {
				
				var option_right_el = jQuery(this);
				
				var option_left_el = jQuery('.learndash-binary-selector-left select option[value="'+option_right_el.val()+'"]', self.selector_div);
				console.log('option_left_el[%o]', option_left_el);
				if ((option_left_el != undefined) && (option_right_el.val() == option_left_el.val())) {
					self.prepare_option_for_move('left', option_left_el);
				}
				option_right_el.remove();
			});
		}	
	}
	
	this.handle_pager = function(clicked_el) {
		var section_el = jQuery(clicked_el).parents('div.learndash-binary-selector-section');
		if (section_el == undefined) {
			return;
		}
		//console.log('section_el[%o]', section_el);

		var query_data = jQuery(section_el).attr('data');
		if (query_data == undefined) {
			return;
		}
		console.log('query_data[%o]', query_data);
			
		var query_data_parsed = JSON.parse(query_data);
		console.log('query_data_parsed[%o]', query_data_parsed);

		var parent_li_el = jQuery(clicked_el).parent('li');
		if (parent_li_el == undefined) {
			return;
		}
		//console.log('parent_li_el[%o]', parent_li_el);
				
		if (parent_li_el.hasClass('learndash-binary-selector-pager-prev')) {
			//console.log('prev');
			if (query_data_parsed['pager']['current_page'] == 1) {
				return;
			} 
			query_data_parsed['data_query']['paged'] = parseInt(query_data_parsed['data_query']['paged']) - 1;
			
		} else if (parent_li_el.hasClass('learndash-binary-selector-pager-next')) {
			//console.log('next');
			if (query_data_parsed['pager']['current_page'] == query_data_parsed['pager']['total_pages']) {
				return;
			}
			query_data_parsed['data_query']['paged'] = parseInt(query_data_parsed['data_query']['paged']) + 1;
		}

		var post_data = {
			'action': 'learndash_binary_selector_pager',
			'query-data': query_data_parsed['data_query']
		};
		jQuery.ajax({
			type: "POST",
			url: ajaxurl,
			dataType: "json",
			cache: false,
			data: post_data,
			error: function(jqXHR, textStatus, errorThrown ) {
				//console.log('init: error HTTP Status['+jqXHR.status+'] '+errorThrown);
				console.log('error [%o]', textStatus);
			},
			success: function(reply_data) {
				console.log('reply_data[%o]', reply_data);
				if (reply_data['options'] != undefined) {
					jQuery('select', section_el).empty().append(reply_data['options']);
				}
				
				if (reply_data['data_query'] != undefined) {
					
					query_data_parsed['data_query'] = reply_data['data_query'];
					console.log('query_data_parsed[%o]', query_data_parsed);
					jQuery(section_el).attr('data', JSON.stringify(query_data_parsed));
					
					var query_data_after = jQuery(section_el).attr('data');
					console.log('query_data_after[%o]', query_data_after);
				}
			}
		});
	}
	
	
	// Called after adding new items to the right side select
	this.sort_right_options = function() {
		var options = jQuery('.learndash-binary-selector-right select option', self.selector_div);
		var arr = options.map(function(_, o) { return { t: jQuery(o).text(), v: o.value }; }).get();
		arr.sort(function(o1, o2) { return o1.t > o2.t ? 1 : o1.t < o2.t ? -1 : 0; });
		options.each(function(i, o) {
		  o.value = arr[i].v;
		  jQuery(o).text(arr[i].t);
		});
	}
	
	this.prepare_option_for_move = function(destination, option_el) {
		if (option_el != undefined) {
			if (destination == 'right') {
				option_el.addClass('selector-option-disabled');
				option_el.prop('selected', false);
				
				option_el.prop('disabled', 'disabled');
			} else if (destination == 'left') {
				option_left_el.removeClass('selector-option-disabled');
				option_left_el.prop('disabled', false);				
			}
		}
	}
}
