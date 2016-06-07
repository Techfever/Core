(function( $ ) {
	$.widget( "custom.combobox", {
		options: {
			id : "",
			source : [],
			autocomplete: {
				appendTo: null,
				autoFocus: false,
				delay: 0,
				minLength: 0,
				position: {
					my: "left top",
					at: "left bottom",
					collision: "none"
				},
				source: null,

				// callbacks
				change: null,
				close: null,
				focus: null,
				open: null,
				response: null,
				search: null,
				select: null
			}
		},
		_create: function() {
			this.options.id = this.element.prop("id"),
			this.wrapper = $( "<span>" )
	        .addClass( "ui-autocomplete-combobox" )
	        .insertAfter( this.element );

			this.element.hide();
		    this._createAutocomplete();
		    this._createShowAllButton();
	    },
	    _createAutocomplete: function() {
			var nodeName = this.element[0].nodeName.toLowerCase();
			this.isTextarea = nodeName === "textarea";
			this.isInput = nodeName === "input";
			this.isSelect = nodeName === "select";
	
			this.valueMethod = this.element[ this.isTextarea || this.isInput ? "val" : "text" ];

	        if($.type(this.options.autocomplete.source) !== "function" && this.isSelect){
	        	var source = [];
				var inputElement = this.element.children( "option" );
				var inputCount = 0;
				inputElement.each(function() {
		        	var text = $( this ).text();
		        	var val = $( this ).val();
		        	source[inputCount] = {
		            	label: text,
		                value: val,
		           	};
					
					inputCount++;
		        });
				this.options.source = source;
				
				var elementevents = [];
				$.each($._data(this.element[0], "events"), function(i, event){
					$.each(event, function(j, handler){
						elementevents.push(handler);
					});
				});
		        this.element.remove();
				
	        	this.options.autocomplete.source = $.proxy( this, "_source" );
	        }
	        if(this.isInput){
				this.element.show();
	        	this.input = this.element;
	        } else if(this.isSelect || this.isTextarea){
		        var input = $( "<input>" );
				$.each(elementevents, function(key, event){
					var type = "";
					var handler = "";
					if(event.type === "change"){
						type = "keypress";
					}else{
						type = event.type;
					}
					if(event.type === "focusout"){
						handler = function(){
							event.handler();
							input.removeClass("ui-input-focus");
						}
					}else{
						handler = function(){
							event.handler();
							input.addClass("ui-input-focus");
						}
					}
					input.on(type, handler);
				});
				this.input = input
		        this.element = this.input;
				this.input.attr("id", this.options.id)
				.attr("name", this.options.id)
	        	if(this.isSelect){
	        		var selected = this.element.children( ":selected" ),
	        		value = selected.val() ? selected.text() : "";
	        		this.input.val( value );
	        	}else if(this.isTextarea){
					this.element.show();
	        	}
	        }
	        
	        this.input.addClass( "ui-autocomplete-combobox-input ui-widget ui-widget-content" )
	        .appendTo( this.wrapper )
	        .autocomplete(this.options.autocomplete);

	        this._on( this.input, {
	            autocompleteselect: function( event, ui ) {
	            },
	        });
		},
		_createShowAllButton: function() {
			var input = this.input,
	        wasOpen = false;
	 
			$( "<a>" )
	        .attr( "tabIndex", -1 )
	        .attr( "title", "<?php echo $this->translate('text_show_all'); ?>" )
	        .tooltip()
	        .appendTo( this.wrapper )
	        .button({
	        	icons: {
	                primary: "ui-icon-triangle-1-s"
	        	},
	        	text: false
	        })
	        .removeClass( "ui-corner-all" )
	        .addClass( "ui-autocomplete-combobox-toggle ui-corner-right" )
	        .mousedown(function() {
	        	wasOpen = input.autocomplete( "widget" ).is( ":visible" );
	        })
	        .on("click", function() {
	        	input.focus();

	        	// Close if already visible
	            if ( wasOpen ) {
	              return;
	            }

	         // Pass empty string as value to search for, displaying all results
	            input.autocomplete( "search", "" );
	        });
		},
		_source: function( request, response ) {
			var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
	        response( this.options.source );
		},
		_destroy: function() {
		}
	});
})( jQuery );