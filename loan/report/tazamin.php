<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	97.09
//-------------------------

require_once '../header.inc.php';
require_once "../request/request.class.php";
require_once "../request/request.data.php";
require_once "ReportGenerator.class.php";

function ReqPersonRender($row,$value){
	return $value == "" ? "منابع داخلی" : $value;
}
function RealRender($row, $value){
	return $value == "YES" ? "حقیقی" : "حقوقی";
}
function IsFreeRender($row, $value){
	return $value == "YES" ? "*" : "";
}

$page_rpg = new ReportGenerator("mainForm","LoanReport_totalObj");

$page_rpg->addColumn("نوع تضمین", "DocumentTitle");
$page_rpg->addColumn("شماره سریال", "DocumentNo");
$page_rpg->addColumn("مبلغ تضمین", "DocumentAmount");
$page_rpg->addColumn("سایر اطلاعات تضمین", "DocumentOtherInfo");

$col = $page_rpg->addColumn("شماره وام", "RRequestID");
$col->queryField = "r.RequestID";

$page_rpg->addColumn("نوع وام", "LoanDesc");
$page_rpg->addColumn("عنوان طرح", "PlanTitle");	
$page_rpg->addColumn("معرفی کننده", "ReqFullname","ReqPersonRender");
$page_rpg->addColumn("زیرواحد سرمایه گذار", "SubDesc");
$col = $page_rpg->addColumn("تاریخ درخواست", "ReqDate");
$col->type = "date";	
$col = $page_rpg->addColumn("تاریخ خاتمه", "EndReqDate");
$col->type = "date";	
$page_rpg->addColumn("سند خاتمه", "EndDocNo");
$page_rpg->addColumn("مبلغ درخواست", "ReqAmount");
$page_rpg->addColumn("وام بالاعوض", "IsFree", "IsFreeRender");
$page_rpg->addColumn("مشتری", "LoanFullname");
$page_rpg->addColumn("حوزه فعالیت", "DomainDesc");
$page_rpg->addColumn("نوع", "IsReal", "RealRender");
$page_rpg->addColumn("کدملی/شناسه ملی", "NationalID");
$page_rpg->addColumn("تلفن", "PhoneNo");
$page_rpg->addColumn("همراه", "mobile");
$page_rpg->addColumn("آدرس", "address");
$page_rpg->addColumn("ایمیل", "email");
$page_rpg->addColumn("وب سایت", "WebSite");

$page_rpg->addColumn("شعبه", "BranchName");
$col = $page_rpg->addColumn("تاریخ پرداخت", "PartDate");
$col->type = "date";
$page_rpg->addColumn("مبلغ تایید شده", "PartAmount");
$page_rpg->addColumn("مبلغ پرداخت شده", "SumPayments");
$page_rpg->addColumn("تعداد اقساط", "InstallmentCount");
$page_rpg->addColumn("تنفس(ماه)", "DelayMonths");
$page_rpg->addColumn("کارمزد مشتری", "CustomerWage");
$page_rpg->addColumn("کارمزد صندوق", "FundWage");
$page_rpg->addColumn("درصد دیرکرد", "ForfeitPercent");
$page_rpg->addColumn("شماره قدیم", "imp_VamCode");
$page_rpg->addColumn("وضعیت", "StatusDesc");
$page_rpg->addColumn("جمع اقساط", "TotalInstallmentAmount");
$col = $page_rpg->addColumn("تاریخ آخرین قسط", "MaxInstallmentDate");
$col->type = "date";
$col = $page_rpg->addColumn("تاریخ آخرین پرداخت", "MaxPayDate");
$col->type = "date";
$page_rpg->addColumn("جمع مبلغ اقساط سررسید شده", "installmentsToNow");

$page_rpg->addColumn("مبلغ آخرین پرداخت", "LastPayAmount");
$page_rpg->addColumn("جمع پرداختی مشتری", "TotalPayAmount");
$page_rpg->addColumn("مانده قابل پرداخت", "remainder");
$page_rpg->addColumn("مبلغ تاخیر", "ForfeitAmount");
$page_rpg->addColumn("تاخیر کل وام", "TotalForfeitAmount");

