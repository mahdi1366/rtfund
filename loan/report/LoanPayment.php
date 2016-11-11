<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.12
//-----------------------------

require_once '../header.inc.php';
require_once "ReportGenerator.class.php";
require_once '../request/request.class.php';
require_once '../request/request.data.php';

if(isset($_REQUEST["show"]))
{
	$RequestID = $_REQUEST["RequestID"];
	
	$dt = LON_installments::SelectAll("r.RequestID=?" , array($RequestID));
	$returnArr = ComputePayments($RequestID, $dt);
	
	//............ get remain untill now ......................
	$PartObj = LON_ReqParts::GetValidPartObj($RequestID);
	$BaseInstallmentAmount = round($PartObj->PartAmount*1/$PartObj->InstallmentCount);
	
	$CurrentRemain = 0;
	$EndingAmount = $returnArr[count($returnArr)-1]["TotalRemainder"];
	foreach($dt as $row)
	{
		if($row["InstallmentDate"] > DateModules::Now())
		{
			$EndingAmount -= $row["InstallmentAmount"]*1 - $BaseInstallmentAmount;
		}
		else
			$CurrentRemain = $row["TotalRemainder"];
	}
	//.........................................................
	
	$rpg = new ReportGenerator();
	$rpg->excel = !empty($_POST["excel"]);
	function dateRender($row, $val){
		return DateModules::miladi_to_shamsi($val);
	}	
	
	function amountRender($row, $val){
		return "<span dir=ltr>" . number_format($val) . "</span>";
	}
	
	$col = $rpg->addColumn("", "InstallmentID");
	$col->hidden = true;
	
	$col = $rpg->addColumn("تاریخ قسط", "InstallmentDate","dateRender");
	$col->rowspanByFields = array("InstallmentID");
	$col->rowspaning = true;
	
	$col = $rpg->addColumn("مبلغ قسط", "InstallmentAmount","amountRender");
	$col->rowspaning = true;
	$col->rowspanByFields = array("InstallmentID");
	
	$col = $rpg->addColumn("تاریخ پرداخت", "PayDate","dateRender");
	$col->rowspaning = true;
	
	$col = $rpg->addColumn("مبلغ پرداخت", "FixPayAmount","amountRender");
	$col->rowspaning = true;
	$col->rowspanByFields = array("PayDate");
	
	$col = $rpg->addColumn("قابل برداشت", "PayAmount","amountRender");
	
	$col = $rpg->addColumn("برداشت شده", "UsedPayAmount","amountRender");
	
	$rpg->addColumn("تعداد روز تاخیر", "ForfeitDays");
	$col = $rpg->addColumn("مبلغ تاخیر", "CurForfeitAmount","amountRender");
	
	$col = $rpg->addColumn("تاخیر کل", "ForfeitAmount","amountRender");
	//$col->EnableSummary();
	
	$rpg->addColumn("مانده قسط", "remainder","amountRender");
	$rpg->addColumn("مانده کل", "TotalRemainder","amountRender");
	
	
	//$rpg->page_size = 20;
	//$rpg->paging = true;
	
	$rpg->mysql_resource = $returnArr;
	echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">';
	echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
			<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
			<td align='center' style='height:100px;vertical-align:middle;font-family:b titr;font-size:15px'>
				گزارش پرداخت وام
			</td>
			<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : " 
		. DateModules::shNow() . "<br>";
	
	echo "</td></tr></table>";
	
	$ReqObj = new LON_requests($RequestID);
	$partObj = LON_ReqParts::GetValidPartObj($RequestID);
	
	?>
	<table style="border:2px groove #9BB1CD;border-collapse:collapse;width:100%;font-family: tahoma;font-size: 12px;line-height: 20px;">
		<tr>
			<td style="padding-right: 10px">وام گیرنده : <b><?= $ReqObj->_LoanPersonFullname ?></b>
				<br>مبلغ وام : <b><?= number_format($ReqObj->ReqAmount) ?></b>
				<br>مبلغ فاز : <b><?= number_format($partObj->PartAmount) ?></b>
			<br>تاریخ پرداخت : <b><?= DateModules::miladi_to_shamsi($partObj->PartDate) ?></b>
			</td>
			<td> فاصله اقساط : <b><?= $partObj->PayInterval . ($partObj->IntervalType == "DAY" ? "روز" : "ماه") ?></b>
				<br> مدت تنفس : <b><?= $partObj->DelayMonths ?> ماه و 
						<?= $partObj->DelayDays ?> روز </b>
				<br> کارمزد وام: <b><?= $partObj->CustomerWage ?> % </b> کارمزد تنفس : 
					<b><?= $partObj->DelayPercent ?> % </b>
				<br> درصد دیرکرد : <b><?= $partObj->ForfeitPercent ?> % </b>
			</td>
			<td style="font-family: tahoma; font-size: 15px; font-weight: bold;line-height: 23px;">
				<table width="440px">
					<tr>
						<td>مانده قابل پرداخت : </td>
						<td><?= number_format($CurrentRemain) ?>ریال </td> 
					</tr>
					<tr>
						<td>مانده تا انتها : </td>
						<td><?= number_format($returnArr[count($returnArr)-1]["TotalRemainder"]) ?>  ريال </td> 
					</tr>
					<tr>
						<td>مبلغ قابل پرداخت در صورت تسویه وام :</td>
						<td><?= number_format($EndingAmount) ?>    ریال </td>
					</tr>
				</table>
			</td>
		</tr>
	</table>	
	<?
	
	$rpg->generateReport();
	die();
}
?>
<script>
LoanReport_payments.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

LoanReport_payments.prototype.showReport = function(btn, e)
{
	this.form = this.get("mainForm")
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.action =  this.address_prefix + "LoanPayment.php?show=true";
	this.form.submit();
	this.get("excel").value = "";
	return;
}

function LoanReport_payments()
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
		width : 650,
		items :[{
			xtype : "combo",
			store: new Ext.data.Store({
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + '../request/request.data.php?task=SelectAllRequests2',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['loanFullname','PartAmount',"RequestID","PartDate", "ReqDate","RequestID",{
					name : "fullTitle",
					convert : function(value,record){
						return "[ " + record.data.RequestID + " ] " + 
							record.data.loanFullname + "  به مبلغ  " + 
							Ext.util.Format.Money(record.data.PartAmount) + " مورخ " + 
							MiladiToShamsi(record.data.PartDate);
					}
				}]				
			}),
			displayField: 'fullTitle',
			pageSize : 10,
			valueField : "RequestID",
			hiddenName : "RequestID",
			width : 600,
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct" style="height: 23px;">',
				'<td style="padding:7px">کد وام</td>',
				'<td style="padding:7px">وام گیرنده</td>',
				'<td style="padding:7px">مبلغ وام</td>',
				'<td style="padding:7px">تاریخ پرداخت</td> </tr>',
				'<tpl for=".">',
					'<tr class="x-boundlist-item" style="border-left:0;border-right:0">',
					'<td style="border-left:0;border-right:0" class="search-item">{RequestID}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">{loanFullname}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">',
						'{[Ext.util.Format.Money(values.PartAmount)]}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">{[MiladiToShamsi(values.PartDate)]}</td> </tr>',
				'</tpl>',
				'</table>'
			),
			itemId : "RequestID"
		}],
		buttons : [{
			text : "مشاهده گزارش",
			handler : Ext.bind(this.showReport,this),
			iconCls : "report"
		}]
	});
}

LoanReport_paymentsObj = new LoanReport_payments();
</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
	<input type="hidden" name="excel" id="excel">
</form>