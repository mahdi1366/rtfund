<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$query = "select sum(CreditorAmount-DebtorAmount) amount, 
				ShareNo,
				TafsiliID,
				TafsiliDesc,
				round(sum(CreditorAmount-DebtorAmount) /" . ShareBaseAmount . ") shareCount
				 
	from ACC_DocItems 
		join ACC_docs using(DocID)
		join ACC_cycles using(CycleID)
		join ACC_tafsilis using(TafsiliID)
		join BSC_persons on(ObjectID=PersonID)
	where CostID=" . COSTID_share . " AND PersonID= " . $_SESSION["USER"]["PersonID"] . "
		AND DocType not in('".DOCTYPE_STARTCYCLE."','".DOCTYPE_ENDCYCLE."')
	group by TafsiliID
	order by amount desc";

$dataTable = PdoDataAccess::runquery($query);

if(count($dataTable) == 0)
{
	echo "<b><br>سهامی به نام شما در سیستم ثبت نشده است.</b>";
	die();
}

$TotalShare = 0;
foreach($dataTable as $row)
	$TotalShare += $row["shareCount"]*1;

?>
<script>
ShareInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ShareInfo()
{
	new Ext.panel.Panel({
		renderTo : this.get("panelDIV"),
		layout : 'vbox',
		frame : true,
		width : 400,
		items : [{
			xtype : "displayfield",
			fieldLabel : "شماره دفتر",
			style : "margin-top:10px",
			fieldCls : "blueText",
			value : "<?= $dataTable[0]["ShareNo"] ?>"
		},{
			xtype : "displayfield",
			fieldLabel : "تعداد سهام",
			style : "margin-top:10px",
			fieldCls : "blueText",
			value : "<?= $dataTable[0]["shareCount"] . " سهم" ?>"
		},{
			xtype : "displayfield",
			fieldLabel : "ارزش سهام",
			style : "margin-top:10px;margin-bottom:10px",
			fieldCls : "blueText",
			value : "<?= number_format($dataTable[0]["amount"]) . " ریال" ?>"
		}],
		buttons : [{
			xtype : "button",
			iconCls : "report",
			text : "چاپ برگه سهام",
			handler : function(){
				window.open("/accounting/share/PrintShare.php?portal=true&print=true&TafsiliID=" +
					"<?= $dataTable[0]["TafsiliID"] ?>");
			}
		}]
	});
}

ShareInfoObject = new ShareInfo();


</script>
<center>
<div id="panelDIV"></div>
</center>