function MakeWhere(&$where, &$pay_where, &$whereParam){

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
				$value === "" || 
				
				strpos($key, "combobox") !== false || 
				strpos($key, "rpcmp") !== false ||
				strpos($key, "checkcombo") !== false || 
				strpos($key, "treecombo") !== false || 
				strpos($key, "reportcolumn_fld") !== false || 
				strpos($key, "reportcolumn_ord") !== false)
			continue;
		
		if(strpos($key, "FILTERPERSON_") !== false)
		{
			$prefix = "p2.";
			$key = str_replace("FILTERPERSON_", "", $key);
			$where .= " AND " . $prefix . $key . " = :$key";
			$whereParam[":$key"] = $value;
			continue;
		}
		
		if($key == "SubAgentID")
		{
			InputValidation::validate($value, InputValidation::Pattern_NumComma);
			$where .= " AND SubAgentID in(" . $value . ")";
			continue;
		}
		if($key == "DocType")
		{
			InputValidation::validate($value, InputValidation::Pattern_NumComma);
			$where .= " AND DocType in(" . $value . ")";
			continue;
		}
		
		$prefix = "";
		$pay = false;
		switch($key)
		{
			case "CustomerWage":
				$prefix = "p.";
				break;
			case "fromRequestID":
			case "toRequestID":
				$prefix = "r.";
				break;
			case "fromReqDate":
			case "toReqDate":
			case "fromPartDate":
			case "toPartDate":
			case "fromEndReqDate":
			case "toEndReqDate":
				$value = DateModules::shamsi_to_miladi($value, "-");
				break;
			case "fromReqAmount":
			case "toReqAmount":
			case "fromPartAmount":
			case "toPartAmount":
				$value = preg_replace('/,/', "", $value);
				break;
			case "fromPayAmount":
			case "toPayAmount":
				$value = preg_replace('/,/', "", $value);
				$pay = true;
				break;
		}
		if(strpos($key, "from") === 0)
			$where_temp = " AND " . $prefix . substr($key,4) . " >= :$key";
		else if(strpos($key, "to") === 0)
			$where_temp = " AND " . $prefix . substr($key,2) . " <= :$key";
		else
			$where_temp = " AND " . $prefix . $key . " = :$key";

		if($pay)
			$pay_where .= $where_temp;
		else
			$where .= $where_temp;
		$whereParam[":$key"] = $value;
	}
	
}	
	
