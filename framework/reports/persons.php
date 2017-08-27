<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	96.05
//-------------------------
require_once '../header.inc.php';
require_once "ReportGenerator.class.php";

$page_rpg = new ReportGenerator("mainForm","BSC_PersonsObj");
$page_rpg->addColumn("نام و نام خانوادگی/شرکت", "fullname");
$page_rpg->addColumn("نوع", "IsReal", "ReportYesNoRender");
$page_rpg->addColumn("کدملی/شناسه ملی", "NationalID");
$page_rpg->addColumn("نام پدر", "FatherName");
$page_rpg->addColumn("شماره شناسنامه", "ShNo");
$page_rpg->addColumn("تلفن", "PhoneNo");
$page_rpg->addColumn("همراه", "mobile");
$page_rpg->addColumn("شماره پیامک", "SmsNo");
$page_rpg->addColumn("آدرس", "address");
$page_rpg->addColumn("ایمیل", "email");
$page_rpg->addColumn("وب سایت", "WebSite");
$page_rpg->addColumn("کد اقتصادی", "NationalID");

$page_rpg->addColumn("شماره ثبت", "RegNo");
$col = $page_rpg->addColumn("تاریخ ثبت", "RegDate");
$col->type = "date";
$page_rpg->addColumn("محل ثبت", "RegPlace");
$page_rpg->addColumn("نوع شرکت", "CompanyTypeDesc");
$page_rpg->addColumn("شماره شبا", "AccountNo");
$page_rpg->addColumn("حوزه فعالیت", "DomainDesc");

$page_rpg->addColumn("مشتری", "IsCustomer", 'ReportTickRender');
$page_rpg->addColumn("سهامدار", "IsShareholder", 'ReportTickRender');
$page_rpg->addColumn("سرمایه گذار", "IsAgent", 'ReportTickRender');
$page_rpg->addColumn("حامی", "IsSupporter", 'ReportTickRender');
$page_rpg->addColumn("همکاران صندوق", "IsStaff", 'ReportTickRender');
$page_rpg->addColumn("کارشناس خارج از صندوق", "IsExpert", 'ReportTickRender');

function MakeWhere(&$where, &$whereParam){
		
		foreach($_POST as $key => $value)
		{
			if($key == "excel" || $key == "OrderBy" || $key == "OrderByDirection" || 
					$value === "" || strpos($key, "combobox") !== false || strpos($key, "rpcmp") !== false ||
					strpos($key, "reportcolumn_fld") !== false || strpos($key, "reportcolumn_ord") !== false)
				continue;
			$prefix = "";
			switch($key)
			{
				case "fromRequestID":
				case "toRequestID":
					$prefix = "b.";
					break;
				case "fromPayDate":
				case "toPayDate":
					$value = DateModules::shamsi_to_miladi($value, "-");
					break;
				case "fromPayAmount":
				case "toPayAmount":
					$value = preg_replace('/,/', "", $value);
					break;
			}
			if(strpos($key, "from") === 0)
				$where .= " AND " . $prefix . substr($key,4) . " >= :$key";
			else if(strpos($key, "to") === 0)
				$where .= " AND " . $prefix . substr($key,2) . " <= :$key";
			else
				$where .= " AND " . $prefix . $key . " = :$key";
			$whereParam[":$key"] = $value;
		}
	}	

function GetData(){
	$where = "";
	$whereParam = array();
	$userFields = ReportGenerator::UserDefinedFields();
	MakeWhere($where, $whereParam);
	
	$query = "select p.*, concat_ws(' ',fname,lname,CompanyName) fullname,
				b1.InfoDesc CompanyTypeDesc,
				d.DomainDesc				
				".
				($userFields != "" ? "," . $userFields : "")."
				
			from BSC_persons p
			left join BaseInfo b1 on(b1.typeID=14 and b1.InfoID=CompanyType)
			left join BSC_ActDomain d using(DomainID)
			" . $where ;
	
	$group = ReportGenerator::GetSelectedColumnsStr();
	$query .= $group == "" ? " " : " group by " . $group;
	$query .= $group == "" ? " order by fullname" : " order by " . $group;
	
	$dataTable = PdoDataAccess::runquery_fetchMode($query, $whereParam);
	$query = PdoDataAccess::GetLatestQueryString();
	//print_r(ExceptionHandler::PopAllExceptions());
	
	return $dataTable;
}
	
