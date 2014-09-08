<?php 
$level = $parameter['hierarchylevel'];
?>
	function HierarchyInit(){
		var defaultlevel = <?php echo $level ?>;
		$('table[class=structurehierarchy]').each(function(){
			var id = this.id;
			var idarr = id.split("_");
			var level = parseInt(idarr[0]);
			var tablestructure = $("table[class=structurehierarchy][id=" + id + "]");
			tablestructure.hide();
			if(level <= defaultlevel){
				tablestructure.show();
			}
			var structurecontainer = $("div[id=structurecontainer]");
			structurecontainer.scrollLeft(0);
		});
	}
	
	function HierarchyClick(key, downline){
		var keyarr = key.split("_");
		var level = parseInt(keyarr[0]);
		var current = keyarr[1];
		var upline = keyarr[2];
		
		if(level > 1){
			var nextlevel = (level + 1);
			if(downline == "False"){
				nextlevel = (nextlevel - 1)
			}
			var tablestructure = $("table[class=structurehierarchy][id=" + nextlevel + "_" + current + "]");
			tablestructure.show();
			
			var previouslevel = (level - 2);
			if(previouslevel >= 0){
				var structurecontainer = $("div[id=structurecontainer]");
				structurecontainer.scrollLeft(145 * previouslevel);
			}
		}
	}