function GetData($mode = "list"){
	
	//.....................................
	$where = "";
	$pay_where = "";
	$userFields = ReportGenerator::UserDefinedFields();
	$whereParam = array();
	MakeWhere($where, $pay_where, $whereParam);
		
	$query = "select
		
				b1.InfoDesc DocumentTitle,
				t_params.DocumentNo,
				t_params.DocumentAmount,
				t_params.DocumentOtherInfo,
				
				r.*,l.LoanDesc,p.*,
				r.RequestID as RRequestID,
				concat_ws(' ',p1.fname,p1.lname,p1.CompanyName) ReqFullname,
				
				concat_ws(' ',p2.fname,p2.lname,p2.CompanyName) LoanFullname,
				p2.IsReal,
				p2.NationalID,
				p2.PhoneNo,
				p2.mobile,
				p2.address,
				p2.email,
				p2.WebSite,
				
				doc.EndReqDate,
				doc.EndDocNo,

				bi.InfoDesc StatusDesc,
				sb.SubDesc,
				ad.DomainDesc,
				dp.PackNo,
				BranchName,
				SumPayments,
				TotalPayAmount,
				TotalInstallmentAmount,
				MaxInstallmentDate,
				MaxPayDate,
				ifnull(LastPayAmount,0) LastPayAmount,
				t5.amount installmentsToNow".
				($mode == "list" && $userFields != "" ? "," . $userFields : "")."
				
			from DMS_documents d
			join BaseInfo b1 on(InfoID=d.DocType AND TypeID=8)
			left join ( 
				select DocumentID,
					group_concat(if(KeyTitle='no',paramValue,'') separator '') DocumentNo,
					group_concat(if(KeyTitle='amount',paramValue,'') separator '') DocumentAmount,
					group_concat(if((KeyTitle<>'amount' AND KeyTitle<>'no') or KeyTitle is null,
						concat(ParamDesc,' : ', paramValue, '<br>'),'') separator '') DocumentOtherInfo
					from DMS_DocParamValues 
					join DMS_DocParams using(ParamID)
					group by DocumentID 
				)t_params on(t_params.DocumentID=d.DocumentID)
			
			join LON_requests r on(d.ObjectID=r.RequestID)
				
			join LON_ReqParts p on(r.RequestID=p.RequestID AND p.IsHistory='NO')
			left join LON_loans l using(LoanID)
			left join BSC_SubAgents sb on(sb.SubID=SubAgentID)
			join BSC_branches using(BranchID)
			left join BaseInfo bi on(bi.TypeID=5 AND bi.InfoID=StatusID)
			left join BSC_persons p1 on(p1.PersonID=r.ReqPersonID)
			left join BSC_persons p2 on(p2.PersonID=r.LoanPersonID)
			left join DMS_packages dp on(p2.PersonID=dp.PersonID AND r.BranchID=dp.BranchID)
			left join BSC_ActDomain ad on(p2.DomainID=ad.DomainID)
			
			left join (
				select SourceID2 RequestID,LocalNo EndDocNo,DocDate EndReqDate
				from ACC_DocItems join ACC_docs using(DocID)
				where DocType=".DOCTYPE_END_REQUEST."
				group by SourceID2
			) doc on(r.RequestID=doc.RequestID)

			left join (
				select RequestID,sum(PayAmount) SumPayments 
				from LON_payments 
				join (select SourceID1,SourceID3 from ACC_DocItems where SourceType=".DOCTYPE_LOAN_PAYMENT." group by SourceID1,SourceID3)t 
					on(t.SourceID1=RequestID AND t.SourceID3=PayID)
				where 1=1 $pay_where
				group by RequestID			
			)t_pay on(r.RequestID=t_pay.RequestID)
			
			left join (
				select RequestID,sum(PayAmount) TotalPayAmount , max(PayDate) MaxPayDate
				from LON_BackPays
				left join ACC_IncomeCheques i using(IncomeChequeID)
				where if(PayType=" . BACKPAY_PAYTYPE_CHEQUE . ",ChequeStatus=".INCOMECHEQUE_VOSUL.",1=1)					
				group by RequestID			
			)t1 on(r.RequestID=t1.RequestID)
			
			left join (
				select RequestID,sum(InstallmentAmount) TotalInstallmentAmount 
				from LON_installments
				where  history='NO' AND IsDelayed='NO'
				group by RequestID			
			)t2 on(r.RequestID=t2.RequestID)
			
			left join (
				select RequestID,max(InstallmentDate) MaxInstallmentDate
				from LON_installments
				where history='NO' AND IsDelayed='NO'
				group by RequestID			
			)inst on(r.RequestID=inst.RequestID)
			
			left join (
				select RequestID,sum(PayAmount) LastPayAmount from LON_BackPays
				join (	select RequestID,max(PayDate) PayDate 
						from LON_BackPays left join ACC_IncomeCheques i using(IncomeChequeID)
						where if(PayType=" . BACKPAY_PAYTYPE_CHEQUE . ",ChequeStatus=".INCOMECHEQUE_VOSUL.",1=1)
							AND PayType<>".BACKPAY_PAYTYPE_CORRECT."
						group by RequestID)t using(PayDate,RequestID) 
				group by RequestID
			)t3 on(r.RequestID=t3.RequestID)
			
			left join (
				select RequestID,sum(InstallmentAmount) amount from LON_installments
				where history='NO' AND IsDelayed='NO' AND InstallmentDate<= " . PDONOW . "
				group by RequestID
			)t5 on(r.RequestID=t5.RequestID)
			
			where d.ObjectType='loan' AND b1.param1=1 " . $where;
	
	$group = ReportGenerator::GetSelectedColumnsStr();
	$query .= $group == "" || $mode == "chart" ? " group by d.DocumentID" : " group by " . $group;
	$query .= $group == "" || $mode == "chart" ? " order by r.RequestID" : " order by " . $group;	
	
	$dataTable = PdoDataAccess::runquery($query, $whereParam);
	$query = PdoDataAccess::GetLatestQueryString();
	if($_SESSION["USER"]["UserName"] == "admin")
	{
		//BeginReport();
		//print_r(ExceptionHandler::PopAllExceptions());
		//echo PdoDataAccess::GetLatestQueryString();
		
	}
	
	for($i=0; $i< count($dataTable); $i++)
	{
		$ComputeArr = LON_Computes::ComputePayments($dataTable[$i]["RequestID"]);
		$TotalRemain = LON_Computes::GetTotalRemainAmount($dataTable[$i]["RequestID"], $ComputeArr);
		$remains = LON_Computes::GetRemainAmounts($dataTable[$i]["RequestID"], $ComputeArr);
		
		$dataTable[$i]["remainder"] = $TotalRemain;
		$dataTable[$i]["ForfeitAmount"] = $remains["remain_pnlt"];
		$dataTable[$i]["TotalForfeitAmount"] = LON_Computes::GetTotalForfeitAmount($dataTable[$i]["RequestID"], $ComputeArr);
	}
			
	return $dataTable; 
}

