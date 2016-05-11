(function( $ ){
	var modalhandler = {
			total : 0,
			process : {},
			link : {},	
			stored: [],
	};
	$.fn.TaskModal = function(options) {
		var defaultoptions = {
				element : this,
				appendto : "body",
				id : "",
				
				progressbar : "",
				url : "", 
				data : "", 
				callback : "", 
				action : "get", 
				type : "json",

				height : "",
				width : "",
				title : "",
				content : "",
				buttons : {
					"<?php echo $this->translate('text_button_cancel') ?>" : {
						"id": "<?php echo strtolower( $this->translate('text_button_cancel') ); ?>",
						"class": "<?php echo strtolower( $this->translate('text_button_cancel') ); ?>",
						"text": "<?php echo $this->translate('text_button_cancel'); ?>",
						"click": function(){
						},
					},
					"<?php echo $this->translate('text_button_submit') ?>" : {
						"id": "<?php echo strtolower( $this->translate('text_button_submit') ); ?>",
						"class": "<?php echo strtolower( $this->translate('text_button_submit') ); ?>",
						"text": "<?php echo $this->translate('text_button_submit'); ?>",
						"click": function(){
						},
					},
				},
				resizable : false,
				modal : false,
				closeonescape : false,
				draggable : false,
				open : "",
				beforeopen : "",
				close : "",
				position : "",
				disableresize : false,
				form: {
					usemodalbutton : false,
					disableplugin : false,
					submit: {
						confirmation : true,
					}
				},
				disablescreenshot : false,
		}
		options = $.extend(true, defaultoptions, options);
		
		var initStatus = true;
		if($.type(options.element) === "object" && options.element.context === undefined){
			initStatus = false;
		}
		
		var instance = {
			getWidth : function(){
				var appendto = $(options.appendto);
				var width = appendto.innerWidth();
				if(system !== undefined && system.modal !== undefined && system.modal.width !== undefined){
					width = system.modal.width;
				}
				return width;
			},
			getHeight : function(){
				var appendto = $(options.appendto);
				var height = appendto.innerHeight();
				if(system !== undefined && system.modal !== undefined && system.modal.height !== undefined){
					height = system.modal.height;
				}
				return height;
			},
			uniqueid : function(){
				var genrandom = {	
					job: function(){
						var newId = Date.now().toString().substr(6); // or use any method that you want to achieve this string
							
						if( !this.check(newId) ){
							modalhandler.stored.push(newId);
							return newId;
						}
						return this.job();
					},
					check: function(id){
						for( var i = 0; i < modalhandler.stored.length; i++ ){
							if( modalhandler.stored[i] == id ) return true;
						}
						return false;
					},
				};
				
				return genrandom.job();
			},
			islinkelement: function(){
				var currentElement = options.element;
				var status = false;
				if($.type(currentElement) === "object" && currentElement.context !== undefined){
					if($.type( currentElement ) === "object"){
						if(currentElement.is("a")){
							status = true;
						}
					}
				}
				return status;
			},
			id: function(){
				if(options.id === undefined || $.type( options.id ) !== "string" || options.id.length <= 0){
					options.id = instance.uniqueid();
				}
				return options.id;
			},
			link: function(element){
				var currentElement = options.element;
				
				if(element !== undefined && $.type( element ) === "object"){
					currentElement = element;
				}

				if(instance.islinkelement()){
					var handler = instance.handler();
					
					var href = currentElement.attr("href");
	
					var uniqueid = instance.uniqueid();
					
				    var val = {};
				    val[uniqueid] = href;
				    handler.link = $.extend(true, handler.link, val);
				    options.id = uniqueid;
				    
					if($.type( currentElement ) === "object"){
						currentElement.attr("modal-data-id", uniqueid);
					}
					
				    currentElement.removeAttr("href");
				}
			},
			href: function(id){
				var url = "";
				var handler = instance.handler();
				if(handler.link[id] !== undefined && handler.link[id].length > 0){
					url = handler.link[id];
				}
				return url;
			},
			resize: function(id){
				var handler = instance.handler();
				$.each( handler.process, function( key, value ) {
					var status = false;
					if(s.uniqueid !== undefined && s.uniqueid.length > 0){
						if(value.id === id){
							status = true;
						}
					}else{
						status = true;
					}
					if(status === true && value.disableresize === false){
						$(value.id).css({
							"height" : instance.getHeight() - 6,
							"width" : instance.getWidth() - 6,
							"top" : 0,
							"left" : 0,
						});
						$(value.id).children(".ui-dialog-content").css({
							"height" :  instance.getHeight() - $(value.id).children(".ui-dialog-titlebar").outerHeight() - $(value.id).children(".ui-dialog-buttonpane").outerHeight() - 16,
						});
						
						if($.type( value.form ) === "object"){
							value.form.resize();
						}
					}
				});
			},
			add: function(options){
				var defaultOptions = {
						modal : "",
						id : "",
						title : "",
						url : "",
						dialog : "",
						link : "",
						uniqueid : "",
						form : "",
						disableformplugin: false,
						disableresize : false,
				}
				var s = $.extend(true, defaultOptions, options);

				if(s.uniqueid === undefined || s.uniqueid.length <= 0){
					s.uniqueid = instance.uniqueid();
				}

				var handler = instance.handler();
				if($.type( s.modal ) === "object"){
				    var val = {};
				    val[s.uniqueid]  = s;
				    handler.process = $.extend(true, handler.process, val);
				    handler.total = handler.total + 1;
				}

				instance.focus(s.uniqueid);
				
				instance.resize(s.id);
				
			    return s.uniqueid;
			},
			focus: function(id){
				var handler = instance.handler();
				$.each( handler.process, function( key, value ) {
					if(id === key && handler.process[id] !== undefined){
						handler.process[id].modal.show();
						instance.resize(id);

						if(options.disablescreenshot === false){
							//$( handler.process[id].id + "-screenshot" ).hide();
						}
					}else{
						instance.blur(key);
					}
				});
			},
			blur: function(id){
			},
			hideall: function(){
				var handler = instance.handler();
				$.each( handler.process, function( key, value ) {
					handler.process[key].modal.hide();
				});
			},
			remove: function(id){
				var handler = instance.handler();
				delete handler.process[id];
			    handler.total = handler.total - 1;
			    if(handler.total > 0){
			    	var count = 0;
					$.each( handler.process, function( key, value ) {
						count++;
						if(count === handler.total){
							instance.focus(key);
						}
					});
			    }
			},
			closeAll: function(){
				var handler = instance.handler();
				$.each( handler.process, function( key, value ) {
					value['modal'].remove(); 
					delete handler.process[id];
				});
			},
			check: function(id, done, fail){
				var status = false;
				var handler = instance.handler();
				if(id !== undefined && id.length > 0){
					$.each( handler.process, function( key, value ) {
						  value['modal'] = $(value['id']);
						  if(id === key){
							  if(value['modal'].html() === undefined){
								  instance.remove(id);
								  status = false;
							  }else{
								  status = true;
							  }
						  }
					});
				}
				return status;
			},
			handler: function(){
				return modalhandler;
			},
			create: function(options) {
				s = $.extend(true, defaultoptions, options);
				s.id = instance.id();

				if(s.height === undefined || s.height.length <= 0){
					s.height = instance.getHeight() - 6;
				}

				if(s.width === undefined || s.width.length <= 0){
					s.width = instance.getWidth() - 6;
				}
				
				if ( $.type( s.url ) !== "string" || ( $.type( s.url ) === "string" && s.url.length <= 0 )){
					if(instance.islinkelement()){
						s.url = instance.href(s.id);
					}
				}
				
				instance.hideall();
				if(instance.check(s.id) === true){
					s.url = "";
					instance.focus(s.id);
				}

				if($.type( s.progressbar ) === "object"){
					s.progressbar.destroy();
				}
				
				var modal;
				if ( $.type( s.url ) === "string" && s.url.length > 0 ){
					$(this).evalCallback(s.beforeopen);
					
					var desktop = $(s.appendto);
					var id = s.element.attr("id");
					var progressBar = desktop.progressBar({
						appendto : s.appendto,
						dialogclass : "ui-dialog-" + id + "-loading-modal",
						id : "ui-dialog-" + id + "-loading-content",
					});
					
					$(this).ajaxSubmit(
							s.url,
							s.data,
							s.action,
							s.type
					).done(function(JSONReturn) {
						var content = JSONReturn.content;
						var dialogid = JSONReturn.dialog;
						var formid = JSONReturn.form;
						var title = JSONReturn.title;
						var javascript = JSONReturn.javascript;
						var css = JSONReturn.css;
						var initCallback = JSONReturn.callback.init;
						s.title = title;
						s.content = content;
						$(this).loadCSS(css, function() {
							$(this).loadJavascript(javascript, function() {
								if(dialogid === undefined || dialogid.length <= 0){
									dialogid = s.id;
								}

								if(s.position === ""){
									s.position = {
										my: "left top", at: "left top", of: desktop
									}
								}
								
								modal = $(this).modal({
									appendto : s.appendto,
									progressbar: progressBar,
									dialogclass: "ui-dialog-" + dialogid + "-modal",
									id: "ui-dialog-" + dialogid + "-content",
									height : s.height,
									width : s.width,
									title : s.title,
									content : s.content,
									buttons : s.buttons,
									resizable : s.resizable,
									modal : s.modal,
									closeonescape : s.closeonescape,
									draggable : s.draggable,
									open: function(){ 
										
										var formplugin = "";
										if(s.form.disableplugin === false){
											if ( $.isFunction( $.fn.form ) && formid !== undefined) {
												formoption = $.extend(true, s.form, {
													appendto: ".ui-dialog-" + dialogid + "-modal", 
													dialog : dialogid, 
												});
												formplugin = $("form#" + formid).form(formoption);
											};
										};
										
										instance.add({
											modal : $(".ui-dialog-" + dialogid + "-modal"),
											id : ".ui-dialog-" + dialogid + "-modal",
											title : s.title,
											url : s.url,
											dialog : dialogid,
											link : s.link,
											uniqueid : s.id,
											form : formplugin,
											disableformplugin: s.form.disableplugin,
											disableresize: s.disableresize,
										});
										
										if(s.disablescreenshot === false){
											$(".ui-dialog-" + dialogid + "-modal").captureScreen({
												renderElement : $(".ui-layout-inner-south"),
												saveimage : true,
												url : {
													upload : "<?php echo $this->serverUrl( $this->baseHref () . '/Image/Upload/' ); ?>",
													display : "<?php echo $this->serverUrl( $this->baseHref () . '/Image/Display/screenshot/' ); ?>",
												},
												filename : "ui-dialog-" + dialogid + "-modal",
												attr : {
													id : "ui-dialog-" + dialogid + "-modal-screenshot",
													class : "ui-dialog-" + dialogid + "-modal-screenshot",
													height: ($(".ui-layout-inner-south").innerHeight() - 20),
													width: 70,
													title : s.title,
													"modal-id" : s.id,
												},
												done: function(){
													id = $(".ui-layout-inner-south").find("img.ui-dialog-" + dialogid + "-modal-screenshot").attr("modal-id");
													$(this).evalCallback(instance.focus(id));
													//$( ".ui-dialog-" + dialogid + "-modal-screenshot" ).hide();
												}
											});
										}
								        
										$(this).evalCallback(s.open);

										$(this).evalCallback(initCallback);
										
									},
									focus: function(){ 
										$(this).evalCallback(instance.focus(s.id));
									},
									blur: function(){
										$(this).evalCallback(instance.blur(s.id));
									},
									close : function(){ 
										$(this).evalCallback(instance.remove(s.id));
										$(".ui-layout-inner-south").windowManage().remove("ui-dialog-" + dialogid + "-modal-screenshot");
										$(this).evalCallback(s.close);
									},
									position : s.position,
								});
							});
						});
					});
				}
				s = $.extend(true, s, {dialog : modal});
				return modal;
			},
			init: function(){
				instance.link();
				if(instance.islinkelement()){
					options.element.click(function(){
						instance.create();
					});
				}
			}
		};
		if(initStatus === true){
			instance.init();
		}
		instance = $.extend(true, instance, { options : options });
		return instance;
	}
	
	$.fn.LoginModal = function(options) {
		var settings = {
				element : this,
				progressbar : "",
		};
		settings = $.extend(true, settings, options);

		var modal = settings.element.TaskModal({
				appendto: "body",
				progressbar : settings.progressbar,
				url : "<?php echo $this->url('Account/Login', array('action' => 'Index')); ?>",
				height : 270,
				width : 210,
				modal: true,
				disableresize: true,
				disablescreenshot: true,
				form: {
					usemodalbutton: true,
					submit: {
						confirmation : false,
					}
				},
				open: function(event, ui) {
					$(".ui-dialog-account-login-index-modal").css({
						"height" : "260px",
						"width" : "210px",
					});
					var logoWidth = $( ".ui-dialog-account-login-index-modal .ui-dialog-titlebar" ).innerWidth();
					var dialogWidth = $( ".ui-dialog-account-login-index-modal" ).innerWidth();
					$(".ui-dialog-account-login-index-modal .ui-dialog-titlebar").css({
						"left": ((dialogWidth - logoWidth) / 2),
					});
				},
				position : {
					my: "center", at: "center", of: $("body")
				},
		}).create();

		return modal;
	}
})( jQuery );