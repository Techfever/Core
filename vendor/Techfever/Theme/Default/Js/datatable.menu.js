<?php 
$datatableid = str_replace('/', '_', $parameter['datatableid']);
$datalisturi = $parameter['datalisturi'];
$datacolumn = (isset($parameter['datacolumn']) ? $parameter['datacolumn'] : null);
$datasearch = (isset($parameter['datasearch']) ? $parameter['datasearch'] : null);
$jtableFields = "" . "\n";
$jtablePrimary = null;
if(is_array($datacolumn) && count($datacolumn) > 0){
	foreach ($datacolumn as $datacolumnvalue) {
		$jtableFields .= "            	".$datacolumnvalue['column'] .": {" . "\n";
		$jtableFields .= "            		"."title: '" . $this->translate('text_'.$datacolumnvalue['key']) . "'," . "\n";
		$jtableFields .= "            		"."width: 'auto'," . "\n";  
		if($datacolumnvalue['primary'] == '1'){
			if(empty($jtablePrimary)){
				$jtablePrimary = $datacolumnvalue['column'] . ' ASC';
			}else{
				$jtablePrimary = ','.$datacolumnvalue['column'] . ' ASC';
			}
			$jtableFields .= "            		"."visibility: 'fixed'," . "\n";  
		}else{
			if($datacolumnvalue['default'] == '1'){
				$jtableFields .= "            		"."list: true," . "\n";
			}else{
				$jtableFields .= "            		"."visibility: 'hidden'," . "\n";
			}
		}
		$jtableFields .= "            	"."}," . "\n";
	}
}
$jtableSearchSimple = null;
$jtableSearchAdvance = null;
if(is_array($datasearch) && count($datasearch) > 0){
	foreach ($datasearch as $datasearchvalue) {
		if ($datasearchvalue['flag'] == 1) {
			$jtableSearchSimple .= $datasearchvalue['column'] .": $('form[id=searchsimple] input[name=search_".$datasearchvalue['column']."]').val()," . "\n";
		}
		$jtableSearchAdvance .= $datasearchvalue['column'] .": $('form[id=searchadvance] input[name=search_".$datasearchvalue['column']."]').val()," . "\n";
	}
}
?>
$(document).ready(function() {	
	$("div[id=menutabs]").tabs({
		collapsible : true,
		activate : function(event, ui) {
			var active = $("div[id=menutabs]").tabs("option", "active");
			var islink = $("div[id=menutabs] ul>li a").eq(active).attr('id');
			if (islink == "href") {
				var href = $("div[id=menutabs] ul>li a").eq(active).attr('href');
				window.location.replace(href);
			}
		}
	});

	$("div[id=tabs-search] div[id=menusearch]").accordion({
		collapsible : true,
		heightStyle : "content",
	});
	
	$("div[id=tabs-search] div[id=menusearch]").find( "div" ).each(
			function() {
				if($(this).attr('id') == 'form'){	
					
				}
			}
	);
	
	$("div[id=tabs-column] div[id=menucolumn]")
			.addClass("ui-accordion ui-widget ui-helper-reset")
			.find("h3")
			.addClass(
					"ui-accordion-header ui-helper-reset ui-state-default ui-corner-all ui-accordion-icons")
			.prepend(
					'<span class="ui-accordion-header-icon ui-icon ui-icon-triangle-1-s"></span>')
			.next()
			.addClass(
					"ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom")
					
	$("div[id=tabs-column] div[id=menucolumn]").find( "div" ).each(
			function() {
				if($(this).attr('id') == 'checkbox'){					
					$(this).buttonset();
					$(this).find( "input[type=checkbox]" ).each(
						function() {
							$(this).click(function () {
		                        var $clickedCheckbox = $(this);
		                        var clickedColumnName = $clickedCheckbox.attr('id');
		                        
		                        $("#datatable").jtable('changeColumnVisibility', clickedColumnName, ($clickedCheckbox.is(':checked') ? 'visible' : 'hidden'));
		                    });
						}
					);
				}
			}
	);

	$("div[id=datatable]").jtable({
            tableId: '<?php echo $datatableid; ?>',       
            saveUserPreferences: false,
            columnSelectable: false,
            defaultSorting: '<?php echo $jtablePrimary; ?>',
            actions: {
                listAction: '<?php echo $datalisturi; ?>/Get',
            },
            sorting: true,
            paging: true,
            pageSize: 10,
            fields: {
            	no: {
                    title: 'No.',
                    visibility: 'fixed',                    
                    sorting: false,
                    width: '10px',
                },                
                id: {
                    key: true,
                    create: false,
                    edit: false,
                    list: false
                },<?php echo $jtableFields; ?>
            	manage: {                     
            		title: '',
                    width: '10px',
                    sorting: false,
                    edit: false,
                    create: false,
                    display: function (recordData) {
                        var $img = $('<img src="<?php echo $this->serverUrl($this->baseHref() . '/Theme/Image/Icons/alacarte-24x24.png') ?>"/>');
                        $img.click(function () {
                            $('div[id=datatable]').jtable('openChildTable',
                                    $img.closest('tr'),
                                    {    
                                		saveUserPreferences: false,
                                		columnSelectable: false,
                                        sorting: false,
                                        paging: false,
                            			disableTableHead: true,
                                        actions: {
                                            listAction: '<?php echo $datalisturi; ?>/Manage/' + recordData.record.id,
                                        },
                                        fields: {                                            
                                        	legend: {
                                                width: '100%'
                                            },
                                        }
                                    }, function (data) { // opened handler
                                        data.childTable.jtable('load');
                                    });
                        });
                        // Return image to show on the person row
                        return $img;
                    },
                },
            }
        });

	
    $("form[id=searchsimple]").submit(function(e) {
        e.defaultPrevented();
        return false;
    });
    
    $('div[id=menusearch] form[id=searchsimple] button[id=simplebutton]').click(function (e) {
        $('div[id=datatable]').jtable('load', {
        	<?php echo $jtableSearchSimple; ?>
        });
        e.defaultPrevented();
        return false;
    });


    $("form[id=searchadvance]").submit(function(e) {
        e.defaultPrevented();
        return false;
    });
    
    $('div[id=menusearch] form[id=searchadvance] button[id=advancebutton]').click(function (e) {
        $('div[id=datatable]').jtable('load', {
        	<?php echo $jtableSearchAdvance; ?>
        });
        e.defaultPrevented();
        return false;
    });
    
    $('div[id=menusearch] form[id=searchsimple] button[id=simplebutton]').click();
});