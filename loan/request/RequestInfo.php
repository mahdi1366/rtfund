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
$dg->addColumn("", "StatusID","", true);
$dg->addColumn("", "PartDate","", true);
$dg->addColumn("", "PartAmount","", true);
$dg->addColumn("", "InstallmentCount","", true);
$dg->addColumn("", "IntervalType","", true);
$dg->addColumn("", "PayInterval","", true);
$dg->addColumn("", "DelayMonths","", true);
$dg->addColumn("", "ForfeitPercent","", true);
$dg->addColumn("", "CustomerWage","", true);
$dg->addColumn("", "FundWage","", true);
$dg->addColumn("", "IsPayed","", true);
$dg->addColumn("", "IsStarted","", true);
$dg->addColumn("", "IsEnded","", true);
$dg->addColumn("", "DocStatus","", true);
$dg->addColumn("", "IsPartEnded","", true);
$dg->addColumn("", "WageReturn","", true);
$dg->addColumn("", "imp_VamCode","", true);

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

$dg = new sadaf_datagrid("dg","/loan/request/request.data.php?task=GetPartInstallments", "grid_div");

$dg->addColumn("", "InstallmentID","", true);
$dg->addColumn("", "PartID","", true);
$dg->addColumn("", "InstallmentAmount","", true);
$dg->addColumn("", "WageAmount","", true);
$dg->addColumn("", "WagePercent","", true);
$dg->addColumn("", "IsPaid","", true);
$dg->addColumn("", "IsPayed","", true);
$dg->addColumn("", "PaidDate","", true);
$dg->addColumn("", "PaidAmount","", true);
$dg->addColumn("", "PaidTypeDesc","", true);
$dg->addColumn("", "PaidBillNo", "", true);

$col = $dg->addColumn("سررسید", "InstallmentDate", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 80; 

$col = $dg->addColumn("شناسه واریز", "");
$col->renderer = "function(v,p,r){ return RequestInfo.PayCodeRender(v,p,r);}";
$col->width = 100;

$col = $dg->addColumn("مبلغ", "InstallmentAmount", GridColumn::ColumnType_money);
$col->width = 100; 

$col = $dg->addColumn("مبلغ جریمه", "ForfeitAmount", GridColumn::ColumnType_money);
$col->width = 80;

$col = $dg->addColumn("شماره چک", "ChequeNo", "");
$col->editor = ColumnEditor::NumberField(true);
$col->width = 70;

$col = $dg->addColumn("بانک", "ChequeBank", "");
$col->editor = ColumnEditor::ComboBox(PdoDataAccess::runquery("select * from ACC_banks"), 
	"BankID", "BankDesc", "", "", true);
$col->width = 70;

$col = $dg->addColumn("شعبه", "ChequeBranch", "");
$col->editor = ColumnEditor::TextField(true);
$col->width = 90;

$col = $dg->addColumn("اطلاعات پرداخت", "PaidRefNo", "");
$col->renderer = "function(v,p,r){ return RequestInfo.InstallmentPaidInfo(v,p,r);}";

$dg->addButton("cmp_computeInstallment", "محاسبه اقساط", "list", "function(){RequestInfoObject.ComputeInstallments();}");

$dg->enableRowEdit = true;
$dg->rowEditOkHandler = "function(store,record){return RequestInfoObject.SavePartPayment(store,record);}";

$dg->emptyTextOfHiddenColumns = true;
$dg->height = 460;
$dg->width = 730;
$dg->EnableSearch = false;
$dg->EnablePaging = false;
$dg->DefaultSortField = "InstallmentDate";
$dg->autoExpandColumn = "PaidRefNo";
$dg->DefaultSortDir = "ASC";
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
		<div style="float:right"><table style="width:180px" class="summary">
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
		<div style="float:right"><table  style="width:170px" class="summary">
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