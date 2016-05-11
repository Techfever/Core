(function( $ ){
	$.fn.Rank = function(options) {
		var defaultoptions = {
				element : this,
				rank : "",
				url : "<?php echo $this->url('Ajax/Rank', array('action' => 'getRankPrice')); ?>",
		};
		settings = $.extend(true, defaultoptions, options);
		var instance = {
				init: function(){
					var rankElement = settings.element.find("select#" + settings.rank);
					rankElement.after('<span class="pricing" style="color: red; font-weight: bold;"></span>');
					if(rankElement.parent().children(".pricing").length > 0){
						rankElement.parent().children(".pricing").html("");
					}
					rankElement.change(function(){
						variable = rankElement.serialize();
						rankElement.parent().children(".pricing").html("");
						$(this).ajaxSubmit( 
								settings.url, 
								variable
								, "post", "json"
						).done(function(ajaxReturn) {
							if (ajaxReturn.success == true && ajaxReturn.valid == true) {
								rankElement.parent().children(".pricing").html(ajaxReturn.price_dl + ' - ' + ajaxReturn.price_pv);
							}
						});
					});
				}
		}
		instance.init();
		instance = $.extend(true, instance, { settings : settings });
		return instance;
	}
})( jQuery );