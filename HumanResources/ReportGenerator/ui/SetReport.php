<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.04
//---------------------------
require_once '../../header.inc.php';
require_once '../data/report.data.php';

function BindDropDown($dropdownName, $type)
{
	$obj = new DROPDOWN();
        $ptWhr = "" ;
        if($type == 16)
           $ptWhr = " AND infoID in(" . manage_access::getValidPersonTypes() . ")"; 
        
	$obj->datasource = PdoDataAccess::runquery("select * from Basic_Info where TypeID=" . $type .$ptWhr.
		" order by Title");
        
	$obj->valuefield = "%InfoID%";
	$obj->textfield = "%Title%";
	$obj->Style = 'style=\'width:98%\' class=\'x-form-text\'';
	$obj->id = $dropdownName;

	$obj->datasource = array_merge(array(array("InfoID" => "-1", "Title" => "---")),$obj->datasource);
	return $obj->bind_dropdown();
}

function BindDropDown2($dropdownName, $tableName, $keyField, $valueField)
{
	$obj = new DROPDOWN();

	$obj->datasource = PdoDataAccess::runquery("select * from " . $tableName);
	$obj->valuefield = "%" . $keyField . "%";
	$obj->textfield = "%" . $valueField . "%";
	$obj->Style = 'style=\'width:98%\' class=\'x-form-text\'';
	$obj->id = $dropdownName;

	$obj->datasource = array_merge(array(array($keyField => "-1", $valueField => "---")),$obj->datasource);
	return $obj->bind_dropdown();
}

function BindCheckList($checkboxPrefix, $basicTypeId)
{
	$obj = new CHECKBOXLIST();

	$obj->datasource = PdoDataAccess::runquery("select * from Basic_Info where TypeID=" . $basicTypeId);
	$obj->idfield = $checkboxPrefix . "%InfoID%";
	$obj->valuefield = $checkboxPrefix . "%InfoID%";
	$obj->textfield = "%Title%";
	$obj->columnCount = 4;
	$obj->Allchecked = true;
	$obj->EnableCheckAllButton = true;

	return $obj->bind_checkboxlist();
}

if(empty($_POST["Q0"]))
	die();
$report_id = $_POST["Q0"];
$obj = new rp_reports($report_id);
$report_name = $obj->report_title;

