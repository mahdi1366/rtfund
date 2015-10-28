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
$dg->addColumn("", "CustomerWage","", true);
$dg->addColumn("", "FundWage","", true);

$col = $dg->addColumn("عنوان مرحله", "PartDesc", "");
$col->editor = ColumnEditor::TextField();
$col->sortable = false;

$col = $dg->addColumn("", "PartID");
$col->renderer = "RequestInfo.OperationRender";
$col->width = 40;

$dg->addButton("addPart", "ایجاد مرحله پرداخت", "add", "function(){RequestInfoObject.PartInfo('new');}");

$dg->HeaderMenu = false;
$dg->hideHeaders = true;

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 150;
$dg->width = 170;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "MaxAmount";
$dg->disableFooter = true;

$grid = $dg->makeGrid_returnObjects();

//......................................................

$dg = new sadaf_datagrid("dg","/loan/request/request.data.php?task=GetPartPayments", "grid_div");

$dg->addColumn("", "PayID","", true);
$dg->addColumn("", "PartID","", true);
$dg->addColumn("", "PayAmount","", true);
$dg->addColumn("", "WageAmount","", true);
$dg->addColumn("", "WagePercent","", true);
$dg->addColumn("", "PaidDate","", true);
$dg->addColumn("", "PaidAmount","", true);
$dg->addColumn("", "StatusID","", true);

$col = $dg->addColumn("تاریخ سررسید", "PayDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->sortable = false;

$col = $dg->addColumn("مبلغ خالص", "PayAmount", GridColumn::ColumnType_money);
$col->width = 80; 

$col = $dg->addColumn("مبلغ کارمزد", "WageAmount", GridColumn::ColumnType_money);
$col->width = 80;

$col = $dg->addColumn("کارمزد مشتری", "CustomerWage", "");
$col->width = 50;
$col->align = "center";

$col = $dg->addColumn("کارمزد صندوق", "FundWage", "");
$col->width = 50;
$col->align = "center";

$dg->addButton("", "محاسبه اقساط", "list", "function(){RequestInfoObject.ComputePayments();}");

$col = $dg->addColumn("شماره چک", "ChequeNo", "");
$col->editor = ColumnEditor::NumberField(true);
$col->width = 50;

$col = $dg->addColumn("تاریخ چک", "ChequeDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField(true);
$col->width = 70;

$col = $dg->addColumn("بانک", "ChequeBank", "");
$col->editor = ColumnEditor::ComboBox(PdoDataAccess::runquery("select * from ACC_banks"), 
	"BankID", "BankDesc", "", true);
$col->width = 70;

$col = $dg->addColumn("شعبه", "ChequeBranch", "");
$col->editor = ColumnEditor::TextField(true);
$col->width = 70;

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(store,record){return RequestInfoObject.SavePartPayment(store,record);}";

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
				<td><div id="SUM_TotalWage" class="blueText">&nbsp;</div></td>
			</tr>
			<tr id="TR_FundWage">
				<td style="direction:rtl;background-color: #dfe8f6;">سهم صندوق</td>
				<td><div id="SUM_FundWage" class="blueText">&nbsp;</div></td>
			</tr>
			<tr id="TR_AgentWage">
				<td style="direction:rtl;background-color: #dfe8f6;">سهم عامل</td>
				<td><div id="SUM_AgentWage" class="blueText">&nbsp;</div></td>
			</tr>
		</table></div>
		<div style="float:right"><table  style="width:200px" class="summary">
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

</center>