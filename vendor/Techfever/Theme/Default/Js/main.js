(function($) {

	$.fn.JSONAjax = function(uri, data, callback) {
		$.post(uri, data + '&XMLHttpRequest=1', callback, 'json');
	};

	$.fn.formValidator = function(options) {
		var defaults = {
			uri : "",
			form : "",
			data : "",
			values : "",
		};
		var options = $.extend(defaults, options);

		$(this).JSONAjax(
				options.uri,
				options.data + options.values,
				function(JsonReturn) {
					if ($.isArray(JsonReturn.messages) != -1) {
						$(this).formMessage({
							form : options.form,
							messages : JsonReturn.messages,
							input : JsonReturn.input,
						});
					}
					if ($.isArray(JsonReturn.relation) != -1) {
						/* Validate Related Field */
						$.each(JsonReturn.relation, function(key, value) {
							if (value.length > 0) {
								$(this).JSONAjax(options.uri,
										options.data + '&Input=' + value,
										function(JsonReturn) {
											$(this).formMessage({
												form : options.form,
												messages : JsonReturn.messages,
												input : JsonReturn.input,
											});
										});
							}
						});
					}
				});
	};

	$.fn.formMessage = function(options) {
		var defaults = {
			form : "",
			messages : "",
			input : "",
		};
		var options = $.extend(defaults, options);

		var isError = false;
		$.each(options.messages, function(key, value) {
			var element = "form[id=" + options.form + "] table[class=form] tr[id=" + key + "] td[class=help] div[class=ui-widget] div[class=ui-state-active]";
			if (options.input != null && options.input.length > 0 && (options.input == 'submit' || options.input == key)) {
				var html = '';
				if (value.length > 0) {
					isError = true;
					html = '<span class="ui-icon ui-icon-closethick"></span>' + value;
				} else {
					html = '<span class="ui-icon ui-icon-check"></span>';
				}
				$(element).html(html);
			}
		});
		return isError;
	};

	$.fn.formSubmit = function(options) {
		var defaults = {
			uri : "",
			form : "",
			data : "",
			title : "",
			message : "",
			failcallback : "",
		};
		var options = $.extend(defaults, options);

		$(this).formDialog({ title : options.title,	text : options.message,	callback : function() {
			$(this).formProgressBar();
			$(this).JSONAjax(options.uri, options.data, function(JsonReturn) {
				if (JsonReturn.valid == false) {
					if (JsonReturn.flashmessages) {
						$(this).formDialog({ title : options.title,	text : JsonReturn.flashmessages});
					}
					if (options.failcallback && typeof (options.failcallback) === "function") {
						options.failcallback();
					}
				} else if (JsonReturn.valid == true && JsonReturn.redirect) {
					window.location.replace(JsonReturn.redirect);
				}
				$(this).formProgressBar(true);
			});

		}
		});
	};

	$.fn.formPreview = function(options) {
		var defaults = {
			uri : "",
			form : "",
			data : "",
			callback : "",
		};
		var options = $.extend(defaults, options);
		$(this).formProgressBar();
		$(this).JSONAjax(options.uri, options.data,	function(JsonReturn) {
			if ($.isArray(JsonReturn.messages) != -1) {
				isError = $(this).formMessage({
					form : options.form,
					messages : JsonReturn.messages,
					input : 'submit',
				});
				if (JsonReturn.messagescount == "0") {
					$("form[id=" + options.form + "] table[class=form]").find("tr").each(
					function() {
						$(this).find(':input').not(':button, :submit, :reset, :hidden').each(
							function() {
								var name = $(this).prop('name');
								var type = $(this).prop('type');
								var val = "";
								switch (type) {
									case 'select-one':
										val = $(this).find("option:selected").text();
										$(this).hide();
										$(this).after('<span class="preview" id="' + name + '">' + val + '</span>');
										break;
									case 'radio':
									case 'checkbox':
										if ($(this).is(':checked')) {
											val = $(this).next('label:first').text();
										}
										var parentElement = $(this).parent('div');
										parentElement.hide();
										parentElement.after('<span class="preview" id="' + name + '">' + val + '</span>');
										break;
									case 'password':
										val = '******';
										$(this).hide();
										$(this).after('<span class="preview" id="' + name + '">' + val + '</span>');
										break;
									case 'undefined':
										break;
									default:
										val = $(this).val();
										$(this).hide();
										$(this).after('<span class="preview" id="' + name + '">' + val + '</span>');
										break;
								}
						});

						$(this).find('td[class=help] div[class=ui-widget] div[class=ui-state-active]').each(
							function() {
								$(this).html('');
						});
							
						$(this).find('td[class=label] span[class=required]').each(
							function() {
								$(this).hide();
						});
					});
					if (options.callback && typeof (options.callback) === "function") {
						options.callback();
					}
				}
				$(this).formProgressBar(true);
			}
		});
	};

	$.fn.formCancel = function(options) {
		var defaults = {
			form : "",
			callback : "",
		};
		var options = $.extend(defaults, options);
		$(this).formProgressBar();
		$("form[id=" + options.form + "] table[class=form]").find("tr").each(
				function() {
					$(this).find(':input').not(':button, :submit, :reset')
							.each(function() {
								var type = $(this).prop('type');
								switch (type) {
								case 'radio':
								case 'checkbox':
									var parentElement = $(this).parent('div');
									parentElement.show();
									break;
								default:
									$(this).show();
									break;
								}
							});

					$(this).find('span[class=preview]').each(function() {
						$(this).remove();
					});

					$(this).find('td[class=label] span[class=required]').each(
							function() {
								$(this).show();
							});
				});

		if (options.callback && typeof (options.callback) === "function") {
			options.callback();
		}
		$(this).formProgressBar(true);
	};

	$.fn.formClear = function(options) {
		var defaults = {
			form : "",
			callback : "",
		};
		var options = $.extend(defaults, options);

		$("form[id=" + options.form + "] table[class=form]")
				.find("tr")
				.each(
						function() {
							$(this).find(':input').not(
									':button, :submit, :reset, :hidden')
									.val('').removeAttr('checked').removeAttr(
											'selected').each(function() {
										var type = $(this).prop('type');
										switch (type) {
										case 'radio':
										case 'checkbox':
											$(this).button("refresh");
											break;
										}
									});

							$(this)
									.find(
											'td[class=help] div[class=ui-widget] div[class=ui-state-active]')
									.each(function() {
										$(this).html('');
									});
						});

		if (options.callback && typeof (options.callback) === "function") {
			options.callback();
		}
	};

	$.fn.formDialog = function(options) {
		var defaults = {
			height : 170,
			width : 300,
			title : "",
			text : "",
			buttons : {
				"<?php echo $this->translate('text_confirm') ?>" : function() {
					$(this).dialog("destroy");

					if (options.callback
							&& typeof (options.callback) === "function") {
						options.callback();
					}
				},
				"<?php echo $this->translate('text_cancel') ?>" : function() {
					$(this).dialog("destroy");
				}
			},
			callback : "",
		};
		var options = $.extend(defaults, options);
		$('<div class="dialog-confirm" title="' + options.title + '"><p>' + options.text + '</p></div>').dialog({
			resizable : false,
			height : options.height,
			width : options.width,
			modal : true,
			buttons : options.buttons
		});
	}

	$.fn.formDialogPreview = function(options) {
		var defaults = {
				uri : "",
				data : "",
				title : "",
				callback : "",
			};
			var options = $.extend(defaults, options);
			$(this).formProgressBar();
			$(this).JSONAjax(options.uri, options.data, function(JsonReturn) {
				if (JsonReturn.valid == true) {
					if (JsonReturn.content) {
						$(this).formDialog({ 
							height : JsonReturn.height,
							width : JsonReturn.width,
							title : options.title,	
							text : JsonReturn.content,
							buttons : {
								"<?php echo $this->translate('text_close') ?>" : function() {
									$(this).dialog("destroy");
								}
							},
						});
					}
					if (options.callback && typeof (options.callback) === "function") {
						options.callback();
					}
				}
				$(this).formProgressBar(true);
			});
		};

		$.fn.formWithdrawPreview = function(options) {
			var defaults = {
					uriapprove : "",
					urireject : "",
					uri : "",
					data : "",
					title : "",
					callback : "",
				};
				var options = $.extend(defaults, options);
				$(this).formProgressBar();
				$(this).JSONAjax(options.uri, options.data, function(JsonReturn) {
					if (JsonReturn.valid == true) {
						if (JsonReturn.content) {
							$(this).formDialog({ 
								height : JsonReturn.height,
								width : JsonReturn.width,
								title : options.title,	
								text : JsonReturn.content,
								buttons : {
									"<?php echo $this->translate('text_approve') ?>" : function() {
										$(this).dialog("destroy");
										$(this).JSONAjax(options.uriapprove, "", function(JsonReturnApprove) {
											$(this).formDialog({ title : JsonReturnApprove.title,	text : JsonReturnApprove.messages, callback : function(){ 
												if (JsonReturnApprove.redirect) {
													window.location.replace(JsonReturnApprove.redirect);
												}
											}});
										});
									},

									"<?php echo $this->translate('text_reject') ?>" : function() {
										$(this).dialog("destroy");
										$(this).JSONAjax(options.urireject, "", function(JsonReturnReject) {
											$(this).formDialog({ title : JsonReturnReject.title,	text : JsonReturnReject.messages, callback : function(){ 
												if (JsonReturnReject.redirect) {
													window.location.replace(JsonReturnReject.redirect);
												}
											}});
										});
									},

									"<?php echo $this->translate('text_close') ?>" : function() {
										$(this).dialog("destroy");
									}
								},
							});
						}
						if (options.callback && typeof (options.callback) === "function") {
							options.callback();
						}
					}
					$(this).formProgressBar(true);
				});
			};

	$.fn.formProgressBar = function(closed) {
		if (closed && closed) {
			$("div[id=dialog-loading]").dialog("destroy");
		} else {
			$(
					'<div id="dialog-loading" title="<?php echo $this->translate("text_loading") ?>"><p><div id="progressbar"><div class="progress-label"><?php echo $this->translate("text_loading_more") ?></div></div></p></div>')
					.dialog({
						resizable : false,
						height : 95,
						modal : true,
						closeOnEscape : true,
						draggable : false,
						open : function(event, ui) {
							$(".ui-dialog-titlebar-close").hide();
						}
					});
			var progressbar = $("#progressbar");
			var progressLabel = $(".progress-label");
			progressbar
					.progressbar({
						value : false,
						change : function() {
							progressLabel.text(progressbar.progressbar("value")
									+ "%");
						},
						complete : function() {
							progressLabel
									.text('<?php echo $this->translate("text_complete") ?>');
						}
					});
		}
	}

})(jQuery);