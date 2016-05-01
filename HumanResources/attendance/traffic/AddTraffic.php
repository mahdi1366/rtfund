<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	95.01
//-------------------------
include('../../header.inc.php');
include_once inc_dataGrid;
require_once 'traffic.class.php';

$dt = ATN_traffic::Get(" AND PersonID=? AND TrafficDate=?", 
	array($_SESSION["USER"]["PersonID"], DateModules::Now() ));

$even = count($dt) == 0 ? true : count($dt) % 2 == 0;

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

	even : <?= $even ? "true" : "false" ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function AddTraffic(){
	
	this.StartWork = new Ext.button.Button({
		text : "شروع کار",
		width : 133,
		height : 133,
		disabled : !this.even,
		iconCls : "start",
		renderTo : this.get("StartWork"),
		handler : function(){ AddTrafficObj.AddRow();}
	});
	
	this.StopWork = new Ext.button.Button({
		text : "پایان کار",
		width : 133,
		height : 133,
		disabled : this.even,
		iconCls : "stop",
		renderTo : this.get("StopWork"),
		handler : function(){ AddTrafficObj.AddRow();}
	});
	
}

AddTraffic.prototype.AddRow = function(){
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix +'traffic.data.php',
		method: "POST",
		params: {
			task: "AddTraffic"
		},
		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(!st.success)
			{
				if(st.data == "")
					Ext.MessageBox.alert("","خطا در اجرای عملیات");
				else
					Ext.MessageBox.alert("",st.data);
			}
			else
			{
				if(AddTrafficObj.StartWork.isDisabled())
				{
					AddTrafficObj.StartWork.enable();
					AddTrafficObj.StopWork.disable();
				}
				else
				{
					AddTrafficObj.StartWork.disable();
					AddTrafficObj.StopWork.enable();
				}
			}
		},
		failure: function(){}
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