function ListDate($IsDashboard = false){
	
	$rpg = new ReportGenerator();
	$rpg->excel = !empty($_POST["excel"]);
	$rpg->mysql_resource = GetData();
	
	function endedRender($row,$value){
		return ($value == "YES") ? "خاتمه" : "جاری";
	}
	
	$rpg->addColumn("شماره وام", "RequestID");
	$rpg->addColumn("نوع وام", "LoanDesc");
	$rpg->addColumn("معرفی کننده", "ReqFullname");
	$rpg->addColumn("تاریخ درخواست", "ReqDate", "ReportDateRender");
	$col = $rpg->addColumn("مبلغ درخواست", "ReqAmount", "ReportMoneyRender");
	$col->EnableSummary();
	$rpg->addColumn("مشتری", "LoanFullname");
	$rpg->addColumn("شعبه", "BranchName");
	$rpg->addColumn("تاریخ پرداخت", "PayDate", "ReportDateRender");
	$col = $rpg->addColumn("مبلغ پرداخت", "PayAmount", "ReportMoneyRender");
	$col->EnableSummary();
	$rpg->addColumn("نوع پرداخت", "PayTypeDesc");
	$rpg->addColumn("شماره فیش", "PayBillNo");
	$rpg->addColumn("کد پیگیری", "PayRefNo");
	$rpg->addColumn("شماره چک", "ChequeNo");
	$rpg->addColumn("شماره سند", "LocalNo");
	
	if(!$rpg->excel && !$IsDashboard)
	{
		BeginReport();
		echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
				<td align='center' style='height:100px;vertical-align:middle;font-family:titr;font-size:15px'>
					گزارش پرداخت های مشتریان
				</td>
				<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
			. DateModules::shNow() . "<br>";
		if(!empty($_POST["fromReqDate"]))
		{
			echo "<br>گزارش از تاریخ : " . $_POST["fromReqDate"] . 
				($_POST["toReqDate"] != "" ? " - " . $_POST["toReqDate"] : "");
		}
		echo "</td></tr></table>";
	}
	if($IsDashboard)
	{
		echo "<div style=direction:rtl;padding-right:10px>";
		$rpg->generateReport();
		echo "</div>";
	}
	else
		$rpg->generateReport();
	die();
}

if(isset($_REQUEST["show"]))
{
	ListDate();
}

if(isset($_REQUEST["rpcmp_chart"]))
{
	$page_rpg->mysql_resource = GetData();
	$page_rpg->GenerateChart();
	die();
}

if(isset($_REQUEST["dashboard_show"]))
{
	$chart = ReportGenerator::DashboardSetParams($_REQUEST["rpcmp_ReportID"]);
	if(!$chart)
		ListDate(true);	
	
	$page_rpg->mysql_resource = GetData();
	$page_rpg->GenerateChart(false, $_REQUEST["rpcmp_ReportID"]);
	die();	
}
?>
<script>
BSC_Persons.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

