/*!
 * @package WP Content Aware Engine
 * @version 2.0
 * @copyright Joachim Jensen <jv@intox.dk>
 * @license GPLv3
 */

/**
 * Namespace
 * @type {Object}
 */
var CAE = CAE || {};

(function($, CAE) {
	"use strict";

	CAE.settings = {
		views: {}
	};

	/**
	 * Backbone Models
	 * 
	 * @type {Object}
	 */
	CAE.Models = {

		Alert: Backbone.Model.extend({
			defaults: {
				text    : "",
				cssClass: "updated"
			},
			sync: function () { return false; },
			url: "",
			reset: function() {
				this.set(this.defaults);
			}
		}),

		Condition: Backbone.Model.extend({
			defaults : {
				'module' : null, 
				'label'  : null,
				'values' : null,
				'options': {}
			},
			sync: function () { return false; },
			url: ""
		}),

		Group: Backbone.Model.extend({
			defaults : {
				'id'        : null, 
				'status'    : null,
				'options'   : {},
				'conditions': null
			},
			initialize: function() {
				if(!this.conditions) {
					this.conditions = new CAE.Models.ConditionCollection();
				}
			},
			parse: function(response) {
				if (_.has(response, "conditions")) {
					var list = [];

					for(var key in response.conditions) {
						if(response.conditions.hasOwnProperty(key)) {
							var values = [];
							for(var key2 in response.conditions[key].data) {
								if(response.conditions[key].data.hasOwnProperty(key2)) {
									values.push({
										text: response.conditions[key].data[key2],
										id: key2
									});
								}
							}
							list.push({
								label  : response.conditions[key].label,
								module : key,
								values : values,
								options: response.conditions[key].options || {}
							});
						}
					}
					this.conditions = new CAE.Models.ConditionCollection(
						list
					);
					delete response.conditions;
				}
				return response;
			},
			sync: function () { return false; },
			url : ""
		}),

		GroupCollection: Backbone.Collection.extend({
			model: function(attrs,options){
				return new CAE.Models.Group(attrs,options);
			},
			parse: function(response) {
				return response;
			}
		}),

		ConditionCollection: Backbone.Collection.extend({
			model: function(attrs,options){
				return new CAE.Models.Condition(attrs,options);
			}
		}) 
	};

	/**
	 * Backbone Views
	 * 
	 * @type {Object}
	 */
	CAE.Views = {

		/**
		 * Alert handler
		 * @author  Joachim Jensen <jv@intox.dk>
		 * @version 1.0
		 */
		Alert: Backbone.View.extend({
			tagName: 'div',
			className: 'wpca-alert',
			template: _.template('<div class="<%= cssClass %>"><%= text %></div>'),
			timer: 4000,
			success: function(text) {
				this.model.set({
					text: text,
					cssClass: "wpca-success"
				});
			},
			failure: function(text) {
				this.model.set({
					text: text,
					cssClass: "wpca-error"
				});
			},
			dismiss: function() {
				this.model.reset();
			},
			initialize: function() {
				this.listenTo(this.model, "change", this.render);
				this.$el.appendTo('body');
			},
			render: function() {
				if(this.model.get('text') !== "") {
					var self = this;
					this.$el
					.hide()
					.html(this.template(this.model.attributes))
					.fadeIn('slow');
					setTimeout(function() {
						self.$el.fadeOut('slow');
						self.dismiss();
					},this.timer);
				} else {
					this.$el.fadeOut('slow');
				}
			}
		}),

		Condition: Backbone.View.extend({
			tagName: "div",
			className: "cas-condition",
			events: {
				"click .js-wpca-condition-remove": "removeModel"
			},
			initialize: function() {
				this.listenTo( this.model, 'destroy', this.remove );
				this.template = _.template($('#wpca-template-'+this.model.get("module")).html());
				this.render();
			},
			render: function() {
				this.$el.append(this.template(this.model.attributes));
				var $suggest = this.$el.find(".js-wpca-suggest");
				if($suggest.length) {
					wpca_admin.createSuggestInput(
						$suggest,
						this.model.get("module"),
						this.model.get("values")
					);
				}
			},
			removeModel: function(e) {
				console.log("cond view: removes condition model");
				var that = this;
				this.$el.slideUp(300,function() {
					that.model.destroy();
					console.log("cond view: condition model removed");
				});
			}
		}),

		Group: Backbone.View.extend({
			tagName: "li",
			className: "cas-group-single",
			template: _.template($('#wpca-template-group').html()),
			events: {
				"change .js-wpca-add-and":      "addConditionModel",
				"click .js-wpca-save-group":    "saveGroup",
				"change .js-wpca-group-status": "statusChanged"
			},
			initialize: function() {
				this.render();
				this.listenTo( this.model, 'destroy', this.remove );
				this.listenTo( this.model.conditions, 'remove', this.conditionRemoved );
				this.listenTo( this.model.conditions, 'add', this.addConditionViewSlide );
			},
			render: function() {
				this.$el.append(this.template(this.model.attributes));
				this.model.conditions.each(this.addConditionViewFade,this);
			},
			addConditionModel: function(e) {
				var $select = $(e.currentTarget);
				if(!this.model.conditions.findWhere({module:$select.val()})) {
					var condition = new CAE.Models.Condition({
						module: $select.val(),
						label: $select.children(":selected").text()
					});
					this.model.conditions.add(condition);
				}
				$select.val(0).blur();
			},
			addConditionView: function(model) {
				if(CAE.Views[model.get("module")]) {
					var condition = new CAE.Views[model.get("module")]({model:model});
				} else {
					var condition = new CAE.Views.Condition({model:model});
				}
				return condition.$el
				.hide().appendTo(this.$el.find(".cas-content"));
			},
			addConditionViewSlide: function(model) {
				this.addConditionView(model).slideDown(300);

			},
			addConditionViewFade: function(model) {
				this.addConditionView(model).fadeIn(300);
			},
			conditionRemoved: function(model) {
				console.log("group view: a condition was removed");
				if(!this.model.conditions.length) {
					if(this.model.get("id")) {
						console.log("group view: save");
						//at this point, we could skip save request
						//and add a faster delete request
						this.saveGroup();
					} else {
						console.log("group view: destroy model");
						this.removeModel();
					}
				}
			},
			removeModel: function() {
				var that = this;
				console.log("group view: group model removing");
				this.$el.slideUp(400,function() {
					that.model.destroy();
					console.log("group view: group model removed");
				});
			},
			saveGroup: function(e) {
				var data = {
					action    : "wpca/add-rule",
					token     : wpca_admin.nonce,
					current_id: wpca_admin.sidebarID
				};
				var self = this;
				if(this.model.get("id")) {
					data.cas_group_id = this.model.get("id");
				}
				this.$el.find("input").each(function(i,obj) {
					var $obj = $(obj);
					var key = $obj.attr("name");
					if(key && ($obj.attr("type") != "checkbox" || $obj.is(":checked"))) {
						var value = $obj.val();
						if(~key.indexOf('cas_condition')) {
							if(!value && $obj.data("wpca-default")) {
								value = $obj.data("wpca-default");
							}
							value = value ? value.split(",") : [];
							if(data[key]) {
								value = value.concat(data[key]);
							}
						}
						if(value) {
							data[key] = value;
						}
					}
					
				});
				$.ajax({
					url: ajaxurl,
					data:data,
					dataType: 'JSON',
					type: 'POST',
					success:function(response){

						wpca_admin.alert.success(response.message);

						if(response.removed) {
							self.removeModel();
						}
						else if(response.new_post_id) {
							self.model.set("id",response.new_post_id);
						}
					},
					error: function(xhr, desc, e) {
						wpca_admin.alert.failure(xhr.responseText);
					}
				});
			},
			statusChanged: function(e) {
				var negated = $(e.currentTarget).is(":checked");
				this.$el.find(".cas-group-sep:first-child").toggleClass("wpca-group-negate",negated);
			},
			slideRemove: function() {
				console.log("group view: group model was destroyed");
				this.$el.slideUp(400,function() {
					this.remove();
				});
			}
		}),

		GroupCollection: Backbone.View.extend({
			el: "#cas-groups",
			events: {
				"change .js-wpca-add-or": "addGroupModel"
			},
			initialize: function() {
				this.render();
				this.listenTo( this.collection, 'add', this.addGroupViewNew );
				this.listenTo( this.collection, 'add remove', this.changeLogicText );
			},
			render: function() {
				this.collection.each(this.addGroupView,this);
				this.changeLogicText();
				$(".js-wpca-add-or").focus();
			},
			addGroupModel: function(e) {
				var $select = $(e.currentTarget);
				var group = new CAE.Models.Group();
				var condition = new CAE.Models.Condition({
					module: $select.val(),
					label: $select.children(":selected").text()
				});
				group.conditions.add(condition);
				this.collection.add(group);

				$select.val(0).blur();
			},
			addGroupView: function(model) {
				var group = new CAE.Views.Group({model:model});
				group.$el.hide().appendTo(this.$el.children("ul").first()).fadeIn(300);
			},
			addGroupViewNew: function(model) {
				var group = new CAE.Views.Group({model:model});
				group.$el.hide().appendTo(this.$el.children("ul").first()).slideDown(300);
			},
			changeLogicText: function() {
				this.$el.find("> .cas-group-sep").toggle(this.collection.length != 0);
			}
		})
	};

	var wpca_admin = {

		nonce: $('#_ca_nonce').val(),
		sidebarID: $('#current_sidebar').val(),
		alert: null,

		init: function() {

			this.alert = new CAE.Views.Alert({model:new CAE.Models.Alert()});
			
			CAE.conditionGroups = new CAE.Views.GroupCollection({
				collection:new CAE.Models.GroupCollection(WPCA.groups,{parse:true})
			});
		},

		createSuggestInput: function($elem,type,data) {
			$elem.select2({
				more: true,
				cacheDataSource: [],
				quietMillis: 400,
				searchTimer: null,
				placeholder:$elem.data("wpca-placeholder"),
				minimumInputLength: 0,
				closeOnSelect: true,//does not work properly on false
				allowClear:true,
				multiple: true,
				width:"100%",
				formatNoMatches: WPCA.noResults,
				formatSearching: WPCA.searching+"...",
				nextSearchTerm: function(selectedObject, currentSearchTerm) {
					return currentSearchTerm;
				},
				query: function(query) {
					var self = this,
						cachedData = self.cacheDataSource[query.term],
						page = query.page;

					if(cachedData && cachedData.page >= page) {
						if(page > 1) {
							page = cachedData.page;
						} else {
							query.callback({results: cachedData.items, more:self.more});
							return;
						}
					}

					clearTimeout(self.searchTimer);
					self.searchTimer = setTimeout(function(){
						$.ajax({
								url: ajaxurl,
								data: {
									search: query.term,
									paged: page,
									action: "wpca/module/"+type,
									sidebar_id: wpca_admin.sidebarID,
									nonce: wpca_admin.nonce
								},
								dataType: 'JSON',
								type: 'POST',
								success: function(data) {
									var results = [];
									for (var key in data) {
										if (data.hasOwnProperty(key)) {
											results.push({
												id:key,
												text:data[key]
											});
										}
									}
									if(results.length < 20) {
										self.more = false;
									}
									if(cachedData) {
										self.cacheDataSource[query.term].page = page;
										self.cacheDataSource[query.term].items = self.cacheDataSource[query.term].items.concat(results);
									} else {
										self.cacheDataSource[query.term] = {
											page: page,
											items: results
										};
									}
									//self.cacheDataSource[query.term] = results;
									query.callback({results: results, more: self.more});
								}
							});
					}, self.quietMillis);
				}
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
			// .on("select2-blur",function(e) {
			// 	var select2 = $(this).data("select2");
			// 	if(!select2.opened()) {
			// 		console.log("can save now");
			// 		wpca_admin.alert.success("Conditions saved automatically");
			// 	}
			// });
			if(data) {
				$elem.select2("data",data);
			}
		}

	};

	$(document).ready(function(){ wpca_admin.init(); });

})(jQuery, CAE);
