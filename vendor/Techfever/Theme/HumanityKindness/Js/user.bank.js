<?php 
$form = $parameter['bankformid'];
?>

$(document).ready(function() {		
	$.fn.Bank = function() {
		var bankNameSelect = $("form[id=<?php echo $form; ?>] table[class=form] select[id=user_bank_name]");
		var bankNameText = $("form[id=<?php echo $form; ?>] table[class=form] tr[id=user_bank_name_text]");
		var bankName = $("form[id=<?php echo $form; ?>] table[class=form] input[id=user_bank_name_text]");
		var bankCountrySelect = $("form[id=<?php echo $form; ?>] table[class=form] select[id=user_bank_country]");
		var bankCountryText = $("form[id=<?php echo $form; ?>] table[class=form] tr[id=user_bank_country_text]");
		var bankCountry = $("form[id=<?php echo $form; ?>] table[class=form] input[id=user_bank_country_text]");
		var bankStateSelect = $("form[id=<?php echo $form; ?>] table[class=form] select[id=user_bank_state]");
		var bankStateText = $("form[id=<?php echo $form; ?>] table[class=form] tr[id=user_bank_state_text]");
		var bankState = $("form[id=<?php echo $form; ?>] table[class=form] input[id=user_bank_state_text]");
		var bankBranchSelect = $("form[id=<?php echo $form; ?>] table[class=form] select[id=user_bank_branch]");
		var bankBranchText = $("form[id=<?php echo $form; ?>] table[class=form] tr[id=user_bank_branch_text]");
		var bankBranch = $("form[id=<?php echo $form; ?>] table[class=form] input[id=user_bank_branch_text]");
		var userModify = $("form[id=<?php echo $form; ?>] table[class=form] input[id=user_modify]");
		
		var bankUser = new Array();
		var bankUserAction = new Array();
		bankUserAction["bank"] = true;
		bankUserAction["country"] = true;
		bankUserAction["state"] = true;
		bankUserAction["branch"] = true;
		$.post("<?php echo $this->url('Ajax/Bank', array('action' => 'getUser')); ?>", {
			'id' : userModify.val()
		}, function(JsonReturn) {
			if (JsonReturn.success == 1) {
				var dataReturn = JsonReturn.data;
				var dataLength = JsonReturn.length;
				var dataCount = 1;
				$.each(JsonReturn.data, function (key, value) {
					bankUser[key] = value;
					if(dataLength == dataCount){
						bankNameSelect.change();
					}
					dataCount++;
				});
			}
		}, 'json');

		bankNameSelect.change(
			function() {	
				bankNameText.hide();
				bankCountrySelect.prop('disabled', true);
				bankCountrySelect.val("");
				bankStateSelect.prop('disabled', true);
				bankStateSelect.val("");
				bankStateText.hide();
				bankBranchSelect.prop('disabled', true);
				bankBranchSelect.val("");
				bankBranchText.hide();
				var id = bankNameSelect.val()
				if(id >= 0 && id != ""){
					bankCountrySelect.prop('disabled', false);
					bankName.val("");
					if(id == "0") {
						bankNameText.show();
						bankName.val(bankUser["user_bank_name_text"]);
					}else{				
						val = bankNameSelect.find("option:selected").text()
						bankName.val(val);
					}
				}
				if(bankUserAction["bank"]){
					bankUserAction["bank"] = false;
					bankCountrySelect.val(bankUser["user_bank_country"]);
					bankCountrySelect.change();
					bankCountry.val(bankUser["user_bank_country_text"]);
				}

				return false;
			}
		);

		bankCountrySelect.change(
			function() {		
				if(bankCountrySelect.val() > 0){
					val = bankCountrySelect.find("option:selected").text();
					bankCountry.val(val);
				}
				$.post("<?php echo $this->url('Ajax/Bank', array('action' => 'getState')); ?>", {
					'country' : bankCountrySelect.val()
				}, function(JsonReturn) {
					bankStateSelect.prop('disabled', true);
					bankStateSelect.val("");
					bankStateText.hide();
					bankBranchSelect.prop('disabled', true);
					bankBranchSelect.val("");
					bankBranchText.hide();
					if (JsonReturn.success == 1) {
						bankStateSelect.find('option').remove().end();
						$.each(JsonReturn.data, function (i, elem) {
							bankStateSelect.append($("<option></option>").attr("value",elem.id).text(elem.value)); 
						});
						if (JsonReturn.valid == "0") {
							bankStateSelect.val("0");
							bankStateText.show();
						}else{
							bankStateSelect.prop('disabled', false);
						}
					}
					if(bankUserAction["country"]){
						bankUserAction["country"] = false;
						bankStateSelect.val(bankUser["user_bank_state"]);
						bankStateSelect.change();
						bankState.val(bankUser["user_bank_state_text"]);
					}
				}, 'json');

				return false;
			}
		);

		bankStateSelect.change(
			function() {	
				bankStateText.hide();
				bankBranchSelect.prop('disabled', false);
				bankBranchSelect.val("");
				bankBranchText.hide();
				
				var id = bankStateSelect.val()
				if(id == "0"){
					bankState.val("");
					bankStateText.show();
					
				} else if(id > 0){
					val = bankStateSelect.find("option:selected").text()
					bankState.val(val);
					$.post("<?php echo $this->url('Ajax/Bank', array('action' => 'getBranch')); ?>", {
						'country' : bankCountrySelect.val(),
						'state' : bankStateSelect.val(),
						'bank' : bankNameSelect.val()
					}, function(JsonReturn) {
						if (JsonReturn.success == 1) {
							bankBranchSelect.find('option').remove().end();

							$.each(JsonReturn.data, function (i, elem) {
								bankBranchSelect.append($("<option></option>").attr("value",elem.id).text(elem.value)); 
							});
							if (JsonReturn.valid == "0") {
								bankBranchSelect.val("0");
								bankBranchText.show();
							}else{
								bankBranchSelect.prop('disabled', false);
							}
						}
					}, 'json');
				}

				if(bankUserAction["state"]){
					bankUserAction["state"] = false;
					bankBranchSelect.val(bankUser["user_bank_branch"]);
					bankBranchSelect.change();
					bankBranch.val(bankUser["user_bank_branch_text"]);
				}

				return false;
			}
		);

		bankBranchSelect.change(
			function() {	
				bankBranchText.hide();
				var id = bankBranchSelect.val()
				bankBranch.val("");
				if(id == "0") {
					bankBranchText.show();
				}else{
					val = bankBranchSelect.find("option:selected").text()
					bankBranch.val(val);
				}

				if(bankUserAction["branch"]){
					bankUserAction["branch"] = false;
					bankBranchSelect.val(bankUser["user_bank_branch"]);
					bankBranch.val(bankUser["user_bank_branch_text"]);
				}

				return false;
			}
		);
	}
});