function ListData($IsDashboard = false){
	
	$rpg = new ReportGenerator();
	$rpg->excel = !empty($_POST["excel"]);
	$rpg->mysql_resource = GetData();
	
	function endedRender($row,$value){
		return ($value == "YES") ? "خاتمه" : "جاری";
	}
	
	$rpg->addColumn("شماره وام", "RRequestID");
	
	$rpg->addColumn("نوع تضمین", "DocumentTitle");
	$rpg->addColumn("شماره سریال", "DocumentNo");
	$rpg->addColumn("مبلغ تضمین", "DocumentAmount", "ReportMoneyRender");
	$rpg->addColumn("سایر اطلاعات تضمین", "DocumentOtherInfo");
		
	$rpg->addColumn("نوع وام", "LoanDesc");
	$rpg->addColumn("عنوان طرح", "PlanTitle");	
	$rpg->addColumn("معرفی کننده", "ReqFullname","ReqPersonRender");
	$rpg->addColumn("زیرواحد سرمایه گذار", "SubDesc");
	$rpg->addColumn("تاریخ درخواست", "ReqDate", "ReportDateRender");
	$col = $rpg->addColumn("مبلغ درخواست", "ReqAmount", "ReportMoneyRender");
	$col->ExcelRender = false;
	$col->EnableSummary();
	$rpg->addColumn("وام بالاعوض", "IsFree", "IsFreeRender");
	$rpg->addColumn("تاریخ خاتمه", "EndReqDate", "ReportDateRender");
	$rpg->addColumn("سند خاتمه", "EndDocNo");
	
	$rpg->addColumn("مشتری", "LoanFullname");
	$rpg->addColumn("شماره پرونده", "PackNo");	
	$rpg->addColumn("حوزه فعالیت", "DomainDesc");
	$rpg->addColumn("نوع", "IsReal", "RealRender");
	$rpg->addColumn("کدملی/شناسه ملی", "NationalID");
	$rpg->addColumn("تلفن", "PhoneNo");
	$rpg->addColumn("همراه", "mobile");
	$rpg->addColumn("آدرس", "address");
	$rpg->addColumn("ایمیل", "email");
	$rpg->addColumn("وب سایت", "WebSite");

	$rpg->addColumn("شعبه", "BranchName");
	$rpg->addColumn("تاریخ پرداخت", "PartDate", "ReportDateRender");
	$col = $rpg->addColumn("مبلغ تایید شده", "PartAmount", "ReportMoneyRender");
	$col->ExcelRender = false;
	$col->EnableSummary();
	$col = $rpg->addColumn("مبلغ پرداخت شده", "SumPayments", "ReportMoneyRender");
	$col->ExcelRender = false;
	$col->EnableSummary();
	$rpg->addColumn("تعداد اقساط", "InstallmentCount");
	$rpg->addColumn("تنفس(ماه)", "DelayMonths");
	$rpg->addColumn("کارمزد مشتری", "CustomerWage");
	$rpg->addColumn("کارمزد صندوق", "FundWage");
	$rpg->addColumn("درصد دیرکرد", "ForfeitPercent");
	$rpg->addColumn("شماره قدیم", "imp_VamCode");
	//$rpg->addColumn("جاری/خاتمه", "IsEnded", "endedRender");
	$rpg->addColumn("وضعیت", "StatusDesc");
	$col = $rpg->addColumn("جمع اقساط", "TotalInstallmentAmount", "ReportMoneyRender");
	$col->ExcelRender = false;
	$col->EnableSummary();
	$rpg->addColumn("تاریخ آخرین قسط", "MaxInstallmentDate", "ReportDateRender");
	$rpg->addColumn("جمع مبلغ اقساط سررسید شده", "installmentsToNow", "ReportMoneyRender");
	

	$rpg->addColumn("تاریخ آخرین پرداخت", "MaxPayDate", "ReportDateRender");
	$rpg->addColumn("مبلغ آخرین پرداخت", "LastPayAmount", "ReportMoneyRender");
	
	$col = $rpg->addColumn("جمع پرداختی مشتری", "TotalPayAmount", "ReportMoneyRender");
	$col->ExcelRender = false;
	$col->EnableSummary();
	$col = $rpg->addColumn("مانده قابل پرداخت", "remainder", "ReportMoneyRender");
	$col->ExcelRender = false;
	$col->EnableSummary();
	
	$col = $rpg->addColumn("مبلغ تاخیر", "ForfeitAmount", "ReportMoneyRender");
	$col->ExcelRender = false;
	$col->EnableSummary();
	
	$col = $rpg->addColumn("تاخیر کل وام", "TotalForfeitAmount", "ReportMoneyRender");
	$col->ExcelRender = false;
	$col->EnableSummary();
	
	if(!$rpg->excel && !$IsDashboard)
	{
		BeginReport();
		echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
				<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
				<td align='center' style='height:100px;vertical-align:middle;font-family:titr;font-size:15px'>
					گزارش کلی وام ها
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
	$page_rpg->mysql_resource = GetData("chart");
	$page_rpg->GenerateChart();
	die();
}

if(isset($_REQUEST["dashboard_show"]))
{
	$chart = ReportGenerator::DashboardSetParams($_REQUEST["rpcmp_ReportID"]);
	if(!$chart)
		ListData(true);	
	
	$page_rpg->mysql_resource = GetData();
	$page_rpg->GenerateChart(false, $_REQUEST["rpcmp_ReportID"]);
	die();	
}
require_once getenv("DOCUMENT_ROOT") . '/framework/ReportDB/Filter_person.php';
?>
<script type="text/javascript" src="/generalUI/ReportGenerator.js"></script>
<script>
LoanReport_total.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

LoanReport_total.prototype.showReport = function(btn, e)
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "tazamin.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function LoanReport_total()
{		
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		layout :{
			type : "table",
			columns :2
		},
		bodyStyle : "text-align:right;padding:5px",
		title : "گزارش کلی وام ها",
		width : 760,
		items :[{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../../framework/person/persons.data.php?' +
						"task=selectPersons&UserTypes=IsAgent,IsSupporter&EmptyRow=true",
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
					el = LoanReport_totalObj.formPanel.down("[itemId=cmp_subAgent]");
					el.getStore().proxy.extraParams["PersonID"] = this.getValue();
					el.getStore().load();
				}
			}
		},{
			xtype : "checkcombo",
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
			displayField : "BranchName",
			valueField : "BranchID",
			hiddenName : "BranchID"
		},{
			xtype : "container",
			html : "وام بلاعوض &nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
				"<input name=IsFree type=radio value='YES' > بلی &nbsp;&nbsp;" +
				"<input name=IsFree type=radio value='NO' > خیر &nbsp;&nbsp;" +
				"<input name=IsFree type=radio value='' checked > هردو " 
		},{
			xtype : "numberfield",
			hideTrigger : true,
			name : "fromRequestID",
			fieldLabel : "از شماره"
		},{
			xtype : "numberfield",
			hideTrigger : true,
			name : "toRequestID",
			fieldLabel : "تا شماره"
		},{
			xtype : "shdatefield",
			name : "fromReqDate",
			fieldLabel : "تاریخ درخواست از"
		},{
			xtype : "shdatefield",
			name : "toReqDate",
			fieldLabel : "تا تاریخ"
		},{
			xtype : "currencyfield",
			name : "fromReqAmount",
			hideTrigger : true,
			fieldLabel : "از مبلغ درخواست"
		},{
			xtype : "currencyfield",
			name : "toReqAmount",
			hideTrigger : true,
			fieldLabel : "تا مبلغ درخواست"
		},{
			xtype : "currencyfield",
			name : "fromPartAmount",
			hideTrigger : true,
			fieldLabel : "از مبلغ تایید پرداخت"
		},{
			xtype : "currencyfield",
			name : "toPartAmount",
			hideTrigger : true,
			fieldLabel : "تا مبلغ تایید پرداخت"
		},{
			xtype : "currencyfield",
			name : "fromPayAmount",
			hideTrigger : true,
			fieldLabel : "از مبلغ پرداخت"
		},{
			xtype : "currencyfield",
			name : "toPayAmount",
			hideTrigger : true,
			fieldLabel : "تا مبلغ پرداخت"
		},{
			xtype : "shdatefield",
			name : "fromPartDate",
			fieldLabel : "تاریخ پرداخت از"
		},{
			xtype : "shdatefield",
			name : "toPartDate",
			fieldLabel : "تا تاریخ"
		},{
			xtype : "numberfield",
			name : "fromInstallmentCount",
			hideTrigger : true,
			fieldLabel : "تعداد اقساط از"
		},{
			xtype : "numberfield",
			name : "toInstallmentCount",
			hideTrigger : true,
			fieldLabel : "تعداد اقساط تا"
		},{
			xtype : "numberfield",
			name : "fromDelayMonths",
			hideTrigger : true,
			fieldLabel : "تنفس از "
		},{
			xtype : "numberfield",
			name : "toDelayMonths",
			hideTrigger : true,
			fieldLabel : "تنفس تا"
		},{
			xtype : "numberfield",
			name : "CustomerWage",
			hideTrigger : true,
			fieldLabel : "کارمزد مشتری"
		},{
			xtype : "numberfield",
			name : "FundWage",
			hideTrigger : true,
			fieldLabel : "کارمزد صندوق"
		},{
			xtype : "numberfield",
			name : "ForfeitPercent",
			hideTrigger : true,
			fieldLabel : "درصد دیرکرد"
		},{
			xtype : "numberfield",
			name : "DelayPercent",
			hideTrigger : true,
			fieldLabel : "کارمزد تنفس"
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../request/request.data.php?' +
						"task=GetAllStatuses",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['InfoID','InfoDesc'],
				autoLoad : true					
			}),
			fieldLabel : "وضعیت وام",
			queryMode : 'local',
			width : 370,
			displayField : "InfoDesc",
			valueField : "InfoID",
			hiddenName : "StatusID"
		},{
			xtype : "container",
			html : "وضعیت خاتمه&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
				"<input name=IsEnded type=radio value='YES' > خاتمه یافته &nbsp;&nbsp;" +
				"<input name=IsEnded type=radio value='NO' > جاری &nbsp;&nbsp;" +
				"<input name=IsEnded type=radio value='' checked > هردو " 
		},{
			xtype : "shdatefield",
			name : "fromEndReqDate",
			fieldLabel : "تاریخ خاتمه از"
		},{
			xtype : "shdatefield",
			name : "toEndReqDate",
			fieldLabel : "تا تاریخ"
		},{
			xtype : "checkcombo",
			fieldLabel : "نوع تضمین",
			hiddenName: "DocType",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../request/request.data.php?' +
						"task=GetTazminDocTypes",
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['InfoID','InfoDesc'],
				autoLoad : true					
			}),
			displayField : "InfoDesc",
			valueField : "InfoID",
			width : 370,
			colspan : 2
		},{
			xtype : "fieldset",
			title : "ستونهای گزارش",
			colspan :2,
			items :[<?= $page_rpg->ReportColumns() ?>]
		},{
			xtype : "fieldset",
			colspan :2,
			title : "رسم نمودار",
			items : [<?= $page_rpg->GetChartItems("LoanReport_totalObj","mainForm","total.php") ?>]
		}],
		buttons : [{
			text : "گزارش ساز",
			iconCls : "db",
			handler : function(){ReportGenerator.ShowReportDB(
						LoanReport_totalObj, 
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
					LoanReport_totalObj.get('excel').value = "true";
				}
			},
			iconCls : "excel"
		},{
			text : "پاک کردن گزارش",
			iconCls : "clear",
			handler : function(){
				LoanReport_totalObj.formPanel.getForm().reset();
				LoanReport_totalObj.get("mainForm").reset();
			}			
		}]
	});
	
	if(<?= session::IsPortal() ? "true" : "false" ?>)
	{
		this.formPanel.down("[hiddenName=ReqPersonID]").getStore().load({
			params : {
				PersonID : "<?= $_SESSION["USER"]["PersonID"] ?>"
			},
			callback : function(){
				me = LoanReport_totalObj;
				me.formPanel.add({
					xtype : "hidden",
					name : "ReqPersonID",
					value : this.getAt(0).data.PersonID
				});
				me.formPanel.down("[hiddenName=ReqPersonID]").setValue(this.getAt(0).data.PersonID);
				me.formPanel.down("[hiddenName=ReqPersonID]").disable();
				
				el = me.formPanel.down("[itemId=cmp_subAgent]");
				el.getStore().proxy.extraParams["PersonID"] = this.getAt(0).data.PersonID;
				el.getStore().load();
			}
		});
	
	}
	
	this.formPanel.getEl().addKeyListener(Ext.EventObject.ENTER, function(keynumber,e){
		
		LoanReport_totalObj.showReport();
		e.preventDefault();
		e.stopEvent();
		return false;
	});
}

LoanReport_total.prototype.ShowChart = function()
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "tazamin.php?chart=true";
	this.form.submit();
	return;
}

LoanReport_totalObj = new LoanReport_total();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>
