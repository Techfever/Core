var system = {
		initial : false,
		modal : {
			width : "",
			height : "",
		},
		layout : {
			outer : {
				layout : "",
				width : "",
				height : "",
				west : {
					layout : "",
					width : "",
					height : "",
				},
			},
			inner : {
				layout : "",
				width : "",
				height : "",
			},
			min	: {
				width : 750,
				height : 400,
			}
		},
};

$(document).ready(function() {
	var BGImageArray = [ 
	            		"<?php echo $this->serverUrl( $this->baseHref () . '/Theme/Image/Backend/background/bg01.jpg' ); ?>", 
	            		"<?php echo $this->serverUrl( $this->baseHref () . '/Theme/Image/Backend/background/bg02.jpg' ); ?>", 
	            		];
	            	var BGImage = BGImageArray[Math.floor(Math.random() * BGImageArray.length)]
	            	$("body").bgResize({
	            			img : BGImage, // Relative path example
	            			opacity : 1, // Opacity. 1 = 100%. This is
											// optional.
	            			center : true // Boolean (true or false). This is
											// optional. Default is true.
	            	});

	system.layout.outer.layout = $("body.ui-layout").layout({
		spacing_open: 0,
		spacing_closed: 0,
		minWidth: 400,
		west: {
			initClosed: true,
			paneSelector:  "#ui-layout-outer-west",
			size: 200,
		},
		east: {
			initClosed: true,
			paneSelector:  "#ui-layout-outer-east",
			size: 200,
		},
		north: {
			initClosed: false,
			paneSelector:  "#ui-layout-outer-north",
			size: 45,
		},
		center: {
			paneSelector:  "#ui-layout-outer-center",
		},
		onresize: function(){
			$(this).refreshLayout();
		}
	}); 

	system.layout.outer.west.layout = $("div.ui-layout-outer-west").layout({
		spacing_open: 0,
		spacing_closed: 0,
		minWidth: 200,
		south: {
			paneSelector:  "#ui-layout-outer-west-inner-south",
			size: 40,
		},
		center: {
			paneSelector:  "#ui-layout-outer-west-inner-center",
		},
		onresize: function(){
			$(this).refreshLayout();
		}
	}); 
	
	var toggleButtons		= "<div class=\"ui-window-toggler ui-widget-header ui-corner-tl ui-corner-tr\"><?php echo $this->translate('text_window') ?></div>";
	system.layout.inner.layout = $("div.ui-layout-outer-center").layout({
		spacing_open: 0,
		spacing_closed: 0,
		maskContents: true,
		maskObjects: true,
		zIndex: 1000,
		north: {
			initClosed: true,
			paneSelector:  "#ui-layout-inner-north",
			size: 60,
		},
		south: {
			initClosed: true,
			spacing_open: 32,
			spacing_closed: 32,
			paneSelector:  "#ui-layout-inner-south",
			size: 80,
			resizable: false,
			togglerLength_closed: 110,
			togglerLength_open:	110,
			togglerContent_closed: toggleButtons,
			togglerContent_open: toggleButtons,
			togglerAlign_closed: "left",
			togglerAlign_open: "left",
			togglerClass: "ui-layout-toggler",
			tips: {
				Open : "<?php echo $this->translate('text_open') ?>",
				Close : "<?php echo $this->translate('text_close') ?>"
			}
		},
		center: {
			paneSelector:  "#ui-layout-inner-center",
			maskContents: true,
			maskObjects: true,
		},
		onresize: function(){
			$(this).refreshLayout();
		}
	}); 
	
	$(".ui-layout-outer-west").shadow({
		type : "raisedall",
		radius : "0"
	});
	
	$(".ui-layout-outer-east").shadow({
		type : "raisedall",
		radius : "0"
	});
	
	$(".ui-layout-inner-north").shadow({
		type : "raisedall",
		radius : "0"
	});
	
	$(".ui-layout-inner-south").shadow({
		type : "raisedall",
		radius : "0"
	});

	$(".ui-layout-inner-south-left").hover(function() {
		$(this).addClass( "ui-state-hover" );
	}, function() {
		$(this).removeClass( "ui-state-hover" );
	}).click(function(){
		$(".ui-layout-inner-south").windowManage().back();
	});

	$(".ui-layout-inner-south-right").hover(function() {
		$(this).addClass( "ui-state-hover" );
	}, function() {
		$(this).removeClass( "ui-state-hover" );
	}).click(function(){
		$(".ui-layout-inner-south").windowManage().next();
	});
	
	$.fn.refreshLayout = function() {
		system.layout.outer.width = system.layout.outer.layout.state.container.innerWidth;
		system.layout.outer.height = system.layout.outer.layout.state.container.innerHeight;
		system.layout.inner.width = system.layout.inner.layout.state.container.innerWidth;
		system.layout.inner.height = system.layout.inner.layout.state.container.innerHeight;

		system.modal.width = system.layout.inner.layout.state.container.innerWidth;
		system.modal.height = system.layout.inner.layout.state.container.innerHeight;

		if(system.layout.inner.layout.state.south.isClosed == false){
			system.modal.height = system.modal.height - $(".ui-layout-inner-south").innerHeight();
		}
		
		var headerHideName = false;
		if((system.layout.outer.width < system.layout.min.width) || $(this).isMobile().any()){
			$(".ui-company-name").hide();
			$(".ui-menu-name").hide();
			$(".ui-notification-name").hide();
			headerHideName = true;
		}else{
			$(".ui-company-name").show();
			$(".ui-menu-name").show();
			$(".ui-notification-name").show();
			headerHideName = false;
		}
		$(".ui-logo").css({ 
			"left" : ((system.layout.outer.width / 2) - (($(".ui-logo").innerWidth() + (headerHideName ? 0 : $(".ui-company-name").innerWidth())) / 2)),
		});
		$(".ui-icon-menu").css({ 
			"left" : 0,
		});
		$(".ui-icon-notification").css({ 
			"right" : 0,
		});
		$(".ui-layout-inner-center").css({
			"z-index" : 900,
		});
		$("#ui-layout-inner-south-resizer").css({
			"width" : "110px",
		});
		
		$(this).TaskModal().resize();

		$(".ui-layout-inner-south").windowManage().resize();
	}
	$(this).refreshLayout();

	$.fn.syncSystem = function() {
		var routeMatch = $(this).routeMatch();
		if ( routeMatch.toLowerCase().indexOf("<?php echo strtolower(SYSTEM_BACKEND_URI); ?>") >= 0 ){
			var progressBar = $("body.ui-layout").progressBar();
			$(this).ajaxSubmit(
					"<?php echo $this->url(SYSTEM_BACKEND_URI, array('action' => 'Loading')); ?>"
			).done(function(JSONReturn) {
				if(JSONReturn.isLogin == false){
					
					$(this).TaskModal().closeAll();
					$(".ui-layout-inner-center").html("");

					$(".ui-layout-outer-west-inner-center").html("");

					$(".ui-layout-outer-east").html("");
					
					if(system.layout.outer.layout.state.east.isClosed == false){
						system.layout.outer.layout.toggle("east");
					}
					
					if(system.layout.outer.layout.state.west.isClosed == false){
						system.layout.outer.layout.toggle("west");
					}
					
					if(system.layout.inner.layout.state.south.isClosed == false){
						system.layout.inner.layout.toggle("south");
					}
					
					$("body.ui-layout").LoginModal({
						progressbar : progressBar
					});
				}else{
					if(JSONReturn.dashboardLayout && JSONReturn.dashboardLayout.length > 0){
						$(".ui-layout-inner-center").html(JSONReturn.dashboardLayout);
					}
					if(JSONReturn.menuLayout && JSONReturn.menuLayout.length > 0){
						$(".ui-layout-outer-west-inner-center").html(JSONReturn.menuLayout);
					}
					if(JSONReturn.notificationLayout && JSONReturn.notificationLayout.length > 0){
						$(".ui-layout-outer-east").html(JSONReturn.notificationLayout);
					}
					
					$(".ui-icon-menu").click(function() {
						if(system.layout.outer.layout.state.east.isClosed == false){
							system.layout.outer.layout.toggle("east");
						}
						system.layout.outer.layout.toggle("west");
					});
					
					$(".ui-icon-notification").click(function() {
						if(system.layout.outer.layout.state.west.isClosed == false){
							system.layout.outer.layout.toggle("west");
						}
						system.layout.outer.layout.toggle("east");
					});
					
					if($.type( progressBar ) === "object"){
						progressBar.destroy();
					}
					setTimeout(function(){
						$(this).syncSystem();
					}, <?php echo (SYSTEM_SESSION_MAX_LIFETIME * 1000 + 500); ?>);
				}
			});
			system.initial = true;
		}
	}
	$(this).syncSystem();
});