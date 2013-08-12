<?php 
$uri = $parameter['stepsformuri'];
$action = $parameter['stepsformaction'];
$form = str_replace('/', '_', ($parameter['stepsformid'] . '/' . $action));
$dialogtitle = $parameter['stepsformdialogtitle'];
$dialogcontent = $parameter['stepsformdialogcontent'];
?>
$(document).ready(function() {	
	$(this).Steps({  
		formname : "<?php echo $form; ?>",
		formuri : "<?php echo $this->url($uri, array('action' => $action)); ?>",
		dialogtitle : "<?php echo $dialogtitle; ?>",
		dialogcontent : "<?php echo $dialogcontent; ?>",
    });
	$(this).Bank();
	$(this).Address();
});