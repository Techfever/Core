(function( $ ){
	$.fn.captureScreen = function (options) {  
		var settings = {
			element : this,
			renderElement : this,
			saveimage : false,
			url : {
				upload : "",
				display : "",
			},
			filename : "",
			attr : {
				id : "",
				class : "",
				height : "",
				width : "",
				title : "",
			},
			done: function(){
				
			}
		};
		// set up the options using the defaults
		settings = $.extend(true, settings, options);
		var instance = {
				init: function(){
					if($.type(settings.renderElement) !== "object"){
						settings.renderElement = $(settings.renderElement);
					}
					if($.type(settings.element) !== "object"){
						settings.element = $(settings.element);
					}
					if ( $.isFunction( screenshot )){
						screenshot(settings.element, {
				            onrendered: function(canvas) {
				            	var img = canvas.toDataURL();
				            	var attribute = {
				        				id : settings.attr.id,
				        				class : settings.attr.class,
				            	};
				            	
								var div = $( '<div></div>' ).attr(attribute).addClass("ui-screenshot-content ui-widget-content").show();
								
								var span = $( '<span></span>' ).attr(attribute).html(settings.attr.title).addClass("ui-screenshot-title");
								
								var image = $( '<img>' ).attr(settings.attr).addClass("ui-screenshot-image");
								
								div.append(image);
								div.append(span);	
								settings.renderElement.append(div);

								div.css({
									height: (image.height() + span.height())
								});
								
				            	if(settings.saveimage === true){
					            	$.post(
											settings.url.upload, 
											{ data : img, screenshot: true, filename: settings.filename, },
											null, "json"
									).done(function(ajaxReturn) {
										if (ajaxReturn.success == true && ajaxReturn.valid == true) {
											$("img#" + settings.attr.id).attr({
												src: settings.url.display + ajaxReturn.file,
											});
										}
									});
				            	}else{
									$("img#" + settings.attr.id).attr({
										src: img,
									});
				            	}
								if ( $.isFunction( settings.done ) ) {
									$("div#" + settings.attr.id).on("click", function(){
										$(this).evalCallback(settings.done);
									});
								}
								settings.renderElement.windowManage().resize();
				            }
				        });
					}
				},
		};
		instance.init();
		
		instance = $.extend(true, instance, { settings : settings });
		return instance;
	}
	$.fn.windowManage = function (options) {  
		var settings = {
				element : this,
				screenshot : {
					element : {},
					total : 0,
				},
				
		};
		// set up the options using the defaults
		settings = $.extend(true, settings, options);
		var instance = {
				resize: function(){
					var totalwidth = 0;
					var status = false;
					$.each(settings.screenshot.element, function(key, value){
						var visible = value['element'].is(":visible");
						if(visible){
							status = true;
						}
						if(status === true){
							value['element'].removeClass("ui-hide").addClass("ui-show").show();
							totalwidth = totalwidth + value['element'].outerWidth() + 10;
							areawidth = settings.element.innerWidth() - 40;
							if(totalwidth >= areawidth){
								value['element'].removeClass("ui-show").addClass("ui-hide").hide();
							}
						}
					});
				},
				remove: function(id){
					settings.element.find("." + id).remove();
				},
				back: function(){
					if(settings.element.find(".ui-screenshot-content.ui-show").first().prev().hasClass("ui-hide")){
						settings.element.find(".ui-screenshot-content.ui-show").last().removeClass("ui-show").addClass("ui-hide").hide();
						settings.element.find(".ui-screenshot-content.ui-show").first().prev().removeClass("ui-hide").addClass("ui-show").show();
					}
				},	
				next: function(){
					if(settings.element.find(".ui-screenshot-content.ui-show").last().next().hasClass("ui-hide")){
						settings.element.find(".ui-screenshot-content.ui-show").first().removeClass("ui-show").addClass("ui-hide").hide();
						settings.element.find(".ui-screenshot-content.ui-show").last().next().removeClass("ui-hide").addClass("ui-show").show();
					}
				},
				init: function(){
					var count = 0;
					var status = false;
					$(settings.element.find(".ui-screenshot-content").get().reverse()).each(function() {
						var element = $(this);
					    var val = {};
					    count++;
					    val[count]  = {
					    		element: element,
					    		id: element.prop("id"),
					    };
					    settings.screenshot.total = count;
					    settings.screenshot.element = $.extend(true, settings.screenshot.element, val);
					});
				},
		};
		instance.init();

		$(window).bind("resize", function() {
			instance.resize();
		});
		
		instance = $.extend(true, instance, { settings : settings });
		return instance;
	}
})( jQuery );