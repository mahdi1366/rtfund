<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.04
//---------------------------
require_once '../../header.inc.php';
require_once '../class/report.class.php';
require_once inc_dataGrid;

if(empty($_POST["Q0"]))
{
	$obj = new rp_reports();
	$obj->Add();
	$obj->report_id = PdoDataAccess::InsertID();
	$report_id = $obj->report_id;
}
else
	$report_id = $_POST["Q0"];

$summaryNames = array("sum" => "مجموع",
						"count" => "تعداد",
						"avg" => "میانگین",
						"max" => "ماکزیمم",
						"min" => "مینیمم");


$obj = new rp_reports($report_id);

//--------------------- columns grid ----------------------
$dg = new sadaf_datagrid("col_dg", $js_prefix_address . "../data/report.data.php?task=selectReportColumns&Q0=" . $report_id, "columns_grid");

$col = $dg->addColumn("نوع ستون", "used_type");
$col->width = 100;

$col = $dg->addColumn("عنوان", "title");

$col = $dg->addColumn('بالا', "", "string");
$col->renderer = "function(v,p,r){return buildReportObject.UPRender(v,p,r);}";
$col->sortable = false;
$col->width = 50;

$col = $dg->addColumn('پایین', "", "string");
$col->renderer = "function(v,p,r){return buildReportObject.DOWNRender(v,p,r);}";
$col->sortable = false;
$col->width = 50;

$col = $dg->addColumn('حذف', "row_id", "string");
$col->renderer = "function(v,p,r){return buildReportObject.deleteRender(v,p,r);}";
$col->sortable = false;
$col->width = 50;

$col = $dg->addColumn("", "row_id", "", true);
$col = $dg->addColumn("", "parent_path", "", true);

