<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataGrid;

$RequestID = !empty($_POST["RequestID"]) ? $_POST["RequestID"] : 0;

if($_SESSION["USER"]["framework"])
	$User = "Staff";
else
{
	if($_SESSION["USER"]["IsAgent"] == "YES")
		$User = "Agent";
	else if($_SESSION["USER"]["IsCustomer"] == "YES")
		$User = "Customer";
}

$dg = new sadaf_datagrid("dg","/loan/request/request.data.php?task=GetRequestParts", "grid_div");

$dg->addColumn("", "RequestID","", true);
$dg->addColumn("", "PayDate","", true);
$dg->addColumn("", "PartAmount","", true);
$dg->addColumn("", "PayCount","", true);
$dg->addColumn("", "IntervalType","", true);
$dg->addColumn("", "PayInterval","", true);
$dg->addColumn("", "DelayMonths","", true);
$dg->addColumn("", "ForfeitPercent","", true);
$dg->addColumn("", "CustomerFee","", true);
$dg->addColumn("", "FundFee","", true);
$dg->addColumn("", "AgentFee","", true);

$col = $dg->addColumn("عنوان مرحله", "PartDesc", "");
$col->editor = ColumnEditor::TextField();
$col->sortable = false;

$col = $dg->addColumn("", "PartID");
$col->renderer = "RequestInfo.OperationRender";
$col->width = 70;

$dg->addButton("addPart", "ایجاد مرحله پرداخت", "add", "function(){RequestInfoObject.PartInfo('new');}");

$dg->HeaderMenu = false;
$dg->hideHeaders = true;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 150;
$dg->width = 180;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "MaxAmount";
$dg->disableFooter = true;

$grid = $dg->makeGrid_returnObjects();

//......................................................

$dg = new sadaf_datagrid("dg","/loan/request/request.data.php?task=GetPartPayments", "grid_div");

$dg->addColumn("", "PayID","", true);
$dg->addColumn("", "PartID","", true);
$dg->addColumn("", "PayDate","", true);
$dg->addColumn("", "PayAmount","", true);
$dg->addColumn("", "FeeAmount","", true);
$dg->addColumn("", "FeePercent","", true);
$dg->addColumn("", "PaidDate","", true);
$dg->addColumn("", "PaidAmount","", true);
$dg->addColumn("", "StatusID","", true);

$col = $dg->addColumn("تاریخ سررسید", "PayDate", "");
$col->sortable = false;

$col = $dg->addColumn("مبلغ خالص", "PartID", GridColumn::ColumnType_money);
$col->width = 80; 

$col = $dg->addColumn("کارمزد", "FeeAmount", GridColumn::ColumnType_money);
$col->width = 80;

$col = $dg->addColumn("کارمزد", "FeePercent", "");
$col->width = 80;

$dg->addButton("", "محاسبه اقساط", "list", "function(){RequestInfoObject.ComputePayments();}");

$dg->HeaderMenu = false;
$dg->hideHeaders = true;
$dg->title = "لیست اقساط";
$dg->emptyTextOfHiddenColumns = true;
$dg->height = 460;
$dg->width = 680;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "PayDate";

$grid2 = $dg->makeGrid_returnObjects();

//......................................................

require_once 'RequestInfo.js.php';

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
	<br>
	<div id="mainForm"></div>
	<div id="PartForm"></div>
	<div id="SendForm"></div>
	<div id="summaryDIV">
		<div style="float:right"><table style="width:190px" class="summary">
			<tr>
				<td style="width:70px;background-color: #dfe8f6;">مبلغ هر قسط</td>
				<td style="background-color: #dfe8f6;">سود دوره تنفس</td>
			</tr>
			<tr>
				<td><div id="SUM_PayAmount" class="blueText">&nbsp;</div></td>
				<td><div id="SUM_Delay" class="blueText">&nbsp;</div></td>
			</tr>
			<tr>
				<td style="background-color: #dfe8f6;">مبلغ قسط آخر</td>
				<td style="background-color: #dfe8f6;">خالص پرداختی</td>
			</tr>
			<tr>
				<td><div id="SUM_LastPayAmount" class="blueText">&nbsp;</div></td>
				<td><div id="SUM_NetAmount" class="blueText">&nbsp;</div></td>
			</tr>			
		</table></div>
		<div style="float:right"><table style="width:170px" class="summary">
			<tr>
				<td style="width:70px;direction:rtl;background-color: #dfe8f6;">کارمزد وام</td>
				<td><div id="SUM_TotalFee" class="blueText">&nbsp;</div></td>
			</tr>
			<tr>
				<td style="direction:rtl;background-color: #dfe8f6;">سهم صندوق</td>
				<td><div id="SUM_FundFee" class="blueText">&nbsp;</div></td>
			</tr>
			<tr>
				<td style="direction:rtl;background-color: #dfe8f6;">سهم عامل</td>
				<td><div id="SUM_AgentFee" class="blueText">&nbsp;</div></td>
			</tr>
		</table></div>
		<div style="float:right"><table  style="width:200px" class="summary">
			<tr>
				<td style="direction:rtl;width:85px;background-color: #dfe8f6;">کارمزد سال اول</td>
				<td><div id="SUM_Fee_1Year" class="blueText">&nbsp;</div></td>
			</tr>
			<tr>
				<td style="direction:rtl;background-color: #dfe8f6;">کارمزد سال دوم</td>
				<td><div id="SUM_Fee_2Year" class="blueText">&nbsp;</div></td>
			</tr>
			<tr>
				<td style="direction:rtl;background-color: #dfe8f6;">کارمزد سال سوم</td>
				<td><div id="SUM_Fee_3Year" class="blueText">&nbsp;</div></td>
			</tr>
			<tr>
				<td style="direction:rtl;background-color: #dfe8f6;">کارمزد سال چهارم</td>
				<td><div id="SUM_Fee_4Year" class="blueText">&nbsp;</div></td>
			</tr>
		</table></div>
	</div> 

</center>