(function( $ ){
	$.fn.Bank = function(options) {
		var defaultoptions = {
				element : this,
				country : "",
				state : "",
		};
		var settings = $.extend(true, defaultoptions, options);
		
		var countryElement = settings.element.find(":input#" + settings.country);
		var stateElement = settings.element.find(":input#" + settings.state);
		var instance = {
				country: function(){
					var autocomplete = null;
					if($.type(settings.country) === "string" && settings.country.length > 0 && countryElement !== undefined){
						countryElement.combobox({
							autocomplete : {
								source: function( request, response) {
									$.getJSON( "<?php echo $this->url('Ajax/Bank', array('action' => 'getCountry')); ?>" ,{
										country: request.term,
									}, response);
								},
								search: function(){
									stateElement.val("");
									stateElement.prop( "disabled" , true);
									stateElement.autocomplete("disable");
								},
								select: function(event, ui){
									if($.type(settings.state) === "string" && settings.state.length > 0){
										stateElement.prop( "disabled" , false);
										stateElement.autocomplete("enable");
									}
									$(event.currentTarget).attr("value" , ui.item.value);
									$(event.currentTarget).val(ui.item.label);
									$(event.currentTarget).keypress();
									return false;
								}
							}
						});
					}
					return autocomplete;
				},
				state: function(){
					var autocomplete = null;
					if($.type(settings.state) === "string" && settings.state.length > 0 && stateElement !== undefined){
						stateElement.combobox({
							autocomplete : {
								source: function( request, response) {
									$.getJSON( "<?php echo $this->url('Ajax/Bank', array('action' => 'getState')); ?>" ,{
										state: request.term,
										country: (($.type(settings.country) === "string" && settings.country.length > 0) ? countryElement.attr("value") : ""),
									}, response);
								},
								select: function(event, ui){
									$(event.currentTarget).attr("value" , ui.item.value);
									$(event.currentTarget).val(ui.item.label);
									$(event.currentTarget).keypress();
									return false;
								}
							}
						});
						
						var country = (($.type(settings.country) === "string" && settings.country.length > 0) ? countryElement.attr("value") : "");
						if(country !== undefined && country.length <= 0){
							stateElement.autocomplete("disable");
							stateElement.prop( "disabled" , true);
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