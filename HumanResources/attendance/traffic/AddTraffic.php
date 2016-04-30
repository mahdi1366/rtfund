<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	95.01
//-------------------------
include('../../header.inc.php');
include_once inc_dataGrid;


$

?>
<style>
	.start {
		background-image: url('/HumanResources/icons/start.png');
		background-position: center center !important;
		height: 128px !important;
		width: 128px !important;
	}
	.stop {
		background-image: url('/HumanResources/icons/stop.png');
		background-position: center center !important;
		height: 128px !important;
		width: 128px !important;
	}
</style>
<script>

AddTraffic.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : '<?= $js_prefix_address ?>',

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function AddTraffic(){
	
	this.StartWork = new Ext.button.Button({
		text : "شروع کار",
		width : 133,
		height : 133,
		disabled : true,
		iconCls : "start",
		renderTo : this.get("StartWork"),
		handler : function(){}
	});
	
	this.StopWork = new Ext.button.Button({
		text : "پایان کار",
		width : 133,
		height : 133,
		disabled : true,
		iconCls : "stop",
		renderTo : this.get("StopWork"),
		handler : function(){}
	});
	
}

var AddTrafficObj = new AddTraffic();

</script>
<center>
	<br><br><br><br>
	<table width="500">
		<tr>
			<td width="50%">
				<div id="StartWork"></div>
			</td>
			<td width="50%">
				<div id="StopWork"></div>
			</td>
		</tr>
	</table>
</center>
