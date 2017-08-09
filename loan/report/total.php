<?php

require_once '../header.inc.php';
require_once "../request/request.class.php";
require_once "../request/request.data.php";
require_once "ReportGenerator.class.php";

if(isset($_REQUEST["show"]))
{
	function dateRender($row, $val){
		return DateModules::miladi_to_shamsi($val);
	}	
	
	function moneyRender($row, $val) {
		return number_format($val);
	}
	
	function MakeWhere(&$where, &$pay_where, &$whereParam){
		
		foreach($_POST as $key => $value)
		{
			if($key == "excel" || $key == "OrderBy" || $key == "OrderByDirection" || 
					$value === "" || strpos($key, "combobox") !== false)
				continue;
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
					$value = DateModules::shamsi_to_miladi($value, "-");
					break;
				case "fromReqAmount":
				case "toReqAmount":
				case "fromPartAmount":
				case "toPartAmount":
					$value = preg_replace('/,/', "", $value);
					$pay = true;
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
	
	//.....................................
	$where = "";
	$pay_where = "";
	$whereParam = array();
	MakeWhere($where, $pay_where, $whereParam);
	
	$query = "select r.*,l.*,p.*,
				concat_ws(' ',p1.fname,p1.lname,p1.CompanyName) ReqFullname,
				concat_ws(' ',p2.fname,p2.lname,p2.CompanyName) LoanFullname,
				bi.InfoDesc StatusDesc,
				BranchName,
				SumPayments,
				TotalPayAmount,
				TotalInstallmentAmount
				
			from LON_requests r
			join LON_ReqParts p on(r.RequestID=p.RequestID AND p.IsHistory='NO')
			left join LON_loans l using(LoanID)
			join BSC_branches using(BranchID)
			left join BaseInfo bi on(bi.TypeID=5 AND bi.InfoID=StatusID)
			left join BSC_persons p1 on(p1.PersonID=r.ReqPersonID)
			left join BSC_persons p2 on(p2.PersonID=r.LoanPersonID)
			left join (
				select RequestID,sum(PayAmount) SumPayments 
				from LON_payments
				join (select SourceID,SourceID3 from ACC_DocItems where SourceType=".DOCTYPE_LOAN_PAYMENT." group by SourceID,SourceID3)t 
					on(t.SourceID=RequestID AND t.SourceID3=PayID)
				where 1=1 $pay_where
				group by RequestID			
			)t_pay on(r.RequestID=t_pay.RequestID)
			left join (
				select RequestID,sum(PayAmount) TotalPayAmount 
				from LON_BackPays
				left join ACC_IncomeCheques i using(IncomeChequeID)
				where if(PayType=" . BACKPAY_PAYTYPE_CHEQUE . ",ChequeStatus=".INCOMECHEQUE_VOSUL.",1=1)
					AND PayType<>" . BACKPAY_PAYTYPE_CORRECT . "
				group by RequestID			
			)t1 on(r.RequestID=t1.RequestID)
			left join (
				select RequestID,sum(InstallmentAmount) TotalInstallmentAmount 
				from LON_installments
				where  history='NO' AND IsDelayed='NO'
				group by RequestID			
			)t2 on(r.RequestID=t2.RequestID)
			where 1=1 " . $where . " 
			
			group by r.RequestID
			order by " . $_POST["OrderBy"] . " " . $_POST["OrderByDirection"];
	
	
	$dataTable = PdoDataAccess::runquery($query, $whereParam);
	$query = PdoDataAccess::GetLatestQueryString();
	for($i=0; $i< count($dataTable); $i++)
	{
		$dt = array();
		$ComputeArr = LON_requests::ComputePayments2($dataTable[$i]["RequestID"], $dt);
		$TotalRemain = LON_requests::GetTotalRemainAmount($dataTable[$i]["RequestID"], $ComputeArr);
		$dataTable[$i]["remainder"] = $TotalRemain;
	}
	
	$rpg = new ReportGenerator();
	$rpg->excel = !empty($_POST["excel"]);
	$rpg->mysql_resource = $dataTable;
	
	function endedRender($row,$value){
		return ($value == "YES") ? "خاتمه" : "جاری";
	}
	
	$rpg->addColumn("شماره وام", "RequestID");
	$rpg->addColumn("نوع وام", "LoanDesc");
	$rpg->addColumn("عنوان طرح", "PlanTitle");	
	$rpg->addColumn("معرفی کننده", "ReqFullname");
	$rpg->addColumn("تاریخ درخواست", "ReqDate", "dateRender");
	$col = $rpg->addColumn("مبلغ درخواست", "ReqAmount", "moneyRender");
	$col->ExcelRender = false;
	$col->EnableSummary();
	$rpg->addColumn("مشتری", "LoanFullname");
	$rpg->addColumn("شعبه", "BranchName");
	$rpg->addColumn("تاریخ پرداخت", "PartDate", "dateRender");
	$col = $rpg->addColumn("مبلغ تایید شده", "PartAmount", "moneyRender");
	$col->ExcelRender = false;
	$col->EnableSummary();
	$col = $rpg->addColumn("مبلغ پرداخت شده", "SumPayments", "moneyRender");
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
	$col = $rpg->addColumn("قابل پرداخت مشتری", "TotalInstallmentAmount", "moneyRender");
	$col->ExcelRender = false;
	$col->EnableSummary();
	$col = $rpg->addColumn("جمع پرداختی مشتری", "TotalPayAmount", "moneyRender");
	$col->ExcelRender = false;
	$col->EnableSummary();
	$col = $rpg->addColumn("مانده قابل پرداخت", "remainder", "moneyRender");
	$col->ExcelRender = false;
	$col->EnableSummary();
	if(!$rpg->excel)
	{
		BeginReport();
		echo "<div style=display:none>" . $query . "</div>";
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
	$rpg->generateReport();
	die();
}
?>
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
	this.form.action =  this.address_prefix + "total.php?show=true";
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
		width : 780,
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
			xtype : "fieldset",
			title : "مرتب سازی خروجی",
			layout : "hbox",
			items : [{
				xtype : "combo",
				store : new Ext.data.SimpleStore({
					data : [
						["r.RequestID", "شماره وام"],
						["r.ReqDate", "تاریخ درخواست"],
						["p.PartDate", "تاریخ پرداخت"],
						["PartAmount", "مبلغ پرداخت"],
						["BranchName", "شعبه"],
						["ReqFullname", "معرفی کننده"]
					],
					fields : ['id','title']
				}),
				value : "r.RequestID",
				valueField : "id",
				displayField : "title",
				hiddenName : "OrderBy"
			},{
				xtype : "combo",
				store : new Ext.data.SimpleStore({
					data : [
						["Desc", "نزولی"],
						["Asc", "صعودی"]
					],
					fields : ['id','title']
				}),
				value : "Desc",
				valueField : "id",
				displayField : "title",
				hiddenName : "OrderByDirection"
			}]
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
	
	this.formPanel.getEl().addKeyListener(Ext.EventObject.ENTER, function(keynumber,e){
		
		LoanReport_totalObj.showReport();
		e.preventDefault();
		e.stopEvent();
		return false;
	});
}

LoanReport_totalObj = new LoanReport_total();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>