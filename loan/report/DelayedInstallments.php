<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 97.06
//-----------------------------

require_once '../header.inc.php';
require_once "../request/request.class.php";
require_once "../request/request.data.php";
require_once "ReportGenerator.class.php";

function ReqPersonRender($row,$value){
	return $value == "" ? "منابع داخلی" : $value;
}
function intervslRender($row, $value){
	return $value . ($row["IntervalType"] == "DAY" ? " روز" : " ماه");
}

	
$page_rpg = new ReportGenerator("mainForm","LoanReport_DelayedInstallsObj");
$page_rpg->addColumn("شماره وام", "RequestID");
$page_rpg->addColumn("شعبه وام", "BranchName");
$page_rpg->addColumn("نوع وام", "LoanDesc");
$page_rpg->addColumn("معرف", "ReqPersonName");
$page_rpg->addColumn("وام گیرنده", "LoanPersonName");
$page_rpg->addColumn("تضامین", "tazamin");
$col = $page_rpg->addColumn("تاریخ آخرین قسط", "LastInstallmentDate");
$col->type = "date";
$col = $page_rpg->addColumn("تاریخ آخرین پرداخت مشتری", "MaxPayDate");
$col->type = "date";
$col = $page_rpg->addColumn("سررسید", "InstallmentDate");
$col->type = "date";
$page_rpg->addColumn("مبلغ قسط", "InstallmentAmount");
$page_rpg->addColumn("قابل پرداخت معوقه", "TotalRemainder");
$page_rpg->addColumn("اصل و کارمزد معوقه", "PureRemain");
$page_rpg->addColumn("شرح", "PartDesc");
$page_rpg->addColumn("مبلغ پرداخت", "PartAmount");
$page_rpg->addColumn("ماه تنفس", "DelayMonths");
$page_rpg->addColumn("روز تنفس", "DelayDays");
$page_rpg->addColumn("فاصله اقساط", "PayInterval", "intervslRender");
$page_rpg->addColumn("تعداد اقساط", "InstallmentCount");
$page_rpg->addColumn("کارمزد مشتری", "CustomerWage");
$page_rpg->addColumn("کارمزد صندوق", "FundWage");
$page_rpg->addColumn("درصد دیرکرد", "ForfeitPercent");

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
		if($key == "excel" || $key == "OrderBy" || $key == "OrderByDirection" || 
				$value === "" || strpos($key, "combobox") !== false || strpos($key, "rpcmp") !== false ||
				strpos($key, "reportcolumn_fld") !== false || strpos($key, "reportcolumn_ord") !== false)
			continue;

		if($key == "ForfeitDays")
			continue;
		
		$prefix = "";
		switch($key)
		{
			case "fromRequestID":
			case "toRequestID":
				$prefix = "i.";
				break;
			case "fromInstallmentDate":
			case "toInstallmentDate":
				$value = DateModules::shamsi_to_miladi($value, "-");
				break;
			case "fromInstallmentAmount":
			case "toInstallmentAmount":
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
	
	ini_set("memory_limit", "1000M");
	ini_set("max_execution_time", "600");
	
	$where = "";
	$whereParam = array();
	$userFields = ReportGenerator::UserDefinedFields();
	MakeWhere($where, $whereParam);
	
	$query = "select p.*,
				l.LoanDesc,
				r.RequestID,LoanPersonID,p1.mobile,
				concat_ws(' ',p1.fname,p1.lname,p1.CompanyName) LoanPersonName,
				concat_ws(' ',p2.fname,p2.lname,p2.CompanyName) ReqPersonName,
				InstallmentAmount,
				InstallmentDate,
				BranchName,
				tazamin,
				t1.LastInstallmentDate,
				t3.MaxPayDate" .
				($userFields != "" ? "," . $userFields : "")."
				
			from LON_installments i
			join LON_requests r using(RequestID)
			join LON_loans l using(LoanID)
			join BSC_persons p1 on(LoanPersonID=p1.PersonID)
			left join BSC_persons p2 on(ReqPersonID=p2.PersonID)
			join LON_ReqParts p on(p.RequestID=r.RequestID AND p.IsHistory='NO')
			join BSC_branches using(BranchID)
			left join (
				select RequestID, max(InstallmentDate) LastInstallmentDate 
				from LON_installments
				where history='NO' AND IsDelayed='NO'
				group by RequestID
			)t1 on(i.RequestID=t1.RequestID)
			left join (
				select ObjectID,group_concat(title,' به شماره سريال ',num, ' و مبلغ ', 
					format(amount,2) separator '<br>') tazamin
				from (	
					select ObjectID,InfoDesc title,group_concat(if(KeyTitle='no',paramValue,'') separator '') num,
					group_concat(if(KeyTitle='amount',paramValue,'') separator '') amount
					from DMS_documents d
					join BaseInfo b1 on(InfoID=d.DocType AND TypeID=8)
					join DMS_DocParamValues dv  using(DocumentID)
					join DMS_DocParams using(ParamID)
				    where ObjectType='loan' AND b1.param1=1
					group by ObjectID, DocumentID
				)t
				group by ObjectID
			)t2 on(t2.ObjectID=r.RequestID)
			
			left join (
				select RequestID,sum(PayAmount) TotalPayAmount , max(PayDate) MaxPayDate
				from LON_BackPays
				left join ACC_IncomeCheques i using(IncomeChequeID)
				where if(PayType=" . BACKPAY_PAYTYPE_CHEQUE . ",ChequeStatus=".INCOMECHEQUE_VOSUL.",1=1)
				group by RequestID			
			)t3 on(r.RequestID=t3.RequestID)
			
			where r.StatusID=" . LON_REQ_STATUS_CONFIRM . "
				AND IsHistory='NO' AND IsEnded='NO'  AND IsDelayed='NO' " . $where . "
		
			order by r.RequestID,p.PartID";
	
	$dt = PdoDataAccess::runquery_fetchMode($query, $whereParam);
	if($_SESSION["USER"]["UserName"] == "admin")
	{
		//echo PdoDataAccess::GetLatestQueryString();
	}
	$result = array();
	$currentRequestID = "";
	$remain = 0;
	$ReqInfoArray = array();
	while($row = $dt->fetch())
	{
		if($currentRequestID != $row["RequestID"])
		{
			$currentRequestID = $row["RequestID"];
			
			$temp = array();
			$computeArr = LON_requests::ComputePayments($row["RequestID"], $temp);
			$remain = LON_requests::GetCurrentRemainAmount($row["RequestID"],$computeArr);
			$PureRemain = LON_requests::GetCurrentRemainAmount($row["RequestID"],$computeArr, false);
			$NonPayedRecord = LON_requests::GetNonPayedInstallmentRow($row["RequestID"],$computeArr, $temp);
			$ReqInfoArray[$currentRequestID] = array(
				"remain" => $remain,
				"PureRemain" => $PureRemain,
				"NonPayedRow" => $NonPayedRecord
			);
		}
		if($ReqInfoArray[$currentRequestID]["NonPayedRow"] == null)
			continue;		
		if($row["InstallmentDate"] != $ReqInfoArray[$currentRequestID]["NonPayedRow"]["InstallmentDate"])
			continue;
		if($ReqInfoArray[$currentRequestID]["NonPayedRow"]["ForfeitDays"]*1 == 0)
			continue;
		if((int)$ReqInfoArray[$currentRequestID]["remain"] == 0)
			continue;
		
		$row["TotalRemainder"] = $remain;
		$row["PureRemain"] = $PureRemain;
		$row["ForfeitDays"] = $ReqInfoArray[$currentRequestID]["NonPayedRow"]["ForfeitDays"];
		
		if($_POST["ForfeitDays"]*1 > 0 && $row["ForfeitDays"] != $_POST["ForfeitDays"])
			continue;

		$result[] = $row;
	}
	
	return $result;
}	
	
