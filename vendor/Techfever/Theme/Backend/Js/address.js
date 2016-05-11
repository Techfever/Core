(function( $ ){
	$.fn.Address = function(options) {
		var defaultoptions = {
				element : this,
				country : "",
				state : "",
		};
		var settings = $.extend(true, defaultoptions, options);
		var instance = {
				country: function(){
					var autocomplete = null;
					if($.type(settings.country) === "string" && settings.country.length > 0 && settings.element.find(":input#" + settings.country) !== undefined){
						autocomplete = settings.element.find(":input#" + settings.country).autocomplete({
							minLength: 0,
							source: function( request, response) {
								$.getJSON( "<?php echo $this->url('Ajax/Address', array('action' => 'getCountry')); ?>" ,{
									country: request.term,
								}, response);
							},
							search: function(){
								settings.element.find(":input#" + settings.state).val("");
								settings.element.find(":input#" + settings.state).prop( "disabled" , true);
								settings.element.find(":input#" + settings.state).autocomplete("disable");
							},
							select: function(event, ui){
								if($.type(settings.state) === "string" && settings.state.length > 0){
									settings.element.find(":input#" + settings.state).prop( "disabled" , false);
									settings.element.find(":input#" + settings.state).autocomplete("enable");
								}
								settings.element.find(":input#" + settings.country).attr("value", ui.item.label);
								settings.element.find(":input#" + settings.country).keypress();
							}
						});
						settings.element.find(":input#" + settings.country).addClass("ui-autocomplete-combobox-input");
						
						var parent = settings.element.find(":input#" + settings.country).parent();
						var wrapper = $( "<div>" ).addClass( "ui-autocomplete-combobox" ).appendTo( parent );
						
						settings.element.find(":input#" + settings.country).appendTo( wrapper ).addClass( "ui-corner-right" );
						
						$( "<a>" ).attr( "tabIndex", -1 ).tooltip().appendTo( wrapper ).button({
				        	icons: {
				                primary: "ui-icon-triangle-1-s"
				        	},
				        	text: false
				        }).removeClass( "ui-corner-all" ).addClass( "ui-autocomplete-combobox-toggle ui-corner-right" ).click(function(){
				        	settings.element.find(":input#" + settings.country).autocomplete( "search" );
				        });
					}
					return autocomplete;
				},
				state: function(){
					var autocomplete = null;
					if($.type(settings.state) === "string" && settings.state.length > 0 && settings.element.find(":input#" + settings.state) !== undefined){
						var country = (($.type(settings.country) === "string" && settings.country.length > 0) ? settings.element.find(":input#" + settings.country).attr("value") : "");
						autocomplete = settings.element.find(":input#" + settings.state).autocomplete({
							minLength: 0,
							source: function( request, response) {
								$.getJSON( "<?php echo $this->url('Ajax/Address', array('action' => 'getState')); ?>" ,{
									state: request.term,
									country: (($.type(settings.country) === "string" && settings.country.length > 0) ? settings.element.find(":input#" + settings.country).attr("value") : ""),
								}, response);
							},
							select: function(event, ui){
								settings.element.find(":input#" + settings.state).attr("value", ui.item.label);
								settings.element.find(":input#" + settings.state).keypress();
							}
						});
						if(country !== undefined && country.length <= 0){
							settings.element.find(":input#" + settings.state).autocomplete("disable");
							settings.element.find(":input#" + settings.state).prop( "disabled" , true);
						}else{
							settings.element.find(":input#" + settings.state).addClass("ui-autocomplete-combobox-input");
							
							var parent = settings.element.find(":input#" + settings.state).parent();
							var wrapper = $( "<div>" ).addClass( "ui-autocomplete-combobox" ).appendTo( parent );
							
							settings.element.find(":input#" + settings.state).appendTo( wrapper ).addClass( "ui-corner-right" );
							
							$( "<a>" ).attr( "tabIndex", -1 ).tooltip().appendTo( wrapper ).button({
					        	icons: {
					                primary: "ui-icon-triangle-1-s"
					        	},
					        	text: false
					        }).removeClass( "ui-corner-all" ).addClass( "ui-autocomplete-combobox-toggle ui-corner-right" ).click(function(){
					        	settings.element.find(":input#" + settings.state).autocomplete( "search" );
					        });
						}
					}
					return autocomplete;
				},
				init: function(){
					instance.country();
					instance.state();
				}
		}
		instance.init();
		
		instance = $.extend(true, instance, { settings : settings });
		return instance;
	}
})( jQuery );