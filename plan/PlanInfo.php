<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.11
//-----------------------------

require_once 'header.inc.php';
require_once inc_dataGrid;

//$PlanID = !empty($_POST["PlanID"]) ? $_POST["PlanID"] : 0;
$PlanID = 1;

if(isset($_SESSION["USER"]["framework"]))
	$User = "Staff";
else
{
	if($_SESSION["USER"]["IsAgent"] == "YES")
		$User = "Agent";
	else if($_SESSION["USER"]["IsCustomer"] == "YES")
		$User = "Customer";
}
//------------------------------------------------------------------------------
$items = MakeForms();

function MakeForms(){
	
	$items = "";
	$data = PdoDataAccess::runquery("select * from PLN_groups where ParentID=0");
	foreach($data as $season)
	{
		$items .= "{title : '" . $season["GroupDesc"] . "',
			items :[
				new Ext.tree.Panel({
					store: new Ext.data.TreeStore({
						proxy: {
							type: 'ajax',
							url: this.address_prefix + 
								'plan.data.php?task=selectSubGroups&ParentID=" . $season["GroupID"] . "'
						}					
					}),
					root: {id: 'src'},
					rootVisible: false,
					height : 100,
					width : 780,
					listeners : {
						itemdblclick : function(v,record){
							if(!record.data.leaf) return; 
							PlanInfoObject.LoadElements(record," . $season["GroupID"] . ");}
					}
				}),
				new Ext.panel.Panel({
					itemId : 'ParentElement_" . $season["GroupID"] . "',
					bodyStyle : 'padding:4px;',
					height : 460,
					border : false,
					autoScroll : true
				})
			]},";
	}
	
	return substr($items, 0, strlen($items)-1);
}
//------------------------------------------------------------------------------

require_once 'PlanInfo.js.php';

if(isset($_SESSION["USER"]["framework"]))
	echo "<br>";
?>
<style>
	.desc{
		text-align: justify;
		line-height: 20px;
		margin:0 10 0 10;
	}
</style>
<center>
	<div align="right" style="width:780px"> 
		<form id="mainForm"></form>
	</div>
</center>