(function( $ ){
	$.fn.navigation = function() {
		var element = this;
		var linkCount = 0;
		var linkTotal = element.find("a").length;
		var result = $.Deferred();
		var routeMatch = $(this).routeMatch().toLowerCase();
		
		element.find("a").each(function() {
			var currentElement = $(this);
			var id = currentElement.prop("id");
			var link = currentElement.prop("href");
			var icon = currentElement.children("span").not(".ui-menu-icon");
			icon.addClass( "ui-menu-icon-left ui-" + id );
			if ( link.toLowerCase().indexOf("<?php echo strtolower(SYSTEM_BACKEND_URI); ?>") < 0 ){
				currentElement.TaskModal({
					appendto: ".ui-layout-inner-center",
					form: {
						usemodalbutton: true,
					},
					beforeopen: function(){
						if(system.layout.outer.layout.state.west.isClosed == false){
							system.layout.outer.layout.toggle("west");
						}
						if(system.layout.inner.layout.state.south.isClosed == false){
							system.layout.inner.layout.toggle("south");
						}
					}
				});
			}
			
			linkCount++;
			if(linkTotal === linkCount){
				result.resolve();
			}
		})
		
		
		result.done(function(){
			return element.menu({
				delay: 100,
				onclick: true,
				position : {
					my: "left top",
					at: "left top+30",
				},
				create: function( event, ui ) {
					$( event.target ).find( ".ui-menu-item" ).click(function(event){
						$(this).siblings().children( ".ui-state-active" ).removeClass( "ui-state-active" );
						$(this).siblings().children( "a" ).addClass( "ui-state-disabled" );
					});
				},
				focus: function( event, ui ) {
					ui.item.children( ".ui-menu" ).css({
						"top" : "2px",
					});
				},
				select: function( event, ui ) {
					ui.item.children( ".ui-menu" ).css({
						"top" : "2px",
					});
				}
			});
		});
		return null;
	}
})( jQuery );