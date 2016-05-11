(function( $ ){
	$.fn.step = function(options) {
		var defaultoptions = {
				element : this,
				appendto : "body",
				dialog : "",
				id : "",
				wizardid : "",
				disableresize : false,
				submit : function(){
					
				},
				cancel : function(){
					
				},
				validator : function(){
					
				},
				modal: {
					element : "",
					id : "",
					exist: false,
				},
				form: {
					element : "",
					id : "",
					exist: false,
				},
				button: {
					element : "",
				}
		};
		var settings = $.extend(true, defaultoptions, options);
		var currentstep = 1;
		var totalstep = 0;
		var steps = new Array();
		var instance = {
				modal: {
					init: function(){
						var element = settings.element.parents('form.ui-form').parents('div.ui-dialog');
						settings.modal.element = element;
						if(element !== undefined && element.length >= 1){
							settings.modal.exist = true;
							var id = element.attr("aria-describedby");
							if($.type(id) === "string" && length > 0){
								settings.modal.id = id;
								instance.modal.removeButton();
							}
						}
					},
					removeButton: function(){
						if(settings.modal.exist === true && settings.modal.element.children(".ui-dialog-buttonpane").children(".ui-dialog-buttonset").length > 0){
							settings.modal.element.children(".ui-dialog-buttonpane").children(".ui-dialog-buttonset").html("");
						}
					},
				},
				form: {
					init: function(){
						var element = settings.element.parents('form.ui-form');
						settings.form.element = element;
						if(element !== undefined && element.length >= 1){
							settings.form.exist = true;
							var id = element.prop("id");
							if($.type(id) === "string" && id.length > 0){
								settings.form.id = id;
								instance.form.removeButton();
							}
						}
					},
					removeButton: function(){
						if(settings.form.exist === true && settings.form.element.find( "div.button" ).length > 0){
							settings.form.element.find( "div.button" ).html("");
						}
					}
				},
				createButton: function(){
					instance.modal.init();
					instance.form.init();

					var buttonElement = {
							"<?php echo strtolower( $this->translate('text_button_back') ); ?>" : {
								"click": function(){
									instance.back();
								},
							},
							"<?php echo strtolower( $this->translate('text_button_next') ); ?>" : {
								"click": function(){
									instance.next();
								},
							},
							"<?php echo strtolower( $this->translate('text_button_preview') ); ?>" : {
								"click": function(){
									instance.preview();
								},
							},
							"<?php echo strtolower( $this->translate('text_button_submit') ); ?>" : {
								"click": function(){
									instance.submit();
								},
							},
							"<?php echo strtolower( $this->translate('text_button_cancel') ); ?>" : {
								"click": function(){
									instance.cancel();
								},
							},
					}
					
					var buttonHTML;
					if(settings.form.exist === true && settings.modal.exist === false){
						buttonHTML = settings.form.element.find( "div.button" );
					}else if(settings.modal.exist === true){
						buttonHTML = settings.modal.element.children(".ui-dialog-buttonpane").children(".ui-dialog-buttonset");
					}
					if(buttonHTML !== undefined && $.type(buttonHTML) === "object"){
						$.each(buttonElement, function(key, props) {
							props = $.extend( { 
								type: "button", 
								"id": key,
								"class": key,
								"text": key,
							}, props );
							$( "<button></button>", props )
								.button()
								.appendTo( buttonHTML );
						});
					}
					settings.button.element = buttonHTML;
					
					settings.button.element.find("button#back").button("disable");
					settings.button.element.find("button#back").hide();
					
					settings.button.element.find("button#next").button("disable");
					settings.button.element.find("button#next").hide();
					
					settings.button.element.find("button#preview").button("disable");
					settings.button.element.find("button#preview").hide();
					
					settings.button.element.find("button#submit").button("disable");
					settings.button.element.find("button#submit").hide();
					
					settings.button.element.find("button#cancel").button("disable");
					settings.button.element.find("button#cancel").hide();

					if(totalstep === 1){
						settings.button.element.find("button#submit").show();
						
						settings.button.element.find("button#cancel").button("enable");
						settings.button.element.find("button#cancel").show();
					}else if(totalstep > 1){
						settings.button.element.find("button#back").show();
						
						settings.button.element.find("button#next").button("enable");
						settings.button.element.find("button#next").show();
					}else{
						settings.button.element.find("button#cancel").button("enable");
						settings.button.element.find("button#cancel").show();
					}
				},
				next: function(){
					if(currentstep < (totalstep - 1) && totalstep > 1){
						var progressBar = settings.element.progressBar({
							appendto : settings.appendto,
							dialogclass : "ui-dialog-" + settings.dialog + "-validator-loading-modal",
							id : "ui-dialog-" + settings.dialog + "-validator-loading-content",
							position : {
								my: "center", at: "center", of: ($.type($(settings.appendto)) === "object" ? $(settings.appendto) : window)
							},
						});
						
						var inputDeferred = $.Deferred();
						var inputElement = $(settings.wizardid + " div.ui-step-content div.ui-step-contentcontrol#" + steps[currentstep] ).find( "div[type=input]" );
						inputElement.each(function() {
							var id = $(this).attr('id');
							settings.validator({  
								progressbar: progressBar,
								input: id,
								action: "validate",
								done: function(){
									inputDeferred.resolve();
								},
								fail: function(){
									inputDeferred.resolve();
								}
							});
						});
						
						inputDeferred.done(function(){
							var checkDeferred = $.Deferred();
							var inputValid = true;
							var inputTotal = inputElement.length;
							var inputCount = 0;
							setTimeout(function(){
								inputElement.each(function() {
									var inputHelp = $(this).children( "div.help").hasClass("ui-form-validator-error");
									if(inputHelp){
										inputValid = false;
									}
									inputCount++;
									if(inputTotal === inputCount && inputValid === true){
										checkDeferred.resolve();
									}
								})
							}, 1000);
							checkDeferred.done(function(){
								currentstep++;
								$(settings.wizardid + " div.ui-step-tab").find("div.ui-step-tabcontrol.ui-state-active").removeClass("ui-state-active");
								$(settings.wizardid + " div.ui-step-content div.ui-step-contentcontrol").hide();
								
								$(settings.wizardid + " div.ui-step-tab div.ui-step-tabcontrol#" + steps[currentstep]).addClass("ui-state-active");
								$(settings.wizardid + " div.ui-step-content div.ui-step-contentcontrol#" + steps[currentstep] ).show();
								
								if(currentstep > 1){
									settings.button.element.find("button#back").button("enable");
									if(currentstep === (totalstep - 1)){
										settings.button.element.find("button#next").button("disable");
										settings.button.element.find("button#next").hide();
	
										settings.button.element.find("button#preview").button("enable");
										settings.button.element.find("button#preview").show();
									}
								}
							});
						});
					}
				},
				back: function(){
					if(currentstep > 1 && totalstep > 1){
						currentstep--;
						$(settings.wizardid + " div.ui-step-tab").find("div.ui-step-tabcontrol.ui-state-active").removeClass("ui-state-active");
						$(settings.wizardid + " div.ui-step-content div.ui-step-contentcontrol").hide();
						
						$(settings.wizardid + " div.ui-step-tab div.ui-step-tabcontrol#" + steps[currentstep]).addClass("ui-state-active");
						$(settings.wizardid + " div.ui-step-content div.ui-step-contentcontrol#" + steps[currentstep] ).show();

						if(currentstep === 1){
							settings.button.element.find("button#back").button("disable");
							
							settings.button.element.find("button#next").button("enable");
							settings.button.element.find("button#next").show();
							
							settings.button.element.find("button#preview").button("disable");
							settings.button.element.find("button#preview").hide();
							
							settings.button.element.find("button#submit").button("disable");
							settings.button.element.find("button#submit").hide();
							
							settings.button.element.find("button#cancel").button("disable");
							settings.button.element.find("button#cancel").hide();
						}
						
						if(currentstep < totalstep){
							settings.button.element.find("button#submit").button("disable");
							settings.button.element.find("button#submit").hide();
							if(currentstep === (totalstep - 1)){
								settings.button.element.find("button#preview").button("enable");
								settings.button.element.find("button#preview").show();
							}
						}
					}
				},
				preview: function(){
					if(currentstep === (totalstep - 1)){
						currentstep++;
						$(settings.wizardid + " div.ui-step-tab").find("div.ui-step-tabcontrol.ui-state-active").removeClass("ui-state-active");
						$(settings.wizardid + " div.ui-step-content div.ui-step-contentcontrol").hide();
						
						$(settings.wizardid + " div.ui-step-tab div.ui-step-tabcontrol#" + steps[currentstep]).addClass("ui-state-active");
						$(settings.wizardid + " div.ui-step-content div.ui-step-contentcontrol#" + steps[currentstep] ).show();

						settings.button.element.find("button#next").button("disable");
						settings.button.element.find("button#next").hide();
						
						settings.button.element.find("button#preview").button("disable");
						settings.button.element.find("button#preview").hide();
						
						settings.button.element.find("button#submit").button("enable");
						settings.button.element.find("button#submit").show();
					}
				},
				submit: function(){
					if(currentstep === totalstep){
						if(settings.submit !== undefined && $.type(settings.submit) === "function"){
							$(this).evalCallback(settings.submit);
						}
					}
				},
				cancel: function(){
					if(settings.cancel !== undefined && $.type(settings.cancel) === "function"){
						$(this).evalCallback(settings.cancel);
					}
				},
				resize: function(id){
					if(settings.disableresize === false){
						var tabcontrol = $(settings.wizardid + " div.ui-step-tab").find( "div.ui-step-tabcontrol" ).not(".ui-state-active");
						if((system.layout.inner.width < system.layout.min.width) || $(this).isMobile().any()){
							tabcontrol.children("#ui-step-tab-name").hide();
						}else{
							tabcontrol.children("#ui-step-tab-name").show();
						}
					}
				},
				init: function(){
					var tabDeferred = $.Deferred();
					if($.type(settings.element) === 'object') {
						if($.type(settings.id) !== 'string' || settings.id.length < 1) {
							settings.id = settings.element.prop("id");
						}
					}
					if($.type(settings.id) == 'string' && settings.id.length > 0) {
						settings.wizardid = "div#" + settings.id;
					}
					
					var tabcontrol = $(settings.wizardid + " div.ui-step-tab").find( "div.ui-step-tabcontrol" );
					totalstep = tabcontrol.length;
					var tabTotal = totalstep;
					var tabCount = 0;
					tabcontrol.each(function() {
						var id = $(this).attr('id');
						steps[(tabCount + 1)] = id;
						var tab = $(this).html();
						$(this).addClass("ui-state-default ui-corner-all").html((tabCount + 1) + '. <span id="ui-step-tab-name">' + tab + '</span>');
						tabCount++;
						if(tabTotal === tabCount){
							tabDeferred.resolve();
						}
					});

					var stepDeferred = $.Deferred();
					tabDeferred.done(function(){
						var stepcontrol = $(settings.wizardid + " div.ui-step-content").find( "div.ui-step-contentcontrol" );
						var stepTotal = stepcontrol.length;
						var stepCount = 0;
						stepcontrol.each(function() {
							$(this).hide();
							$(this).addClass("ui-widget-content ui-corner-all");
							stepCount++;
							if(stepTotal === stepCount){
								stepDeferred.resolve();
							}
						});
						
						$(settings.wizardid + " div.ui-step-tab div.ui-step-tabcontrol#" + steps[currentstep]).addClass("ui-state-active");
						$(settings.wizardid + " div.ui-step-content div.ui-step-contentcontrol#" + steps[currentstep] ).show();
					});
					
					stepDeferred.done(function(){
						instance.createButton();
					});
					
					instance.resize();
				},
		}
		instance.init();
		
		instance = $.extend(true, instance, { settings : settings });
		return instance;
	}
})( jQuery );