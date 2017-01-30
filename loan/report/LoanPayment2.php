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
	$ReqObj = new LON_requests($RequestID);
	$dt = array();
	$returnArr = LON_requests::ComputePayments2($RequestID, $dt);
	$PartObj = LON_ReqParts::GetValidPartObj($RequestID);

	//............ get remain untill now ......................
	$CurrentRemain = LON_requests::GetCurrentRemainAmount($RequestID, $returnArr);
	//.........................................................
	
	$rpg = new ReportGenerator();
		
	function RowColorRender($row){
		return $row["ActionType"] == "pay" ? "#fcfcb6" : "";
	}
	$rpg->rowColorRender = "RowColorRender";
	
	
	function ActionRender($row, $value){
		return $value == "installment" ? "قسط" : "پرداخت";
	}
	$rpg->addColumn("نوع عملیات", "ActionType", "ActionRender");
		
	$rpg->addColumn("تاریخ عملیات", "ActionDate","ReportDateRender");

	$rpg->addColumn("مبلغ", "ActionAmount","ReportMoneyRender");
	
	$rpg->addColumn("تعداد روز تاخیر", "ForfeitDays");
	$rpg->addColumn("مبلغ تاخیر", "CurForfeitAmount","ReportMoneyRender");
	
	$rpg->addColumn("تاخیر کل", "ForfeitAmount","ReportMoneyRender");
	
	$rpg->addColumn("مانده کل", "TotalRemainder","ReportMoneyRender");
	
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
	$report2 = "";
	if($ReqObj->ReqPersonID != SHEKOOFAI)
	{
		$extraAmount = 0;
		$startDate = DateModules::miladi_to_shamsi($PartObj->PartDate);
		$DelayDuration = DateModules::JDateMinusJDate(
			DateModules::AddToJDate($startDate, $PartObj->DelayDays, $PartObj->DelayMonths), $startDate)+1;
		if($PartObj->DelayDays*1 > 0)
			$TotalDelay = round($PartObj->PartAmount*$PartObj->DelayPercent*$DelayDuration/36500);
		else
			$TotalDelay = round($PartObj->PartAmount*$PartObj->DelayPercent*$PartObj->DelayMonths/1200);
		if($PartObj->DelayReturn == "INSTALLMENT")
			$extraAmount += $TotalDelay*($PartObj->FundWage/$PartObj->DelayPercent);
		if($PartObj->AgentDelayReturn == "INSTALLMENT" && $PartObj->DelayPercent>$PartObj->FundWage)
			$extraAmount += $TotalDelay*(($PartObj->DelayPercent-$PartObj->FundWage)/$PartObj->DelayPercent);
		$totalAmount = $PartObj->PartAmount + $extraAmount;
		
		$rpg2 = new ReportGenerator();
		$rpg2->mysql_resource = PdoDataAccess::runquery("
			select * from LON_installments where RequestID=? AND IsDelayed='NO'", array($RequestID));
		$col = $rpg2->addColumn("", "InstallmentID");
		$col->hidden = true;

		$col = $rpg2->addColumn("تاریخ قسط", "InstallmentDate","ReportDateRender");
		$col = $rpg2->addColumn("مبلغ قسط", "InstallmentAmount","ReportMoneyRender");

		function profitRender(&$row, $value, $param, $prevRow){

			if($param->PayInterval == 0)
			{
				$row["profit"] = 0;
				return number_format($row["profit"]);
			}
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
		$col = $rpg2->addColumn("مانده اصل وام", "InstallmentID","pureRemainRender",$totalAmount);

		ob_start();
		$rpg2->generateReport();
		$report2 = ob_get_clean();

		//..........................................................
		$EndingAmount = -1;
		$EndingDate = DateModules::Now(); 
		$EndingInstallment = 0;
		for($i=count($rpg2->mysql_resource)-1; $i >= 0;$i--)
		{
			if($rpg2->mysql_resource[$i]["InstallmentDate"] <= DateModules::Now())
			{
				if($i == count($rpg2->mysql_resource)-1)
				{
					$EndingAmount = 0;
					break;
				}
				$EndingAmount = $rpg2->mysql_resource[$i+1]["pureAmount"]*1;
				$EndingDate = $rpg2->mysql_resource[$i]["InstallmentDate"];
				$EndingInstallment = $rpg2->mysql_resource[$i]["InstallmentID"];
				break;
			}
		}	
		if($EndingAmount == -1)
		{
			$EndingAmount = $rpg2->mysql_resource[0]["pureAmount"]*1;
			$EndingDate = $rpg2->mysql_resource[0]["InstallmentDate"];
			$EndingInstallment = $rpg2->mysql_resource[0]["InstallmentID"];
		}
		//----------------------
		for($i=count($rpg->mysql_resource)-1; $i != 0;$i--)
		{
			$row = $rpg->mysql_resource[$i];
			
			if($row["InstallmentID"] == $EndingInstallment)
			{
				$EndingAmount += $row["TotalRemainder"];
				break;
			}
			
			if($row["ActionType"] == "pay")
			{
				$EndingAmount += $row["TotalRemainder"];
				break;
			}
		}
		
		$EndingAmount += $returnArr[ count($returnArr)-1 ]["ForfeitAmount"]*1;
		//..........................................................
		/*$TotalUsedPayAmount = 0;
		$LastPayedInstallment = null;
		foreach($rpg->mysql_resource as $row)
		{
			if($row["ActionType"] == "installment" && $row["TotalRemainder"]*1 <= 0)
			{
				$LastPayedInstallment = $row;
				$TotalUsedPayAmount = -1*$row["TotalRemainder"]*1;
			}
		}
		if($LastPayedInstallment == null)
			$EndingAmount = $returnArr[count($returnArr)-1]["TotalRemainder"]*1 + 
								$returnArr[ count($returnArr)-1 ]["ForfeitAmount"]*1;
		else
		{
			for($i=0; $i < count($rpg2->mysql_resource);$i++)
			{
				if($rpg2->mysql_resource[$i]["InstallmentID"] == $LastPayedInstallment["InstallmentID"])
				{
					if($i+1 == count($rpg2->mysql_resource) )
						$EndingAmount = $rpg->mysql_resource[ count($rpg->mysql_resource)-1 ]["TotalRemainder"];
					else
						$EndingAmount = $rpg2->mysql_resource[$i+1]["pureAmount"]*1 + 
							$rpg->mysql_resource[ count($rpg->mysql_resource)-1 ]["ForfeitAmount"]*1 -
							$TotalUsedPayAmount;
					break;
				}
			}
		}*/
		//..........................................................
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
						<td>مانده قابل پرداخت معوقه : </td>
						<td><b><?= number_format($CurrentRemain)?> ریال
							</b></td>
					</tr>
					<tr>
						<td>مانده تا انتها : </td>
						<td><b><?= number_format(
								$returnArr[count($returnArr)-1]["TotalRemainder"]*1 + 
								$returnArr[ count($returnArr)-1 ]["ForfeitAmount"]*1)?> ریال
							</b></td>
					</tr>
					<? if($ReqObj->ReqPersonID != SHEKOOFAI){ ?>
					<tr>
						<td>مبلغ قابل پرداخت در صورت تسویه وام :</td>
						<td><b><?= number_format($EndingAmount) ?> ریال
							</b></td>
					</tr>
					<? } ?>
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