function ListData($IsDashboard = false){
	
	$rpt = new ReportGenerator();
	$rpt->excel = !empty($_POST["excel"]);
	$rpt->mysql_resource = GetData();
	
	function LoanReportRender($row,$value){
		return "<a href=LoanPayment.php?show=tru&RequestID=" . $value . " target=blank >" . $value . "</a>";
	}
	
	$rpt->addColumn("کد وام", "RequestID", "LoanReportRender");
	$rpt->addColumn("شعبه وام", "BranchName");
	$rpt->addColumn("نوع وام", "LoanDesc");
	$rpt->addColumn("تضامین", "tazamin");
	$rpt->addColumn("معرف", "ReqPersonName");
	$rpt->addColumn("وام گیرنده", "LoanPersonName");
	$rpt->addColumn("موبایل", "mobile");
	$rpt->addColumn("وام گیرنده", "LoanPersonName");
	$rpt->addColumn("تاریخ آخرین قسط", "LastInstallmentDate","ReportDateRender");
	$rpt->addColumn("تاریخ آخرین پرداخت مشتری", "MaxPayDate","ReportDateRender");
	$rpt->addColumn("سررسید", "InstallmentDate","ReportDateRender");
	$rpt->addColumn("تعداد روز تاخیر", "ForfeitDays");
	$rpt->addColumn("مبلغ قسط", "InstallmentAmount","ReportMoneyRender");
	$rpt->addColumn("قابل پرداخت معوقه", "TotalRemainder","ReportMoneyRender");
	$rpt->addColumn("اصل و کارمزد معوقه", "PureRemain","ReportMoneyRender");
	$rpt->addColumn("مبلغ وام", "PartAmount", "ReportMoneyRender");
	$rpt->addColumn("شرح", "PartDesc");
	$rpt->addColumn("ماه تنفس", "DelayMonths");
	$rpt->addColumn("روز تنفس", "DelayDays");
	$rpt->addColumn("فاصله اقساط", "PayInterval", "intervslRender");
	$rpt->addColumn("تعداد اقساط", "InstallmentCount");
	$rpt->addColumn("کارمزد مشتری", "CustomerWage");
	$rpt->addColumn("کارمزد صندوق", "FundWage");
	$rpt->addColumn("درصد دیرکرد", "ForfeitPercent");
	
	
	if(!$rpt->excel && !$IsDashboard)
	{
		BeginReport();
		echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
				<td align='center' style='height:100px;vertical-align:middle;font-family: titr;font-size:15px'>
					گزارش اقساط وام ها 
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
		$rpt->generateReport();
		echo "</div>";
	}
	else
		$rpt->generateReport();
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
LoanReport_DelayedInstalls.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

LoanReport_DelayedInstalls.prototype.showReport = function(btn, e)
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "DelayedInstallments.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function LoanReport_DelayedInstalls()
{		
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		layout :{
			type : "table",
			columns :2
		},
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش اقساط معوق",
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
					el = LoanReport_DelayedInstallsObj.formPanel.down("[itemId=cmp_subAgent]");
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
			name : "fromInstallmentDate",
			fieldLabel : "تاریخ قسط از"
		},{
			xtype : "shdatefield",
			name : "toInstallmentDate",
			fieldLabel : "تا تاریخ"
		},{
			xtype : "currencyfield",
			name : "fromInstallmentAmount",
			hideTrigger : true,
			fieldLabel : "از مبلغ قسط"
		},{
			xtype : "currencyfield",
			name : "toInstallmentAmount",
			hideTrigger : true,
			fieldLabel : "تا مبلغ قسط"
		},{
			xtype : "textfield",
			name : "ForfeitDays",
			colspan : 2,
			hideTrigger : true,
			labelWidth : 300,
			fieldLabel : "تعداد روز تاخیر از آخرین قسط سررسید شده پرداخت نشده"
		},{
			xtype : "fieldset",
			title : "ستونهای گزارش",
			colspan :2,
			items :[<?= $page_rpg->ReportColumns() ?>]
		},{
			xtype : "fieldset",
			colspan :2,
			title : "رسم نمودار",
			items : [<?= $page_rpg->GetChartItems("LoanReport_DelayedInstallsObj","mainForm","installments.php") ?>]
		}],
		buttons : [{
			text : "گزارش ساز",
			iconCls : "db",
			handler : function(){ReportGenerator.ShowReportDB(
						LoanReport_DelayedInstallsObj, 
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
					LoanReport_DelayedInstallsObj.get('excel').value = "true";
				}
			},
			iconCls : "excel"
		},{
			text : "پاک کردن گزارش",
			iconCls : "clear",
			handler : function(){
				LoanReport_DelayedInstallsObj.formPanel.getForm().reset();
				LoanReport_DelayedInstallsObj.get("mainForm").reset();
			}			
		}]
	});
	
	this.formPanel.getEl().addKeyListener(Ext.EventObject.ENTER, function(keynumber,e){
		
		LoanReport_DelayedInstallsObj.showReport();
		e.preventDefault();
		e.stopEvent();
		return false;
	});
}

LoanReport_DelayedInstallsObj = new LoanReport_DelayedInstalls();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>