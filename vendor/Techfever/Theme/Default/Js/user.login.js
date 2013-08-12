<?php 
$uri = $parameter['loginformuri'];
$action = $parameter['loginformaction'];
$form = str_replace('/', '_', ($parameter['loginformid'] . '/' . $action));
?>
$(document).ready(function() {	
	$.fn.Login = function() {
		var formname = "<?php echo $form; ?>";
		var formid = "form[id=" + formname + "]";
		var formuri = "<?php echo $this->url($uri, array('action' => $action)); ?>";
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

		var flashmessage = $("div[id=flashmessager]");
		var buttonLoginClicked = false; 
		buttonLogin = $(formid + " table[class=form] button[id=login]");
		buttonLogin.click(function() {
			if(flashmessage){
				flashmessage.html("");
			}
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
											}
										);
								}
							);
						buttonLoginClicked = false;
					} else if (JsonReturn.valid == true && JsonReturn.redirect) {
						window.location.replace(JsonReturn.redirect);
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