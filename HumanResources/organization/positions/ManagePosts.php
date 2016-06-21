<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.12
//---------------------------
require_once '../header.inc.php';
require_once 'post.data.php';
require_once inc_dataGrid;


jsConfig::initialExt();
jsConfig::grid();
jsConfig::date();
require_once 'ManagePosts.js.php';

$dg = new sadaf_datagrid("dg", $js_prefix_address . "post.data.php?task=selectPost", "div_grid","mainForm");

$col = $dg->addColumn("شماره شناسایی پست", "post_id");
$col->width = 60;

$col = $dg->addColumn("شماره پست", "post_no");
$col->width = 40;
$dg->addColumn("عنوان پست", "title");
$dg->addColumn("نوع پست", "post_type_title");

$col = $dg->addColumn("واحد سازمانی", "unitTitle");
$col = $dg->addColumn("عنوان کامل واحد سازمانی", "full_unit_title");
$col = $dg->addColumn("رسته", "jobCategory");


$dg->addColumn("","parent_path","",true);

//.....................................................
$dg->addButton = true;
$dg->addHandler = "AddPost";

$col = $dg->addColumn("ویرایش", "");
$col->renderer = "GridEdit";
$col->width = 30;

$col = $dg->addColumn("حذف", "");
$col->renderer = "GridRemove";
$col->width = 30;


$col = $dg->addColumn("زیر پست ها", "");
$col->renderer = "subPostRender";
$col->width = 30;

$dg->addButton("back", "برگشت", "undo", "back");
$dg->title = "پست های سازمانی";
$dg->DefaultSortField = "post_id";
$dg->primaryKey = "post_id";
$dg->width = 900;
$dg->height = 500;
$dg->makeGrid();

?>
<body dir="rtl">
	<form id="mainForm">
	<input type="hidden" id="CurrentPost" name="CurrentPost">
	</form>
	<table align="center" width="750px">
		<tr>
			<td valign="top" style="padding-right: 5px" align="center">
				<!-- -------------------------------------------- -->
				<div align="right" id="DIV_NewPost" class="x-hide-display">
					<div id="PNL_NewPost">
					</div>
				</div>
				<!-- -------------------------------------------- -->
			</td>
		</tr>
		<tr>
			<td width="40%" align="center">
				<div align="right" id="div_grid"></div>
				<!--<div align="right" id="tree-div" style="overflow:auto; width:350px;border:1px solid #c3daf9;"></div>-->
			</td>
		</tr>
	</table>
</body>

