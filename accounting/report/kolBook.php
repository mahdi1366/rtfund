<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";
require_once '../docs/doc.class.php';
require_once inc_CurrencyModule;

if(isset($_REQUEST["show"]))
{
	$query = "select b1.BlockCode,b1.BlockDesc,
			d.DocDate,
			if(d.DocType=".DOCTYPE_ENDCYCLE.",1,0) IsEndDoc,
			b.InfoDesc TafsiliType,
			t.TafsiliDesc,
			t2.TafsiliDesc TafsiliDesc2,
			di.details,
			sum(DebtorAmount) DSUM, 
			sum(CreditorAmount) CSUM
			
		from ACC_DocItems di
			join ACC_docs d using(docID)
			join ACC_CostCodes cc using(CostID)
			left join ACC_blocks b1 on(cc.level1=b1.BlockID)
			left join BaseInfo b on(di.TafsiliType=InfoID AND TypeID=2)
			left join ACC_tafsilis t on(t.TafsiliID=di.TafsiliID)
			left join ACC_tafsilis t2 on(t2.TafsiliID=di.TafsiliID2)
			
		where d.CycleID=" . $_SESSION["accounting"]["CycleID"];
	
	$whereParam = array();
	
	if(!empty($_POST["fromDate"]))
	{
		$query .= " AND d.DocDate >= :q1 ";
		$whereParam[":q1"] = DateModules::shamsi_to_miladi($_POST["fromDate"], "-");
	}
	if(!empty($_POST["toDate"]))
	{
		$query .= " AND d.DocDate <= :q2 ";
		$whereParam[":q2"] = DateModules::shamsi_to_miladi($_POST["toDate"], "-");
	}
	
	if(!isset($_REQUEST["IncludeRaw"]))
		$query .= " AND d.DocStatus != 'RAW' ";
	
	$query .= " group by if(DocType=".DOCTYPE_ENDCYCLE.",2,1),b1.blockCode,DocDate ";
	$query .= " order by b1.blockCode,if(DocType=".DOCTYPE_ENDCYCLE.",2,1),DocDate,if(DebtorAmount<>0,0,1)";
	
	$dataTable = PdoDataAccess::runquery($query, $whereParam);
	//echo PdoDataAccess::GetLatestQueryString();
	$rpg = new ReportGenerator();
	//$rpg->rowNumber = false;
	//$rpg->page_size = 20;
	//$rpg->paging = true;
	$rpg->excel = !empty($_POST["excel"]);
	$rpg->mysql_resource = $dataTable;if(!$rpg->excel)
	{
		BeginReport();
		$rpg->headerContent = 
		"<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
				<td align='center' style='height:100px;vertical-align:middle;font-family:titr;font-size:15px'>
					گزارش دفتر کل
					 <br> ".
				 $_SESSION["accounting"]["BranchName"]. "<br>" . "دوره سال " .
				$_SESSION["accounting"]["CycleID"] .
				"</td>
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";
		if(!empty($_POST["fromDate"]))
		{
			$rpg->headerContent .= "<br>گزارش از تاریخ : " . $_POST["fromDate"] . ($_POST["toDate"] != "" ? " - " . $_POST["toDate"] : "");
		}
		$rpg->headerContent .= "</td></tr></table>";
	}

	$rpg->groupPerPage = true;
	$rpg->groupField = "BlockCode";
	$rpg->groupLabelRender = "GroupRender";
	function GroupRender($row, $index){
		return "حساب کل : [ " . $row["BlockCode"] . " ] " . $row["BlockDesc"];
	}

	$rpg->addColumn("تاریخ سند", "DocDate","ReportDateRender");
	$rpg->addColumn("بدهکار", "DSUM","ReportMoneyRender");
	$rpg->addColumn("بستانکار", "CSUM","ReportMoneyRender");
	
	function bdremainRender($row){
		$v = $row["DSUM"] - $row["CSUM"];
		return $v < 0 ? 0 : number_format($v);
	}
	
	function bsremainRender($row){
		$v = $row["CSUM"] - $row["DSUM"];
		return $v < 0 ? 0 : number_format($v);
	}
	
	$col = $rpg->addColumn("مانده بدهکار", "DSUM", "bdremainRender");
	$col->EnableSummary(true);
	$col = $rpg->addColumn("مانده بستانکار", "CSUM", "bsremainRender");
	$col->EnableSummary(true);
	
	echo $rpg->generateReport();
	die();
}
?>
<script>
AccReport_daily.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

AccReport_daily.prototype.showReport = function(btn, e)
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "kolBook.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function AccReport_daily()
{
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		layout :{
			type : "table",
			columns :2
		},
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش  دفتر روزنامه",
		defaults : {
			labelWidth :120
		},
		width : 600,
		items :[{
			xtype : "shdatefield",
			name : "fromDate",
			fieldLabel : "تاریخ سند از"
		},{
			xtype : "shdatefield",
			name : "toDate",
			fieldLabel : "تا"
		},{
			xtype : "container",
			colspan : 2,
			html : "<input type=checkbox name=IncludeRaw> گزارش شامل اسناد پیش نویس نیز باشد"
		}],
		buttons : [{
			text : "مشاهده گزارش",
			handler : Ext.bind(this.showReport,this),
			iconCls : "report"
		},{
			text : "خروجی excel",
			handler : Ext.bind(this.showReport,this),
			listeners : {
				click : function(){
					AccReport_dailyObj.get('excel').value = "true";
				}
			},
			iconCls : "excel"
		},{
			text : "پاک کردن گزارش",
			iconCls : "clear",
			handler : function(){
				AccReport_dailyObj.formPanel.getForm().reset();
				AccReport_dailyObj.get("mainForm").reset();
			}			
		}]
	});
}

AccReport_dailyObj = new AccReport_daily();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>