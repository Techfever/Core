<?php 
$uri = $parameter['updateformuri'];
$form = $parameter['updateformid'];
$callback = (isset($parameter['updatecallback']) ? $parameter['updatecallback'] : null);
?>
$(document).ready(function() {	
	$.fn.AccountUpdate = function() {
		var formname = "<?php echo $form; ?>";
		var formid = "form[id=" + formname + "]";
		var formuri = "<?php echo $uri; ?>";
		$(formid + " table[class=form]").find( "tr" ).each(
			function() {
				$(this).find( ':input' ).not(':button, :submit, :reset').each(
					function() {					
						var id = $(this).attr('id');
						$(this).focus(function() {  
						    $(this).addClass("Focus");  
						});  
						
						$(this).focusout(function() {
						    $(this).removeClass("Focus");
						    $(this).formValidator({  
								uri: formuri,
								form: formname,  
								data: $(formid).serialize(),  
								values: '&Input=' + id,
						    });
							return false;
						}); 
					}
				);
			}
		);
		
		$(formid + " table[class=form] :input").not(':button, :submit, :reset').tooltip({
			position: {
				my: 'left top-20', 
				at: 'right+8 bottom'
			},
			content: function () {
	            return $(this).prop('title');
	        },  	
	        open: function( event, ui ) {
	            setTimeout(function(){
	                $(ui.tooltip).hide('fade');
	            }, 5000);
	        },
			tooltipClass: 'right'
		});

		var flashmessage = $("div[id=flashmessager]");
		var buttonUpdateClicked = false; 
		buttonCancel = $(formid + " table[class=form] button[id=cancel]");
		buttonUpdate = $(formid + " table[class=form] button[id=update]");
		buttonUpdate.click(function() {
			if(flashmessage){
				flashmessage.html("");
			}
			if(buttonUpdateClicked == false){
				$(this).formProgressBar();
				$(this).JSONAjax(formuri, $(formid).serialize() + '&submit=submit', function(JsonReturn) {
					if (JsonReturn.valid == false) {
						flashmessage.html(JsonReturn.flashmessages);
						buttonCancel.click();
						buttonCancel.removeClass("disable");
						
						buttonUpdate.removeClass("disable");
						buttonUpdateClicked = false;
					} else if (JsonReturn.valid == true && JsonReturn.redirect) {
						window.location.replace(JsonReturn.redirect);
					}
					$(this).formProgressBar(true);
				});
				buttonCancel.addClass("disable");
				
				buttonUpdate.addClass("disable");
				buttonUpdateClicked = true;
			}
		});
		
		buttonCancel.click(function() {
			$(formid + " table[class=form]").find( "tr" ).each(
					function() {
						$(this).find( ':input' ).not(':button, :submit, :reset').each(
								function() {					
									$(this).val("");
								}
							);
					}
				);
		});
		
	    $(formid).submit(function(e) {
	        e.defaultPrevented();
	        return false;
	    });
		
		var jsCallback = "<?php echo $callback; ?>";
		if(jsCallback){
			eval(jsCallback);
		}
	};
	
	$(this).AccountUpdate();
});