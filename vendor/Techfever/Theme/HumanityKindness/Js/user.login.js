<?php 
$uri = $parameter['loginformuri'];
$form = $parameter['loginformid'];
?>
$(document).ready(function() {	
	$.fn.Login = function() {
		var formname = "<?php echo $form; ?>";
		var formid = "form[id=" + formname + "]";
		var formuri = "<?php echo $uri; ?>";
		var buttonLogin = $(formid + " table[class=form] button[id=login]");
		buttonLogin.hide();
		$(formid + " table[class=form]").find( "tr" ).each(
			function() {
				$(this).find( ':input' ).not(':button, :submit, :reset').each(
					function() {					
						var id = $(this).attr('id');
						var type = $(this).attr('type');
						if(type !== 'hidden'){
							$(this).val("");
						}
						$(this).focus(function() {  
						    $(this).addClass("Focus");  
						});  
						
						$(this).focusout(function() {
							buttonLogin.hide();
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

		var flashmessage = $("div[id=flashmessager]");
		var buttonLoginClicked = false; 
		buttonLogin.click(function() {
			if(flashmessage){
				flashmessage.html("");
			}
			buttonLogin.disabled = true;
			if(buttonLoginClicked == false){
				$(this).formProgressBar();
				$(this).JSONAjax(formuri, $(formid).serialize() + '&submit=submit', function(JsonReturn) {
					if (JsonReturn.valid == false) {
						flashmessage.html(JsonReturn.flashmessages);
						$(formid + " table[class=form]").find( "tr" ).each(
								function() {
									$(this).find( ':input' ).not(':button, :submit, :reset').each(
											function() {					
												$(this).val("");
												var id = $(this).prop('id');
												$(formid + " table[class=form] tr[id=" + id + "] td[class=help] div[class=ui-widget] div[class=ui-state-active]").html('');
											}
										);
								}
							);
						buttonLoginClicked = false;
						buttonLogin.disabled = false;
						buttonLogin.hide();
					} else if (JsonReturn.valid == true && JsonReturn.redirect) {
						window.location.replace(JsonReturn.redirect);
					}
					if(JsonReturn.js){
						eval(JsonReturn.js);
					}
					$(this).formProgressBar(true);
				});
				buttonLoginClicked = true;
			}
		});

		
	    $(formid).submit(function(e) {
	        e.defaultPrevented();
	        return false;
	    });
	};
	
	$(this).Login();
});
