(function( $ ){
	$.fn.form = function (options) {  
		var settings = {
			appendto : "body",
			dialog : "",
			form : this,
			id : "",
			url : "",
			method : "",
			enctype : "",
			modify : "",
			usemodalbutton : false,
			disablevalidator : false,
			disableresize : false,
			init : {
				callback: function(){
					
				},
			},
			data: {
				init: {
					
				},
				user: {
					
				},
			},
			validator : {
				progressbar : "",
				input : "",
				action : "validate",
				done: function(){
					
				},
				fail: function(){
					
				},
			},
			message : {
				messages : "",
				total : 0,
				input : "",
			},
			submit : {
				button : "submit",
				confirmation : true,
			},
			cancel : {
				button : "cancel",
				confirmation : true,
			},
			step: {
				hasStep : false,
				instance : {},
				disableplugin : false,
			},
			event: {
				ajaxCall : {},
			}
		};
		// set up the options using the defaults
		settings = $.extend(true, settings, options);
		var form = {
				getWidth : function(){
					var appendto = $(this);
					var width = appendto.innerWidth();
					if(system !== undefined && system.form !== undefined && system.form.width !== undefined){
						width = system.form.width;
					}
					return width;
				},
				getHeight : function(){
					var appendto = $(this);
					var height = appendto.innerHeight();
					if(system !== undefined && system.form !== undefined && system.form.height !== undefined){
						height = system.form.height;
					}
					return height;
				},
				resize: function(id){
					if(settings.disableresize === false){
						if((system.layout.inner.width < system.layout.min.width) || $(this).isMobile().any()){
							settings.form.find( "div[type=input]" ).each(function() {
								var rowWidth = $(this).width();
								var labelWidth = $(this).children( "div.label" ).outerWidth();
								var labelNewWidth = Math.round((labelWidth <= 160 ? 160 : (rowWidth * 30 / 100)));
								var valueNewWidth = (rowWidth - labelNewWidth - 30);
								$(this).children( "div.label" ).css({
									"width" : labelNewWidth,
								});
								$(this).children( "div.value" ).css({
									"width" : valueNewWidth,
								}).children( ":input" ).not( ":button, :submit, :reset").css({
									"width" : (valueNewWidth - 4),
								});
								
								if($(this).children( "div.help" ).find("span.ui-icon-check").length > 0){
									$(this).children( "div.help" ).removeAttr("style");
								}else{
									$(this).children( "div.help" ).css({
										"display" : "unset",
									});
								}
							});
						}else{
							settings.form.find( "div[type=input]" ).children( "div.label" ).removeAttr("style");
							settings.form.find( "div[type=input]" ).children( "div.value" ).removeAttr("style");
							settings.form.find( "div[type=input]" ).children( "div.value" ).children( ":input" ).not( ":button, :submit, :reset").removeAttr("style");
							settings.form.find( "div[type=input]" ).children( "div.help" ).removeAttr("style");
						}
						if( settings.step.instance !== undefined && $.type(settings.step.instance) === "object"){
							$.each(settings.step.instance, function(key, value){
								value.resize();
							});
						}
					}
				},
				init: function(){
					if($.type(settings.form) === 'object') {
						if($.type(settings.id) !== 'string' || settings.id.length < 1) {
							settings.id = settings.form.prop("id");
						}
						if($.type(settings.url) !== 'string' || settings.url.length < 1) {
							var url = '<?php echo $this->serverUrl($this->baseHref()); ?>/';
							settings.url = url + settings.form.prop("name");
						}
						if($.type(settings.method) !== 'string' || settings.method.length < 1) {
							settings.method = settings.form.prop("method");
						}
						if($.type(settings.enctype) !== 'string' || settings.enctype.length < 1) {
							settings.enctype = settings.form.prop("enctype");
						}
					}
					settings.form.addClass("ui-form");

					form.event();

					$(this).evalCallback(settings.init.callback);

					var val = form.toserialize(true);
					settings.data.init = $.extend(settings.data.init, val);

					if(settings.step.disableplugin === false){
						var stepElement = settings.form.find(".ui-step");
						if(stepElement.length > 0){
							settings.step.hasStep = true;
							stepElement.each(function(){
								var id = stepElement.prop("id");
								if ( $.isFunction( $.fn.step ) && id !== undefined) {
								    var val = {};
								    val[id] = $("#" + id + ".ui-step").step({
								    	disableresize : settings.disableresize,
								    	dialog : settings.dialog,
								    	appendto : settings.appendto,
								    	submit : function(){
											form.submit();
								    	},
							    		cancel : function(){
											form.cancel();
								    	},
							    		validator : function(options){
											form.validator(options);
								    	},
								    });
									settings.step.instance = $.extend(true, settings.step.instance, val);
								};
							});
						}
					}
				},
				data: {
					init: function(){
						return {
							check: function(id){
								var status = false;
								$.each( settings.data.init, function( key, value ) {
									if(id === key){
										status = true;
									}
								});
								return status;
							},
							variable: function(id){
								var val = "";
								if(form.data.init().check(id)){
									val = settings.data.init[id];
								}
								return val;
							},
							match: function(id, value){
								var status = false;
								if(form.data.init().check(id)){
									var val = form.data.init().variable(id);
									if(value === val){
										status = true;
									}
								}
								return status;
							}
						}
					},
					user: function(){
						var val = form.toserialize(true);
						settings.data.user = $.extend(settings.data.user, val);
						return {
							check: function(id){
								var status = false;
								$.each( settings.data.user, function( key, value ) {
									if(id === key){
										status = true;
									}
								});
								return status;
							},
							variable: function(id){
								var val = "";
								if(form.data.user().check(id)){
									val = settings.data.user[id];
								}
								return val;
							},
						}
					},
				},
				input: function(element){
					var id = element.prop('id');
					var runvalidator = true;
					if(element.hasClass( "captcha" ) && element.prop('type') !== "hidden"){
						var captcha = element.val();
						var captchalength = captcha.length;
						if(captchalength < <?php echo CAPTCHA_LENGTH; ?>){
							runvalidator = false;
						}
					}
					form.submitButton("disable");
					var validatorCall;
					if(settings.event.ajaxCall[id] !== undefined){
						validatorCall = settings.event.ajaxCall[id];
						validatorCall.abort();
						delete settings.event.ajaxCall[id];
					}
					if(runvalidator === true){
						var val = form.data.user().variable(id);
						if(form.data.init().match(id, val) !== true){
							validatorCall = form.validator({  
								input: id,
								action: "validate",
							});
						    var val = {};
						    val[id]  = validatorCall.ajax;
						    settings.event.ajaxCall = $.extend(true, settings.event.ajaxCall, val);
						}
					}
					return settings.event.ajaxCall;
				},
				event: function(){
					var inputDeferred = $.Deferred();
					var inputElement = settings.form.find( "div[type=input]" );
					var inputTotal = inputElement.length;
					var inputCount = 0;
					
					if(inputTotal > 0){
						inputElement.each(function() {
						    $(this).addClass("ui-state-default");
							var id = $(this).attr('id');
							var elementcss = $(this).hasClass('row');
							$(this).hover(function() {
								if (elementcss) {
									$(this).addClass( "ui-input-hover" );
								}
							}, function() {
								if (elementcss) {
									$(this).removeClass( "ui-input-hover" );
								}
							});
							
							var valueElement = $(this).find( "div.value" );
							inputTotal = inputTotal + valueElement.length;
							valueElement.each(function() {
								$(this).tooltip({
									content: function () {
							            return $(this).prop('title');	
							        },
								});
								var radioElement = $(this).find( "div#radio" );
								radioElement.each(function() {
									var element = $(this);
									element.buttonset();
								});
								
								var fieldElement = $(this).find( ":input" );
								inputTotal = inputTotal + fieldElement.length;
								fieldElement.not(':button, :submit, :reset').each(function() {
									var element = $(this);
									if(element.hasClass( "captcha" ) && element.prop('type') !== "hidden"){
										element.attr("maxlength", "<?php echo CAPTCHA_LENGTH; ?>");
									}
								    element.addClass("ui-widget ui-widget-content");
									element.focus(function() {  
									    element.addClass("ui-input-focus");
									});
									element.focusout(function() {  
										element.removeClass("ui-input-focus");
									});
									switch(element.prop("type")){
										case'radio':			
											element.change(function() {
												setTimeout(function(){
													form.input(element);
												}, 1000);
											});
										break;
										case'select-one':
											element.change(function() {
												setTimeout(function(){
													form.input(element);
												}, 1000);
											});
										break;
										default:
											element.keypress(function() {
												setTimeout(function(){
													form.input(element);
												}, 1000);
											});
										break;
									}

									inputCount++;
								});
								
								inputCount++;
							});
							
							inputCount++;
							if(inputTotal === inputCount){
								inputDeferred.resolve();
							}
						});
					}

					inputDeferred.done(function(){
						if(settings.usemodalbutton === true){
							var formButton = settings.form.find( "div.button" ).html();
							var dialogButton = settings.form.parents('div.ui-dialog').children(".ui-dialog-buttonpane").children(".ui-dialog-buttonset");
							if(formButton !== undefined && formButton.length >= 1 && dialogButton.length >= 1){
								dialogButton.html(formButton);
								settings.form.find( "div.button" ).remove();
								form.submitButton({disabled: true});
								form.cancelButton({disabled: false});
							}
						}else{
							settings.form.find( ":button, :submit, :reset").each(
								function() {
									var id = $(this).prop('id');
									$(this).button();
								}
							);
						}

						if($.type(settings.submit.button) === "object"){
							settings.submit.button.click(function() {
								var disabled = settings.submit.button.prop( "disabled" );
								if(disabled !== true){
									form.submit();
								}
							});
						}

						if($.type(settings.cancel.button) === "object"){
							settings.cancel.button.click(function() {
								form.cancel();
							});
						}
						form.resize();
					});
				},
				message: function(options){
					var s = $.extend(settings.message, options);
					var messageDeferred = $.Deferred();
					var html = '';
					var element = $("form#" + settings.id + " div#" + s.input + " div.help");

					if($.type(element) === 'object') {
						html = '<div class="ui-state-highlight ui-corner-all">';
						html += '	<span class="ui-icon ui-icon-check"></span>';
						html += '</div>';
						element.removeClass("ui-form-validator-error").addClass("ui-form-validator-valid").html(html);
					}
					if ($.type(s.messages) === "object") {
						var messageTotal = s.total;
						var messageCount = 0;
						if(messageTotal > 0){
							$.each(s.messages, function(input, value) {
								if (($.type(s.input) === "string" && s.input.length > 0) && (s.input == 'submit' || s.input == input)) {
									if ($.type(value) === "string" && value.length > 0) {
										element = $("form#" + settings.id + " div#" + input + " div.help");
										html = '<div class="ui-state-error ui-corner-all">';
										html += '	<span class="ui-icon ui-icon-closethick"></span>';
										html += '	<span>' + value + '</span>';
										html += '</div>';
										if($.type(element) === 'object') {
											element.removeClass("ui-form-validator-valid").addClass("ui-form-validator-error").html(html);
										}
									}
								}
								
								messageCount++;
								if(messageTotal === messageCount){
									messageDeferred.resolve();
								}
							});
						}else{
							messageDeferred.resolve();
						}
					}else{
						messageDeferred.resolve();
					}

					messageDeferred.done(function(){
						form.resize();
					});
					return s;
				},
				validator: function(options){
					var s = $.extend(true, settings.validator, options);
					var deferred = false;
					var id;
					var post;
					var valid;
					var submit;
					var verified;
					var redirect;
					var input;
					var relation;
					var messages;
					var messagescount;
					var captcha;
					var doneCallback;
					var failCallback;
					var validCallback;
					var result = $.Deferred();
					var ajaxCall;

					if(settings.disablevalidator === false){
						form.submitButton("disable");
						
						var ajaxData = form.toserialize();
						if($.type(settings.modify) === "string" && settings.modify.length > 0){
							ajaxData = ajaxData + "&modify=" + settings.modify;
						}
						
						ajaxCall = $(this).ajaxSubmit( 
								settings.url, 
								ajaxData + 
								(($.type(s.input) === "string" && s.input.length > 0) ? "&input=" + s.input : "") + 
								(($.type(s.action) === "string" && s.action.length > 0) ? "&action=" + s.action : "&action=validate")
								, "post", "json"
						).done(function(ajaxReturn) {
							id = ajaxReturn.id;
							post = ajaxReturn.post;
							valid = ajaxReturn.valid;
							submit = ajaxReturn.submit;
							verified = ajaxReturn.verified;
							redirect = ajaxReturn.redirect;
							input = ajaxReturn.input;
							relation = ajaxReturn.relation;
							messages = ajaxReturn.messages;
							messagescount = ajaxReturn.messagescount;
							captcha = ajaxReturn.captcha;
							doneCallback = ajaxReturn.callback.done;
							failCallback = ajaxReturn.callback.fail;
							validCallback = ajaxReturn.callback.valid;
							var refreshCaptcha = false;

							if ( $.type(redirect) === "string" && redirect.length > 0 ) {
								$(this).pageRedirect(redirect);
							}else{
								if ( $.type(post) === "boolean" && post === true ) {
									form.message({
										messages : {},
										total : 0,
										input : input,
									});
									if ( $.type(valid) === "boolean" && valid === true ) {
										form.submitButton("enable");
										if((($.type(s.action) === "string" && s.action.length > 0) ? s.action : "validate") == "submit"){
											if ( $.type(submit) === "boolean" && submit === true ) {
												if ( $.type(verified) === "boolean" && verified === true ) {
													result.resolve(s.done);
												}else{
													result.reject(s.fail);
												}
											}else{
												result.reject(s.fail);
											}
										}
									}else{
										refreshCaptcha = true;
										result.reject(s.fail);
										$(this).evalCallback(validCallback);
									}
								}else{
									refreshCaptcha = true;
									result.reject(s.fail);
								}
								if (refreshCaptcha && $.type(captcha) === "object") {
									$("form#" + settings.id + " img#" + captcha.element + "-image").attr('src', captcha.src); 
									$("form#" + settings.id + " input#" + captcha.element + "-hidden").attr('value', captcha.id);
								}
							}
						});
					}
					result.done(function(callback){
						var error = true;
						if ( $.type(post) === "boolean" && post === true ) {
							if ( $.type(valid) === "boolean" && valid === true ) {
								if ( $.type(submit) === "boolean" && submit === true ) {
									if ( $.type(verified) === "boolean" && verified === true ) {
										error = false;
									}
								}
							}
						}

						if(error === false){
							if($.type( s.progressbar ) === "object"){
								s.progressbar.destroy();
							}
							$(this).evalCallback(callback);
							if((($.type(s.action) === "string" && s.action.length > 0) ? s.action : "validate") == "submit"){
								$(this).evalCallback(doneCallback);
								form.destroyModal();
							}
						}
					});
					result.fail(function(callback){
						var error = false;
						if ( $.type(post) !== "boolean" || post === false ) {
							error = true;
						}
						if ( $.type(valid) !== "boolean" || valid === false ) {
							error = true;
						}
						if ( $.type(submit) !== "boolean" || submit === false ) {
							error = true;
						}
						if ( $.type(verified) !== "boolean" || verified === false ) {
							error = true;
						}

						if(error === true){
							form.submitButton("disable");
							form.message({
								messages : messages,
								total : messagescount,
								input : input,
							});
							
							if ($.type(relation) === "array" && relation.length > 0) {
								$.each(relation, function(key, input) {
									if (input !== undefined && input.length > 0) {
										$(this).ajaxSubmit( 
												settings.url, ajaxData + 
												"&input=" + input + 
												(($.type(s.action) === "string" && s.action.length > 0) ? "&action=" + s.action : "&action=validate")
												, "post", "json"
										).done(function(ajaxReturn) {
											var input = ajaxReturn.input;
											var messages = ajaxReturn.messages;
											var messagescount = ajaxReturn.messagescount;
											form.message({
													messages : messages,
													total : messagescount,
													input : input,
											});
										});
									}
								});
							}
							if($.type( s.progressbar ) === "object"){
								s.progressbar.destroy();
							}
							$(this).evalCallback(callback);
							if((($.type(s.action) === "string" && s.action.length > 0) ? s.action : "validate") == "submit"){
								$(this).evalCallback(failCallback);
							}
						}
					});
					s = $.extend(true, s, { ajax: ajaxCall });
					return s;
				},
				submit: function(options){
					var s = $.extend(true, settings.submit, options);
					var module = settings.id.toLowerCase();
					var progressBar = settings.form.progressBar({
						appendto : settings.appendto,
						dialogclass : "ui-dialog-" + settings.dialog + "-submit-loading-modal",
						id : "ui-dialog-" + settings.dialog + "-submit-loading-content",
						position : {
							my: "center", at: "center", of: ($.type($(settings.appendto)) === "object" ? $(settings.appendto) : window)
						},
					});
					if(s.confirmation === true){
						settings.form.modal({
							appendto : settings.appendto,
							progressbar: progressBar,
							dialogclass : "ui-dialog-" + settings.dialog + "-submit-modal",
							id : "ui-dialog-" + settings.dialog + "-submit-content",
							height : 170,
							width : 300,
							title : "<?php echo $this->translate('text_submit_save_title') ?>",
							content : "<?php echo $this->translate('text_submit_save_content') ?>",
							buttons : {
								"<?php echo $this->translate('text_confirm') ?>" : function() {
									var progressBar = settings.form.progressBar({
										appendto : settings.appendto,
										dialogclass : "ui-dialog-" + settings.dialog + "-validator-loading-modal",
										id : "ui-dialog-" + settings.dialog + "-validator-loading-content",
										position : {
											my: "center", at: "center", of: ($.type($(settings.appendto)) === "object" ? $(settings.appendto) : window)
										},
									});
									form.validator({  
										progressbar: progressBar,
										action: "submit",
									});
									$(".ui-dialog-" + settings.dialog + "-submit-content").dialog("close");
								},
								"<?php echo $this->translate('text_cancel') ?>" : function() {
									$(".ui-dialog-" + settings.dialog + "-submit-content").dialog("close");
								},
							},
							position : {
								my: "center", at: "center", of: ($.type($(settings.appendto)) === "object" ? $(settings.appendto) : window)
							},
						});
					}else{
						form.validator({  
							progressbar: progressBar,
							action: "submit",
						});
					}
				},
				cancel: function(options){
					var s = $.extend(true, settings.submit, options);
					var module = settings.id.toLowerCase();
					if(s.confirmation === true){
						var progressBar = settings.form.progressBar({
							appendto : settings.appendto,
							dialogclass : "ui-dialog-" + settings.dialog + "-cancel-loading-modal",
							id : "ui-dialog-" + settings.dialog + "-cancel-loading-content",
							position : {
								my: "center", at: "center", of: ($.type($(settings.appendto)) === "object" ? $(settings.appendto) : window)
							},
						});
						settings.form.modal({
							appendto : settings.appendto,
							progressbar: progressBar,
							dialogclass : "ui-dialog-" + settings.dialog + "-cancel-modal",
							id : "ui-dialog-" + settings.dialog + "-cancel-content",
							height : 170,
							width : 300,
							title : "<?php echo $this->translate('text_cancel_save_title') ?>",
							content : "<?php echo $this->translate('text_cancel_save_content') ?>",
							buttons : {
								"<?php echo $this->translate('text_confirm') ?>" : function() {
									form.destroyModal();
									$(".ui-dialog-" + settings.dialog + "-cancel-content").dialog("close");
								},
								"<?php echo $this->translate('text_cancel') ?>" : function() {
									$(".ui-dialog-" + settings.dialog + "-cancel-content").dialog("close");
								},
							},
							position : {
								my: "center", at: "center", of: ($.type($(settings.appendto)) === "object" ? $(settings.appendto) : window)
							},
						});
					}
				},
				toserialize: function(store){
					if(store === true){
						var data = {};
					}else{
						var data = [];
					}
					settings.form.find("div.row").each(function() {
						$(this).find(":input").not(':button, :submit, :reset').each(function() {
							var elementclass = $(this).prop('class');
							var elementid = $(this).prop('id');
							var elementname = $(this).prop('name');
							var elementvalue = $(this).val();
							var variable = "";
							switch(elementclass){
								case'htmleditor':
									variable = elementname + "=" + tinymce.get(elementid).getContent();
								break;
								default:
									variable = $(this).serialize();
								break;
							}
							if(store === true){
							    var val = {};
							    var substr = variable.substring(0, elementid.length);
							    if(elementid === substr){
							    	variable = variable.substring((elementid.length + 1), variable.length);
							    }
							    val[elementid]  = variable;
							    data = $.extend(true, data, val);
							}else{
								data.push(variable);
							}
						});
					});
					settings.form.find("div.hidden").each(function() {
						$(this).find(":input").not(':button, :submit, :reset').each(function() {
							var elementid = $(this).prop('id');
							variable = $(this).serialize();
							if(store === true){
							    var val = {};
							    var substr = variable.substring(0, elementid.length);
							    if(elementid === substr){
							    	variable = variable.substring((elementid.length + 1), variable.length);
							    }
							    val[elementid]  = variable;
							    data = $.extend(true, data, val);
							}else{
								data.push(variable);
							}
						});
					});
					if(store === true){
						return data;
					}else{
						var query = data.join("&").toString();
						query = query.replace( /\&\&/gi, "&" );
						return query;
					}
				},
				getOptions: function(){
					return settings;
				},
				destroyModal: function(){
					if($.type(settings.dialog) === 'string' && settings.dialog.length > 0) {
						$(".ui-dialog-" + settings.dialog + "-content").dialog( "close" );
					}
				},
				submitButton: function(options){
					if($.type(settings.submit.button) === 'string' && settings.id.length > 0) {
						if(settings.usemodalbutton === true){
							settings.submit.button = $(".ui-dialog-" + settings.dialog + "-modal .ui-dialog-buttonpane .ui-dialog-buttonset button#" + settings.submit.button);
						}else{
							settings.submit.button = $("form#" + settings.id + " div.button button#" + settings.submit.button);
						}
					}
					if($.type(settings.submit.button) === "object"){
						return settings.submit.button.button(options);
					}
				},
				cancelButton: function(options){
					if($.type(settings.cancel.button) === 'string' && settings.id.length > 0) {
						if(settings.usemodalbutton === true){
							settings.cancel.button = $(".ui-dialog-" + settings.dialog + "-modal .ui-dialog-buttonpane .ui-dialog-buttonset button#" + settings.cancel.button);
						}else{
							settings.cancel.button = $("form#" + settings.id + " div.button button#" + settings.cancel.button);
						}
					}
					if($.type(settings.cancel.button) === "object"){
						return settings.cancel.button.button(options);
					}
				},
		};
		form.init();
		return form;
	}
})( jQuery );