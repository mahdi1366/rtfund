<?php
//---------------------------
// programmer:	SH.Jafarkhani
// create Date:	90.06
//---------------------------
require_once("../../../header.inc.php");
require_once inc_dataGrid;

$dg = new sadaf_datagrid("dg",$js_prefix_address . "../data/exe_posts.data.php?task=selectAll","divGRID","form_exePosts");

$col = $dg->addColumn("", "assign_post", "");
$col->renderer = "function(v,p,r){return ExePostsObject.asignRender(v,p,r);}";
$col->width = 40;

$col= $dg->addColumn("ردیف","row_no","int");
$col->width = 40;

$col= $dg->addColumn("شماره پست","post_id","int");
$col->width = 80;

$col= $dg->addColumn("عنوان پست","postTitle","int");

$col= $dg->addColumn("شماره نامه","letter_no","int");
$col->width = 100;

$col= $dg->addColumn("تاریخ نامه","letter_date",GridColumn::ColumnType_date);
$col->width = 80;

$col= $dg->addColumn("از تاریخ","from_date",GridColumn::ColumnType_date);
$col->width = 80;

$col= $dg->addColumn("تا تاریخ","to_date",GridColumn::ColumnType_date);
$col->width = 80;

$col = $dg->addColumn("شرح", "description", "string");
$col->width = 100;
$dg->addColumn("", "staff_id", "string", true);
$dg->addColumn("", "post_no", "string", true);

$dg->width = 950;
$dg->DefaultSortField = "row_no";
$dg->DefaultSortDir = "ASC";

$col = $dg->addColumn("عملیات", "");
$col->renderer = "function(v,p,r){return ExePostsObject.DeleteRender(v,p,r);}";
$col->width = 50;

$dg->addButton = true;
$dg->addHandler = "function(){ExePostsObject.Add();}";

$dg->EnableSearch = false;
$dg->autoExpandColumn = "postTitle";
$grid = $dg->makeGrid_returnObjects();


require_once '../js/exe_posts.js.php';
?>
<form method="post" id="form_exePosts">
	<center>
		<br>
        <div>
		<div id="exe_DIV" style="width: 750px">
			<table id="exe_TBL" style="width: 100%">
				<tr>
					<td width="10%" >انتخاب فرد :</td>
					<td width="90%" colspan="3"><br>
						<input type="hidden" name="staff_id" id="staff_id">
					</td>
				</tr>
				<tr>
					<td colspan="4" align="center"><br>
						<input type="button" class="big_button" value="بارگزاری اطلاعات" onclick="ExePostsObject.LoadInfo();">
					</td>
				</tr>
			</table>
		</div><br>
		<div id="new_exe_post" align="right">
			<table id="pnl_exe_post" width="80%">
			<tr>
				<td><font color="red">*</font>انتخاب پست</td>
				<td><input type="text" id="post_id" name="post_id"><input type="hidden" id="row_no" name="row_no"></td>
				<td class="blueText" colspan="2" id="post_title"></td>
			</tr>
			<tr>
				<td width="15%">شماره نامه :</td>
				<td width="35%"><input type="text" id="letter_no" name="letter_no" class="x-form-text x-form-field"></td>
				<td width="15%"><font color="red">*</font>تاریخ نامه :</td>
				<td><input type="text" id="letter_date" name="letter_date" class="x-form-text x-form-field"></td>
			</tr>
			<tr>
				<td><font color="red">*</font>از تاریخ :</td>
				<td><input type="text" id="from_date" name="from_date" class="x-form-text x-form-field"></td>
				<td>تا تاریخ :</td>
				<td><input type="text" id="to_date" name="to_date" class="x-form-text x-form-field"></td>
			</tr>
			<tr>
				<td>شرح :</td>
				<td colspan="3"><input style="width:90%" type="text" id="description" name="description"
					class="x-form-text x-form-field"></td>
			</tr>
			<tr>
				<td colspan="4"><input type="checkbox" id="assign_post" name="assign_post">
					پست سازماني به فرد واگذار شده است<br>
					<font color="green">(پست سازماني از طريق انتخاب اين گزينه به فرد واگذار مي شود و يا از فرد گرفته مي شود)</font></td>
			</tr>
			<br><br>
			</table>
		</div></div>
        <div>
            <div id="divGRID"></div>
        </div>
	</center>
</form>


