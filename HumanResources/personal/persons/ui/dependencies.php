<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------
require_once '../../../header.inc.php';
require_once("../data/person.data.php");
require_once inc_dataGrid;

require_once '../js/dependencies.js.php';

$dg = new sadaf_datagrid("dep",$js_prefix_address . "../data/dependent.data.php?task=selectDep&Q0=".$_POST['Q0'],"depGRID");

$colu= $dg->addColumn("شماره پرسنلی","PersonID","int","true");
$colu= $dg->addColumn("کد وابستگی","dependency","int","true");

$colu=$dg->addColumn("محل صدور شناسنامه","idcard_location","string",true);
$colu->renderer = "function(v){ return ''; }"; 
$colu=$dg->addColumn("تاریخ ازدواج","marriage_date","int",true);
$colu->renderer = "function(v){ return ''; }"; 
$colu=$dg->addColumn("تاریخ طلاق","separation_date","int",true);
$colu->renderer = "function(v){ return ''; }"; 
$colu=$dg->addColumn("شماره بیمه","insure_no","int",true);
$colu->renderer = "function(v){ return ''; }"; 
$colu=$dg->addColumn("توضیحات","comments","string",true);
$colu->renderer = "function(v){ return ''; }"; 

$colu = $dg->addColumn("ردیف","row_no", "string");
$colu->width = 30;

$colu = $dg->addColumn("نام", "fname", "string");
$colu->width = 70;

$colu = $dg->addColumn("نام خانوادگی", "lname", "string");

$colu = $dg->addColumn("وابستگی", "Title", "string");
$colu->width = 90;

$colu = $dg->addColumn("ش.ش", "idcard_no", "string");
$colu->width = 60;

$colu = $dg->addColumn("تاریخ تولد", "birth_date", GridColumn::ColumnType_date);
//$colu->editor = ColumnEditor::SHDateField();
$colu->width = 80;

$colu = $dg->addColumn("نام پدر", "father_name", "");
$colu->width = 80;

$colu = $dg->addColumn("نوع بیمه", "insure_type", "string");
$colu->width = 100;

$colu = $dg->addColumn("عملیات", "", "string");
$colu->renderer = "PersonDependency.opRender";
$colu->width = 80;

$dg->height = 400;
$dg->width = 780;
$dg->DefaultSortField = "row_no";
$dg->autoExpandColumn = "lname";
$dg->EnableSearch = false;
$dg->DefaultSortDir = "ASC";

$dg->addButton = true;
$dg->addHandler = "function(){ return PersonDependencyObject.AddDep();}";


$dg->EnablePaging = false;
$gridDep = $dg->makeGrid_returnObjects();
//______________________________________________________________________________

$dgh = new sadaf_datagrid("deph",$js_prefix_address . "../data/dependent.data.php?task=selectDepSupport","dephistoryGRID");

$dgh->addColumn("شماره پرسنلی","PersonID","int",true);
$dgh->addColumn("","master_row_no","int",true);

$col = $dgh->addColumn("ردیف","row_no", "string");
$col->width = 100;

$col = $dgh->addColumn("دلیل کفالت", "support_cause", GridColumn::ColumnType_string);
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_SupporCause(), "InfoID", "InfoDesc");
$col->width = 120;
$dgh->addColumn("", "support_cause", "", true);

$col = $dgh->addColumn("نوع بیمه", "insure_type", "string");
$col->editor = ColumnEditor::ComboBox(manage_domains::GETALL_InsureType(), "InfoID", "InfoDesc");
$dgh->addColumn("", "insure_type", "", true);

