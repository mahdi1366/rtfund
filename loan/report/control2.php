<?php

require_once '../header.inc.php';
require_once "../request/request.class.php";
require_once "../request/request.data.php";
require_once "ReportGenerator.class.php";

function ReqPersonRender($row,$value){
	return $value == "" ? "منابع داخلی" : $value;
}
function IsDocRegisteredRender($row,$value){
	return $value == "YES" ? "*" : "";
}
		
$page_rpg = new ReportGenerator("mainForm","LoanReport_ComControlObj");
$page_rpg->addColumn("شماره وام", "ReqID");
$page_rpg->addColumn("نوع وام", "LoanDesc");
$page_rpg->addColumn("معرفی کننده", "ReqFullname", "ReqPersonRender");
$page_rpg->addColumn("مبلغ درخواست", "ReqAmount");
$page_rpg->addColumn("مشتری", "LoanFullname");
$page_rpg->addColumn("شعبه", "BranchName");
$page_rpg->addColumn("وضعیت", "StatusDesc");
$page_rpg->addColumn("مانده وام", "LoanRemain");

function MakeWhere(&$where, &$whereParam){

	if(session::IsPortal() && isset($_REQUEST["dashboard_show"]))
	{
		if($_REQUEST["DashboardType"] == "shareholder" || $_REQUEST["DashboardType"] == "agent")
			$where .= " AND ReqPersonID=" . $_SESSION["USER"]["PersonID"];
		if($_REQUEST["DashboardType"] == "customer")
			$where .= " AND LoanPersonID=" . $_SESSION["USER"]["PersonID"];
	}
	
	foreach($_POST as $key => $value)
	{
		if($key == "excel" || $key == "OrderBy" || 
				$key == "OrderByDirection" || 
				$value === "" || 
				strpos($key, "combobox") !== false || 
				strpos($key, "rpcmp") !== false ||
				strpos($key, "reportcolumn_fld") !== false || 
				strpos($key, "reportcolumn_ord") !== false)
			continue;

		if($key == "IsEndedInclude" || $key == "ZeroRemain")
			continue;

		$prefix = "";
		switch($key)
		{
			case "fromRequestID":
			case "toRequestID":
				$prefix = "r.";
				break;
			case "fromReqDate":
			case "toReqDate":
				$value = DateModules::shamsi_to_miladi($value, "-");
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

	$where .= isset($_POST["IsEndedInclude"]) ? 
			" AND r.StatusID in('".LON_REQ_STATUS_CONFIRM."','".LON_REQ_STATUS_ENDED."')" : 
			" AND r.StatusID in('".LON_REQ_STATUS_CONFIRM."')";
}	

function GetData(){
	
	ini_set("memory_limit", "1000M");
	ini_set("max_execution_time", "600");
	
	$where = "";
	$whereParam = array();
	$userFields = ReportGenerator::UserDefinedFields();
	MakeWhere($where, $whereParam);
	
	$query = "select r.RequestID ReqID, r.*,l.*,p.*,bi.InfoDesc StatusDesc,
				concat_ws(' ',p1.fname,p1.lname,p1.CompanyName) ReqFullname,
				concat_ws(' ',p2.fname,p2.lname,p2.CompanyName) LoanFullname,
				BranchName,
				
				". ($userFields != "" ? "," . $userFields : "")."
				
			from LON_requests r 
			left join BaseInfo bi on(bi.TypeID=5 AND bi.InfoID=StatusID)
			join LON_ReqParts p on(r.RequestID=p.RequestID AND p.IsHistory='NO')
			left join LON_loans l using(LoanID)
			join BSC_branches using(BranchID)
			left join BSC_persons p1 on(p1.PersonID=r.ReqPersonID)
			left join BSC_persons p2 on(p2.PersonID=r.LoanPersonID)
			
			join CostCodes cc on(cc.param1=".CostCode_param_loan." or
								 cc.param2=".CostCode_param_loan." or
								 cc.param3=".CostCode_param_loan.")
			join ACC_DocItems di on(cc.CostID=di.CostID)

			where 1=1 " . $where;
	
	$group = ReportGenerator::GetSelectedColumnsStr();
	$query .= $group == "" ? " group by r.RequestID,cc.CostID" : " group by " . $group;
	$query .= $group == "" ? " order by r.RequestID desc" : " order by " . $group;		
	
	$dt = PdoDataAccess::runquery($query, $whereParam);
	//-----------------------------------------------
	for($i=0; $i<count($dt); $i++)
	{
		$row = $dt[$i];
		$RequestID = $row["RequestID"];
		$ComputeArr = LON_Computes::ComputePayments($RequestID);
		$remains = LON_Computes::GetRemainAmounts($RequestID, $ComputeArr);
		
		$dt["remain_pure"] = $remains["remain_pure"];
		$dt["remain_wage"] = $remains["remain_wage"];
		$dt["remain_late"] = $remains["remain_late"];
		$dt["remain_pnlt"] = $remains["remain_pnlt"];
		$dt["remain"] = $remains["remain_pure"] + $remains["remain_wage"] + 
				$remains["remain_late"] + $remains["remain_pnlt"];
	}
	//-----------------------------------------------
	
	return $dt;
}	
	
function ListData($IsDashboard = false){
	
	$rpg = new ReportGenerator();
	$rpg->excel = !empty($_POST["excel"]);
	$rpg->mysql_resource = GetData();
	if($_SESSION["USER"]["UserName"] == "admin")
	{
		print_r(ExceptionHandler::PopAllExceptions());
		//echo PdoDataAccess::GetLatestQueryString();
	}
	function endedRender($row,$value){
		return ($value == "YES") ? "خاتمه" : "جاری";
	}
	
	$col = $rpg->addColumn("شماره وام", "ReqID");
	$col = $rpg->addColumn("نوع وام", "LoanDesc");
	$col = $rpg->addColumn("معرفی کننده", "ReqFullname");
	$col = $rpg->addColumn("تاریخ درخواست", "ReqDate", "ReportDateRender");
	$col = $rpg->addColumn("مبلغ درخواست", "ReqAmount", "ReportMoneyRender");
	$col->EnableSummary();
	$col = $rpg->addColumn("مشتری", "LoanFullname");
	$col = $rpg->addColumn("شعبه", "BranchName");
	$rpg->addColumn("وضعیت", "StatusDesc");
	
	function LoanRemainRender($row, $value){
		return "<a href=LoanPayment.php?show=tru&RequestID=" . $row["RequestID"] . " target=blank >" . number_format($value) . "</a>";
	}
	$col = $rpg->addColumn("باقیمانده وام (اقساط - پرداخت مشتری )", "LoanRemain", "LoanRemainRender");
	$col->ExcelRender = false;
	$col->SummaryOfRender = false;
	$col->EnableSummary();
	
	$col = $rpg->addColumn("باقیمانده اصل وام ", "AccRemain2", "ReportMoneyRender");
	$col->ExcelRender = false;
	$col->SummaryOfRender = false;
	$col->EnableSummary();
	
	function AccRemainRender($row, $value){
		
		return "<a target=_blank href='/accounting/report/flow.php?show=true&IncludeRaw=true".
				"&CostID=" . $row["CostID"] . 
				"&TafsiliID=" . $row["LoanPersonTafsili"] . 
				"&TafsiliID2=" . $row["ReqPersonTafsili"] . "' >" . 
				number_format($value) . "</a>";
	}
	$col = $rpg->addColumn("مانده حساب وام در مالی", "AccRemain", "AccRemainRender");
	$col->ExcelRender = false;
	$col->SummaryOfRender = false;
	$col->EnableSummary();
	
	function TodieeRender($row, $value){
		
		return "<a target=_blank href='/accounting/report/flow.php?show=true&IncludeRaw=true".
				"&CostID=" . $row["TodieeCostID"] . 
				"&TafsiliID=" . $row["LoanPersonTafsili"] . 
				"&TafsiliID2=" . $row["ReqPersonTafsili"] . "' >" . 
				number_format($value) . "</a>";
	}
	$col = $rpg->addColumn("حساب تودیعی", "Todiee", "TodieeRender");
	$col->ExcelRender = false;
	$col->SummaryOfRender = false;
	$col->EnableSummary();
		
	$rpg->addColumn("تطبیق با اصل", "diff2","ReportMoneyRender");
	
	function diffRender($row, $value){
		
		return "<span style=color:". ($value*1 < 0 ? "red" : "black" ) . ">" . 
				number_format($value) . "</span>";
	}	
	$col = $rpg->addColumn("تطبیق با سیستم", "diff","diffRender");
	$col->ExcelRender = false;
	$col->SummaryOfRender = false;
	$col->EnableSummary();
	
	if(!$rpg->excel && !$IsDashboard)
	{
		BeginReport();
		echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
				<td align='center' style='height:100px;vertical-align:middle;font-family: titr;font-size:15px'>
					گزارش  کنترل وام با مالی
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
	ListData();
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
LoanReport_ComControl.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

LoanReport_ComControl.prototype.showReport = function(btn, e)
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "control.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function LoanReport_ComControl()
{		
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		layout :{
			type : "table",
			columns :2
		},
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش کنترل وام با مالی",
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
					el = LoanReport_ComControlObj.formPanel.down("[itemId=cmp_subAgent]");
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
			name : "fromReqDate",
			fieldLabel : "تاریخ وام از"
		},{
			xtype : "shdatefield",
			name : "toReqDate",
			fieldLabel : "تا تاریخ وام"
		},{
			xtype : "container",
			colspan : 2,
			html : "<input type=checkbox name=IsEndedInclude >  گزارش شامل وام های خاتمه یافته نیز باشد"
		},{
			xtype : "fieldset",
			title : "ستونهای گزارش",
			colspan :2,
			items :[<?= $page_rpg->ReportColumns() ?>]
		},{
			xtype : "fieldset",
			colspan :2,
			title : "رسم نمودار",
			items : [<?= $page_rpg->GetChartItems("LoanReport_ComControlObj","mainForm","installments.php") ?>]
		}],
		buttons : [{
			text : "گزارش ساز",
			iconCls : "db",
			handler : function(){ReportGenerator.ShowReportDB(
						LoanReport_ComControlObj, 
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
					LoanReport_ComControlObj.get('excel').value = "true";
				}
			},
			iconCls : "excel"
		},{
			text : "پاک کردن گزارش",
			iconCls : "clear",
			handler : function(){
				LoanReport_ComControlObj.formPanel.getForm().reset();
				LoanReport_ComControlObj.get("mainForm").reset();
			}			
		}]
	});
	
	this.formPanel.getEl().addKeyListener(Ext.EventObject.ENTER, function(keynumber,e){
		
		LoanReport_ComControlObj.showReport();
		e.preventDefault();
		e.stopEvent();
		return false;
	});
}

LoanReport_ComControlObj = new LoanReport_ComControl();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>