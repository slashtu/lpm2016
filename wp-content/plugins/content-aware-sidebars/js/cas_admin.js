/*!
 * @package Content Aware Sidebars
 * @author Joachim Jensen <jv@intox.dk>
 * @license GPLv3
 * @copyright 2016 by Joachim Jensen
 */

(function($) {

	var cas_options = {

		sidebarID: $('#current_sidebar').val(),

		init: function() {

			this.addHandleListener();
			this.reviewNoticeHandler();
			this.suggestVisibility();

		},

		/**
		 * The value of Handle selection will control the
		 * accessibility of the host sidebar selection
		 * If Handling is manual, selection of host sidebar will be disabled
		 * 
		 * @since  2.1
		 */
		addHandleListener: function() {
			var host = $("select[name='host']");
			var code = $('<p>Shortcode:</p><code>[ca-sidebar id='+this.sidebarID+']</code>'+
				'<p>Template Tag:</p><code>ca_display_sidebar();</code>');
			var merge_pos = $('span.merge-pos');
			host.parent().append(code);
			$("select[name='handle']").change(function(){
				var handle = $(this);
				host.attr("disabled", handle.val() == 2);
				if(handle.val() == 2) {
					host.hide();
					code.show();
				} else {
					host.show();
					code.hide();
				}
				if(handle.val() == 3) {
					merge_pos.hide();
				} else {
					merge_pos.show();
				}
			}).change(); //fire change event on page load
		},

		suggestVisibility: function() {
			var $elem = $('.js-cas-visibility');
			$elem.select2({
				placeholder: CASAdmin.allVisibility,
				minimumInputLength: 0,
				closeOnSelect: true,//does not work properly on false
				allowClear:true,
				multiple: true,
				width:"resolve",
				nextSearchTerm: function(selectedObject, currentSearchTerm) {
					return currentSearchTerm;
				},
				data: CASAdmin.visibility
			})
			.on("select2-selecting",function(e) {
				$elem.data("forceOpen",true);
			})
			.on("select2-close",function(e) {
				if($elem.data("forceOpen")) {
					e.preventDefault();
					$elem.select2("open");
					$elem.data("forceOpen",false);
				}
			});
		},

		/**
		 * Handle clicks on review notice
		 * Sends dismiss event to backend
		 *
		 * @since  3.1
		 * @return {void}
		 */
		reviewNoticeHandler: function() {
			$notice = $(".js-cas-notice-review");
			$("#wpbody-content").on("click","a, button", function(e) {
				$this = $(this);
				$.ajax({
					url: ajaxurl,
					data:{
						'action': 'cas_dismiss_review_notice',
						'dismiss': $this.attr("href") ? 1 : 0
					},
					dataType: 'JSON',
					type: 'POST',
					success:function(data){
						$notice.fadeOut(400,function() {
							$notice.remove();
						});
					},
					error: function(xhr, desc, e) {
						console.log(xhr.responseText);
					}
				});
			});
		}
	};

	$(document).ready(function(){ cas_options.init(); });

})(jQuery);
