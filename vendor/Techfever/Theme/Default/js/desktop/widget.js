
$(document).ready(function() {	
	var widgetInitialLeftStatus = false;
	var widgetInitialRightStatus = false;
	var widgetInitialHeaderStatus = false;
	var widgetInitialFooterStatus = false;
	var widgetInitialBeforeStatus = false;
	var widgetInitialAfterStatus = false;
	var widgetInitialDashboardStatus = false;
	
	$.fn.widgetInitial = function() {
		var layoutCenter = $("#" + system.layout.content.id);
		var location = new Array('left', 'right', 'header', 'footer', 'before', 'after', 'dashboard', 'content');
		$.each(location, function(key, value) {
			if (value.length > 0) {
				var url = "";
				var layout = "";
				switch (value) {
				case 'content':
					layout = $("#" + system.layout.content.content.id);
					url = "<?php echo $this->url('Widget', array('action' => 'Initial', 'location' => 'Content', 'module' => 'Initial')); ?>";
					break; 
				case 'left':
					layout = $("#" + system.layout.left.content.id);
					url = "<?php echo $this->url('Widget', array('action' => 'Initial', 'location' => 'Left', 'module' => 'Initial')); ?>";
					break; 
				case 'right':
					layout = $("#" + system.layout.right.content.id);
					url = "<?php echo $this->url('Widget', array('action' => 'Initial', 'location' => 'Right', 'module' => 'Initial')); ?>";
					break; 
				case 'header':
					layout = $("#" + system.layout.header.content.id);
					url = "<?php echo $this->url('Widget', array('action' => 'Initial', 'location' => 'Header', 'module' => 'Initial')); ?>";
					break; 
				case 'footer':
					layout = $("#" + system.layout.footer.content.id);
					url = "<?php echo $this->url('Widget', array('action' => 'Initial', 'location' => 'Footer', 'module' => 'Initial')); ?>";
					break; 
				case 'before':
					layout = $("#" + system.layout.before.content.id);
					url = "<?php echo $this->url('Widget', array('action' => 'Initial', 'location' => 'Before', 'module' => 'Initial')); ?>";
					break; 
				case 'after':
					layout = $("#" + system.layout.after.content.id);
					url = "<?php echo $this->url('Widget', array('action' => 'Initial', 'location' => 'After', 'module' => 'Initial')); ?>";
					break; 
				case 'dashboard':
					layout = $("#" + system.layout.dashboard.content.id);
					url = "<?php echo $this->url('Widget', array('action' => 'Initial', 'location' => 'Dashboard', 'module' => 'Initial')); ?>";
					break; 
				}
				if (url.length > 0 && layout) {
					$(this).ajaxQuery(url).done(function(ajaxReturn) {
						if (ajaxReturn.success == true) {
							if (ajaxReturn.redirect && ajaxReturn.redirect.length > 0) {
								window.location.replace(ajaxReturn.redirect);
							}
							if ($.isArray(ajaxReturn.content) != -1 && ajaxReturn.content != "") {
								layout.html("");
								var layoutContent = layout.html();
								if(layoutContent === undefined){
									layoutContent = "";
								}
								$.each(ajaxReturn.content, function(ckey, cvalue) {
									var cContent = jQuery.parseJSON( cvalue );
									if (cContent.redirect && cContent.redirect.length > 0) {
										window.location.replace(cContent.redirect);
									}
									if (cContent.success == true) {
										if (cContent.title && cContent.title.length > 0) {
										}
										if (cContent.content && cContent.content.length > 0) {
											layoutContent = layoutContent + cContent.content;
										}
									}
								});
								if (layoutContent && layoutContent.length > 0) {
									layout.html(layoutContent + '<div style="clear:both;"></div>');
									if (layoutCenter){
										$(window).resize();
										switch (value) {
										case 'left':
											if(system.layout.outer.layout.state.west.isClosed == true){
												system.layout.outer.layout.sizePane("west", ajaxReturn.width);
												system.layout.outer.layout.toggle("west");
											}
											layoutCenter.css('margin-left', ajaxReturn.width + 'px');
											break; 
										case 'right':
											if(system.layout.outer.layout.state.east.isClosed == true){
												system.layout.outer.layout.sizePane("east", ajaxReturn.width);
												system.layout.outer.layout.toggle("east");
											}
											layoutCenter.css('margin-right', ajaxReturn.width + 'px');
											break; 
										}
									}
								}
							}
						}
					});
				}
			}
		});
	}
	$(this).widgetInitial();

});