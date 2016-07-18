/**
 * Go Pricing - WordPress Responsive Pricing Tables
 * 
 * Description: The New Generation Pricing Tables. If you like traditional Pricing Tables, but you would like get much more out of it, then this rodded product is a useful tool for you.
 * Version:     3.2.1
 * Author:      Granth
 * License:		http://codecanyon.net/licenses/
 * 
 * (C) 2015 Granth (http://granthweb.com)
 */


(function ($, undefined) {
	"use strict";
		
	$(function () {	
	
		/**
		 * Initialize
		 */
		
		$.GoPricing = {

			/* Init function */
			Init : function () {
				this.$wrap = $('.gw-go');
				this.equalize = this.$wrap.data('equalize');
				this.InitMediaElementPlayer();
				this.InitGoogleMap();
				this.isIE = document.documentMode != undefined && document.documentMode >5 ? document.documentMode : false;
				if (this.isIE) {
					this.$wrap.addClass('gw-go-ie');
					if (this.isIE < 9) this.$wrap.addClass('gw-go-oldie');
				};
				if ($.GoPricing!=undefined && $.GoPricing.equalize==true) {
					this.EqualizeRows();
				};
				this.eventType = this.detectEvent();
				this.timeout = [];
			},

			/* Show Tooltip */
			showTooltip : function ($elem, content, top) {
				
				if ($elem === undefined) return;
				
				var $rowTooltip = $elem.find('.gw-go-tooltip-content'),
					rowTooltipContent = $rowTooltip.length ? $rowTooltip.prop('outerHTML') : '',
					$colWrap = $elem.closest('.gw-go-col-wrap'),
					$col = $colWrap.find('.gw-go-col'),
					$tooltip = $col.find('.gw-go-tooltip'),
					colIndex = $colWrap.data('col-index'),
					rIndex = $elem.data('row-index');	
				
				if (!$tooltip.length ) $tooltip = $('<div class="gw-go-tooltip"></div>').appendTo($col);
								
				if ($tooltip.data('index') != rIndex) {
					$tooltip.removeClass('gw-go-tooltip-visible');
				} else {
					clearTimeout($.GoPricing.timeout[colIndex]);
				}

				if (rowTooltipContent != '') {
					$tooltip.html(rowTooltipContent).data('index', rIndex)
					setTimeout(function() { $tooltip.addClass('gw-go-tooltip-visible').css('top', $col.find('.gw-go-body').position().top + $elem.position().top - $tooltip.outerHeight()); }, 10);
				}

			},

			/* Hide Tooltip */
			hideTooltip : function ($elem) {

				if ($elem === undefined) return;
				
				if ($elem.hasClass('gw-go-tooltip')) {
					$elem.removeClass('gw-go-tooltip-visible');
				} else {
					
					var $colWrap = $elem.closest('.gw-go-col-wrap'),
						$col = $colWrap.find('.gw-go-col'),
						$tooltip = $col.find('.gw-go-tooltip'),
						colIndex = $colWrap.data('col-index');
				
					$.GoPricing.timeout[colIndex] = setTimeout(function() { $tooltip.removeClass('gw-go-tooltip-visible'); }, 10);
				
				}
				
			},

			/* Mediaelement Player init */
			InitMediaElementPlayer : function () {
				
				if (jQuery().mediaelementplayer && $.GoPricing.$wrap.find('audio, video').length) {	
					$.GoPricing.$wrap.find('audio, video').mediaelementplayer({
						audioWidth: '100%',
						videoWidth: '100%'
					});			
				};	

			},
			
			/* Google map init */
			InitGoogleMap : function () {
				
				if (jQuery().goMap && $.GoPricing.$wrap.find('.gw-go-gmap').length) {
					$.GoPricing.$wrap.find('.gw-go-gmap').each(function(index) {
						var $this=$(this);
						$this.goMap($this.data('map'));
					});
				};
				
			},
			
			/* Equalize rows */
			EqualizeRows : function () {
				
				for (var x = 0; x < $.GoPricing.$wrap.length; x++) {
										
					if ($.GoPricing.$wrap.eq(x).is(':hidden') || $.GoPricing.$wrap.eq(x).offset().top>parseInt($(document).scrollTop()+window.innerHeight+500) || $.GoPricing.$wrap.eq(x).data('eq-ready') === true )  continue; 

					var $pricingTable = $.GoPricing.$wrap.eq(x),
						$colWrap = $pricingTable.find('.gw-go-col-wrap'),
						colCnt = $colWrap.length,
						equalizeCnt = colCnt,
						views = $pricingTable.data('views') !== undefined ? $pricingTable.data('views') : {};
					
					for (var key in views) {						
						
						var mqSizes = [], mq = '';
						if (views[key].min !== undefined && views[key].min !== '') mqSizes.push('(min-width:'+views[key].min+'px)');
						if (views[key].max !== undefined && views[key].max !== '') mqSizes.push('(max-width:'+views[key].max+'px)');
						mq = mqSizes.join(' and ');						
						
						if (mq != '') if (window.matchMedia && window.matchMedia(mq).matches) { 
							
							equalizeCnt = views[key].cols !== undefined && views[key].cols !== '' && views[key].cols <= colCnt ? views[key].cols : colCnt;
						}					
						
					}
								
					if (equalizeCnt == 1) {
						$pricingTable.find('.gw-go-body li .gw-go-body-cell').css('height', 'auto');
						$pricingTable.find('.gw-go-col-wrap').css('height', 'auto');
						$pricingTable.find('.gw-go-footer').css('height', 'auto');
						continue;
						
					}
								
					for (var z = 0; z<colCnt/equalizeCnt; z++) {
						
						if (!$pricingTable.is(':hidden')) $pricingTable.data('eq-ready', true);
	
						var rowHeights = [], footerHeights = [], colHeights = [];
							
						/* Body */
						if ($pricingTable.data('equalize').body != undefined) {
						
							for (var i = 0; i < colCnt; i++) {
								if (i >= (z*equalizeCnt) && i <= (z*equalizeCnt)+equalizeCnt-1) {
									var $currentCol = $colWrap.eq(i),
										$row = $currentCol.find('.gw-go-body li .gw-go-body-cell');
										
									for (var rIndex = 0; rIndex < $row.length; rIndex++) {
										
										var $currentRow = $row.eq(rIndex);
										$currentRow.css('height', 'auto');
										
										if (typeof rowHeights[rIndex] !== 'undefined' ) {
											if ($currentRow.height() > rowHeights[rIndex] ) {
												rowHeights[rIndex] = $currentRow.height();
											}
										} else {
											rowHeights[rIndex] = $currentRow.height();
										}
										
									}
									
								}
							
							}
							
							for (var i = 0; i < colCnt; i++) {
								var $currentCol = $colWrap.eq(i),
									$row = $currentCol.find('.gw-go-body li .gw-go-body-cell');
		
								if (i >= (z*equalizeCnt) && i <= (z*equalizeCnt)+equalizeCnt-1) {
									for (var rIndex = 0; rIndex < $row.length; rIndex++) {								
										var $currentRow = $row.eq(rIndex);
										$currentRow.css('height', rowHeights[rIndex]);
									}							
									
								}
								
							
								
							}
						
						}
						
						/* Footer */
						if ($pricingTable.data('equalize').footer != undefined) {
													
							for (var i = 0; i < colCnt; i++) {
								var $footer = $colWrap.eq(i).find('.gw-go-footer');
								
								if (i >= (z*equalizeCnt) && i <= (z*equalizeCnt)+equalizeCnt-1) {
									$footer.css('height', 'auto');
									if (typeof footerHeights[z] !== 'undefined' ) {
										if ($footer.height() > footerHeights[z] ) {
											footerHeights[z] = $footer.height();
										}
									} else {
										footerHeights[z] = $footer.height();
									}
								}
								
							}
							
							for (var i = 0; i < colCnt; i++) {
								var $footer = $colWrap.eq(i).find('.gw-go-footer');
								
								if (i >= (z*equalizeCnt) && i <= (z*equalizeCnt)+equalizeCnt-1) {
									$footer.css('height', footerHeights[z]);
	
								}
								
							}
						
						}
						
						/* Column */
						if ($pricingTable.data('equalize').column != undefined) {						
						
							for (var i = 0; i < colCnt; i++) {
								var $currentCol = $colWrap.eq(i);
								
								if (i >= (z*equalizeCnt) && i <= (z*equalizeCnt)+equalizeCnt-1) {
									$currentCol.css('height', 'auto');
									if (typeof colHeights[z] !== 'undefined' ) {
										if ($currentCol.outerHeight(true) > colHeights[z] ) {
											colHeights[z] = $currentCol.outerHeight(false);
										}
									} else {
										colHeights[z] = $currentCol.outerHeight(false);
									}
								}
								
							}
							
							for (var i = 0; i < colCnt; i++) {
								var $currentCol = $colWrap.eq(i);
								
								if (i >= (z*equalizeCnt) && i <= (z*equalizeCnt)+equalizeCnt-1) {
									$currentCol.css('height', colHeights[z]);
								}
								
							}
						
						}
						
						
					}
					
				}

			},

			/* Detect event type */
			detectEvent : function() {
				var eventType = 'mouseenter mouseleave';
				if ('ontouchstart' in window) {
					eventType = 'touchstart';
				} else if  (window.navigator.pointerEnabled && navigator.msMaxTouchPoints) {
					eventType = "pointerdown";
				} else if (window.navigator.msPointerEnabled && navigator.msMaxTouchPoints) {
					eventType = "MSPointerDown";
				} 
				return eventType;
			}
		
		};

		/*setTimeout(function() {
			$.GoPricing.EqualizeRows();
		}, 10);	*/

		
		/* Init */
		$.GoPricing.Init();	
		
		$(window).on('scroll', function() { 
		
			$.GoPricing.EqualizeRows();
		
		});
		
		setTimeout(function() {
			$.GoPricing.EqualizeRows();
		}, 10);				
		
		/* Submit button event if form found */
		$.GoPricing.$wrap.delegate('span.gw-go-btn', 'click', function(){	
			var $this=$(this);
			if ($this.find('form').length) { $this.find('form').submit(); };
		});	
			

		/* Show & hide tooltip - Event on tooltip */
		$.GoPricing.$wrap.on( 'mouseenter mouseleave', '.gw-go-tooltip', function(e) {	

			var $this=$(this),
				$colWrap = $this.closest('.gw-go-col-wrap'),
				colIndex = $colWrap.data('col-index');
			
			if (e.type == 'mouseenter') {
				clearTimeout($.GoPricing.timeout[colIndex]);
			} else {
				$.GoPricing.timeout[colIndex] = setTimeout(function() { $.GoPricing.hideTooltip($this); }, 10);
			}
			
		});
		

		/* Show & hide tooltip - Event on row */
		$.GoPricing.$wrap.on( 'mouseenter mouseleave', 'ul.gw-go-body li', function(e) {	
			
			var $this=$(this);
			
			if (e.type == 'mouseenter') {		
				$.GoPricing.showTooltip($this);
			} else {
				$.GoPricing.hideTooltip($this);
			}
			
		});			


		/* Event handling */
		$('body').on($.GoPricing.eventType, '.gw-go-col-wrap', function(e) {
			var $this = $(this);
			
			if (e.type == 'mouseenter' && !$this.hasClass('gw-go-disable-hover')) {
				$this.addClass('gw-go-hover').siblings(':not(.gw-go-disable-hover)').removeClass('gw-go-hover');
				$this.closest('.gw-go').addClass('gw-go-hover');
			} else if (e.type == 'mouseleave' && !$this.hasClass('gw-go-disable-hover')) {
				$this.removeClass('gw-go-hover');
				$this.closest('.gw-go').find('[data-current="1"]:not(.gw-go-disable-hover)').addClass('gw-go-hover');
				$this.closest('.gw-go').removeClass('gw-go-hover')
			} else if (!$this.hasClass('gw-go-disable-hover')) {
				$this.closest('.gw-go').addClass('gw-go-hover')
				$this.addClass('gw-go-hover').siblings(':not(.gw-go-disable-hover)').removeClass('gw-go-hover');
			};
			
		});
			

		/**
	 	 * Google map
		 */
			
		if (typeof jQuery.goMap !== 'undefined' && $.GoPricing.$wrap.find('.gw-go-gmap').length) {
			var GoPricing_MapResize=false;
			$(window).on('resize', function(e) {
				if (GoPricing_MapResize) { clearTimeout(GoPricing_MapResize); }
				GoPricing_MapResize = setTimeout(function() {
					$.GoPricing.$wrap.find('.gw-go-gmap').each(function(index, element) {
					  $(this).goMap();
					  //console.log($.goMap.getMarkers('markers')[0].position);
					});
				}, 400);
			});			
		};
		
	
		/* Equalize heights on resize */
		$(window).on('resize', function(e) { 
			
			for (var x = 0; x < $.GoPricing.$wrap.length; x++) {
				$.GoPricing.$wrap.eq(x).data('eq-ready', false);			
			}
			$.GoPricing.EqualizeRows();
			
		});		

	});
}(jQuery));	