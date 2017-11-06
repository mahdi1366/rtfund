<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

//................  GET ACCESS  .....................
$accessObj = FRW_access::GetAccess(empty($_POST["MenuID"]) ? "0" : $_POST["MenuID"]);
//...................................................
if(!empty($_SESSION["USER"]["portal"]))
{
	$accessObj->AddFlag = true;
	$accessObj->EditFlag = true;
	$accessObj->RemoveFlag = true;
}

$RequestID = !empty($_POST["RequestID"]) ? $_POST["RequestID"] : 0;
$ReadOnly = isset($_REQUEST["ReadOnly"]) && $_REQUEST["ReadOnly"] == "true" ? true : false;

if(isset($_SESSION["USER"]["framework"]))
	$User = "Staff";
else
{
	if(isset($_REQUEST["mode"]))
		$User = $_REQUEST["mode"];
	else
	{
		if($_SESSION["USER"]["IsAgent"] == "YES")
			$User = "Agent";
		else if($_SESSION["USER"]["IsCustomer"] == "YES")
			$User = "Customer";
	}
}

$dg = new sadaf_datagrid("dg","/loan/request/request.data.php?task=GetRequestParts", "grid_div");

$dg->addColumn("", "RequestID","", true);
$dg->addColumn("", "StatusID","", true);
$dg->addColumn("", "PartDate","", true);
$dg->addColumn("", "PartStartDate","", true);
$dg->addColumn("", "PartAmount","", true);
$dg->addColumn("", "InstallmentCount","", true);
$dg->addColumn("", "IntervalType","", true);
$dg->addColumn("", "PayInterval","", true);
$dg->addColumn("", "DelayMonths","", true);
$dg->addColumn("", "DelayDays","", true);
$dg->addColumn("", "ForfeitPercent","", true);
$dg->addColumn("", "CustomerWage","", true);
$dg->addColumn("", "FundWage","", true);
$dg->addColumn("", "IsStarted","", true);
$dg->addColumn("", "IsEnded","", true);
$dg->addColumn("", "IsPaid","", true);
$dg->addColumn("", "WageReturn","", true);
$dg->addColumn("", "DelayReturn","", true);
$dg->addColumn("", "imp_VamCode","", true);
$dg->addColumn("", "PayCompute","", true);
$dg->addColumn("", "MaxFundWage","", true);
$dg->addColumn("", "ReqPersonID","", true);
$dg->addColumn("", "AgentReturn","", true);
$dg->addColumn("", "AgentDelayReturn","", true);
$dg->addColumn("", "IsDocRegister","", true);
$dg->addColumn("", "DelayPercent","", true);
$dg->addColumn("", "IsHistory","", true);
$dg->addColumn("", "PayDuration","", true);

$col = $dg->addColumn("عنوان شرایط", "PartDesc", "");
$col->sortable = false;

if(!$ReadOnly)
{
	if($User == "Staff" && $accessObj->EditFlag)
		$dg->addButton("addPart", "ایجاد شرایط", "add", "function(){RequestInfoObject.PartInfo(false);}");
	
	$col = $dg->addColumn("", "PartID");
	$col->renderer = "RequestInfo.OperationRender";
	$col->width = 50;	
}

$dg->HeaderMenu = false;
$dg->hideHeaders = true;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 180;
$dg->width = 120;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "MaxAmount";
//$dg->disableFooter = true;

$grid = $dg->makeGrid_returnObjects();

require_once 'RequestInfo.js.php';

if(isset($_SESSION["USER"]["framework"]))
	echo "<br>";
?>
<style>
	.summary {
		border : 1px solid #b5b8c8;
		border-collapse: collapse;
	}
	.summary td{
		border: 1px solid #b5b8c8;
		line-height: 21px;
		direction: ltr;
		padding: 0 5px;
	}
</style>
<center>
	<div id="mainForm"></div>
	<div id="PartForm"></div>
	<div id="SendForm"></div>
	<div id="summaryDIV" style="display:none">
		<div style="float:right"><table style="width:200px" class="summary">
			<tr>
				<td style="width:70px;background-color: #dfe8f6;">مبلغ هر قسط</td>
				<td style="background-color: #dfe8f6;">سود دوره تنفس</td>
			</tr>
			<tr>
				<td><div id="SUM_InstallmentAmount" class="blueText">&nbsp;</div></td>
				<td><div id="SUM_Delay" class="blueText">&nbsp;</div></td>
			</tr>
			<tr>
				<td style="background-color: #dfe8f6;">مبلغ قسط آخر</td>
				<td style="background-color: #dfe8f6;">خالص پرداختی</td>
			</tr>
			<tr>
				<td><div id="SUM_LastInstallmentAmount" class="blueText">&nbsp;</div></td>
				<td><div id="SUM_NetAmount" class="blueText">&nbsp;</div></td>
			</tr>			
		</table></div>
		<div style="float:right"><table style="width:190px" class="summary">
			<tr>
				<td style="width:90px;direction:rtl;background-color: #dfe8f6;">کارمزد وام</td>
				<td><div id="SUM_TotalWage" class="blueText">&nbsp;</div></td>
			</tr>
			<tr id="TR_FundWage">
				<td style="direction:rtl;background-color: #dfe8f6;">سهم صندوق</td>
				<td><div id="SUM_FundWage" class="blueText">&nbsp;</div></td>
			</tr>
			<tr id="TR_AgentWage">
				<td style="direction:rtl;background-color: #dfe8f6;">سهم سرمایه گذار</td>
				<td><div id="SUM_AgentWage" class="blueText">&nbsp;</div></td>
			</tr>
		</table></div>
		<div style="float:right"><table id="div_yearly" style="width:190px" class="summary">
			<tr>
				<td style="direction:rtl;width:85px;background-color: #dfe8f6;">کارمزد سال اول</td>
				<td><div id="SUM_Wage_1Year" class="blueText">&nbsp;</div></td>
			</tr>
			<tr>
				<td style="direction:rtl;background-color: #dfe8f6;">کارمزد سال دوم</td>
				<td><div id="SUM_Wage_2Year" class="blueText">&nbsp;</div></td>
			</tr>
			<tr>
				<td style="direction:rtl;background-color: #dfe8f6;">کارمزد سال سوم</td>
				<td><div id="SUM_Wage_3Year" class="blueText">&nbsp;</div></td>
			</tr>
			<tr>
				<td style="direction:rtl;background-color: #dfe8f6;">کارمزد سال چهارم</td>
				<td><div id="SUM_Wage_4Year" class="blueText">&nbsp;</div></td>
			</tr>
		</table></div>
	</div> 
	<br>
</center>