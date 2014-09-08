<?php 
$form = $parameter['permissionformid'];
?>
$(document).ready(function() {	
	$.fn.Permission = function() {
		var divPermissionDefined = $("form[id=<?php echo $form; ?>] table[class=form] tr[id=user_access_permission]");
		var radioPermissionAs = $("form[id=<?php echo $form; ?>] table[class=form]").find("input[type=radio][name=user_access_permission_as]");
		
		radioPermissionAs.change(
			function() {
				divPermissionDefined.hide();
				if ($(this).is(':checked')) {
					val = $(this).val();
					if(val == "user_access_permission_as_defined"){
						divPermissionDefined.show();
					}
				}
			}
		);
		
		radioPermissionAs.change();
	};
});