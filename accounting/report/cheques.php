<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.02
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";

if(isset($_REQUEST["show"]))
{
	$Year = $_SESSION["accounting"]["CycleYear"];
	$query = "
	select c.*,d.docDate,a.*,b.InfoDesc as checkTitle,t.tafsiliDesc,bankDesc

	from ACC_DocCheques c
	left join ACC_tafsilis t using(tafsiliID)
	join ACC_docs d using(DocID)
	join ACC_accounts a using(AccountID)
	join ACC_banks bb using(BankID)
	join BaseInfo b on(b.typeID=3 AND b.infoID=CheckStatus)
	
	where d.DocStatus != 'RAW' AND d.CycleID=" . $_SESSION["accounting"]["CycleID"] . "
				AND d.BranchID=" . $_SESSION["accounting"]["BranchID"];

	$whereParam = array();
	if(!empty($_POST["fromDate"]))
	{
		$query .= " AND substring(d.docDate,1,10) >= :q1 ";
		$whereParam[":q1"] = DateModules::shamsi_to_miladi($_POST["fromDate"], "-");
	}
	if(!empty($_POST["toDate"]))
	{
		$query .= " AND substring(d.docDate,1,10) <= :q2 ";
		$whereParam[":q2"] = DateModules::shamsi_to_miladi($_POST["toDate"], "-");
	}
	if(!empty($_POST["l_fromDate"]))
	{
		$query .= " AND c.checkDate >= :fd ";
		$whereParam[":fd"] = DateModules::shamsi_to_miladi($_POST["l_fromDate"], "-");
	}
	if(!empty($_POST["l_toDate"]))
	{
		$query .= " AND c.checkDate <= :td ";
		$whereParam[":td"] = DateModules::shamsi_to_miladi($_POST["l_toDate"], "-");
	}
	if(!empty($_POST["checkStatus"]))
	{
		$query .= " AND c.checkStatus = :cs ";
		$whereParam[":cs"] = $_POST["checkStatus"];
	}
	if(!empty($_POST["checkNo"]))
	{
		$query .= " AND c.checkNo = :cn ";
		$whereParam[":cn"] = $_POST["checkNo"];
	}
	if(!empty($_POST["bankID"]))
	{
		$query .= " AND a.bankID = :b ";
		$whereParam[":b"] = $_POST["bankID"];
	}
	if(!empty($_POST["accountID"]))
	{
		$query .= " AND c.accountID = :ac ";
		$whereParam[":ac"] = $_POST["accountID"];
	}
	if(!empty($_POST["tafsiliID"]))
	{
		$query .= " AND c.tafsiliID = :taf ";
		$whereParam[":taf"] = $_POST["tafsiliID"];
	}
	$query .= " order by checkDate";

	$dataTable = PdoDataAccess::runquery($query, $whereParam);

	function dateRender($row, $value){
		return DateModules::miladi_to_shamsi($value);
	}
	
	function dateRender2($row,$val){
		return DateModules::miladi_to_shamsi($val);
	}
	
	function moneyRender($row,$val)
	{
		return number_format($val, 0, '.', ',');
	}
	function durationRender($row)
	{
		return (string)((int)substr($row["toDate"], 5, 2) - (int)substr($row["fromDate"], 5, 2) + 1);
	}
	
	$rpg = new ReportGenerator();
	$rpg->excel = !empty($_POST["excel"]);
	
	$rpg->addColumn("شماره سند", "docID");
	$rpg->addColumn("تاریخ سند", "docDate","dateRender");
	$rpg->addColumn("شماره چک", "checkNo");
	$rpg->addColumn("بانک", "bankTitle");
	$rpg->addColumn("شماره حساب", "accountNo");
	$rpg->addColumn("تاریخ چک", "checkDate","dateRender");
	$rpg->addColumn("وضعیت چک", "checkTitle");
	$col = $rpg->addColumn("مبلغ", "amount");
	$col->EnableSummary();
	$rpg->addColumn("تفصیلی گیرنده", "tafsiliTitle");
	
	$rpg->mysql_resource = $dataTable;
	if(!$rpg->excel)
	{
		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
		echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='../img/logo3.png'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>اعتماد شما سرلوحه خدمت ماست<br>
					گزارش چک ها
					";
		if(!empty($_POST["l_fromDate"]))
		{
			echo "<br>سررسید چک ها از تاریخ : " . $_POST["l_fromDate"] . ($_POST["l_toDate"] != "" ? " - " . $_POST["l_toDate"] : "");
		}
		if(!empty($_POST["checkStatus"]))
		{
			echo "<br>وضعیت : " . $_POST["statusName"];
		}
		echo	"</td>
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
AccReport_checks.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

AccReport_checks.prototype.showReport = function(btn, e)
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "cheques.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function AccReport_checks()
{
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش چک ها",
		defaults : {
			labelWidth :150
		},
		layout :{
			type : "table",
			columns :2
		},
		width : 700,
		items :[{
			xtype : "shdatefield",
			name : "fromDate",
			fieldLabel : "سند از تاریخ"
		},{
			xtype : "shdatefield",
			name : "toDate",
			fieldLabel : "تا تاریخ"
		},{
			xtype : "shdatefield",
			name : "l_fromDate",
			fieldLabel : "سررسید چک از تاریخ"
		},{
			xtype : "shdatefield",
			name : "l_toDate",
			fieldLabel : "تا تاریخ"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + "../baseinfo/baseinfo.data.php?task=SelectChequeStatuses",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['InfoID','InfoDesc'],
				autoLoad : true
			}),
			displayField : 'InfoDesc',
			valueField : 'infoID',
			hiddenName : "checkStatus",
			inputId : "statusName",
			fieldLabel : "وضعیت چک"
		},
		{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?' +
						"task=GetBankData",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['BankID','BankDesc'],
				autoLoad : true
			}),
			displayField : 'BankDesc',
			valueField : 'bankID',
			hiddenName : "bankID",
			fieldLabel : "نام بانک"
		},
		{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../baseinfo/baseinfo.data.php?' +
						"task=SelectAccounts",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['BankID','BankDesc'],
				autoLoad : true
			}),
			displayField : 'accountTitle',
			valueField : 'accountID',
			hiddenName : "accountID",
			fieldLabel : "حساب"
		},{
			xtype : "numberfield",
			fieldLabel : "شماره چک",
			name : "checkNo",
			hideTrigger : true
		},{
			xtype : "combo",
			colspan : 2,
			width : 620,
			displayField : "tafsiliTitle",
			fieldLabel : "حساب تفصیلی",
			valueField : "tafsiliID",
			hiddenName : "tafsiliID",
			store : new Ext.data.Store({
				fields:["tafsiliID","tafsiliTitle"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../account/data/tafsilis.data.php?task=selectTafsili',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			}),
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct">'
				,'<td>کد</td><td>عنوان</td>'
				,'<tpl for=".">'
				,'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
				,'<td style="border-left:0;border-right:0" class="search-item">{tafsiliID}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{tafsiliTitle}</td>'
				,'</tpl>'
				,'</table>')
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
					AccReport_checksObj.get('excel').value = "true";
				}
			},
			iconCls : "excel"
		},{
			text : "پاک کردن گزارش",
			iconCls : "clear",
			handler : function(){
				AccReport_checksObj.formPanel.getForm().reset();
				AccReport_checksObj.get("mainForm").reset();
			}			
		}]
	});
}

AccReport_checksObj = new AccReport_checks();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>