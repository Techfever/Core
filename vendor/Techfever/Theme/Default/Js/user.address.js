<?php 
$form = $parameter['addressformid'];
?>

$(document).ready(function() {	
	$.fn.Address = function() {
	var addressCountrySelect = $("form[id=<?php echo $form; ?>] table[class=form] select[id=user_address_country]");
	var addressCountryText = $("form[id=<?php echo $form; ?>] table[class=form] tr[id=user_address_country_text]");
	var addressCountry = $("form[id=<?php echo $form; ?>] table[class=form] input[id=user_address_country_text]");
	var addressStateSelect = $("form[id=<?php echo $form; ?>] table[class=form] select[id=user_address_state]");
	var addressStateText = $("form[id=<?php echo $form; ?>] table[class=form] tr[id=user_address_state_text]");
	var addressState = $("form[id=<?php echo $form; ?>] table[class=form] input[id=user_address_state_text]");
	var userModify = $("form[id=<?php echo $form; ?>] table[class=form] input[id=user_modify]");
	
	var addressUser = new Array();
	var addressUserAction = new Array();
	addressUserAction["country"] = true;
	addressUserAction["state"] = true;
	
	$.post("<?php echo $this->url('Ajax/Address', array('action' => 'getUser')); ?>", {
		'id' : userModify.val()
	}, function(JsonReturn) {
		if (JsonReturn.success == 1) {
			var dataReturn = JsonReturn.data;
			var dataLength = JsonReturn.length;
			var dataCount = 1;
			$.each(dataReturn, function (key, value) {
				addressUser[key] = value;
				if(dataLength == dataCount){
					addressCountrySelect.change();
				}
				dataCount++;
			});
		}
	}, 'json');
	
	addressCountrySelect.change(
		function() {		
			if(addressCountrySelect.val() > 0){
				val = addressCountrySelect.find("option:selected").text();
				addressCountry.val(val);
			}

			$.post("<?php echo $this->url('Ajax/Address', array('action' => 'getState')); ?>", {
				'country' : addressCountrySelect.val()
			}, function(JsonReturn) {
				addressStateSelect.prop('disabled', true);
				addressStateSelect.val("");
				addressStateText.hide();
				if (JsonReturn.success == 1) {
					addressStateSelect.find('option').remove().end();
					$.each(JsonReturn.data, function (i, elem) {
						addressStateSelect.append($("<option></option>").attr("value",elem.id).text(elem.value)); 
					});
					if (JsonReturn.valid == "0") {
						addressStateSelect.val("0");
						addressStateText.show();
					}else{
						addressStateSelect.prop('disabled', false);
					}
					if(addressUserAction["country"]){
						addressUserAction["country"] = false;
						addressStateSelect.val(addressUser["user_address_state"]);
						addressStateSelect.change();
						addressState.val(addressUser["user_address_state_text"]);
					}
				}
			}, 'json');

			return false;
		}
	);

	addressStateSelect.change(
		function() {	
			addressStateText.hide();
			var id = addressStateSelect.val();
			addressState.val("");
			if(id == 0){
				addressStateText.show();
			}else{				
				val = addressStateSelect.find("option:selected").text()
				addressState.val(val);
			}
			if(addressUserAction["state"]){
				addressUserAction["state"] = false;
				addressStateSelect.val(addressUser["user_address_state"]);
				addressState.val(addressUser["user_address_state_text"]);
			}

			return false;
		}
	);
	}
});