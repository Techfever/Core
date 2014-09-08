<?php 
$uri = $parameter['stepsformuri'];
$form = $parameter['stepsformid'];
$dialogtitle = $parameter['stepsformdialogtitle'];
$dialogcontent = $parameter['stepsformdialogcontent'];
?>
$(document).ready(function() {	
	$(this).Steps({  
		formname : "<?php echo $form; ?>",
		formuri : "<?php echo $uri; ?>",
		dialogtitle : "<?php echo $dialogtitle; ?>",
		dialogcontent : "<?php echo $dialogcontent; ?>",
    });
	$(this).Bank();
	$(this).Address();
});