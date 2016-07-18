(function($) {
	/**
	 * @memberOf $
	 */
	$.wpProQuizFront = function(element, options) {
		
		var $e = $(element);
		var config = options;
		var plugin = this;
		var results = new Object();
		var catResults = new Object();
		var startTime = 0;
		var currentQuestion = null;
		var quizSolved = [];
		var lastButtonValue = "";
		var inViewQuestions = false;
		var currentPage = 1;
		var timespent = 0;
		var sending_timer = null;

		var bitOptions = {
			randomAnswer: 0, 
			randomQuestion: 0,
			disabledAnswerMark: 0,
			checkBeforeStart: 0,
			preview: 0,
			cors: 0,
			isAddAutomatic: 0,
			quizSummeryHide: 0,
			skipButton: 0,
			reviewQustion: 0,
			autoStart: 0,
			forcingQuestionSolve: 0,
			hideQuestionPositionOverview: 0,
			formActivated: 0,
			maxShowQuestion: 0,
			sortCategories: 0
		};
		
		var quizStatus = {
			isQuizStart: 0,
			isLocked: 0,
			loadLock: 0,
			isPrerequisite: 0,
			isUserStartLocked: 0
		};
		
		/*
		var QuizRepeats = {
			quizRepeats: 0,
			userAttemptsTaken: 0,
			userAttemptsLeft: 0,
		};
		*/
		var globalNames = {
			check: 'input[name="check"]',
			next: 'input[name="next"]',
			questionList: '.wpProQuiz_questionList',
			skip: 'input[name="skip"]',
			singlePageLeft: 'input[name="wpProQuiz_pageLeft"]',
			singlePageRight: 'input[name="wpProQuiz_pageRight"]'
		};

		var globalElements = {
			back: $e.find('input[name="back"]'),
			next: $e.find(globalNames.next),
			quiz: $e.find('.wpProQuiz_quiz'),
			questionList: $e.find('.wpProQuiz_list'),
			results: $e.find('.wpProQuiz_results'),
			sending: $e.find('.wpProQuiz_sending'),
			quizStartPage: $e.find('.wpProQuiz_text'),
			timelimit: $e.find('.wpProQuiz_time_limit'),
			toplistShowInButton: $e.find('.wpProQuiz_toplistShowInButton'),
			listItems: $()
		};
		
		var toplistData = {
			token: '',
			isUser: 0
		};
		
		var formPosConst = {
			START: 0,
			END: 1
		};
		
		/**
		 * @memberOf timelimit
		 */
		var timelimit = (function() {
	var _counter = config.timelimit;
	var _intervalId = 0;
	var instance = {};

	// set cookie for different users and different quizzes
	var timer_cookie = 'ldadv-time-limit-' + config.user_id + '-' + config.quizId;

	instance.stop = function() {
		if(_counter) {
			$.removeCookie(timer_cookie);
			window.clearInterval(_intervalId);
			globalElements.timelimit.hide();
		}
	};

	instance.start = function() {
		if(!_counter)
			return;

		$.cookie.raw = true;

		var full = _counter * 1000;
		var tick = $.cookie(timer_cookie);
		var limit = tick ? tick : _counter;
		var x = limit * 1000;

		var $timeText = globalElements.timelimit.find('span').text(plugin.methode.parseTime(limit));
		var $timeDiv = globalElements.timelimit.find('.wpProQuiz_progress');

		globalElements.timelimit.show();

		var beforeTime = +new Date();

		_intervalId = window.setInterval(function() {

			var diff = (+new Date() - beforeTime);
			var remainingTime = x - diff;

			if(diff >= 500) {
				tick = remainingTime / 1000;
				$timeText.text(plugin.methode.parseTime(Math.ceil(tick)));
				$.cookie(timer_cookie, tick);
			}

			$timeDiv.css('width', (remainingTime / full * 100) + '%');

			if(remainingTime <= 0) {
				instance.stop();
				plugin.methode.finishQuiz(true);
			}

		}, 16);
	};

	return instance;

})();
		
		
		/**
		 * @memberOf reviewBox
		 */
		var reviewBox = new function() {
			
			var $contain = [], $cursor = [], $list = [], $items = [];
			var x = 0, offset = 0, diff = 0, top = 0, max = 0;
			var itemsStatus = [];
			
			this.init = function() {
				$contain = $e.find('.wpProQuiz_reviewQuestion');
				$cursor = $contain.find('div');
				$list = $contain.find('ol');
				$items = $list.children();
				
				$cursor.mousedown(function(e) {
					e.preventDefault();
					e.stopPropagation();
					
					offset = e.pageY - $cursor.offset().top  + top;
					
					$(document).bind('mouseup.scrollEvent', endScroll);
					$(document).bind('mousemove.scrollEvent', moveScroll);
					
				});
				
				$items.click(function(e) {
					plugin.methode.showQuestion($(this).index());
				});
				
				$e.bind('questionSolved', function(e) {
					itemsStatus[e.values.index].solved = e.values.solved;
					setColor(e.values.index);
				});
				
				$e.bind('changeQuestion', function(e) {
					$items.removeClass('wpProQuiz_reviewQuestionTarget');
					
					$items.eq(e.values.index).addClass('wpProQuiz_reviewQuestionTarget');
					
					scroll(e.values.index);
				});
						
				$e.bind('reviewQuestion', function(e) {
					itemsStatus[e.values.index].review = !itemsStatus[e.values.index].review;
					setColor(e.values.index);
				});
				
				$contain.bind('mousewheel DOMMouseScroll', function(e) {
					e.preventDefault();
					
					var ev = e.originalEvent;
					var w = ev.wheelDelta ? -ev.wheelDelta / 120 : ev.detail / 3;
					var plus = 20 * w;
					
					var x = top - $list.offset().top  + plus;
					
					if(x > max)
						x = max;
					
					if(x < 0)
						x = 0;

					var o = x / diff;
					
					$list.attr('style', 'margin-top: ' + (-x) + 'px !important');
					$cursor.css({top: o});
					
					return false;
				});
			};
			
			this.show = function(save) {
				if(bitOptions.reviewQustion)
					$contain.parent().show();
				
				$e.find('.wpProQuiz_reviewDiv .wpProQuiz_button2').show();
				
				if(save)
					return;
				
				$list.attr('style', 'margin-top: 0px !important');
				$cursor.css({top: 0});
				
				var h = $list.outerHeight();
				var c = $contain.height();
				x = c - $cursor.height();
				offset = 0;
				max = h-c;
				diff = max / x;
				
				this.reset();
				
				if(h > 100) {
					$cursor.show();
				}
				
				top = $cursor.offset().top;
			};
			
			this.hide = function() {
				$contain.parent().hide();
			};
			
			this.toggle = function() {
				if(bitOptions.reviewQustion) {
					$contain.parent().toggle();
					$items.removeClass('wpProQuiz_reviewQuestionTarget');
					$e.find('.wpProQuiz_reviewDiv .wpProQuiz_button2').hide();
					
					$list.attr('style', 'margin-top: 0px !important');
					$cursor.css({top: 0});
					
					var h = $list.outerHeight();
					var c = $contain.height();
					x = c - $cursor.height();
					offset = 0;
					max = h-c;
					diff = max / x;
					
					if(h > 100) {
						$cursor.show();
					}
					
					top = $cursor.offset().top;
				}
			};
			
			this.reset = function() {
				for(var i = 0, c = $items.length; i < c; i++) {
					itemsStatus[i] = {};
				}
				
				$items.removeClass('wpProQuiz_reviewQuestionTarget').css('background-color', '');
			};
			
			function scroll(index) {
				var $item = $items.eq(index);
				var iTop = $item.offset().top;
				var cTop = $contain.offset().top;
				var calc = iTop - cTop;
				
				
				if((calc - 4) < 0 || (calc + 32) > 100) {
					var x = cTop - $items.eq(0).offset().top - (cTop - $list.offset().top)  + $item.position().top;
					
					if(x > max)
						x = max;

					var o = x / diff;
					
					$list.attr('style', 'margin-top: ' + (-x) + 'px !important');
					$cursor.css({top: o});
				}
			}
			
			function setColor(index) {
				var color = '';
				var itemStatus = itemsStatus[index];
				
				if(itemStatus.review) {
					color = '#FFB800';
				} else if(itemStatus.solved) {
					color = '#6CA54C';
				}
				
				$items.eq(index).css('background-color', color);
			}
			
			function moveScroll(e) {
				e.preventDefault();

				var o = e.pageY - offset;
				
				if(o < 0)
					o = 0;
					
				if(o > x)
					o = x;
				
				var v = diff * o;

				$list.attr('style', 'margin-top: ' + (-v) + 'px !important');
				
				$cursor.css({top: o});
			}
			
			function endScroll(e) {
				e.preventDefault();
				
				$(document).unbind('.scrollEvent');
			}
		};
		
		
		function QuestionTimer() {
			var questionStartTime = 0;
			var currentQuestionId = -1;
			
			var quizStartTimer = 0;
			var isQuizStart = false;
			
			this.questionStart = function(questionId) {
				if(currentQuestionId != -1)
					this.questionStop();
				
				currentQuestionId = questionId;
				questionStartTime = +new Date();
			};
			
			this.questionStop = function() {
				if(currentQuestionId == -1)
					return;
				
				results[currentQuestionId].time += Math.round((new Date() - questionStartTime) / 1000);
				
				currentQuestionId = -1;
			};
			
			this.startQuiz = function() {
				if(isQuizStart)
					this.stopQuiz();
				
				quizStartTimer = +new Date();
				isQuizStart = true;
			};
			
			this.stopQuiz = function() {
				if(!isQuizStart)
					return;
				
				results['comp'].quizTime += Math.round((new Date() - quizStartTimer) / 1000);
				isQuizStart = false;
			};
			
			this.init = function() {
				
			};
			
		};

		var questionTimer = new QuestionTimer();


		var readResponses = function(name, data, $question, $questionList) {
			var response = {};
			var func = {
				singleMulti: function() {
					var input = $questionList.find('.wpProQuiz_questionInput').attr('disabled', 'disabled');
					$questionList.children().each(function(i) {
						var $item = $(this);
						var index = $item.data('pos');
						var checked = input.eq(i).is(':checked');
						response[index] = checked;
					});
				},
				
				sort_answer: function() {
					var $items = $questionList.children();
					
					$items.each(function(i, v) {
						var $this = $(this);
						response[i] = $this.data('pos');

					});
					$questionList.sortable("destroy");
				},
				
				matrix_sort_answer: function() {
					var $items = $questionList.children();
					var matrix = new Array();
					statistcAnswerData = {0:-1};
					
					$items.each(function() {
						var $this = $(this);
						var id = $this.data('pos');
						var $stringUl = $this.find('.wpProQuiz_maxtrixSortCriterion');
						var $stringItem = $stringUl.children();
						
						if($stringItem.length)
							statistcAnswerData[$stringItem.data('pos')] = id;

						response = statistcAnswerData;
					});
					$question.find('.wpProQuiz_sortStringList, .wpProQuiz_maxtrixSortCriterion').sortable("destroy");

				},
				
				free_answer: function() {
					var $li = $questionList.children();
					var value = $li.find('.wpProQuiz_questionInput').attr('disabled', 'disabled').val();
					response = value;
				},
				
				cloze_answer: function() {
					response = {};
					$questionList.find('.wpProQuiz_cloze').each(function(i, v) {
						var $this = $(this);
						var cloze = $this.children();
						var input = cloze.eq(0);
						var span = cloze.eq(1);
						var inputText = plugin.methode.cleanupCurlyQuotes(input.val());
						response[i] = inputText;
						input.attr('disabled', 'disabled');
					});
				},
				
				assessment_answer: function() {
					correct = true;
					var $input = $questionList.find('.wpProQuiz_questionInput').attr('disabled', 'disabled');
					var val = 0;
					
					$input.filter(':checked').each(function() {
						val += parseInt($(this).val());
					});
					
					response = val;
				}
			};
			
			func[name]();
			
			return {response:response}; 
		}; 
				
		/**
		 *  @memberOf formClass
		 */
		var formClass = new function() {
			var funcs = {
					isEmpty: function(str) {
						str = $.trim(str);
						return (!str || 0 === str.length);
					}
					
//					testValidate: function(str, type) {
//						switch (type) {
//						case 0: //None
//							return true;
//						case 1: //Text
//							return !funcs.isEmpty(str);
//						case 2: //Number
//							return !isNaN(str);
//						case 3: //E-Mail
//							return new RegExp(/^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/)
//                                          .test($.trim(str));
//						}
//					}
			};
			
			var typeConst = {
					TEXT: 0,
					TEXTAREA: 1,
					NUMBER: 2,
					CHECKBOX: 3,
					EMAIL: 4,
					YES_NO: 5,
					DATE: 6,
					SELECT: 7,
					RADIO: 8
			};
			
			this.checkForm = function() {
				var check = true;

				$e.find('.wpProQuiz_forms input, .wpProQuiz_forms textarea, .wpProQuiz_forms .wpProQuiz_formFields, .wpProQuiz_forms select').each(function() {
					var $this = $(this);
					var isRequired = $this.data('required') == 1;
					var type = $this.data('type');
					var test = true;
					var value = $.trim($this.val());
					
					switch (type) {
					case typeConst.TEXT:
					case typeConst.TEXTAREA:
					case typeConst.SELECT:
						if(isRequired)
							test = !funcs.isEmpty(value);
						
						break;
					case typeConst.NUMBER:
						if(isRequired || !funcs.isEmpty(value))
							test = !funcs.isEmpty(value) && !isNaN(value);
						
						break;
					case typeConst.EMAIL:
						if(isRequired || !funcs.isEmpty(value))
							test = !funcs.isEmpty(value) && new RegExp(/^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/)
                            										.test(value);
						
						break;
					case typeConst.CHECKBOX:
						if(isRequired)
							test = $this.is(':checked');
						
						break;
					case typeConst.YES_NO:
					case typeConst.RADIO:
						if(isRequired)
							test = $this.find('input[type="radio"]:checked').val() !== undefined;
						break;
					case typeConst.DATE:
						var num = 0, co = 0;
						
						$this.find('select').each(function() {
							num++;
							co += funcs.isEmpty($(this).val()) ? 0 : 1;
						});
						
						if(isRequired || co > 0)
							test = num == co;
						
						break;
					}
					
					if(test) {
						$this.siblings('.wpProQuiz_invalidate').hide();
					} else {
						check = false;
						$this.siblings('.wpProQuiz_invalidate').show();
					}
					
				});
				
//				$('.wpProQuiz_forms input, .wpProQuiz_forms textarea').each(function() {
//					var $this = $(this);
//					var isRequired = $this.data('required') == 1;
//					var validate = $this.data('validate') & 0xFF;
//					var test = false;
//					var $infos = $this.parents('div:eq(0)').find('.wpProQuiz_invalidate');
//					
//					if(isRequired) {
//						if($this.attr('type') == 'checkbox') {
//							if($this.is(':checked'))
//								test = true;
//								
//						} else {
//							if(!funcs.isEmpty($this.val()))
//								test = true;
//						}
//						
//						if(!test) {
//							check = false;
//							$infos.eq(0).show();
//						} else {
//							$infos.eq(0).hide();
//						}
//					}
//					
//					if(!funcs.testValidate($this.val(), validate)) {
//						check = false;
//						$infos.eq(1).show();
//					} else {
//						$infos.eq(1).hide();
//					}
//					
//				});
				
//				if(!check)
//					alert(WpProQuizGlobal.fieldsNotFilled);
//				
				return check;
			};
			
			this.getFormData = function() {
				var data = {};
				
				$e.find('.wpProQuiz_forms input, .wpProQuiz_forms textarea, .wpProQuiz_forms .wpProQuiz_formFields, .wpProQuiz_forms select').each(function() {
					var $this = $(this);
					var id = $this.data('form_id');
					var type = $this.data('type');
					
					switch (type) {
					case typeConst.TEXT:
					case typeConst.TEXTAREA:
					case typeConst.SELECT:
					case typeConst.NUMBER:
					case typeConst.EMAIL:
						data[id] = $this.val();
						break;
					case typeConst.CHECKBOX:
						data[id] = $this.is(':checked') ? 1 : 0;
						break;
					case typeConst.YES_NO:
					case typeConst.RADIO:
						data[id] = $this.find('input[type="radio"]:checked').val();
						break;
					case typeConst.DATE:
						data[id] = {
							day: $this.find('select[name="wpProQuiz_field_' + id +'_day"]').val(),
							month: $this.find('select[name="wpProQuiz_field_' + id +'_month"]').val(),
							year: $this.find('select[name="wpProQuiz_field_' + id +'_year"]').val()
						};
						break;
					}
				});
				
				return data;
			};
		};
		
		var fetchAllAnswerData = function(resultData) {
			$e.find('.wpProQuiz_questionList').each(function() {
				var $this = $(this);
				var questionId = $this.data('question_id');
				var type = $this.data('type');
				var data = {};
				
				if(type == 'single' || type == 'multiple') {
					$this.find('.wpProQuiz_questionListItem').each(function() {
						data[$(this).data('pos')] = +$(this).find('.wpProQuiz_questionInput').is(':checked');
					});
				} else if(type == 'free_answer') {
					data[0] = $this.find('.wpProQuiz_questionInput').val();
				} else if(type == 'sort_answer') {
					return true;
//					$this.find('.wpProQuiz_questionListItem').each(function() {
//						data[$(this).index()] = $(this).data('pos');
//					});
				} else if(type == 'matrix_sort_answer') {
					return true;
//					$this.find('.wpProQuiz_questionListItem').each(function() {
//						data[$(this).data('pos')] = $(this).find('.wpProQuiz_answerCorrect').length;
//					});
				} else if(type == 'cloze_answer') {
					var i = 0;
					$this.find('.wpProQuiz_cloze input').each(function() {
						data[i++] = $(this).val();
					});
				} else if(type == 'assessment_answer') {
					data[0] = '';
					
					$this.find('.wpProQuiz_questionInput:checked').each(function() {
						data[$(this).data('index')] = $(this).val();
					});
				}
				
				resultData[questionId]['data'] = data;
				
			});
		};
		
		plugin.methode = {
			/**
			 * @memberOf plugin.methode
			 */
				
			parseBitOptions: function() {
				if(config.bo) {
					bitOptions.randomAnswer = config.bo & (1 << 0);
					bitOptions.randomQuestion = config.bo & (1 << 1);
					bitOptions.disabledAnswerMark = config.bo & (1 << 2);
					bitOptions.checkBeforeStart = config.bo & (1 << 3);
					bitOptions.preview = config.bo & (1 << 4);
					bitOptions.isAddAutomatic = config.bo & (1 << 6);
					bitOptions.reviewQustion = config.bo & ( 1 << 7);
					bitOptions.quizSummeryHide = config.bo & (1 << 8);
					bitOptions.skipButton = config.bo & (1 << 9);
					bitOptions.autoStart = config.bo & (1 << 10);
					bitOptions.forcingQuestionSolve = config.bo & (1 << 11);
					bitOptions.hideQuestionPositionOverview = config.bo & (1 << 12);
					bitOptions.formActivated = config.bo & (1 << 13);
					bitOptions.maxShowQuestion = config.bo & (1 << 14);
					bitOptions.sortCategories = config.bo & (1 << 15);
					
					var cors = config.bo & (1 << 5);
					
					if(cors && jQuery.support != undefined && jQuery.support.cors != undefined && jQuery.support.cors == false) {
						bitOptions.cors = cors;
					}
				}
			},
			
			setClozeStyle: function() {
				$e.find('.wpProQuiz_cloze input').each(function() {
					var $this = $(this);
					var word = "";
					var wordLen = $this.data('wordlen');
					
					for(var i = 0; i < wordLen; i++)
						word += "w";
					
					var clone = $(document.createElement("span"))
						.css('visibility', 'hidden')
						.text(word)
						.appendTo($('body'));
					
					
					var width = clone.width();
					
					clone.remove();
					
					$this.width(width + 5);
				});
			},
			
			parseTime: function(sec) {
				var seconds = parseInt(sec % 60);
		        var minutes = parseInt((sec / 60) % 60);
		        var hours = parseInt((sec / 3600) % 24);
		        
		        seconds = (seconds > 9 ? '' : '0') + seconds;
		        minutes = (minutes > 9 ? '' : '0') + minutes;
		        hours = (hours > 9 ? '' : '0') + hours;
		        
		        return hours + ':' +  minutes + ':' + seconds;
			},
			
			cleanupCurlyQuotes: function(str) {
				str = str.replace(/\u2018/, "'");
				str = str.replace(/\u2019/, "'");
				
				str = str.replace(/\u201C/, '"');
				str = str.replace(/\u201D/, '"');
				
				return $.trim(str).toLowerCase();
			},
			
			resetMatrix: function(selector) {
				selector.each(function() {
					var $this = $(this);
					var $list = $this.find('.wpProQuiz_sortStringList');
					
					$this.find('.wpProQuiz_sortStringItem').each(function() {
						$list.append($(this));
					});
				});
			},
			
			marker: function(e, correct) {
				if(!bitOptions.disabledAnswerMark) {
					if(correct) {
						e.addClass('wpProQuiz_answerCorrect');
					} else {
						e.addClass('wpProQuiz_answerIncorrect');
					}
				}
				
			},
			
			startQuiz: function(loadData) {
				if(quizStatus.loadLock) {
					quizStatus.isQuizStart = 1;
					
					return;
				}
				
				quizStatus.isQuizStart = 0;
				
				if(quizStatus.isLocked) {
					globalElements.quizStartPage.hide();
					$e.find('.wpProQuiz_lock').show();
					
					return;
				}
				
				if(quizStatus.isPrerequisite) {
					globalElements.quizStartPage.hide();
					$e.find('.wpProQuiz_prerequisite').show();
					
					return;
				}
				
				if(quizStatus.isUserStartLocked) {
					globalElements.quizStartPage.hide();
					$e.find('.wpProQuiz_startOnlyRegisteredUser').show();
					
					return;
				}
				
				if(bitOptions.maxShowQuestion && !loadData) {

				if(config.formPos == formPosConst.START) {
					if(!formClass.checkForm())
						return;
				}

					globalElements.quizStartPage.hide();
					$e.find('.wpProQuiz_loadQuiz').show();
					
					plugin.methode.loadQuizDataAjax(true);					
					
					return;
				}
				
				if(bitOptions.formActivated && config.formPos == formPosConst.START) {
					if(!formClass.checkForm())
						return;
				}
				
				plugin.methode.loadQuizData();
				
				if(bitOptions.randomQuestion) {
					plugin.methode.random(globalElements.questionList);
				}
				
				if(bitOptions.randomAnswer) {
					plugin.methode.random($e.find(globalNames.questionList));
				}
				
				if(bitOptions.sortCategories) {
					plugin.methode.sortCategories();
				}
				
				plugin.methode.random($e.find('.wpProQuiz_sortStringList'));
				plugin.methode.random($e.find('[data-type="sort_answer"]'));
				
				$e.find('.wpProQuiz_listItem').each(function(i, v) {
					var $this = $(this);
					$this.find('.wpProQuiz_question_page span:eq(0)').text(i+1);
					$this.find('> h5 span').text(i+1);
					
					$this.find('.wpProQuiz_questionListItem').each(function(i, v) {
						$(this).find('> span:not(.wpProQuiz_cloze)').text(i+1 + '. ');
					});
				});
				
				globalElements.next = $e.find(globalNames.next);
				
				switch (config.mode) {
					case 3:
						$e.find('input[name="checkSingle"]').show();
						break;
					case 2:
						$e.find(globalNames.check).show();
						
						if(!bitOptions.skipButton && bitOptions.reviewQustion)
							$e.find(globalNames.skip).show();
						
						break;
					case 1:
						$e.find('input[name="back"]').slice(1).show();
					case 0:
						globalElements.next.show();
						break;
				}
				
				if(bitOptions.hideQuestionPositionOverview || config.mode == 3)
					$e.find('.wpProQuiz_question_page').hide();
				
				//Change last name
				var $lastButton = globalElements.next.last();
				lastButtonValue = $lastButton.val();
				$lastButton.val(config.lbn);
				
				var $listItem = globalElements.questionList.children();
				
				globalElements.listItems = $e.find('.wpProQuiz_list > li');
				
				if(config.mode == 3) {
					plugin.methode.showSinglePage(0);
//					if(config.qpp) {
//						$listItem.slice(0, config.qpp).show();
//						$e.find(globalNames.singlePageRight).show();
//						$e.find('input[name="checkSingle"]').hide();
//					} else {
//						$listItem.show();
//					}
				} else {
					currentQuestion = $listItem.eq(0).show();
					
					var questionId = currentQuestion.find(globalNames.questionList).data('question_id');
					questionTimer.questionStart(questionId);
				}
				
				questionTimer.startQuiz();
				
				$e.find('.wpProQuiz_sortable').parents('ul').sortable({
					update: function( event, ui ) {
						var $p = $(this).parents('.wpProQuiz_listItem');
						
						$e.trigger({type: 'questionSolved', values: {item: $p, index: $p.index(), solved: true}});
					}
				}).disableSelection();
				
				$e.find('.wpProQuiz_sortStringList, .wpProQuiz_maxtrixSortCriterion').sortable({
					connectWith: '.wpProQuiz_maxtrixSortCriterion:not(:has(li)), .wpProQuiz_sortStringList',
					placeholder: 'wpProQuiz_placehold',
					update: function( event, ui ) {
						var $p = $(this).parents('.wpProQuiz_listItem');
						
						$e.trigger({type: 'questionSolved', values: {item: $p, index: $p.index(), solved: true}});
					}
				}).disableSelection();
				
				quizSolved = [];
				
				timelimit.start();
				
				startTime = +new Date();
				
				results = {comp: {points: 0, correctQuestions: 0, quizTime: 0}};
				
				$e.find('.wpProQuiz_questionList').each(function() {
					var questionId = $(this).data('question_id');
					
					results[questionId] = {time: 0};
				});
				
				catResults = {};
				
				$.each(options.catPoints, function(i, v) {
					catResults[i] = 0;
				});
				
				globalElements.quizStartPage.hide();
				$e.find('.wpProQuiz_loadQuiz').hide();
				globalElements.quiz.show();
				reviewBox.show();
				
				if(config.mode != 3) {
					$e.trigger({type: 'changeQuestion', values: {item: currentQuestion, index: currentQuestion.index()}});
				}
			},
			
			showSingleQuestion: function(question) {
				var page = question ? Math.ceil(question / config.qpp) : 1;
				
				this.showSinglePage(page);
				
//				plugin.methode.scrollTo($element, 1);
			},
			
			showSinglePage: function(page) {
				$listItem = globalElements.questionList.children().hide();
				
				if(!config.qpp) {
					$listItem.show();
					
					return;
				}
				
				page = page ? +page : 1;
				
				var maxPage = Math.ceil($e.find('.wpProQuiz_list > li').length / config.qpp);
				
				if(page > maxPage)
					return;
				
				var pl = $e.find(globalNames.singlePageLeft).hide();
				var pr = $e.find(globalNames.singlePageRight).hide();
				var cs = $e.find('input[name="checkSingle"]').hide();
				
				if(page > 1) {
					pl.val(pl.data('text').replace(/%d/, page-1)).show();
				}
				
				if(page == maxPage) {
					cs.show();
				} else {
					pr.val(pr.data('text').replace(/%d/, page+1)).show();
				}
				
				currentPage = page;
				
				var start = config.qpp * (page - 1);
				
				$listItem.slice(start, start + config.qpp).show();
				plugin.methode.scrollTo(globalElements.quiz);
			},
			
			nextQuestion: function() {
//				currentQuestion = currentQuestion.hide().next().show();
//				
//				plugin.methode.scrollTo(globalElements.quiz);
//				
//				$e.trigger({type: 'changeQuestion', values: {item: currentQuestion, index: currentQuestion.index()}});
//				
//				if(!currentQuestion.length) {
//					plugin.methode.showQuizSummary();			
//				}
				jQuery( ".mejs-pause" ).trigger( "click" );
				this.showQuestionObject(currentQuestion.next());
			},
			
			prevQuestion: function() {
//				currentQuestion = currentQuestion.hide().prev().show();
//				
//				plugin.methode.scrollTo(globalElements.quiz);
//				
//				$e.trigger({type: 'changeQuestion', values: {item: currentQuestion, index: currentQuestion.index()}});
//				
				this.showQuestionObject(currentQuestion.prev());
			},
			
			showQuestion: function(index) {
				var $element = globalElements.listItems.eq(index);
				
				if(config.mode == 3 || inViewQuestions) {
					if(config.qpp) {
						plugin.methode.showSingleQuestion(index+1);
//						questionTimer.startQuiz();
//						return;
					}
//					plugin.methode.scrollTo($e.find('.wpProQuiz_list > li').eq(index), 1);
					plugin.methode.scrollTo($element, 1);
					questionTimer.startQuiz();
					return;
				}
				
//				currentQuestion.hide();
//				
//				currentQuestion = $element.show();
//				
//				plugin.methode.scrollTo(globalElements.quiz);
//				
//				$e.trigger({type: 'changeQuestion', values: {item: currentQuestion, index: currentQuestion.index()}});
//				
//				if(!currentQuestion.length)
//					plugin.methode.showQuizSummary();
				
				this.showQuestionObject($element);
			},
			
			showQuestionObject: function(obj) {
				if(!obj.length && bitOptions.forcingQuestionSolve && bitOptions.quizSummeryHide && bitOptions.reviewQustion) {
					for(var i = 0, c = $e.find('.wpProQuiz_listItem').length; i < c; i++) {
						if(!quizSolved[i]) {
							alert(WpProQuizGlobal.questionsNotSolved);
							return false;
						}
					}
				}
				
				currentQuestion.hide();

				currentQuestion = obj.show();
				
				plugin.methode.scrollTo(globalElements.quiz);
				
				$e.trigger({type: 'changeQuestion', values: {item: currentQuestion, index: currentQuestion.index()}});
				
				if(!currentQuestion.length) {
					plugin.methode.showQuizSummary();
				} else {
					var questionId = currentQuestion.find(globalNames.questionList).data('question_id');
					questionTimer.questionStart(questionId);
				}
			},
			
			skipQuestion: function() {
				$e.trigger({type: 'skipQuestion', values: {item: currentQuestion, index: currentQuestion.index()}});

				plugin.methode.nextQuestion();
			},
			
			reviewQuestion: function() {
				$e.trigger({type: 'reviewQuestion', values: {item: currentQuestion, index: currentQuestion.index()}});
			},
			
			showQuizSummary: function() {
				questionTimer.questionStop();
				questionTimer.stopQuiz();
				
				if(bitOptions.quizSummeryHide || !bitOptions.reviewQustion) {
					if(bitOptions.formActivated && config.formPos == formPosConst.END) {
						reviewBox.hide();
						globalElements.quiz.hide();
						plugin.methode.scrollTo($e.find('.wpProQuiz_infopage').show());
					} else {
						plugin.methode.finishQuiz();
					}
					
					return;
				}
				
				var quizSummary = $e.find('.wpProQuiz_checkPage');
				
				quizSummary.find('ol:eq(0)').empty()
					.append($e.find('.wpProQuiz_reviewQuestion ol li').clone().removeClass('wpProQuiz_reviewQuestionTarget'))
					.children().click(function(e) {
						quizSummary.hide();
						globalElements.quiz.show();
						reviewBox.show(true);
						
						plugin.methode.showQuestion($(this).index());
					});
				
				var cSolved = 0;
				
				for(var i = 0, c = quizSolved.length; i < c; i++) {
					if(quizSolved[i]) {
						cSolved++;
					}
				}
				
				quizSummary.find('span:eq(0)').text(cSolved);
				
				reviewBox.hide();
				globalElements.quiz.hide();
				
				quizSummary.show();
				
				plugin.methode.scrollTo(quizSummary);
			},
			
			finishQuiz: function(timeover) {
				
				questionTimer.questionStop();
				questionTimer.stopQuiz();
				timelimit.stop();
				
				var time = (+new Date() - startTime) / 1000;
				time = (config.timelimit && time > config.timelimit) ? config.timelimit : time;
				timespent = time;
				$e.find('.wpProQuiz_quiz_time span').text(plugin.methode.parseTime(time));
				
				if(timeover) {
					globalElements.results.find('.wpProQuiz_time_limit_expired').show();
				}
				
				plugin.methode.checkQuestion(globalElements.questionList.children(), true);
				
				
			},
			finishQuizEnd: function() {
				$e.find('.wpProQuiz_correct_answer').text(results.comp.correctQuestions);
				
				results.comp.result = Math.round(results.comp.points / config.globalPoints * 100 * 100) / 100;
				
				if(typeof certificate_details !== 'undefined' && certificate_details.certificateLink != undefined && certificate_details.certificateLink != "" && results.comp.result >= certificate_details.certificate_threshold * 100 ) {
					$e.find('.wpProQuiz_certificate').show();
				}
				
				//console.log('QuizRepeats[%o]', QuizRepeats);
				//console.log('results.comp.result[%o] options.passingpercentage[%o]', results.comp.result, options.passingpercentage);
				
				if ((typeof options.passingpercentage !== 'undefined') && (parseFloat(options.passingpercentage) >= 0.0)) {
				
					if (results.comp.result >= options.passingpercentage) {
						//For now, Just append the HTML to the page
						if(typeof continue_details !== 'undefined') {
							$e.find('.quiz_continue_link').html(continue_details);
							$e.find('.quiz_continue_link').show();
						}				
					} else {
						$e.find('.quiz_continue_link').hide();
					}
				}
				
				$pointFields = $e.find('.wpProQuiz_points span');
				
				$pointFields.eq(0).text(results.comp.points);
				$pointFields.eq(1).text(config.globalPoints);
				$pointFields.eq(2).text(results.comp.result + '%');
				
				$e.find('.wpProQuiz_resultsList > li').eq(plugin.methode.findResultIndex(results.comp.result)).show();
				
				plugin.methode.setAverageResult(results.comp.result, false);
				
				this.setCategoryOverview();
				
				plugin.methode.sendCompletedQuiz();
				
				if(bitOptions.isAddAutomatic && toplistData.isUser) {
					plugin.methode.addToplist();
				}

				reviewBox.hide();
				
				$e.find('.wpProQuiz_checkPage, .wpProQuiz_infopage').hide();
				globalElements.quiz.hide();
				

			},
			sending: function(start, end, step_size) {
				globalElements.sending.show();
				var sending_progress_bar = globalElements.sending.find('.sending_progress_bar');
				var i;
				if(typeof start == undefined || start == null) {
					i = parseInt(sending_progress_bar.width()*100/sending_progress_bar.offsetParent().width()) + 156;
				}
				else
					i = start;

				if(end == undefined)
					var end = 80;

				if(step_size == undefined)
					step_size = 1;

				if(sending_timer != null && typeof sending_timer != undefined)
				{
					clearInterval(sending_timer);
				}
				sending_timer = setInterval(function(){
												var currentWidth = parseInt(sending_progress_bar.width()*100/sending_progress_bar.offsetParent().width());
												if(currentWidth >= end) {
													clearInterval(sending_timer);
													if(currentWidth >= 100) {
														setTimeout(plugin.methode.showResults(), 2000);
													}
												}
												sending_progress_bar.css("width", i + "%");
												i = i + step_size;
											}, 300);
			},
			showResults: function() {
				globalElements.sending.hide();
				globalElements.results.show();
				plugin.methode.scrollTo(globalElements.results);
			},
			setCategoryOverview: function() {
				results.comp.cats = {};
					
				$e.find('.wpProQuiz_catOverview li').each(function() {
					var $this = $(this);
					var catId = $this.data('category_id');
					
					if(config.catPoints[catId] === undefined) {
						$this.hide();
						return true;
					}
					
					var r = Math.round(catResults[catId] / config.catPoints[catId] * 100 * 100) / 100;
					
					results.comp.cats[catId] = r;
					
					$this.find('.wpProQuiz_catPercent').text(r + '%');
					
					$this.show();
				});
			},
			
			questionSolved: function(e) {
				quizSolved[e.values.index] = e.values.solved;
			},
			
			sendCompletedQuiz: function() {
				if(bitOptions.preview)
					return;
				
				fetchAllAnswerData(results);
				
				var formData = formClass.getFormData();
		
				jQuery.post(WpProQuizGlobal.ajaxurl, {
					action : 'wp_pro_quiz_completed_quiz',
					quiz : config.quiz,					
					quizId : config.quizId,
					results : results,
					timespent: timespent,					
					forms: formData
				}).success(function(json) {
					plugin.methode.sending(null, 100, 15);	//Complete the remaining progress bar faster and show results
				});
			},
			
			findResultIndex: function(p) {
				var r = config.resultsGrade;
				var index = -1;
				var diff = 999999;
				
				for(var i = 0; i < r.length; i++){
					var v = r[i];
					
					if((p >= v) && ((p-v) < diff)) {
						diff = p-v;
						index = i;
					}
				}
				
				return index;
			},
			
			showQustionList: function() {
				inViewQuestions = !inViewQuestions;
				globalElements.toplistShowInButton.hide();
				globalElements.quiz.toggle();
				$e.find('.wpProQuiz_QuestionButton').hide();
				globalElements.questionList.children().show();
				reviewBox.toggle();
				
				$e.find('.wpProQuiz_question_page').hide();
			},
			
			random: function(group) {
				group.each(function() {
					var e = $(this).children().get().sort(function() { 
						return Math.round(Math.random()) - 0.5;
					});
					
					$(e).appendTo(e[0].parentNode);
				});
			},
			
			sortCategories: function() {
				var e = $('.wpProQuiz_list').children().get().sort(function(a, b) {
					var aQuestionId = $(a).find('.wpProQuiz_questionList').data('question_id');
					var bQuestionId = $(b).find('.wpProQuiz_questionList').data('question_id');
					
					return config.json[aQuestionId].catId - config.json[bQuestionId].catId;
				});
				
				$(e).appendTo(e[0].parentNode);
			},
			
			restartQuiz: function() {
				globalElements.results.hide();
				globalElements.quizStartPage.show();
				globalElements.questionList.children().hide();
				globalElements.toplistShowInButton.hide();
				reviewBox.hide();
				
				$e.find('.wpProQuiz_questionInput, .wpProQuiz_cloze input').removeAttr('disabled').removeAttr('checked')
					.css('background-color', '');
				
//				$e.find('.wpProQuiz_cloze input').val('');
				$e.find('.wpProQuiz_questionListItem input[type="text"]').val('');
				
				$e.find('.wpProQuiz_answerCorrect, .wpProQuiz_answerIncorrect').removeClass('wpProQuiz_answerCorrect wpProQuiz_answerIncorrect');
				
				$e.find('.wpProQuiz_listItem').data('check', false);
				
				$e.find('.wpProQuiz_response').hide().children().hide();
				
				plugin.methode.resetMatrix($e.find('.wpProQuiz_listItem'));
				
				$e.find('.wpProQuiz_sortStringItem, .wpProQuiz_sortable').removeAttr('style');
				
				$e.find('.wpProQuiz_clozeCorrect, .wpProQuiz_QuestionButton, .wpProQuiz_resultsList > li').hide();
				
				$e.find('.wpProQuiz_question_page, input[name="tip"]').show();

				$e.find(".wpProQuiz_certificate").attr('style', 'display: none !important');
				
				globalElements.results.find('.wpProQuiz_time_limit_expired').hide();
				
				globalElements.next.last().val(lastButtonValue);
				
				inViewQuestions = false;
			},
			showSpinner: function() {
				$e.find(".wpProQuiz_spinner").show();
			},
			hideSpinner: function() {
				$e.find(".wpProQuiz_spinner").hide();
			},
			checkQuestion: function(list, endCheck) {				

				var finishQuiz = (list == undefined) ? false : true;				
				var responses = {};
				var r = {};

				list = (list == undefined) ? currentQuestion : list;

				list.each(function() {
					var $this = $(this);
					var $questionList = $this.find(globalNames.questionList);
					var question_id = $questionList.data('question_id');		
					var data = config.json[$questionList.data('question_id')];			
					var name = data.type;					

					questionTimer.questionStop();
					
					if($this.data('check')) {
						return true;
					}
					
					if(data.type == 'single' || data.type == 'multiple') {
						name = 'singleMulti';
					}
					responses[question_id] = readResponses(name, data, $this, $questionList);
				});

				config.checkAnswers = {list: list, responses: responses, endCheck:endCheck, finishQuiz:finishQuiz};

				if(finishQuiz) {
					plugin.methode.sending(1, 80, 3);
				} else {
					plugin.methode.showSpinner();	
				}					

				plugin.methode.ajax({
					action: 'ld_adv_quiz_pro_ajax',
					func: 'checkAnswers',
					data: {
						quizId: config.quizId,
						responses: responses
					}
				}, function(json) {


						plugin.methode.hideSpinner();
						var list = config.checkAnswers.list;
						var responses = config.checkAnswers.responses;
						var r = config.checkAnswers.r;
						var endCheck = config.checkAnswers.endCheck;
						var finishQuiz = config.checkAnswers.finishQuiz;

						list.each(function() {
							var $this = $(this);
							var $questionList = $this.find(globalNames.questionList);
							var question_id = $questionList.data('question_id');
							//var data = {id: question_id};
				
							if($this.data('check')) {
								return true;
							}

							data = config.json[$questionList.data('question_id')];

							var result = json[question_id];

							$this.find('.wpProQuiz_response').show();
							$this.find(globalNames.check).hide();
							$this.find(globalNames.skip).hide();
							$this.find(globalNames.next).show();
							
							results[data.id].points = result.p;
							results[data.id].correct = Number(result.c);
							results[data.id].data = result.s;
							
							// If the sort_answer or matrix_sort_answer question type is not 100% correct then the returned
							// result.s object will be empty. So in order to pass the user's answers to the server for the 
							// sendCompletedQuiz AJAX call we need to grab the result.e.r object and store into results. 
							if (jQuery.isEmptyObject(results[data.id].data)) {
								if ((result.e.type != undefined) && ((result.e.type == 'sort_answer') || (result.e.type == 'matrix_sort_answer'))) {
									results[data.id].data = result.e.r;
								}
							}
							
							
							results['comp'].points += result.p;
							
							catResults[data.catId] += result.p;
							

							//Marker
							plugin.methode.markCorrectIncorrect(result, $this, $questionList);


							if(result.c) {
								if(typeof result.e.AnswerMessage != "undefined")
								$this.find('.wpProQuiz_correct').find(".wpProQuiz_AnswerMessage").html(result.e.AnswerMessage);
								$this.find('.wpProQuiz_correct').show();
								results['comp'].correctQuestions += 1;
							} else {
								if(typeof result.e.AnswerMessage != "undefined")
								$this.find('.wpProQuiz_incorrect').find(".wpProQuiz_AnswerMessage").html(result.e.AnswerMessage);
								$this.find('.wpProQuiz_incorrect').show();
							}							
							
							$this.find('.wpProQuiz_responsePoints').text(result.p);
							
							$this.data('check', true);
							
							if(!endCheck)
								$e.trigger({type: 'questionSolved', values: {item: $this, index: $this.index(), solved: true}});
						});
						if(finishQuiz)
						plugin.methode.finishQuizEnd();
				});
			},
			
			markCorrectIncorrect: function(result, $question, $questionList) {
				if(typeof result.e.c == "undefined")
					return;

				switch(result.e.type) {
					case 'single': 
					case 'multiple': 
						$questionList.children().each(function(i) {
						var $item = $(this);
						var index = $item.data('pos');
	//					var checked = input.eq(i).is(':checked');
						
							if(result.e.c[index]) {
								plugin.methode.marker($item, true);
							}
							else
							{
								if(!result.c && result.e.r[index])
								plugin.methode.marker($item, false);
							}
						});
						break;
					case 'free_answer':
						var $li = $questionList.children();
						if(result.c)
							plugin.methode.marker($li, true);
						else
							plugin.methode.marker($li, false);
						break;

					case 'cloze_answer':
						$questionList.find('.wpProQuiz_cloze').each(function(i, v) {
							var $this = $(this);
							var cloze = $this.children();
							var input = cloze.eq(0);
							var span = cloze.eq(1);
							var inputText = plugin.methode.cleanupCurlyQuotes(input.val());
							
							if(result.s[i])
								input.css('background-color', '#B0DAB0');
							else {
								input.css('background-color', '#FFBABA');
								
								if(typeof result.e.c[i] != "undefined" ) {
								span.html("(" + result.e.c[i].join() + ")");
								span.show();
								}
							}
							input.attr('disabled', 'disabled');
						});
						break;
					case 'sort_answer':
						var $items = $questionList.children();
						
						$items.each(function(i, v) {
							var $this = $(this);
														
							if(result.e.c[i] == $this.data('pos')) {
								plugin.methode.marker($this, true);

							} else {
								plugin.methode.marker($this, false);
							}
						});
						
						$items.children().css({'box-shadow': '0 0', 'cursor': 'auto'});
						
//						$questionList.sortable("destroy");
						
						var index = new Array();
						jQuery.each(result.e.c, function(i,v) {
							index[v] = i;
						});
						$items.sort(function(a, b) {
							return index[$(a).data('pos')] > index[$(b).data('pos')] ? 1 : -1;
						});
						
						$questionList.append($items);
						break;
					case 'matrix_sort_answer':
						var $items = $questionList.children();
						var matrix = new Array();
						statistcAnswerData = {0:-1};
						
						$items.each(function() {
							var $this = $(this);
							var id = $this.data('pos');
							var $stringUl = $this.find('.wpProQuiz_maxtrixSortCriterion');
							var $stringItem = $stringUl.children();
							var i = $stringItem.data('pos');
							
							if($stringItem.length && result.e.c[i] == $this.data('pos')) {
								plugin.methode.marker($this, true);
							} else {
								plugin.methode.marker($this, false);
							}
							
							matrix[i] = $stringUl;
						});
						
						plugin.methode.resetMatrix($question);
						
						$question.find('.wpProQuiz_sortStringItem').each(function() {
							var x = matrix[$(this).data('pos')];
							if(x != undefined)
								x.append(this);
						}).css({'box-shadow': '0 0', 'cursor': 'auto'});
						
					//	$question.find('.wpProQuiz_sortStringList, .wpProQuiz_maxtrixSortCriterion').sortable("destroy");
					break;
				}
			},
			showTip: function() {
				var $this = $(this);
				var id = $this.siblings('.wpProQuiz_question').find(globalNames.questionList).data('question_id');

				$this.siblings('.wpProQuiz_tipp').toggle('fast');
				
				results[id].tip = 1;
				
				$(document).bind('mouseup.tipEvent', function(e) {
					
					var $tip = $e.find('.wpProQuiz_tipp');
					var $btn = $e.find('input[name="tip"]');
					
					if(!$tip.is(e.target) && $tip.has(e.target).length == 0 && !$btn.is(e.target)) {
						$tip.hide('fast');
						$(document).unbind('.tipEvent');
					}
				});
			},
			
			ajax: function(data, success, dataType) {
				dataType = dataType || 'json';
				
				if(bitOptions.cors) {
					jQuery.support.cors = true;
				}

				//$.post(WpProQuizGlobal.ajaxurl, data, success, dataType);
				$.post(WpProQuizGlobal.ajaxurl+'?XDEBUG_SESSION_START=sublime.xdebug', data, success, dataType);
				
				if(bitOptions.cors) {
					jQuery.support.cors = false;
				}
			},
			
			checkQuizLock: function() {
				
				quizStatus.loadLock = 1;
				
				plugin.methode.ajax({
					action: 'wp_pro_quiz_check_lock',
					quizId: config.quizId
				}, function(json) {
					
					if(json.lock != undefined) {
						quizStatus.isLocked = json.lock.is;
						
						if(json.lock.pre) {
							$e.find('input[name="restartQuiz"]').hide();
						}
					}
					
					if(json.prerequisite != undefined) {
						quizStatus.isPrerequisite = 1;
						$e.find('.wpProQuiz_prerequisite span').text(json.prerequisite);
					}
					
					if(json.startUserLock != undefined) {
						quizStatus.isUserStartLocked = json.startUserLock;
					}
					
					quizStatus.loadLock = 0;
					
					if(quizStatus.isQuizStart) {
						plugin.methode.startQuiz();
					}
				});
			},
			
			loadQuizData: function() {
				plugin.methode.ajax({
					action: 'wp_pro_quiz_load_quiz_data',
					quizId: config.quizId
				}, function(json) {
					if(json.toplist) {
						plugin.methode.handleToplistData(json.toplist);
					}
					
					if(json.averageResult != undefined) {
						plugin.methode.setAverageResult(json.averageResult, true);
					}
					
					//if(json.quiz_repeats != undefined) {
					//	plugin.methode.setQuizRepeats(json);
					//}
					
				});
			},
			
			setAverageResult: function(p, g) {
				 var v = $e.find('.wpProQuiz_resultValue:eq(' + (g ? 0 : 1) + ') > * ');
				 
				 v.eq(1).text(p + '%');
				 v.eq(0).css('width', (240 * p / 100) + 'px');
			},
			/*
			setQuizRepeats: function(json) {

				if(json.quiz_repeats != undefined) {
					QuizRepeats.quizRepeats = parseInt(json.quiz_repeats);
				}
				if(json.user_attempts_left != undefined) {
					QuizRepeats.userAttemptsLeft = parseInt(json.user_attempts_left);
				}
				if(json.user_attempts_taken != undefined) {
					QuizRepeats.userAttemptsTaken = parseInt(json.user_attempts_taken);
				}
			},
			*/
			handleToplistData: function(json) {
				var $tp = $e.find('.wpProQuiz_addToplist');
				var $addBox = $tp.find('.wpProQuiz_addBox').show().children('div');
				
				if(json.canAdd) {
					$tp.show();
					$tp.find('.wpProQuiz_addToplistMessage').hide();
					$tp.find('.wpProQuiz_toplistButton').show();
					
					toplistData.token = json.token;
					toplistData.isUser = 0;
					
					if(json.userId) {
						$addBox.hide();
						toplistData.isUser = 1;
						
						if(bitOptions.isAddAutomatic) {
							$tp.hide();
						}
					} else {
						$addBox.show();
						
						var $captcha = $addBox.children().eq(1);
						
						if(json.captcha) {
							
							$captcha.find('input[name="wpProQuiz_captchaPrefix"]').val(json.captcha.code);
							$captcha.find('.wpProQuiz_captchaImg').attr('src', json.captcha.img);
							$captcha.find('input[name="wpProQuiz_captcha"]').val('');
							
							$captcha.show();
						} else {
							$captcha.hide();
						}
					}
				} else {
					$tp.hide();
				}
			},
			
			scrollTo: function(e, h) {
				var x = e.offset().top - 100;
				
				if(h || (window.pageYOffset || document.body.scrollTop) > x) {
					$('html,body').animate({scrollTop: x}, 300);
				}
			},
			
			addToplist: function() {
				if(bitOptions.preview)
					return;
				
				var $addToplistMessage = $e.find('.wpProQuiz_addToplistMessage').text(WpProQuizGlobal.loadData).show();
				var $addBox = $e.find('.wpProQuiz_addBox').hide();
				
				plugin.methode.ajax({
					action: 'wp_pro_quiz_add_toplist',
					quizId: config.quizId,
					token: toplistData.token,
					name: $addBox.find('input[name="wpProQuiz_toplistName"]').val(),
					email: $addBox.find('input[name="wpProQuiz_toplistEmail"]').val(),
					captcha: $addBox.find('input[name="wpProQuiz_captcha"]').val(),
					prefix: $addBox.find('input[name="wpProQuiz_captchaPrefix"]').val(),
					points: results.comp.points,
					totalPoints:config.globalPoints,
					timespent:timespent
				}, function(json) {
					$addToplistMessage.text(json.text);
					
					if(json.clear) {
						$addBox.hide();
						plugin.methode.updateToplist();
					} else {
						$addBox.show();
					}
					
					if(json.captcha) {
						$addBox.find('.wpProQuiz_captchaImg').attr('src', json.captcha.img);
						$addBox.find('input[name="wpProQuiz_captchaPrefix"]').val(json.captcha.code);
						$addBox.find('input[name="wpProQuiz_captcha"]').val('');
					}
				});
			},
			
			updateToplist: function() {
				if(typeof(wpProQuiz_fetchToplist) == "function") {
					wpProQuiz_fetchToplist();
				}
			},
			
			registerSolved: function() {
				$e.find('.wpProQuiz_questionInput[type="text"]').change(function(e) {
					var $this = $(this);
					var $p = $this.parents('.wpProQuiz_listItem');
					var s = false;
					
					if($this.val() != '') {
						s = true;
					}
					
					$e.trigger({type: 'questionSolved', values: {item: $p, index: $p.index(), solved: s}});
				});
				
				$e.find('.wpProQuiz_questionList[data-type="single"] .wpProQuiz_questionInput, .wpProQuiz_questionList[data-type="assessment_answer"] .wpProQuiz_questionInput').change(function(e) {
					var $this = $(this);
					var $p = $this.parents('.wpProQuiz_listItem');
					var s = this.checked;
					
					$e.trigger({type: 'questionSolved', values: {item: $p, index: $p.index(), solved: s}});
				});
				
				$e.find('.wpProQuiz_cloze input').change(function() {
					var $this = $(this);
					var $p = $this.parents('.wpProQuiz_listItem');
					var s = true;
					
					$p.find('.wpProQuiz_cloze input').each(function() {
						if($(this).val() == '') {
							s = false;
							return false;
						}
					});
					
					$e.trigger({type: 'questionSolved', values: {item: $p, index: $p.index(), solved: s}});
				});
				
				$e.find('.wpProQuiz_questionList[data-type="multiple"] .wpProQuiz_questionInput').change(function(e) {
					var $this = $(this);
					var $p = $this.parents('.wpProQuiz_listItem');
					var c = 0;
					
					$p.find('.wpProQuiz_questionList[data-type="multiple"] .wpProQuiz_questionInput').each(function(e) {
						if(this.checked)
							c++;
					});
					
					$e.trigger({type: 'questionSolved', values: {item: $p, index: $p.index(), solved: (c) ? true : false}});
					
				});
			},
			
			loadQuizDataAjax: function(quizStart) {
				plugin.methode.ajax({
					action: 'wp_pro_quiz_admin_ajax',
					func: 'quizLoadData',
					data: {
						quizId: config.quizId
					}
				}, function(json) {
					config.globalPoints = json.globalPoints;
					config.catPoints = json.catPoints;
					config.json = json.json;
					
					globalElements.quiz.remove();
					
					$e.find('.wpProQuiz_quizAnker').after(json.content);
					
					$('table.wpProQuiz_toplistTable caption span.wpProQuiz_max_points').html(config.globalPoints);
					
					//Reinit globalElements
					globalElements = {
						back: $e.find('input[name="back"]'),
						next: $e.find(globalNames.next),
						quiz: $e.find('.wpProQuiz_quiz'),
						questionList: $e.find('.wpProQuiz_list'),
						results: $e.find('.wpProQuiz_results'),
						sending: $e.find('.wpProQuiz_sending'),
						quizStartPage: $e.find('.wpProQuiz_text'),
						timelimit: $e.find('.wpProQuiz_time_limit'),
						toplistShowInButton: $e.find('.wpProQuiz_toplistShowInButton'),
						listItems: $()
					};
					
					plugin.methode.initQuiz();
					
					if(quizStart)
						plugin.methode.startQuiz(true);
                        
                    // load script to show player for ajax content
                        var data = json.content; 
                         var audiotag = data.search("wp-audio-shortcode");
                         var videotag = data.search("wp-video-shortcode");
                         if(audiotag != '-1' || videotag != '-1') {
                         $.getScript(json.site_url+"/wp-includes/js/mediaelement/mediaelement-and-player.min.js");
                         $.getScript(json.site_url+"/wp-includes/js/mediaelement/wp-mediaelement.js");
                         $("<link/>", {  rel: "stylesheet",  type: "text/css",  href: json.site_url+"/wp-includes/js/mediaelement/mediaelementplayer.min.css"}).appendTo("head");        
                         }
					
				});
			},
			
			nextQuestionClicked: function() {
				
				var $questionList = currentQuestion.find(globalNames.questionList);
				var data = config.json[$questionList.data('question_id')];			

				// Within the following logic. If the question type is 'sort_answer' there is a chance
				// the sortable answers will be displayed in the correct order. In thar case the user will click 
				// the next button. 
				// The trigger to set the question was answered is normally a function of the sort/drag action 
				// by the user. So we need to set the question answered flag in the case the Quiz summary is enabled. 
				if (data.type == 'sort_answer') {
					var question_index = currentQuestion.index();
					if ( typeof quizSolved[question_index] === 'undefined') {
						$e.trigger({type: 'questionSolved', values: {item: currentQuestion, index: question_index, solved: true}});
					}
				}
				
				if(bitOptions.forcingQuestionSolve && !quizSolved[currentQuestion.index()]
						&& (bitOptions.quizSummeryHide || !bitOptions.reviewQustion)) {
					
					alert(WpProQuizGlobal.questionNotSolved);
					return false;
				}
				
				plugin.methode.nextQuestion();
			},
			
			
			initQuiz: function() {
				plugin.methode.setClozeStyle();
				plugin.methode.registerSolved();
				
				globalElements.next.click(plugin.methode.nextQuestionClicked);
				
				globalElements.back.click(function() {
					plugin.methode.prevQuestion();
				});
				
				$e.find(globalNames.check).click(function() {
					if(bitOptions.forcingQuestionSolve && !quizSolved[currentQuestion.index()] 
							&& (bitOptions.quizSummeryHide || !bitOptions.reviewQustion)) {
						
						alert(WpProQuizGlobal.questionNotSolved);
						return false;
					}
					plugin.methode.checkQuestion();
				});
				
				$e.find('input[name="checkSingle"]').click(function() {
					if(bitOptions.forcingQuestionSolve	&& (bitOptions.quizSummeryHide || !bitOptions.reviewQustion)) {
						for(var i = 0, c = $e.find('.wpProQuiz_listItem').length; i < c; i++) {
							if(!quizSolved[i]) {
								alert(WpProQuizGlobal.questionsNotSolved);
								return false;
							}
						}
					}
					
					plugin.methode.showQuizSummary();
				});
				
				$e.find('input[name="tip"]').click(plugin.methode.showTip);
				$e.find('input[name="skip"]').click(plugin.methode.skipQuestion);
				
				$e.find('input[name="wpProQuiz_pageLeft"]').click(function() {
					plugin.methode.showSinglePage(currentPage-1);
				});
				
				$e.find('input[name="wpProQuiz_pageRight"]').click(function() {
					plugin.methode.showSinglePage(currentPage+1);
				});
			}
		};

//		/**
//		 * @memberOf plugin
//		 */
//		plugin.init = function() {
//			//
//			plugin.methode.setClozeStyle();
//			//
//			plugin.methode.registerSolved();
//			
//			
//			reviewBox.init();
//			
//			if(bitOptions.checkBeforeStart && !bitOptions.preview) {
//				plugin.methode.checkQuizLock();
//			}
//			
//			$e.find('input[name="startQuiz"]').click(function() {
//				plugin.methode.startQuiz();
//				return false;
//			});
//			
//			//
//			globalElements.next.click(function() {
//				if(bitOptions.forcingQuestionSolve && !quizSolved[currentQuestion.index()]
//						&& (bitOptions.quizSummeryHide || !bitOptions.reviewQustion)) {
//					
//					alert(WpProQuizGlobal.questionNotSolved);
//					return false;
//				}
//				
//				plugin.methode.nextQuestion();
//			});
//			
//			//
//			globalElements.back.click(function() {
//				plugin.methode.prevQuestion();
//			});
//			
//			$e.find('input[name="reShowQuestion"]').click(function() {
//				plugin.methode.showQustionList();
//			});
//			
//			$e.find('input[name="restartQuiz"]').click(function() {
//				plugin.methode.restartQuiz();
//			});
//			
//			//
//			$e.find(globalNames.check).click(function() {
//				if(bitOptions.forcingQuestionSolve && !quizSolved[currentQuestion.index()] 
//						&& (bitOptions.quizSummeryHide || !bitOptions.reviewQustion)) {
//					
//					alert(WpProQuizGlobal.questionNotSolved);
//					return false;
//				}
//				
//				plugin.methode.checkQuestion();
//			});
//			
//			//
//			$e.find('input[name="checkSingle"]').click(function() {
//				if(bitOptions.forcingQuestionSolve	&& (bitOptions.quizSummeryHide || !bitOptions.reviewQustion)) {
//					for(var i = 0, c = $e.find('.wpProQuiz_listItem').length; i < c; i++) {
//						if(!quizSolved[i]) {
//							alert(WpProQuizGlobal.questionsNotSolved);
//							return false;
//						}
//					}
//				}
//				
//				plugin.methode.showQuizSummary();
//			});
//			
//			//
//			$e.find('input[name="tip"]').click(plugin.methode.showTip);
//
//			//
//			$e.find('input[name="skip"]').click(plugin.methode.skipQuestion);
//			
//			$e.find('input[name="review"]').click(plugin.methode.reviewQuestion);
//			
//			$e.find('input[name="wpProQuiz_toplistAdd"]').click(plugin.methode.addToplist);
//			
//			$e.find('input[name="quizSummary"]').click(plugin.methode.showQuizSummary);
//			
//			$e.find('input[name="endQuizSummary"]').click(function() {
//				if(bitOptions.forcingQuestionSolve) {
//					for(var i = 0, c = $e.find('.wpProQuiz_listItem').length; i < c; i++) {
//						if(!quizSolved[i]) {
//							alert(WpProQuizGlobal.questionsNotSolved);
//							return false;
//						}
//					}
//				}
//				
//				if(bitOptions.formActivated && config.formPos == formPosConst.END && !formClass.checkForm())
//					return;
//				
//				plugin.methode.finishQuiz();
//			});
//			
//			$e.find('input[name="endInfopage"]').click(function() {
//				if(formClass.checkForm())
//					plugin.methode.finishQuiz();
//			});
//			
//			$e.find('input[name="showToplist"]').click(function() {
//				globalElements.quiz.hide();
//				globalElements.toplistShowInButton.toggle();
//			});
//			
//			//
//			$e.find('input[name="wpProQuiz_pageLeft"]').click(function() {
//				plugin.methode.showSinglePage(currentPage-1);
//			});
//			
//			//
//			$e.find('input[name="wpProQuiz_pageRight"]').click(function() {
//				plugin.methode.showSinglePage(currentPage+1);
//			});
//			
//			$e.bind('questionSolved', plugin.methode.questionSolved);
//		};
		
		/**
		 * @memberOf plugin
		 */
		plugin.preInit = function() {
			plugin.methode.parseBitOptions();
			reviewBox.init();
			
			$e.find('input[name="startQuiz"]').click(function() {
				plugin.methode.startQuiz();
				return false;
			});
			
			if(bitOptions.checkBeforeStart && !bitOptions.preview) {
				plugin.methode.checkQuizLock();
			}
			
			$e.find('input[name="reShowQuestion"]').click(function() {
				plugin.methode.showQustionList();
			});
			
			$e.find('input[name="restartQuiz"]').click(function() {
				plugin.methode.restartQuiz();
			});
			
			
			$e.find('input[name="review"]').click(plugin.methode.reviewQuestion);
			
			$e.find('input[name="wpProQuiz_toplistAdd"]').click(plugin.methode.addToplist);
			
			$e.find('input[name="quizSummary"]').click(plugin.methode.showQuizSummary);
			
			$e.find('input[name="endQuizSummary"]').click(function() {
				if(bitOptions.forcingQuestionSolve) {
					for(var i = 0, c = $e.find('.wpProQuiz_listItem').length; i < c; i++) {
						if(!quizSolved[i]) {
							alert(WpProQuizGlobal.questionsNotSolved);
							return false;
						}
					}
				}
				
				if(bitOptions.formActivated && config.formPos == formPosConst.END && !formClass.checkForm())
					return;
				
				plugin.methode.finishQuiz();
			});
			
			$e.find('input[name="endInfopage"]').click(function() {
				if(formClass.checkForm())
					plugin.methode.finishQuiz();
			});
			
			$e.find('input[name="showToplist"]').click(function() {
				globalElements.quiz.hide();
				globalElements.toplistShowInButton.toggle();
			});
			
			$e.bind('questionSolved', plugin.methode.questionSolved);
			
			if(!bitOptions.maxShowQuestion) {
				plugin.methode.initQuiz();
			}
			
			if(bitOptions.autoStart)
				plugin.methode.startQuiz();
		};
		
		plugin.preInit();
	};
	
	$.fn.wpProQuizFront = function(options) {
		return this.each(function() {
			if(undefined == $(this).data('wpProQuizFront')) {
				$(this).data('wpProQuizFront', new $.wpProQuizFront(this, options));
			}
		});
	};
	
})(jQuery);