$temp = PdoDataAccess::runquery("
	select distinct c.column_id,
					if(rc.field_title<>'', rc.field_title, c.col_name) as col_name,
					c.search_mode,
					basic_info_table,
					basic_type_id,
					check_value,
					c.field_name,
					c.lov,
					t.*
	from rp_report_columns rc 
		join rp_columns c using(column_id)
		left join rp_tables t on(t.table_id=basic_info_table)
	where rc.used_type='filter' AND report_id=? order by rc.row_id ", array($report_id));

$odd = true;
$triggerComponents = "";
$output = "<tr>";
for($i=0; $i<count($temp); $i++)
{
	if($temp[$i] == "")
	{
		$output .= "<td>&nbsp;</td>";
		continue;
	}
	$id = $temp[$i]["column_id"];
	switch ($temp[$i]["search_mode"])
	{
		case "INT":
			$output .= "<td>" . $temp[$i]["col_name"] . " از:</td>";
			$output .= "<td><input class='x-form-text x-form-field' type='text' style='width:107px'
					name='FINT:" . $id . "' id='FINT:" . $id . "'></td><td style='width:15'> تا </td><td>
				<input class='x-form-text x-form-field' type='text' style='width:102px'
					name='TINT:" . $id . "' id='TINT:" . $id . "'></td>";

			if($temp[$i]["lov"] != "")
				$triggerComponents .= "
					new Ext.form.TriggerField({
						triggerCls:'x-form-search-trigger',
						onTriggerClick : function(){
							var returnVal = " . $temp[$i]["lov"] . "();
							this.setValue(returnVal" . ($temp[$i]["lov"] == "LOV_Post" ? "." . $temp[$i]["field_name"] : "") . ");
						},
						applyTo :  this.get('FINT:" . $id . "'),
						width : 100
					});
					new Ext.form.TriggerField({
						triggerCls:'x-form-search-trigger',
						onTriggerClick : function(){
							var returnVal = " . $temp[$i]["lov"] . "();
							this.setValue(returnVal" . ($temp[$i]["lov"] == "LOV_Post" ? "." . $temp[$i]["field_name"] : "") . ");
						},
						applyTo : this.get('TINT:" . $id . "'),
						width : 100
					});";

			break;
		
		case "TEXT":
			$output .= "<td>" . $temp[$i]["col_name"] . ":</td>";
			$output .= "<td colspan='3'><input class='x-form-text x-form-field' type='text'
				style='width:98%' name='TEXT:" . $id . "'></td>";
			break;
			
		case "DATE":
			$output .= "<td>" . $temp[$i]["col_name"] . " از:</td>";
			$output .= "<td><input type='text' id='FDATE:" . $id . "' name='FDATE:" . $id . "'></td><td> تا </td><td>
				<input type='text' id='TDATE:" . $id . "' name='TDATE:" . $id . "'>
				<script>new Ext.form.SHDateField({applyTo: SetReportObject.get('FDATE:" . $id . "'),width : 100,format: 'Y/m/d'});
				new Ext.form.SHDateField({applyTo: SetReportObject.get('TDATE:" . $id . "'),width : 100,format: 'Y/m/d'});</script>
				</td>";
			break;
			
		case "SELECT":
			$output .= "<td>" . $temp[$i]["col_name"] . ":</td>";
			if($temp[$i]["lov"] != "")
			{
				$output .= "<td colspan='3'><input type='text' name='SELECT:" . $temp[$i]["column_id"] . "' id='SELECT:" . $temp[$i]["column_id"] . "'></td>";
				$triggerComponents .= "
					new Ext.form.TriggerField({
						triggerCls:'x-form-search-trigger',
						onTriggerClick : function(){
							var returnVal = " . $temp[$i]["lov"] . "();
							this.setValue(returnVal" . ($temp[$i]["lov"] == "LOV_Post" ? "." . $temp[$i]["column_id"] : "") . ");
						},
						applyTo :  this.get('SELECT:" . $temp[$i]["column_id"] . "'),
						width : 90
					});";
			}
			else
			{
				if($temp[$i]["basic_type_id"] != "" && $temp[$i]["basic_type_id"] != 0)
				{
					$output .= "<td colspan='3'>" . BindDropDown("SELECT:" . $temp[$i]["column_id"], $temp[$i]["basic_type_id"]) . "</td>";
				}
				else if($temp[$i]["basic_info_table"] != "" && $temp[$i]["basic_info_table"] != 0)
				{
					$output .= "<td colspan='3'>" . BindDropDown2("SELECT:" . $temp[$i]["column_id"],
						$temp[$i]["table_name"], $temp[$i]["key_field"], $temp[$i]["value_field"]) . "</td>";
				}
				else
				{
					$output .= "<td colspan='3'>&nbsp;</td>";
				}
			}
			break;

		case "CHECKLIST" :
			if($temp[$i]["basic_type_id"] != "" && $temp[$i]["basic_type_id"] != 0)
			{
				if(!$odd)
				{
					/*$tmp = isset($temp[$i+1]) ? $temp[$i+1] : "";
					$temp[$i+1] = $temp[$i];
					$temp[$i] = $tmp;
					$i--;
					continue;*/
					$output .= "<td colspan=5>&nbsp;</td></tr><tr>";
					$odd = true;
				}

				$output .= "<td colspan='9' style='padding:5px' align=center>
								<fieldset class='x-fieldset x-fieldset-default' style='border-width:1px 1px 1px 1px;width:98%'>
									<legend class='x-fieldset-header x-fieldset-header-default'>" . $temp[$i]["col_name"] . ":</legend>" .
										BindCheckList("CHECKLIST:" . $temp[$i]["field_name"] . ":", $temp[$i]["basic_type_id"], "") .
								"</fieldset>
							</td>";
				$odd = false;
			}
			else
			{
				$output .= "<td colspan='4'>&nbsp;</td>";
			}
			break;
		
		case "CHECK" :
			$output .= "<td>" . $temp[$i]["col_name"] . ":</td>";
			$output .= "<td colspan='3'>
				<input value='" . $temp[$i]["check_value"] . "'	type='radio' name='CHECK:" . $id . "'>&nbsp;بلی
				<input value='0' type='radio' name='CHECK:" . $id . "'>&nbsp;خیر
				<input value='-1' type='radio' name='CHECK:" . $id . "' checked>&nbsp;همه
			</td>";
			break;
	}
	if(!$odd)
	{
		$output .= "</tr><tr>";
		$odd = true;
	}
	else 
	{
		$output .= "<td>&nbsp;&nbsp;</td>";
		$odd = false;
	}
}

require_once '../js/SetReport.js.php';
?>
<link rel=stylesheet href='/HumanResources/css/reportGenerator.css'>
<center>
<form id="form_SetReport">
	<div id="SetReportDIV" style="width: 100%;">
		<div id="SetReportPNL" style="width: 100%;">
			<table style="width: 100%;">
				<tr>
					<td style="width:15%">&nbsp;</td>
					<td style="width:34%" colspan="3">&nbsp;</td>
					<td style="width:2%" align="center">&nbsp;&nbsp;</td>
					<td style="width:15%">&nbsp;</td>
					<td style="width:34%" colspan="3">&nbsp;</td>
				</tr>
				<?= $output ?>
			</table>
		</div>
	</div>

	<div id="result" style="display:none;border: 2px groove #99BBE8;height: 580px;overflow-y: scroll;padding-top:5px;padding-bottom:5px"></div>

</form>

</center>