(function( $ ){
	$.fn.Rank = function(options) {
		var defaultoptions = {
				element : this,
				rank : "",
				url : "<?php echo $this->url('Ajax/Rank', array('action' => 'getRankPrice'), array(), true); ?>",
		};
		settings = $.extend(true, defaultoptions, options);
		var instance = {
				init: function(){
					var rankElement = settings.element.find("select#" + settings.rank);
					var pricingElement = $( "<a span>" ).addClass("pricing");
					rankElement.after(pricingElement);
					rankElement.combobox({
						autocomplete : {
							select: function(event, ui){
								var pricingElement = $(event.currentTarget).parent().parent().children(".pricing")
								$(this).ajaxQuery( 
										settings.url, 
										settings.rank + "=" + ui.item.value
										, "post", "json"
								).done(function(ajaxReturn) {
									if (ajaxReturn.success == true && ajaxReturn.valid == true) {
										pricingElement.html(ajaxReturn.price_dl + ' - ' + ajaxReturn.price_pv);
									}
								});
								$(event.currentTarget).attr("value" , ui.item.value);
								$(event.currentTarget).val(ui.item.label);
								$(event.currentTarget).keypress();
								return false;
							}
						}
					});
				}
		}
		instance.init();
		instance = $.extend(true, instance, { settings : settings });
		return instance;
	}
})( jQuery );