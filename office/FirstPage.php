<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.10
//-----------------------------

require_once 'header.inc.php';
require_once inc_dataGrid;



?>
<script>

StartPage.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function StartPage(){
	
	new Ext.panel.Panel({
		frame : true,
		title : "خلاصه کارتابل",
		applyTo : 
	});
}

StartPageObject = new StartPage();

</script>
<center><br>
	<div id="DivGrid1"></div><br>
	<div id="DivGrid2"></div>
</center>