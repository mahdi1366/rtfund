<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.02
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";

if(isset($_REQUEST["show"]))
{
	$param = array();
	$query = "select p.* , 
			concat_ws(' ',fname,lname,CompanyName) fullname,
			PartAmount,
			PartDate,
			b.BankDesc, 
			bi2.InfoDesc ChequeStatusDesc
		from LON_BackPays p 
		join LON_ReqParts using(PartID)
		join LON_requests using(RequestID)
		join BSC_persons on(LoanPersonID=PersonID)
		left join ACC_banks b on(ChequeBank=BankID)
		left join BaseInfo bi2 on(bi2.TypeID=4 AND bi2.InfoID=p.ChequeStatus)
		
		where p.PayType=9 ";
	
	//.........................................................
	if(!empty($_POST["FromNo"]))
	{
		$query .= " AND ChequeNo >= :cfn";
		$param[":cfn"] = $_POST["FromNo"];
	}
	if(!empty($_POST["ToNo"]))
	{
		$query .= " AND ChequeNo <= :ctn";
		$param[":ctn"] = $_POST["ToNo"];
	}
	if(!empty($_POST["FromDate"]))
	{
		$query .= " AND PayDate >= :fd";
		$param[":fd"] = DateModules::shamsi_to_miladi($_POST["FromDate"], "-");
	}
	if(!empty($_POST["ToDate"]))
	{
		$query .= " AND PayDate <= :td";
		$param[":td"] = DateModules::shamsi_to_miladi($_POST["ToDate"], "-");
	}
	if(!empty($_POST["FromAmount"]))
	{
		$query .= " AND PayAmount >= :fa";
		$param[":fa"] = preg_replace('/,/', "", $_POST["FromAmount"]);
	}
	if(!empty($_POST["ToAmount"]))
	{
		$query .= " AND PayAmount <= :ta";
		$param[":ta"] = preg_replace('/,/', "", $_POST["ToAmount"]);
	}
	if(!empty($_POST["ChequeBank"]))
	{
		$query .= " AND ChequeBank = :cb";
		$param[":cb"] = $_POST["ChequeBank"];
	}
	if(!empty($_POST["ChequeBranch"]))
	{
		$query .= " AND ChequeBranch like :cb";
		$param[":cb"] = "%" . $_POST["ChequeBranch"] . "%";
	}
	if(!empty($_POST["ChequeStatus"]))
	{
		$query .= " AND ChequeStatus = :cst";
		$param[":cst"] = $_POST["ChequeStatus"];
	}
	//.........................................................
	
	$dataTable = PdoDataAccess::runquery_fetchMode($query, $param);

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
	
	$rpg->addColumn("وام گیرنده", "fullname");
	$rpg->addColumn("تاریخ وام", "PartDate","dateRender");
	$rpg->addColumn("شماره چک", "ChequeNo");
	$rpg->addColumn("بانک", "BankDesc");
	$rpg->addColumn("شعبه", "ChequeBranch");
	$rpg->addColumn("تاریخ چک", "PayDate","dateRender");
	$rpg->addColumn("وضعیت چک", "ChequeStatusDesc");
	$col = $rpg->addColumn("مبلغ چک", "PayAmount", "moneyRender");
	$col->EnableSummary();
	
	//echo PdoDataAccess::GetLatestQueryString();
	
	$rpg->mysql_resource = $dataTable;
	if(!$rpg->excel)
	{
		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
		echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
				<td align='center' style='font-family:b titr;font-size:15px'>اعتماد شما سرلوحه خدمت ماست<br>
					گزارش چک های دریافتی
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
AccReport_IncomeCheque.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

AccReport_IncomeCheque.prototype.showReport = function(btn, e)
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "IncomeCheques.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function AccReport_IncomeCheque()
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
			xtype : "numberfield",
			name : "FromNo",
			hideTrigger : true,
			fieldLabel : "از شماره چک"
		},{
			xtype : "numberfield",
			name : "ToNo",
			hideTrigger : true,
			fieldLabel : "تا شماره چک"
		},{
			xtype : "shdatefield",
			name : "FromDate",
			fieldLabel : "از تاریخ چک"
		},{
			xtype : "shdatefield",
			name : "ToDate",
			fieldLabel : "تا تاریخ چک"
		},{
			xtype : "currencyfield",
			name : "FromAmount",
			hideTrigger : true,
			fieldLabel : "از مبلغ"
		},{
			xtype : "currencyfield",
			name : "ToAmount",
			hideTrigger : true,
			fieldLabel : "تا مبلغ"
		},{
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
			fieldLabel : "بانک",
			displayField : "BankDesc",
			queryMode : "local",
			valueField : "BankID",
			hiddenName :"ChequeBank"
		},{
			xtype : "textfield",
			name : "ChequeBranch",
			fieldLabel : "شعبه"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../cheque/cheques.data.php?task=SelectIncomeChequeStatuses',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['InfoID','InfoDesc'],
				autoLoad : true
			}),
			fieldLabel : "وضعیت چک",
			displayField : "InfoDesc",
			queryMode : "local",
			valueField : "InfoID",
			hiddenName :"ChequeStatus"
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
					AccReport_IncomeChequeObj.get('excel').value = "true";
				}
			},
			iconCls : "excel"
		},{
			text : "پاک کردن گزارش",
			iconCls : "clear",
			handler : function(){
				AccReport_IncomeChequeObj.formPanel.getForm().reset();
				AccReport_IncomeChequeObj.get("mainForm").reset();
			}			
		}]
	});
}

AccReport_IncomeChequeObj = new AccReport_IncomeCheque();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>