$dg->width = 650;
$dg->height = 200;
$dg->EnablePaging = false;
$dg->EnableSearch = false;
$dg->title = "ستون های گزارش";
$dg->autoExpandColumn = "title";
$grid = $dg->makeGrid_returnObjects();
//------------------------------------------------------------------------------
$temp = PdoDataAccess::runquery("
	select * from rp_report_columns rc join rp_columns c using(column_id)
	where report_id=" . $report_id . " order by row_id");

$groupOutput = "";
$orderOutput = "";
$separationOutput = "";
$filterOutput = "";
$conditionOutput = $obj->conditions;

$index = 0;
$conditionJsArray = "new Array(";

for($i=0; $i<count($temp); $i++)
{
	switch($temp[$i]["used_type"])
	{
		case "group":
			$groupOutput .= "<option value='" . $temp[$i]["parent_path"] . "'>" . $temp[$i]["col_name"] . "</option>";
			break;
		case "order":
			$orderOutput .= "<option value='" . $temp[$i]["parent_path"] . "'>" . $temp[$i]["col_name"] . "</option>";
			break;
		case "separation":
			$separationOutput .= "<option value='" . $temp[$i]["parent_path"] . "'>" . $temp[$i]["col_name"] . "</option>";
			break;
		case "filter":
			$filterOutput .= "<option value='" . $temp[$i]["parent_path"] . "'>" . $temp[$i]["col_name"] . "</option>";
			break;
		case "condition":
			$conditionOutput = str_replace("[" . $temp[$i]["parent_path"] . "]", "[" . $index . "-" . $temp[$i]["col_name"] . "]", $conditionOutput);
			$conditionJsArray .= "new Array('[" . $index . "-" . $temp[$i]["col_name"] . "]', '[" . $temp[$i]["parent_path"] . "]'),";
			$index++;
			break;
	}
}
$conditionJsArray = ($conditionJsArray == "new Array(") ? $conditionJsArray . ")" :
														  substr($conditionJsArray, 0, strlen($conditionJsArray)-1) . ")";
//----------------------------------------------------------
$buttonName = "تغییر گزارش";


require_once '../js/buildReport.js.php';
?>
<form id="form_buildReport">
	<input type="hidden" id="report_id" name="report_id" value="<?= $obj->report_id ?>">
	<table style="margin: 5px">
		<tr>
			<td valign="top">
				<input type="text" class="x-form-field x-form-text" id="report_title" name="report_title"
					   style="width:400px" value="<?= $obj->report_title ?>">
				
			</td>
			<td style='width:5px'>&nbsp;</td>
			<td>
				<span style='float:right'>صفحه مرجع گزارش : </span>
				<input type="refer_page" name="refer_page" style="width:330px;"
					value="<?= $obj->refer_page ?>" class="x-form-text x-form-field">
				<input type="button" class="big_button" value="<?= $buttonName ?>" onclick="buildReportObject.CreateReport();">
				<input type="button" class="big_button" value="مشاهده گزارش" onclick="buildReportObject.preview();">
			</td>
		</tr>
		<tr>
			<td valign="top">
				<div id="tree-div"></div>
			</td>
			<td style='width:5px'>&nbsp;</td>
			<td>
				<table style="width:100%">
					<tr>
						<td colspan="4"><div id="columns_grid"></div></td>
					</tr>
					<tr>
						<td colspan="4">
							<br><div  id="reportMakeFormulaDIV">
								<div id="reportMakeFormulaPNL" style="width: 100%;" align="center">
									<span style='float:right'>عنوان فیلد  :</span>
									<input type="text" class="x-form-text x-form-field" id="mf_title" style="float:left;width: 80%"><br>
									<input class="x-form-text x-form-field" style="width: 100%;direction:ltr" type="text" id="txt_formula">
										<div style="float:right" align="right"><input type="checkbox" id="base_evaluate"
											 name="base_evaluate"> محاسبه فرمول بر اساس مقادیر اصلی فیلدها</div>
										<div style="float:left" align="left"><input class="big_button" type="button" name="btn_add"
											onclick="return buildReportObject.addFormula();" value="اضافه به ليست"></div>
									<input type="hidden" id="hdn_temp">
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="4">
							<div id="reportWhereDIV">
								<div id="reportWherePNL" style="width: 100%;" align="center">
									<table style="width: 98%">
										<tr>
											<td><textarea class="x-form-field" style="direction:ltr;text-align:left;width: 99%;"
												type="text" id="txt_condition" rows="5"><?= stripcslashes($conditionOutput) ?></textarea>
											<span>در صورتی که می خواهید از پارامتر های 
											REQUEST_$
											 استفاده کنید کافیست نام پارامتر را در ## قرار دهید .
											به عنوان مثال #ouid#
											</span>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td valign="top" width="25%"><br><div id="reportGroupsDIV">
								<div id="reportGroupsPNL">
									<select multiple="multiple" id="lst_reportGroups" onkeypress="buildReportObject.deleteItem(event,this);"
											class="x-form-field x-form-text" style="width: 100%; height: 200px">
										<?= $groupOutput ?>
									</select>
								</div>
							</div>
						</td>
						<!-- ------------------------------------------------------------------------------ -->
						<td valign="top" width="25%" style="padding-right: 5px"><br><div id="reportOrderDIV">
								<div id="reportOrderPNL">
									<select multiple="multiple" id="lst_reportOrder" onkeypress="buildReportObject.deleteItem(event,this);"
											class="x-form-field x-form-text" style="width: 100%; height: 200px">
										<?= $orderOutput ?>
									</select>
								</div>
							</div>
						</td>
						<!-- ------------------------------------------------------------------------------ -->
						<td valign="top" width="25%" style="padding-right: 5px"><br><div id="reportSeparationDIV">
								<div id="reportSeparationPNL">
									<select multiple="multiple" id="lst_reportSeparation" onkeypress="buildReportObject.deleteItem(event,this);"
											class="x-form-field x-form-text" style="width: 100%; height: 200px">
										<?= $separationOutput ?>
									</select>
								</div>
							</div>
						</td>
						<!-- ------------------------------------------------------------------------------ -->
						<td valign="top" width="25%" style="padding-right: 5px"><br><div id="reportFilterDIV">
								<div id="reportFilterPNL">
									<select multiple="multiple" id="lst_reportFilter" onkeypress="buildReportObject.deleteItem(event,this);"
											class="x-form-field x-form-text" style="width: 100%; height: 200px">
										<?= $filterOutput ?>
									</select>
								</div>
							</div>
						</td>
						<!-- ------------------------------------------------------------------------------ -->
					</tr>
				</table>	
			</td>
		</tr>
	</table>
</form>