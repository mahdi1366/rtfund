<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";

if(isset($_REQUEST["show"]))
{
	$query = "select d.*, 
					concat(fname,' ',lname) as regPerson, 
					sum(CreditorAmount) bsSum,
					sum(DebtorAmount) bdSum
			from ACC_docs d
			join ACC_DocItems di using(docID)
			join FRW_persons p on(RegPersonID=PersonID)
			where d.CycleID=" . $_SESSION["accounting"]["CycleID"] . "
				AND d.BranchID=" . $_SESSION["accounting"]["BranchID"];
	
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
	if(!empty($_POST["bdFromAmount"]))
	{
		$query .= " AND di.DebtorAmount <= :q3 ";
		$whereParam[":q3"] = $_POST["bdFromAmount"];
	}
	if(!empty($_POST["bdToAmount"]))
	{
		$query .= " AND di.DebtorAmount >= :q4 ";
		$whereParam[":q4"] = $_POST["bdToAmount"];
	}
	if(!empty($_POST["bsFromAmount"]))
	{
		$query .= " AND di.CreditorAmount <= :q5 ";
		$whereParam[":q5"] = $_POST["bsFromAmount"];
	}
	if(!empty($_POST["bsToAmount"]))
	{
		$query .= " AND di.CreditorAmount >= :q6 ";
		$whereParam[":q6"] = $_POST["bsToAmount"];
	}
	if(!empty($_POST["from_regDate"]))
	{
		$query .= " AND d.RegDate >= :q7 ";
		$whereParam[":q7"] = DateModules::shamsi_to_miladi($_POST["from_regDate"], "-");
	}
	if(!empty($_POST["to_regDate"]))
	{
		$query .= " AND d.RegDate <= :q8 ";
		$whereParam[":q8"] = DateModules::shamsi_to_miladi($_POST["to_regDate"], "-");
	}
	if(!empty($_POST["description"]))
	{
		$query .= " AND d.description like :q9 ";
		$whereParam[":q9"] = '%' . $_POST["description"] . "%";
	}
	$query .= " group by DocID order by DocID";
	$dataTable = PdoDataAccess::runquery($query, $whereParam);

	$rpg = new ReportGenerator();
	$rpg->excel = !empty($_POST["excel"]);
	
	function dateRender($row, $val){
		return DateModules::miladi_to_shamsi($val);
	}	
	
	$rpg->addColumn("شماره سند", "LocalNo");
	$rpg->addColumn("تاریخ سند", "DocDate","dateRender");
	$rpg->addColumn("تاریخ ثبت سند", "RegDate","dateRender");
	$rpg->addColumn("ثبت کننده سند", "regPerson");
	$rpg->addColumn("توضیحات", "description");
	$rpg->addColumn("جمع بدهکار", "bdSum");
	$rpg->addColumn("جمع بسنانکار", "bsSum");
	
	$rpg->rowColorRender = "RowColorRender";
	function RowColorRender($row){
		if($row["DocStatus"] == "CONFIRM")
			return "#D0F7E2";
		if($row["DocStatus"] == "ARCHIVE")
			return "#FFFF9E";
		return "white";
	}
	
	$rpg->mysql_resource = $dataTable;
	if(!$rpg->excel)
	{
		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
		echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.png'></td>
				<td align='center' style='height:100px;vertical-align:middle;font-family:b titr;font-size:15px'>
					گزارش اسناد حسابداری
				</td>
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";
		if(!empty($_POST["fromDate"]))
		{
			echo "<br>گزارش از تاریخ : " . $_POST["fromDate"] . ($_POST["toDate"] != "" ? " - " . $_POST["toDate"] : "");
		}
		echo "</td></tr></table>";
		}
	$rpg->generateReport();
	die();
}
?>
<script>
AccReport_docs.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

AccReport_docs.prototype.showReport = function(btn, e)
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "docs.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function AccReport_docs()
{
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		layout :{
			type : "table",
			columns :2
		},
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش اسناد",
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
			xtype : "numberfield",
			name : "bdFromAmount",
			fieldLabel : "مبلغ ردیف بدهکار از",
			hideTrigger : true
		},{
			xtype : "numberfield",
			name : "bdToAmount",
			fieldLabel : "تا",
			hideTrigger : true
		},{
			xtype : "numberfield",
			name : "bsFromAmount",
			fieldLabel : "مبلغ ردیف بستانکار از",
			hideTrigger : true
		},{
			xtype : "numberfield",
			name : "bsToAmount",
			fieldLabel : "تا",
			hideTrigger : true
		},{
			xtype : "shdatefield",
			name : "from_regDate",
			fieldLabel : "تاریخ ثبت از"
		},{
			xtype : "shdatefield",
			name : "to_regDate",
			fieldLabel : "تا"
		},{
			xtype : "textfield",
			name : "description",
			fieldLabel : "شرح سند"
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
					AccReport_docsObj.get('excel').value = "true";
				}
			},
			iconCls : "excel"
		},{
			text : "پاک کردن گزارش",
			iconCls : "clear",
			handler : function(){
				AccReport_docsObj.formPanel.getForm().reset();
				AccReport_docsObj.get("mainForm").reset();
			}			
		}]
	});
}

AccReport_docsObj = new AccReport_docs();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>