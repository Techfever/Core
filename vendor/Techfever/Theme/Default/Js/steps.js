$(document).ready(function() {		
	var formname = "";
	var formid = "";
	var formuri = "";
	var wizardid = "";
	var tabcontrolid = "";
	var stepcontrolid = "";
	var actioncontrolid = "";
	
	var dialogtitle = "";
	var dialogcontent = "";
	var currentstep = 1;
	var totalstep = 1;
	var steps = new Array();
	
	var buttonPrevious = "";
	var buttonNext = "";
	var buttonFinish = "";

	$.fn.StepsReset = function() {
		formname = "";
		formid = "";
		formuri = "";
		wizardid = "";
		tabcontrolid = "";
		stepcontrolid = "";
		actioncontrolid = "";
		
		dialogtitle = "";
		dialogcontent = "";
		currentstep = 1;
		totalstep = 1;
		steps = new Array();
		
		buttonPrevious = "";
		buttonNext = "";
		buttonFinish = "";
	}
	
	$.fn.Steps = function(options) {
		var defaults = {
				formname : "",
				formuri : "",
				dialogtitle : "",
				dialogcontent : "",
			};
		var options = $.extend(defaults, options);
		
		formname = options.formname;
		formid = "form[id=" + options.formname + "]";
		formuri = options.formuri;
		wizardid = "table[id=wizardstep]";
		tabcontrolid = "table[class=tabcontrol]";
		stepcontrolid = "div[class=stepcontrol]";
		actioncontrolid = "table[class=actioncontrol]";

		dialogtitle = options.dialogtitle;
		dialogcontent = options.dialogcontent;
	
		buttonPrevious = $(formid + " " + wizardid + " " + actioncontrolid + " a[id=previous]");
		buttonNext = $(formid + " " + wizardid + " " + actioncontrolid + " a[id=next]");
		buttonFinish = $(formid + " " + wizardid + " " + actioncontrolid + " a[id=finish]");

		$(formid + " " + wizardid + " " + tabcontrolid + "").find( "td" ).each(
			function() {
				var id = $(this).attr('id');
				steps[totalstep] = id;
				$(formid + " " + wizardid + " " + tabcontrolid + " td[id="+id+"] div").removeClass("show");
				$(formid + " " + wizardid + " " + stepcontrolid + "[id="+id+"]").hide();
				totalstep++;
			}
		);
		totalstep--;

		$(formid + " " + wizardid + " " + tabcontrolid + " td[id="+steps[currentstep]+"] div").addClass("show");
		$(formid + " " + wizardid + " " + stepcontrolid + "[id="+steps[currentstep]+"]").show();
		
		$(formid + " table[class=form]").find( "tr" ).each(
			function() {
				var id = $(this).attr('id');
				var elementcss = $(this).attr('class');
				$(this).hover(
						function () {
							if (elementcss == "row") {
								$(this).addClass("hover"); 
							}
						}, 
						function () {
							if (elementcss == "row") {
								$(this).removeClass("hover"); 
							}
						}
					);
		
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
		
				$(this).find( 'div[id=radio]' ).each(
					function() {				
						$(this).buttonset();
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

		if(buttonFinish.attr('id') !== undefined){
			buttonFinish.addClass("disable").hide();
		}
		buttonPrevious.addClass("disable").hide();
		buttonNext.addClass("enable").show();

		buttonPrevious.click(function() {
			var previousstep = (currentstep - 1);
			$(this).stepPrevious(currentstep, previousstep);
		});
		
		buttonNext.click(function() {
			var nextstep = (currentstep + 1);
			if(currentstep <= totalstep){
				var ajaxPost = $.post(
						formuri,
						$(formid + " " + wizardid + " " + stepcontrolid + "[id="+steps[currentstep]+"] :input").serialize() + '&XMLHttpRequest=1', null, 'json'
		    	);
				
				ajaxPost.done(function(JsonReturn) {
					var messages = JsonReturn.messages;
					var totalMsg = JsonReturn.messagescount;
					var countMsg = 0;
					var isValid = true;
					if(totalMsg >= 1){
				    	$.each( messages, function( key, message ) {
				    		countMsg++;
				    		
							var element = formid + " " + wizardid + " " + stepcontrolid + "[id="+steps[currentstep]+"] tr[id=" + key + "] td[class=help] div[class=ui-widget] div[class=ui-state-active]";
							var elementclass = $(element).attr('class');
							if(elementclass !== undefined){
								var html = '';
								if (message.length > 0) {
									isValid = false;
									html = '<span class="ui-icon ui-icon-closethick"></span>' + message;
								} else {
									html = '<span class="ui-icon ui-icon-check"></span>';
								}
								$(element).html(html);
							}
							if((countMsg == totalMsg) && (isValid)){
								$(this).stepNext(currentstep, nextstep);
							}
				    	});
					}else{
						$(this).stepNext(currentstep, nextstep);
					}
		    	});
			}
		});

		if(buttonFinish.attr('id') !== undefined){
			buttonFinish.click(function() {
				$(this).stepFinish(currentstep);
			});
		}
	}
	
	$.fn.stepPrevious = function(currentIndex, previousIndex) {
		if(previousIndex == 1){
			buttonPrevious.removeClass("enable");
			buttonPrevious.addClass("disable").hide();
		}			
		if(buttonFinish.attr('id') !== undefined){
			buttonFinish.removeClass("enable");
			buttonFinish.addClass("disable").hide();
		}
		buttonNext.removeClass("disable");
		buttonNext.addClass("enable").show();
		
		buttonNext.html("<?php echo $this->translate('text_next'); ?>");
		
		$(formid + " " + wizardid + " " + tabcontrolid + " td[id="+steps[currentIndex]+"] div").removeClass("show");
			
		$(formid + " " + wizardid + " " + stepcontrolid + "[id="+steps[currentIndex]+"]").hide();
		$(formid + " " + wizardid + " " + stepcontrolid + "[id="+steps[previousIndex]+"]").show();

		currentstep--;
	}

	$.fn.stepNext = function(currentIndex, nextIndex) {	
		if(nextIndex == totalstep){
			buttonNext.removeClass("enable");
			buttonNext.addClass("disable").hide();

			if(buttonFinish.attr('id') !== undefined){
				buttonFinish.removeClass("disable");
				buttonFinish.addClass("enable").show();
			}

			buttonNext.html("<?php echo $this->translate('text_preview'); ?>");
		}
		buttonPrevious.removeClass("disable");
		buttonPrevious.addClass("enable").show();

		$(formid + " " + wizardid + " " + stepcontrolid + "[id="+steps[currentIndex]+"]").find(':input').not(':button, :submit, :reset, :hidden').each(function() {	
			var key = $(this).prop('name');
			var type = $(this).prop('type');
			var val = "";
			switch (type) {
				case 'select-one':
					val = $(this).find("option:selected").text();
					var isDay = key.indexOf("[day]");
					if(isDay > 0){
						key = key.substr(0, (key.length - 5));
						if(val.length > 0){
							val = val + "-";
						}else{
							val = "N/A-";
						}
					}
					var isMonth = key.indexOf("[month]");
					if(isMonth > 0){
						key = key.substr(0, (key.length - 7));
						if(val.length > 0){
							val = val + "-";
						}else{
							val = "N/A-";
						}
					}
					var isYear = key.indexOf("[year]");
					if(isYear > 0){
						key = key.substr(0, (key.length - 6));
						if(val.length > 0){
							val = val;
						}else{
							val = "N/A";
						}
					}
					break;
				case 'radio':
				case 'checkbox':
					if ($(this).is(':checked')) {
						val = $(this).next('label:first').text();
					}
					break;
				case 'password':
					val = '******';
					break;
				case 'undefined':
					break;
				default:
					val = $(this).val();
					break;
			}

			if(val.length < 1){
				val = "N/A";
			}
			var previewelement = formid + " " + wizardid + " " + stepcontrolid + "[id=preview] tr[id=" + key + "] td[class=value]";
			var previewelementclass = $(previewelement).attr('class');
			if(previewelementclass !== undefined){
				$(previewelement).html(val);
			}
		});
		
		$(formid + " " + wizardid + " " + tabcontrolid + " td[id="+steps[nextIndex]+"] div").addClass("show");
		
		$(formid + " " + wizardid + " " + stepcontrolid + "[id="+steps[currentIndex]+"]").hide();
		$(formid + " " + wizardid + " " + stepcontrolid + "[id="+steps[nextIndex]+"]").show();
		
		currentstep++;			
	}

	$.fn.stepFinish = function(currentIndex) {
		if(currentIndex == totalstep){
			var ajaxPost = $.post(
					formuri,
					$(formid + " " + wizardid + " " + stepcontrolid + "[id="+steps[currentIndex]+"] :input").serialize() + '&XMLHttpRequest=1', null, 'json'
	    	);
			
			ajaxPost.done(function(JsonReturn) {
				var messages = JsonReturn.messages;
				var totalMsg = JsonReturn.messagescount;
				var countMsg = 0;
				var isValid = true;
				if(totalMsg >= 1){
			    	$.each( messages, function( key, message ) {
			    		countMsg++;
			    		
						var element = formid + " " + wizardid + " " + stepcontrolid + "[id="+steps[currentIndex]+"] tr[id=" + key + "] td[class=help] div[class=ui-widget] div[class=ui-state-active]";
						var elementclass = $(element).attr('class');
						if(elementclass !== undefined){
							var html = '';
							if (message.length > 0) {
								isValid = false;
								html = '<span class="ui-icon ui-icon-closethick"></span>' + message;
							} else {
								html = '<span class="ui-icon ui-icon-check"></span>';
							}
							$(element).html(html);
						}
			    	});
				}else{
				    $(this).formSubmit({  
						uri: formuri,  
						form: formname,  
						data: $(formid).serialize() + '&submit=submit',
						title: dialogtitle,  
						message: dialogcontent,
						failcallback: function(){
							$(this).stepClear();
						},
				    });
				}
	    	});
			
		}
	}

	$.fn.stepClear = function() {
    	$.each( steps, function( key, step ) {
    		$(formid + " " + wizardid + " " + stepcontrolid + "[id=" + step + "]").find(':input').not(':button, :submit, :reset, :hidden').val('').removeAttr('checked').removeAttr('selected').each(function() {
				var type = $(this).prop('type');
				var key = $(this).prop('id');
				switch (type) {
				case 'radio':
				case 'checkbox':
					$(this).button("refresh");
					break;
				}
	    		
				$(formid + " " + wizardid + " " + stepcontrolid + "[id=" + step + "] tr[id=" + key + "] td[class=help] div[class=ui-widget] div[class=ui-state-active]").html('');

				var isDay = key.indexOf("[day]");
				if(isDay > 0){
					key = key.substr(0, (key.length - 5));
				}
				var isMonth = key.indexOf("[month]");
				if(isMonth > 0){
					key = key.substr(0, (key.length - 7));
				}
				var isYear = key.indexOf("[year]");
				if(isYear > 0){
					key = key.substr(0, (key.length - 6));
				}
				$(formid + " " + wizardid + " " + stepcontrolid + "[id=preview] tr[id=" + key + "] td[class=value]").html('');
			});
		});
	}
});