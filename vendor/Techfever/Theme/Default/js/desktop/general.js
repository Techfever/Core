(function( $ ){
	var loading = {
			css: {},
			javascript: {},
	};
	
	$.fn.evalCallback = function(callback) {
		if(callback !== undefined){
			if ($.type (callback) === "function") {
				callback();
			}else{
				eval(callback);
			}
		}
	}
	
	$.fn.isMobile = function() {
		var instance = {
				Android: function (){
					return navigator.userAgent.match(/Android/i);
				},
				BlackBerry: function (){
					return navigator.userAgent.match(/BlackBerry/i);
				},
				iOS: function (){
					return navigator.userAgent.match(/iOS/i);
				},
				Opera: function (){
					return navigator.userAgent.match(/Opera Mini/i);
				},
				Windows: function (){
					return navigator.userAgent.match(/IEMobile/i);
				},
				any: function (){
					return (instance.Android() || instance.BlackBerry() || instance.iOS() || instance.Opera() || instance.Windows());
				},
		};
		return instance;
	}

	$.fn.loadCSS = function(cssPath, callback) {
		var jqueryreturn;
		var result = $.Deferred();
		if(cssPath !== null && cssPath !== undefined && cssPath.length > 0){
			var cssPathArray = [];
			if ( $.type( cssPath ) === "string" ) {
				cssPathArray[0] = cssPath;
			}else if ($.isArray(cssPath)) {
				cssPathArray = cssPath;
			}
			var totalCSS = cssPathArray.length;
			var countCSS = 1;
			$.each(cssPathArray, function(key, value) {
				var cssPathUrl = "<?php echo $this->url('Theme/CSS', array('referral' => '')); ?>/" + value;
				if(loading.css[cssPathUrl] === undefined){
					jQuery.ajax({
						url: cssPathUrl,
						dataType: "text",
						cache: true,
					}).done(function(data){
						var cssURL = cssPathUrl
						$('<style type="text/css">\n' + data + '</style>').appendTo('head');
					    var cssRaw = {};
					    cssRaw[cssURL] = "True";
					    loading.css = $.extend(true, loading.css, cssRaw);
						if(countCSS >= totalCSS){
							result.resolve();
						}
						countCSS++;
					});
				}else{
					if(countCSS >= totalCSS){
						result.resolve();
					}
					countCSS++;
				}
			});
		}else{
			result.resolve();
		}
		result.done(function(){
			$(this).evalCallback(callback);
		});
	}

	$.fn.loadJavascript = function(jsPath, callback) {
		var jqueryreturn;
		var result = $.Deferred();
		if(jsPath !== null && jsPath !== undefined && jsPath.length > 0){
			var jsPathArray = [];
			if ( $.type( jsPath ) === "string" ) {
				jsPathArray[0] = jsPath;
			}else if ($.isArray(jsPath)) {
				jsPathArray = jsPath;
			}
			var totalJavascript = jsPathArray.length;
			var countJavascript = 1;
			$.each(jsPathArray, function(key, value) {
				var jsPathUrl = "<?php echo $this->url('Theme/Javascript', array('referral' => '')); ?>/" + value;
				if(loading.javascript[jsPathUrl] === undefined){
					jQuery.ajax({
						url: jsPathUrl,
						dataType: "script",
						cache: true,
					}).done(function(data){
						var jsURL = jsPathUrl;
						var javascriptRaw = {};
						javascriptRaw[jsURL] = "True";
						loading.javascript = $.extend(true, loading.javascript, javascriptRaw);
						if(countJavascript >= totalJavascript){
							result.resolve();
						}
						countJavascript++;
					});
				}else{
					if(countJavascript >= totalJavascript){
						result.resolve();
					}
					countJavascript++;
				}
			});
		}else{
			result.resolve();
		}
		result.done(function(){
			$(this).evalCallback(callback);
		});
	}
	
	$.fn.ajaxQuery = function(url, data, action, type) {
		if(action == undefined){
			action = 'post'
		}
		action = action.toUpperCase();
		if(type == undefined){
			type = 'json'
		}
		var jsonreturn = $.ajax({
			url: url,
			type: action,
			dataType: type,
			data: data + '&XMLHttpRequest=1'
		});
		return jsonreturn;
	};
	
	$.fn.pageRedirect = function(redirect) {
		if ( redirect && redirect.length > 0 ) {
			window.location.replace(redirect);
		}
	};
	
	$.fn.routeMatch = function() {
		var url = '<?php echo $this->serverUrl($this->baseHref()); ?>/';
	    var loc = window.location;
	    var pathName = loc.pathname.substring(0, loc.pathname.lastIndexOf('/') + 1);
	    return loc.href.substring(url.length, loc.href.length);
	};

	$.fn.progressBar = function(options) {
		var settings = {
				appendto : "body",
				element : this,
				dialogclass : "ui-dialog-loading-modal",
				id : "ui-dialog-loading-content",
				height : 85,
				width : 210,
				title : '<?php echo $this->translate("text_loading") ?>',
				content : '<div id="progressbar"><div class="progress-label"><?php echo $this->translate("text_loading_more") ?></div></div>',
				buttons : "",
				resizable : false,
				modal : true,
				buttons : "",
				closeonescape : false,
				draggable : false,
				open: function(event, ui) {
					$("."+ settings.dialogclass +" .ui-dialog-titlebar-close").hide();
					$("."+ settings.dialogclass +" .ui-dialog-titlebar").hide();
					$("."+ settings.dialogclass +" .ui-dialog-buttonpane").hide();
				},
				close : "",
				position : {
					my: "center", at: "center", of: ($.type(this) === "object" ? this : window)
				},
		}
		settings = $.extend(true, settings, options);

		var modal = settings.element.modal({
			appendto : settings.appendto,
			dialogclass : settings.dialogclass,
			id : settings.id,
			resizable : settings.resizable,
			draggable : settings.draggable,
			modal : settings.modal,
			height : settings.height,
			width : settings.width,
			title : settings.title,
			content : settings.content,
			buttons : settings.buttons,
			closeonescape : settings.closeonescape,
			open : function(){ 
				$(this).evalCallback(settings.open);
			},
			close : function(){ 
				$(this).evalCallback(settings.close);
			},
			focus : "",
			blur : "",
			position : settings.position
		});
		var progressbar = $("#progressbar");
		var progressLabel = $(".progress-label");
		progressbar.progressbar({
			value : false,
			change : function() {
				progressLabel.text(progressbar.progressbar("value") + "%");
			},
			complete : function() {
				progressLabel.text('<?php echo $this->translate("text_complete") ?>');
			}
		});
		
		return modal;
	}

	$.fn.modal = function(options) {
		var settings = {
				appendto : "body",
				element : this,
				progressbar : "",
				dialogclass : "ui-dialog-confirm-modal",
				id : "ui-dialog-confirm-content",
				height : 170,
				width : 300,
				title : "",
				content : "",
				callback : "",
				buttons : {
					"<?php echo $this->translate('text_confirm') ?>" : function() {
						$(this).dialog("destroy");

						if (settings.callback && $.type (settings.callback) === "function") {
							settings.callback();
						}
					},
					"<?php echo $this->translate('text_cancel') ?>" : function() {
						$(this).dialog("destroy");
					},
				},
				resizable : false,
				modal : true,
				buttons : "",
				closeonescape : false,
				draggable : false,
				open : function(){
					
				},
				focus : function(){
					
				},
				blur : function(){
					
				},
				beforeclose : function(){
					
				},
				close : function(){
					
				},
				position : {
					my: "center", at: "center", of: ($.type(this) === "object" ? this : window)
				},
		};
		
		settings = $.extend(true, settings, options);

		if($.type( settings.progressbar ) === "object"){
			settings.progressbar.destroy();
		}
		var modal = $('<div id="' + settings.id + '" class="' + settings.id + '" title=\'' + settings.title + '\'><p>' + settings.content + '</p></div>').dialog({
			resizable : settings.resizable,
			height : settings.height,
			width : settings.width,
			modal : settings.modal,
			buttons : settings.buttons,
			dialogClass : settings.dialogclass,
			closeOnEscape : settings.closeonescape,
			draggable : settings.draggable,
			open : function(){
				var callback = settings.open;
				$(this).evalCallback(callback);
			},
			close : function(){
				var callback = settings.close;
				$(this).evalCallback(callback);
				$( "." + settings.id ).dialog( "destroy" );
			},
			focus : function(){
				var callback = settings.focus;
				$(this).evalCallback(callback);
			},
			beforeClose : function(){
				var callback = settings.beforeclose;
				$(this).evalCallback(callback);
			},
			position : settings.position,
			appendTo : settings.appendto,
		});
		if(settings.modal === true){
			$("." + settings.dialogclass).css('z-index', '201');
			$("." + settings.dialogclass).prev(".ui-widget-overlay.ui-front").css('z-index', '100');
		}
		if(settings.blur !== undefined && $.type (settings.blur) === "function"){
			modal.parent(0).blur(function() {
				var callback = settings.blur;
				$(this).evalCallback(callback);
			});
		}
		settings = $.extend(true, settings, {
			destroy: function(callback){
				$(this).evalCallback(callback);
				$( "." + settings.id ).dialog( "close" );
			},
		});
		return settings;
	}
})( jQuery );