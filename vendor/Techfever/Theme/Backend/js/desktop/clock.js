(function( $ ){
	$.fn.Clock = function(options) {
		var defaultoptions = {
				element : this,
		};
		var settings = $.extend(true, defaultoptions, options);

		var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ]; 
		var dayNames= ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"]
		
		var instance = {
				day: function(){
					setInterval( function() {
						var newDate = new Date();
						newDate.setDate(newDate.getDate());
						settings.element.children(".ui-clock-date").children(".ui-clock-date-day").html(dayNames[newDate.getDay()]);
					}, 1000);	
				},
				date: function(){
					setInterval( function() {
						var newDate = new Date();
						newDate.setDate(newDate.getDate());
						settings.element.children(".ui-clock-date").children(".ui-clock-date-date").html(newDate.getDate());
					}, 1000);	
				},
				month: function(){
					setInterval( function() {
						var newDate = new Date();
						newDate.setDate(newDate.getDate());
						settings.element.children(".ui-clock-date").children(".ui-clock-date-month").html(monthNames[newDate.getMonth()]);
					}, 1000);	
				},
				year: function(){
					setInterval( function() {
						var newDate = new Date();
						newDate.setDate(newDate.getDate());
						settings.element.children(".ui-clock-date").children(".ui-clock-date-year").html(newDate.getFullYear());
					}, 1000);	
				},
				seconds: function(){
					setInterval( function() {
						var seconds = new Date().getSeconds();
						settings.element.children(".ui-clock-time").children(".ui-clock-time-seconds").html(( seconds < 10 ? "0" : "" ) + seconds);
					}, 1000);	
				},
				minutes: function(){
					setInterval( function() {
						var minutes = new Date().getMinutes();
						settings.element.children(".ui-clock-time").children(".ui-clock-time-minutes").html(( minutes < 10 ? "0" : "" ) + minutes);
					}, 1000);	
				},
				hours: function(){
					setInterval( function() {
						var hours = new Date().getHours();
						settings.element.children(".ui-clock-time").children(".ui-clock-time-hours").html(( hours < 10 ? "0" : "" ) + hours);
					}, 1000);	
				},
				init: function(){
					instance.day();
					instance.date();
					instance.month();
					instance.year();
					instance.hours();
					instance.minutes();
					instance.seconds();
				}
		}
		instance.init();
		
		instance = $.extend(true, instance, { settings : settings });
		return instance;
	}
})( jQuery );