BSC_Persons.prototype.showReport = function(btn, e)
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "persons.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function BSC_Persons()
{		
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		layout :{
			type : "table",
			columns :2
		},
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش پرداخت های مشتریان",
		width : 780,
		items :[{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../../framework/person/persons.data.php?' +
						"task=selectPersons&UserTypes=IsAgent,IsSupporter",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['PersonID','fullname']
			}),
			fieldLabel : "معرفی کننده",
			pageSize : 25,
			width : 370,
			displayField : "fullname",
			valueField : "PersonID",
			hiddenName : "ReqPersonID",
			listeners :{
				select : function(record){
					el = BSC_PersonsObj.formPanel.down("[itemId=cmp_subAgent]");
					el.getStore().proxy.extraParams["PersonID"] = this.getValue();
					el.getStore().load();
				}
			}
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../../framework/person/persons.data.php?' +
						"task=selectSubAgents",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['SubID','SubDesc']
			}),
			fieldLabel : "زیر واحد سرمایه گذار",
			queryMode : "local",
			width : 370,
			displayField : "SubDesc",
			valueField : "SubID",
			hiddenName : "SubAgentID",
			itemId : "cmp_subAgent"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../../framework/person/persons.data.php?' +
						"task=selectPersons&UserType=IsCustomer",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['PersonID','fullname']
			}),
			fieldLabel : "مشتری",
			displayField : "fullname",
			pageSize : 20,
			width : 370,
			valueField : "PersonID",
			hiddenName : "LoanPersonID"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../loan/loan.data.php?task=GetAllLoans',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['LoanID','LoanDesc'],
				autoLoad : true					
			}),
			fieldLabel : "نوع وام",
			queryMode : 'local',
			width : 370,
			displayField : "LoanDesc",
			valueField : "LoanID",
			hiddenName : "LoanID"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../../framework/baseInfo/baseInfo.data.php?' +
						"task=SelectBranches",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['BranchID','BranchName'],
				autoLoad : true					
			}),
			fieldLabel : "شعبه اخذ وام",
			queryMode : 'local',
			width : 370,
			colspan : 2,
			displayField : "BranchName",
			valueField : "BranchID",
			hiddenName : "BranchID"
		},{
			xtype : "numberfield",
			name : "fromRequestID",
			hideTrigger : true,
			fieldLabel : "شماره وام از"
		},{
			xtype : "numberfield",
			name : "toRequestID",
			hideTrigger : true,
			fieldLabel : "تا شماره"
		},{
			xtype : "shdatefield",
			name : "fromPayDate",
			fieldLabel : "تاریخ پرداخت از"
		},{
			xtype : "shdatefield",
			name : "toPayDate",
			fieldLabel : "تا تاریخ"
		},{
			xtype : "currencyfield",
			name : "fromPayAmount",
			hideTrigger : true,
			fieldLabel : "مبلغ پرداخت از"
		},{
			xtype : "currencyfield",
			name : "toPayAmount",
			hideTrigger : true,
			fieldLabel : "تا مبلغ"
		},{
			xtype : "fieldset",
			colspan :2,
			title : "ستونهای گزارش",
			items :[<?= $page_rpg->ReportColumns() ?>]
		},{
			xtype : "fieldset",
			colspan :2,
			title : "رسم نمودار",
			items : [<?= $page_rpg->GetChartItems("BSC_PersonsObj","mainForm","persons.php") ?>]
		}],
		buttons : [{
			text : "گزارش ساز",
			iconCls : "db",
			handler : function(){ReportGenerator.ShowReportDB(
						BSC_PersonsObj, 
						<?= $_REQUEST["MenuID"] ?>,
						"mainForm",
						"formPanel"
						);}
		},'->',{
			text : "مشاهده گزارش",
			handler : Ext.bind(this.showReport,this),
			iconCls : "report"
		},{
			text : "خروجی excel",
			handler : Ext.bind(this.showReport,this),
			listeners : {
				click : function(){
					BSC_PersonsObj.get('excel').value = "true";
				}
			},
			iconCls : "excel"
		},{
			text : "پاک کردن گزارش",
			iconCls : "clear",
			handler : function(){
				BSC_PersonsObj.formPanel.getForm().reset();
				BSC_PersonsObj.get("mainForm").reset();
			}			
		}]
	});
	
	this.formPanel.getEl().addKeyListener(Ext.EventObject.ENTER, function(keynumber,e){
		
		BSC_PersonsObj.showReport();
		e.preventDefault();
		e.stopEvent();
		return false;
	});
}

BSC_PersonsObj = new BSC_Persons();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>