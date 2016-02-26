<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.12
//-----------------------------

require_once '../header.inc.php';
require_once '../global/ManageReport.class.php';
require_once inc_CurrencyModule;

if(isset($_REQUEST["print"]))
{
	$TafsiliID = !empty($_REQUEST["TafsiliID"]) ? $_REQUEST["TafsiliID"] : "";
	$param = array();
	if($TafsiliID != "")
		$param[] = $TafsiliID;
	
	$query = "select sum(CreditorAmount-DebtorAmount) amount, TafsiliDesc ,
		round(sum(CreditorAmount-DebtorAmount) /" . ShareBaseAmount . ") shareCount
				
	from ACC_DocItems 
		join ACC_docs using(DocID)
		join ACC_tafsilis using(TafsiliID)
		join BSC_persons on(ObjectID=PersonID)
	where CostID=" . COSTID_share . " AND CycleID=" . $_SESSION["accounting"]["CycleID"] . 
	($TafsiliID != "" ? " AND TafsiliID=?" : "") .
	" group by TafsiliID";

	$query .= " order by amount desc";
	$dataTable = PdoDataAccess::runquery($query, $param);
	
	echo Manage_Report::BeginReport();
	echo "<style>
		.page {
			padding : 200px
		}
		td {
			font-family:b titr;
			font-size:20px;
		}
		</style>";
	for($i=0; $i<count($dataTable);$i++)
	{
		echo "<div class=page><table width=100%>
			<tr><td align=center>" . $dataTable[$i]["TafsiliDesc"] . "</td></tr>
			<tr><td align=center>" . CurrencyModulesclass::CurrencyToString($dataTable[$i]["amount"]) . "</td><tr>
		</table></div>";
		
		if($i != count($dataTable)-1)
			echo Manage_Report::PageBreak();
	}
	echo "</center></body>";
	die();
}

?>
<script>
PrintShare.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	DocID : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function PrintShare()
{
	this.mainPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "چاپ برگه سهام",
		width : 400,
		items :[{
			xtype : "combo",
			width : 380,
			store: new Ext.data.Store({
				fields:["TafsiliID","TafsiliDesc"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=GetAllTafsilis',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				listeners : {
					beforeload : function(store){
						if(!store.proxy.extraParams.TafsiliType)
						{
							group = AccDocsObject.tafsiliGroupCombo.getValue();
							if(group == "")
								return false;
							this.proxy.extraParams["TafsiliType"] = group;
						}
					}
				}
			}),
			emptyText:'انتخاب تفصیلی ...',
			typeAhead: false,
			pageSize : 10,
			itemId : "TafsiliID",
			valueField : "TafsiliID",
			displayField : "TafsiliDesc"
		}],
		buttons : [{
			text : "چاپ برگه سهام",
			iconCls : "print",
			handler : function()
			{
				window.open(PrintShareObj.address_prefix + "PrintShare.php?print=true&TafsiliID=" + 
					PrintShareObj.mainPanel.down("[itemId=TafsiliID]").getValue());
			}
		}]
	});
}

PrintShareObj = new PrintShare();


</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div><br>
		<div><div id="regDocDIV"></div></div>
	</center>
</form>