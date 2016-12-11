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
	
	$dt = array();
	$returnArr = LON_requests::ComputePayments($RequestID, $dt);
	
	//............ get remain untill now ......................
	$PartObj = LON_ReqParts::GetValidPartObj($RequestID);
	$CurrentRemain = 0;
	foreach($dt as $row)
	{
		if($row["InstallmentDate"] <= DateModules::Now())
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
	
	$rpg->mysql_resource = $returnArr;
	BeginReport();
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
	
	//..........................................................
	$rpg2 = new ReportGenerator();
	$rpg2->mysql_resource = PdoDataAccess::runquery("
		select * from LON_installments where RequestID=?", array($RequestID));
	$col = $rpg2->addColumn("", "InstallmentID");
	$col->hidden = true;
	
	$col = $rpg2->addColumn("تاریخ قسط", "InstallmentDate","dateRender");
	$col = $rpg2->addColumn("مبلغ قسط", "InstallmentAmount","amountRender");
	
	function profitRender(&$row, $value, $param, $prevRow){
		
		$R = $param->IntervalType == "MONTH" ? 1200/$param->PayInterval : 36500/$param->PayInterval;
		$V = !$prevRow ? $param->PartAmount : $prevRow["EndingBalance"];
		
		$row["profit"] = round( $V*($param->CustomerWage/$R) );
		
		return number_format($row["profit"]);
	}
	$col = $rpg2->addColumn("بهره قسط", "InstallmentID","profitRender", $PartObj);
	
	function SumProfitRender(&$row, $value, $param, $prevRow){
		
		if(!$prevRow)
			$row["SumProfit"] = $row["profit"];
		else
			$row["SumProfit"] = $prevRow["SumProfit"] + $row["profit"];
		
		return number_format($row["SumProfit"]);
	}
	$col = $rpg2->addColumn("بهره قسط (تجمعي)", "InstallmentID","SumProfitRender");
	
	function pureRender($row, $value, $param, $prevRow){
		return number_format($row["InstallmentAmount"] - $row["profit"]);
	}
	$col = $rpg2->addColumn("اصل قسط", "InstallmentID","pureRender", $PartObj);
	
	function pureRemainRender(&$row, $value, $param, $prevRow){
		if(!$prevRow)
			$row["pureAmount"] = $param;
		else
			$row["pureAmount"] = $prevRow["EndingBalance"];	
		
		$row["EndingBalance"] = $row["pureAmount"] - ($row["InstallmentAmount"] - $row["profit"]);
		
		return number_format($row["pureAmount"]);
	}
	$col = $rpg2->addColumn("مانده اصل وام", "InstallmentID","pureRemainRender",$PartObj->PartAmount);
	
	ob_start();
	$rpg2->generateReport();
	$report2 = ob_get_clean();
	
	//..........................................................
	$LastPayedInstallment = 0;
	foreach($rpg->mysql_resource as $row)
	{
		if($row["remainder"]*1 == 0 && isset($row["InstallmentID"]))
			$LastPayedInstallment = $row["InstallmentID"];
	}
	if($LastPayedInstallment == 0)
		$EndingAmount = $rpg2->mysql_resource[0]["pureAmount"];
	else
	{
		for($i=0; $i < count($rpg2->mysql_resource);$i++)
		{
			if($rpg2->mysql_resource[$i]["InstallmentID"] == $LastPayedInstallment)
			{
				if($i+1 == count($rpg2->mysql_resource) )
					$EndingAmount = 0;
				else
					$EndingAmount = $rpg2->mysql_resource[$i+1]["pureAmount"];
				break;
			}
		}
	}
	//..........................................................
	?>
	<table style="border:2px groove #9BB1CD;border-collapse:collapse;width:100%;font-family: nazanin;
		   font-size: 16px;line-height: 20px;">
		<tr>
			<td style="padding-right: 10px">وام گیرنده : <b><?= $ReqObj->_LoanPersonFullname ?></b>
				<br>مبلغ درخواست : <b><?= number_format($ReqObj->ReqAmount) ?></b>
				<br>مبلغ وام : <b><?= number_format($partObj->PartAmount) ?></b>
			<br>تاریخ پرداخت : <b><?= DateModules::miladi_to_shamsi($partObj->PartDate) ?></b>
			</td>
			<td> فاصله اقساط : <b><?= $partObj->PayInterval . ($partObj->IntervalType == "DAY" ? "روز" : "ماه") ?></b>
				<br> مدت تنفس : <b><?= $partObj->DelayMonths ?> ماه و 
						<?= $partObj->DelayDays ?> روز </b>
				<br> کارمزد وام: <b><?= $partObj->CustomerWage ?> % </b> کارمزد تنفس : 
					<b><?= $partObj->DelayPercent ?> % </b>
				<br> درصد دیرکرد : <b><?= $partObj->ForfeitPercent ?> % </b>
			</td>
			<td style="font-family: nazanin; font-size: 18px; font-weight: bold;line-height: 23px;">
				<table width="440px">
					<tr>
						<td>مانده قابل پرداخت : </td>
						<td><b><?= number_format($CurrentRemain)?> ریال
							</b></td>
					</tr>
					<tr>
						<td>مانده تا انتها : </td>
						<td><b><?= number_format($returnArr[count($returnArr)-1]["TotalRemainder"])?> ریال
							</b></td>
					</tr>
					<tr>
						<td>مبلغ قابل پرداخت در صورت تسویه وام :</td>
						<td><b><?= number_format($EndingAmount) ?> ریال
							</b></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>	
	<?
	
	$rpg->generateReport();
	
	echo "<br>";
	
	echo $report2;
	
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