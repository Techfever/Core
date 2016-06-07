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
			},
		}); 
		$(".ui-layout-debug-toggler").addClass("ui-corner-tl").addClass("ui-corner-tr");
	}
	system.layout.outer.layout = $("#" + system.layout.id).layout({
		spacing_open: 0,
		spacing_closed: 0,
		minWidth: system.layout.size,
		north: {
			paneSelector:  "#" + system.layout.header.id,
			size: 40,
		},
		south: {
			paneSelector:  "#" + system.layout.footer.id,
			size: 50,
		},
		west: {
			initClosed: true,
			paneSelector:  "#" + system.layout.left.id,
			size: 50,
		},
		east: {
			initClosed: true,
			paneSelector:  "#" + system.layout.right.id,
			size: 50,
		},
		center: {
			paneSelector:  "#" + system.layout.content.id,
		},
		onresize: function(){
			$(this).refreshLayout();
		}
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
		$Container.css({
			"width": "100%"
		});
		$("#" + system.layout.id + " #" + system.layout.content.id).css({
			"left" : "0",
			"right" : "0",
		});
		if($(".ui-layout-debug").length > 0){
			$(".ui-layout-debug").height( bodyHeight );
		}
	}
	
	$(window).resize(function(){
		system.layout.outer.layout.resizeAll();
		if($(".ui-layout-debug").length > 0){
			system.layout.debug.layout.resizeAll();
		}
	});
	$(window).resize();

	$.fn.headerOnTop = function() {
	    var window_top = $(window).scrollTop();
	    var div_top = $('#ui-layout-header-content-anchor').offset().top;
	    if (window_top > div_top) {
	        $('#ui-layout-header-content').addClass('stick');
	        $('#ui-layout-header-content-anchor').height($('#ui-layout-header-content').outerHeight());
		    $('#ui-layout-header').css({
		    	"z-index" : "100"
		    });
	    } else {
	        $('#ui-layout-header-content').removeClass('stick');
	        $('#ui-layout-header-content-anchor').height(0);
		    $('#ui-layout-header').css({
		    	"z-index" : "0"
		    });
	    }
	}

	$(window).scroll(function(){
		$(this).headerOnTop();
		var winheight = $(window).height();
	    var docheight = $(document).height();
	    var scrollTop = $(window).scrollTop();
	    var trackLength = docheight - winheight;
	    var pctScrolled = Math.floor(scrollTop/trackLength * 100);
	    if(pctScrolled >= 70){
	    	// News Feed refresh
	    }
	});
	$(this).headerOnTop();
});