<?php 
$uri = $parameter['walletformuri'];
$form = $parameter['walletformid'];
$callback = $parameter['walletcallback'];
?>
$(document).ready(function() {	
	$.fn.Wallet = function() {
		var formname = "<?php echo $form; ?>";
		var formid = "form[id=" + formname + "]";
		var formuri = "<?php echo $uri; ?>";
		var formcallback = "<?php echo $callback; ?>";
				
		if(formcallback != ""){
			eval(formcallback);
		}
	};
});