$(document).ready(function() {
	if($(".ui-layout-debug").length > 0){
		$(".ui-layout-debug").addClass("ui-widget-content");
		system.layout.debug.layout = $("body").layout({
			spacing_open: 10,
			spacing_closed: 10,
			minWidth: system.layout.size,
			south: {
				initClosed: true,
				paneSelector:  "#ui-layout-debug",
				togglerClass: "ui-layout-debug-toggler",
				togglerLength_closed: 110,
				togglerLength_open:	110,
				resizable: false,
			},
			center: {
				paneSelector:  "#" + system.layout.id,
				onresize: "system.layout.outer.layout.resizeAll"
			},
		}); 
		$(".ui-layout-debug-toggler").addClass("ui-corner-tl").addClass("ui-corner-tr");
	}
	system.layout.outer.layout = $("#" + system.layout.id).layout({
		spacing_open: 0,
		spacing_closed: 0,
		minWidth: 400,
		south: {
			paneSelector:  "#" + system.layout.footer.id,
			size: system.layout.footer.size,
		},
		center: {
			paneSelector:  "#" + system.layout.inner.id,
			onresize: "system.layout.inner.layout.resizeAll"
		},
		onresize: function(){
			$(this).refreshLayout();
		}
	}); 
	
	system.layout.inner.layout = $("#" + system.layout.inner.id).layout({
		spacing_open: 0,
		spacing_closed: 0,
		west: {
			paneSelector:  "#" + system.layout.left.id,
			size: system.layout.left.size,
		},
		center: {
			paneSelector:  "#" + system.layout.inner2.id,
			onresize: "system.layout.inner2.layout.resizeAll"
		},
	}); 
	
	system.layout.inner2.layout = $("#" + system.layout.inner2.id).layout({
		spacing_open: 0,
		spacing_closed: 0,
		north: {
			paneSelector:  "#" + system.layout.header.id,
			size: system.layout.header.size,
		},
		east: {
			paneSelector:  "#" + system.layout.right.id,
			size: system.layout.right.size,
		},
		center: {
			paneSelector:  "#" + system.layout.content.id,
		},
	}); 

	var $Container	= $("#" + system.layout.id);
	$Container.height( $(window).height() - $Container.offset().top );
	
	$.fn.refreshLayout = function() {
		var $Container	= $("#" + system.layout.id)
		,	$Content	= $("#" + system.layout.id + " #" + system.layout.content.id + " #" + system.layout.content.content.id)
		,	$Header	= $("#" + system.layout.id + " #" + system.layout.header.id)
		,	$Footer	= $("#" + system.layout.id + " #" + system.layout.footer.id)
		,	$Left	= $("#" + system.layout.id + " #" + system.layout.left.id + " #" + system.layout.left.content.id)
		,	$Right	= $("#" + system.layout.id + " #" + system.layout.right.id + " #" + system.layout.right.content.id);

		var contentHeight = $Content.outerHeight();
		var leftHeight = $Left.outerHeight();
		var rightHeight = $Right.outerHeight();
		var headerHeight = $Header.outerHeight();
		var footerHeight = $Footer.outerHeight();
		var bodyHeight = headerHeight + footerHeight;
		
		var allHeight = [contentHeight, leftHeight, rightHeight];
		bodyHeight = bodyHeight + Math.max.apply(null, allHeight);
		var windowHeight = $(window).outerHeight();
		if(bodyHeight < windowHeight){
			bodyHeight = windowHeight;
		}
		$Container.height( bodyHeight );
	}
	system.layout.outer.layout.resizeAll();
	
	$(window).resize(function(){
		system.layout.outer.layout.resizeAll();
	});
});