$col = $dgh->addColumn("از تاریخ", "from_date", GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField();
$col->width = 100;

$col = $dgh->addColumn("تا تاریخ", "to_date",GridColumn::ColumnType_date);
$col->editor = ColumnEditor::SHDateField(true);
$col->width = 100;

$col = $dgh->addColumn("وضعیت", "status_title", "string");
$col->width = 100;
	$col = $dgh->addColumn("حذف", "", "string");
	$col->renderer = "PersonDependency.opDelRender";
	$col->width = 100;

$dgh->addButton = true;
$dgh->addHandler = "function(v,p,r){ return PersonDependencyObject.AddDepSupport(v,p,r);}";

$dgh->title = "سوابق کفالت بستگان";
$dgh->EnableSearch = false;
$dgh->height = 300;
$dgh->width = 780;
$dgh->autoExpandColumn = "insure_type";
$dgh->EnablePaging = false ;
$dgh->DefaultSortField = "row_no";
$dgh->DefaultSortDir = "ASC";
$dgh->notRender = true ;

$dgh->enableRowEdit = true ;
$dgh->rowEditOkHandler = "function(v,p,r){ return PersonDependencyObject.SaveSupport(v,p,r);}";

$gridSupport = $dgh->makeGrid_returnObjects(); 
//-----------------------------------------
$drp_dependency = manage_domains::DRP_Dependencies("dependency", "","","width:50%");

?>
<script>
	PersonDependency.prototype.afterLoad = function()
	{
		this.grid = <?= $gridDep?>;
		this.grid.render(this.get("depGRID"));
		Ext.get(this.get("SupportCompleteInfo")).setDisplayed(false);
		this.supportGrid = <?= $gridSupport?>;
		this.PersonID = <?= $_POST['Q0']?>;
	}
	var PersonDependencyObject = new PersonDependency();
</script>
<form id="form_PersonDependency" method="post">
	<input type='hidden' id='row_no' name='row_no' >
	<div id='depDIV'  style="width: 700px;display: none;"  class="panel" >
	<table width="100%">
		<tr>
			<td width="15%">نام :</td>
			<td width="35%"><input type="text" id="dep_fname" name="fname" class="x-form-text x-form-field" style="width: 90%" ></td>
			<td width="15%">نام خانوادگی:</td>
			<td width="35%"><input type="text" id="dep_lname" name="lname" class="x-form-text x-form-field" style="width: 90%" ></td>
		</tr>
		<tr>
			<td>نام پدر:</td>
			<td><input type="text" id="dep_father_name" name="father_name" class="x-form-text x-form-field" style="width: 90%" ></td>
			<td>نوع وابستگی:</td>
			<td><?= $drp_dependency ?></td>
		</tr>
		<tr>
			<td>شماره شناسنامه:</td>
			<td><input type="text" id="dep_idcard_no" name="idcard_no" class="x-form-text x-form-field" style="width: 90%" ></td>
			<td>محل صدور شناسنامه:</td>
			<td><input type="text" id="dep_idcard_location" name="idcard_location" class="x-form-text x-form-field" style="width: 90%" ></td>
		</tr>
		<tr>
			<td>تاریخ تولد:</td>
			<td><input type="text" id="dep_birth_date" name="birth_date" class="x-form-text x-form-field"  style="width: 100px"></td>
			<td>تاریخ ازدواج:</td>
			<td><input type="text" id="dep_marriage_date" name="marriage_date" class="x-form-text x-form-field"  style="width: 100px"></td>
		</tr>
		<tr>
			<td>تاریخ فوت یا طلاق:</td>
			<td><input type="text" id="dep_separation_date" name="separation_date" class="x-form-text x-form-field"   style="width: 100px"></td>
			<td>شماره بیمه:	</td>
			<td><input type="text" id="insure_no" name="insure_no" class="x-form-text x-form-field" style="width: 90%" ></td>
		</tr>
		<tr>
			<td>توضیحات:</td>
			<td colspan="3"><textarea id="comments" name="comments" rows="5" style="width:96%" class=" x-form-field"></textarea></td>
		</tr>
		<tr>
			<td colspan="4"><hr></td>
		</tr>
		<tr>
			<td></td>
			<td colspan="3">
				<input type="button" class="button" onclick="PersonDependencyObject.saveDep();" value="ذخیره">
				<input type="button" id="btn_cancel" class="button" onclick="PersonDependencyObject.DepCancle();" value="بازگشت">
			</td>
		</tr>
	</table>
	</div><br>
	<div id="SupportCompleteInfo">
		<table width="55%" >
		<tr>
			<td width="35%" >&nbsp;</td>
			<td>
				<a style="color:#15428B;font-size: 11px;font-weight: bold;" href="#" onclick="PersonDependencyObject.SupportDependencyInfo();">
				لیست کامل سابقه کفالت فرد
				</a>
			</td>
			<td>&nbsp;</td>
		</tr>
		</table>
	</div><br>
	<div id="ShowInfoWindow" class="x-hidden"></div>
	<div id="dephistoryGRID"></div>
	<div id="depGRID"></div>
</form>