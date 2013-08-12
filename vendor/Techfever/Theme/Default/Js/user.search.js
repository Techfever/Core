<?php 
$searchuri = $parameter['searchformuri'];
$searchusername = $parameter['searchformusername'];
$searchform = str_replace('/', '_', $parameter['searchformid']);
$updateform = str_replace('/', '_', $parameter['updateformid']);
?>

$(document).ready(function() {	
	var actionSearch = "<?php echo $this->translate('text_search') ?>";
	var actionCancel = "<?php echo $this->translate('text_cancel') ?>";
	
    $("form[id=<?php echo $searchform; ?>]").submit(function(e) {
        e.defaultPrevented();
        return false;
    });

	var flashmessage = $("div[id=flashmessager]");
	var searchButton = $("form[id=<?php echo $searchform; ?>] button[id=search]");
	var searchInput = $("form[id=<?php echo $searchform; ?>] input[id=search_username]");
	var messageDiv = $("td[class=help] div[class=ui-widget] div[class=ui-state-active]");
	var formDiv = $("div[class=bodycontent] div[class=form]");
	var previewDiv = $("div[class=bodycontent] div[class=preview]");
	searchButton.click(
		function() {		
			messageDiv.html("");
			if(formDiv){
				formDiv.html("");
				formDiv.hide();
			}
			if(previewDiv){
				previewDiv.html("");
				previewDiv.hide();
			}
			if(flashmessage){
				flashmessage.html("");
			}
			searchInput.prop('disabled', false);
			if(searchButton.html() == 'Cancel'){
				searchInput.val("");
				searchButton.html(actionSearch);
	            $(this).formClear({  
					form: "<?php echo $updateform; ?>",  
	            });
				$(this).JSONAjax("<?php echo $this->url($searchuri, array('action' => 'Search')); ?>", $("form[id=<?php echo $searchform; ?>]").serialize());
			}else if(searchButton.html() == 'Search'){
				$(this).JSONAjax("<?php echo $this->url($searchuri, array('action' => 'Search')); ?>", $("form[id=<?php echo $searchform; ?>]").serialize(), function(JsonReturn) {
					if(JsonReturn.valid == true){
						if(formDiv){
							if(JsonReturn.inputmodel){
								formDiv.html(JsonReturn.inputmodel);
								formDiv.show();
							}
						}
						if(previewDiv){
							if(JsonReturn.previewmodel){
								previewDiv.html(JsonReturn.previewmodel);
								previewDiv.show();
							}
						}

						searchInput.prop('disabled', true);
						searchButton.html(actionCancel);
						if(JsonReturn.js){
							eval(JsonReturn.js);
						}
					}else{
						if(JsonReturn.messages){
							if(messageDiv){
								messageDiv.html('<span class="ui-icon ui-icon-alert"></span>' + JsonReturn.messages);
							}
						}
					}
				});
			}
		}
	);

	var searchUsername = "<?php echo $searchusername; ?>";
	if(searchUsername.length > 0){
		searchInput.val(searchUsername);
		searchButton.click();
	}
});