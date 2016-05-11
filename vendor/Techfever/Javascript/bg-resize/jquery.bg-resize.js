/******************************************************
    * jQuery plug-in
    * Easy Background Image Resizer
    * Developed by J.P. Given (http://johnpatrickgiven.com)
    * Useage: anyone so long as credit is left alone
******************************************************/

(function($) {
	// Global Namespace
    var jqez = {};

    // Define the plugin
    $.fn.bgResize = function(options) {
		
		// Set global to obj passed
		jqez = options;
		
		// If img option is string convert to array.
		// This is in preparation for accepting an slideshow of images.
		if (!$.isArray(jqez.img)) {
			var tmp_img = jqez.img;
			jqez.img = [tmp_img]
		}
		
		$("<img/>").attr("src", jqez.img).load(function() {
			jqez.width = this.width;
			jqez.height = this.height;
			
			// Create a unique div container
			$("body").append('<div id="jquery_bg_resize"></div>');

			// Add the image to it.
			$("#jquery_bg_resize").html('<img src="' + jqez.img[0] + '" width="' + jqez.width + '" height="' + jqez.height + '" border="0">');

			// First position object
	        $("#jquery_bg_resize").css("visibility","hidden");

			// Overflow set to hidden so scroll bars don't mess up image size.
	        $("body").css({
	            "overflow":"hidden"
	        });

			resizeImage();
		});
    };

	$(window).bind("resize", function() {
		resizeImage();
	});
	
	// Actual resize function
    function resizeImage() {
	
        $("#jquery_bg_resize").css({
            "position":"fixed",
            "top":"0px",
            "left":"0px",
            "z-index":"-1",
            "overflow":"hidden",
            "width":$(window).width() + "px",
            "height":$(window).height() + "px",
			"opacity" : jqez.opacity
        });
		
		// Image relative to its container
		$("#jquery_bg_resize").children('img').css("position", "relative");

        // Resize the img object to the proper ratio of the window.
        var iw = $("#jquery_bg_resize").children('img').width();
        var ih = $("#jquery_bg_resize").children('img').height();
        
        if ($(window).width() > $(window).height()) {
            //console.log(iw, ih);
            if (iw > ih) {
                var fRatio = iw/ih;
                $("#jquery_bg_resize").children('img').css("width",$(window).width() + "px");
                $("#jquery_bg_resize").children('img').css("height",Math.round($(window).width() * (1/fRatio)));

                var newIh = Math.round($(window).width() * (1/fRatio));

                if(newIh < $(window).height()) {
                    var fRatio = ih/iw;
                    $("#jquery_bg_resize").children('img').css("height",$(window).height());
                    $("#jquery_bg_resize").children('img').css("width",Math.round($(window).height() * (1/fRatio)));
                }
            } else {
                var fRatio = ih/iw;
                $("#jquery_bg_resize").children('img').css("height",$(window).height());
                $("#jquery_bg_resize").children('img').css("width",Math.round($(window).height() * (1/fRatio)));
            }
        } else {
            var fRatio = ih/iw;
            $("#jquery_bg_resize").children('img').css("height",$(window).height());
            $("#jquery_bg_resize").children('img').css("width",Math.round($(window).height() * (1/fRatio)));
        }
		
		// Center the image
		if (typeof(jqez.center) == 'undefined' || jqez.center) {
			if ($("#jquery_bg_resize").children('img').width() > $(window).width()) {
				var this_left = ($("#jquery_bg_resize").children('img').width() - $(window).width()) / 2;
				$("#jquery_bg_resize").children('img').css({
					"top"  : 0,
					"left" : -this_left
				});
			}
			if ($("#jquery_bg_resize").children('img').height() > $(window).height()) {
				var this_height = ($("#jquery_bg_resize").children('img').height() - $(window).height()) / 2;
				$("#jquery_bg_resize").children('img').css({
					"left" : 0,
					"top" : -this_height
				});
			}
		}

        $("#jquery_bg_resize").css({
			"visibility" : "visible"
		});

		// Allow scrolling again
		$("body").css({
            "overflow":"auto"
        });
		
        
    }
})(jQuery);