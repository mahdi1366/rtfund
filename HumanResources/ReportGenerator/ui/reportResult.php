<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.04
//---------------------------

require_once '../../header.inc.php';
require_once '../data/report.data.php';
require_once inc_QueryHelper;
require_once inc_manage_unit;

$rowColors = array("3F5F96","5886D6","8DACE3","B3CEFC","B3CEFC","B3CEFC");

$report_id = $_REQUEST["Q0"];
/*if($_SESSION['UserID'] != 'delkalaleh' && $_SESSION['UserID'] != 'jafarkhani' &&  $_SESSION['UserID'] != 'm-hakimi' ) {
	
	echo " این گزارش بطور موقت قابل دسترس نمی باشد." ;  
	die() ; 
}*/
$rptobj = new rp_reports($report_id);

$columns = PdoDataAccess::runquery("select *
		from rp_report_columns rc 
			left join rp_columns c using(column_id)
			left join rp_base_tables bt on(bt.table_id=c.table_name)
		where report_id=?
		order by row_id", array($report_id));

$QUERY_SELECT = "select ";
$QUERY_GROUP = "";
$QUERY_ORDER = "";
$seprationOrder = "";
$QUERY_WHERE = stripslashes($rptobj->conditions);

$header = array("ردیف");
$columnCount = 1;

$separationRows = array();
$currentSeparation = array();

$QUERY_WHERE = $QUERY_WHERE != "" ? $QUERY_WHERE : "1=1";
$whereParam = array();
$formula_columns = array();
$keys = array();

for($i=0; $i < count($columns) ; $i++)
{
	$field = $columns[$i]["field"];
	$base_field = $columns[$i]["base_field"];
	$columns[$i]["col_name"] = $columns[$i]["col_name"] == "" ? $columns[$i]["field_title"] : $columns[$i]["col_name"];

	if($field != $base_field)
	{
		$keys[$columns[$i]["row_id"] . "2"] = $base_field;
		$QUERY_SELECT .= $base_field . " as '" . $columns[$i]["row_id"] . "2',";
	}
	switch($columns[$i]["used_type"])
	{
		case "basic" :
			$keys[$columns[$i]["row_id"]] = $field . "-" . $base_field;
			
			if($columns[$i]["search_mode"] == "CHECK")
			{
				$QUERY_SELECT .= "if($field = '" . $columns[$i]["check_value"] . "','" . $columns[$i]["check_text"] . "','')
					as '" . $columns[$i]["row_id"] . "',";
				
				$keys[$columns[$i]["row_id"] . "2"] = $base_field;
				$QUERY_SELECT .= $base_field . " as '" . $columns[$i]["row_id"] . "2',";
			}
			else
				$QUERY_SELECT .= $field . " as '" . $columns[$i]["row_id"] . "',";
			
			$header[] = $columns[$i]["col_name"];
			$columnCount++;
			break;
		
		case "separation" :
			$keys[$columns[$i]["row_id"]] = $columns[$i]["table_name"] . "." . $columns[$i]["field_name"];
			$QUERY_SELECT .= $field . " as '" . $columns[$i]["row_id"] . "',";
			$seprationOrder .= $field . ",";
			$separationRows[] = $columns[$i];
			$separationRows[count($separationRows)-1]["renderer"] = $columns[$i]["renderer"];
			$currentSeparation[count($separationRows)-1] = "";
			
			break;

		case "summary" :
			$keys[$columns[$i]["row_id"]] = $columns[$i]["table_name"] . "." . $columns[$i]["field_name"];
			
			$QUERY_SELECT .= $columns[$i]["summary_type"] . "(" . $base_field . ") as '" . $columns[$i]["row_id"] . "',";
			$header[] = $SUMMARY_NAMES[$columns[$i]["summary_type"]] . " " . $columns[$i]["col_name"];
			$columnCount++;
			break;

		case "group" :
			$QUERY_GROUP .= $base_field . ",";
			break;

		case "order" :
			$QUERY_ORDER .= $field . ",";
			break;

		case "formula":
			$keys[$columns[$i]["row_id"]] = $columns[$i]["col_name"];
			$columns[$i]["parent_path"] = str_replace("[", "[" . $columns[$i]["base_evaluate"] . ":", $columns[$i]["parent_path"]);
			$QUERY_SELECT .= stripcslashes($columns[$i]["parent_path"]) . " as '" . $columns[$i]["row_id"] . "',";
			$header[] = $columns[$i]["col_name"];
			$columnCount++;
			break;

		case "formula_column":
			$formula_columns[] = array($columns[$i]["parent_path"], $field, $base_field);
			break;

		case "condition":
			$QUERY_WHERE = str_replace("[" . $columns[$i]["parent_path"] . "]", $base_field, $QUERY_WHERE);
			break;
	}
	
	//--------------------------------------------------------------------------
	$QUERY_WHERE = str_replace("CostFn", manage_access::getValidCostCenters(), $QUERY_WHERE);
	$QUERY_WHERE = str_replace("PersonFn", manage_access::getValidPersonTypes(), $QUERY_WHERE);
	//--------------------------------------------------------------------------
	
	$column_id = $columns[$i]["column_id"];
	$field_name = $field;
	//---------------------- make where of set params ------------------------------
	if($columns[$i]["used_type"] != "filter")
		continue;
	switch($columns[$i]["search_mode"])
	{
		case "INT":
			if(!empty($_POST["FINT:" . $column_id]))
			{
				$QUERY_WHERE .= " AND " . $field_name . ">= :f" . $column_id;
				$whereParam[":f" . $column_id] = $_POST["FINT:" . $column_id];
			}
			if(!empty($_POST["TINT:" . $column_id]))
			{
				$QUERY_WHERE .= " AND " . $field_name . "<= :t" . $column_id;
				$whereParam[":t" . $column_id] = $_POST["TINT:" . $column_id];
			}
			break;

		case "TEXT":
			if(!empty($_POST["TEXT:" . $column_id]))
			{
				$QUERY_WHERE .= " AND " . $field_name . " LIKE :t" . $column_id;
				$whereParam[":t" . $column_id] = $_POST["TEXT:" . $column_id];
			}
			break;

		case "DATE":
			if(!empty($_POST["FDATE:" . $column_id]))
			{
				$QUERY_WHERE .= " AND " . $field_name . ">= :f" . $column_id;
				$whereParam[":f" . $column_id] = DateModules::Shamsi_to_Miladi($_POST["FDATE:" . $column_id]);
			}
			if(!empty($_POST["TDATE:" . $column_id]))
			{
				$QUERY_WHERE .= " AND " . $field_name . "<= :t" . $column_id;
				$whereParam[":t" . $column_id] = DateModules::Shamsi_to_Miladi($_POST["TDATE:" . $column_id]);
			}
			break;

		case "SELECT":
			if(isset($_POST["SELECT:" . $column_id]) && $_POST["SELECT:" . $column_id] != -1)
			{
				if(strpos($base_field, "ouid") !== false)
				{
					 preg_match_all('/tbl[0-9]*/', $columns[$i]["field"], $prefix);
                    $prefix = $prefix[0][(count($prefix[0])-1)];
					$org = QueryHelper::MK_org_units($_POST["SELECT:" . $columns[$i]["column_id"]], true, $prefix);
					if($org["where"] != "")
					{
						$QUERY_WHERE .= " AND " . $org["where"];
						$whereParam = array_merge($whereParam, $org["param"]);
					}
				}
				else
				{
					$QUERY_WHERE .= " AND " . $base_field . " = :f" . $i;
					$whereParam[":f" . $i] = $_POST["SELECT:" . $column_id];
				}
			}
			break;

		case "CHECK" :
			if($_POST["CHECK:" . $column_id] != "-1")
			{
				$QUERY_WHERE .= " AND " . $field_name . " = :t" . $column_id;
				$whereParam[":t" . $column_id] = $_POST["CHECK:" . $column_id];
			}
			break;

		case "CHECKLIST" :
			$st = QueryHelper::makeWhereOfCheckboxList($base_field, "CHECKLIST:" . $columns[$i]["field_name"] . ":");
			$QUERY_WHERE .= $st != "" ? " AND " . $st : "";
			break;
	}
}

for($i=0; $i<count($formula_columns); $i++)
{
	$QUERY_SELECT = str_replace("[0:" . $formula_columns[$i][0] . "]", $formula_columns[$i][1], $QUERY_SELECT);
	$QUERY_SELECT = str_replace("[1:" . $formula_columns[$i][0] . "]", $formula_columns[$i][2], $QUERY_SELECT);
}


$QUERY_SELECT = substr($QUERY_SELECT, 0, strlen($QUERY_SELECT)-1);
$QUERY_GROUP = $QUERY_GROUP != "" ? substr($QUERY_GROUP, 0, strlen($QUERY_GROUP)-1) : "";
$QUERY_ORDER = $seprationOrder . $QUERY_ORDER;
$QUERY_ORDER = $QUERY_ORDER != "" ? substr($QUERY_ORDER, 0, strlen($QUERY_ORDER)-1) : "";
//------------------------------------------------------------------------------
preg_match_all('|#([^#].*)#|U', $QUERY_WHERE, $requestParams);

for($i=0; $i<count($requestParams[0]); $i++)
	$QUERY_WHERE = str_replace($requestParams[0][$i], "'" . $_REQUEST[$requestParams[1][$i]] . "'", $QUERY_WHERE);

//------------------------------------------------------------------------------
$TotalQuery = $QUERY_SELECT;
$TotalQuery .= $rptobj->query;
$TotalQuery .= " where " .	$QUERY_WHERE . " AND persons.person_type in(" . manage_access::getValidPersonTypes() . ")"; // bahar
$TotalQuery .= $QUERY_GROUP != "" ? " group by " . $QUERY_GROUP : "";
$TotalQuery .= $QUERY_ORDER != "" ? " order by " . $QUERY_ORDER : "";

if(!isset($_REQUEST["excel"]))
{
	echo "<div style='display:none'>" . $TotalQuery . "<br>";
	print_r($whereParam);
	echo "</div>";
}
if(isset($_REQUEST["preview"]))
	$TotalQuery .= " limit 10";

$statement = PdoDataAccess::runquery_fetchMode($TotalQuery, $whereParam);

if(ExceptionHandler::GetExceptionCount() != 0)
	print_r(ExceptionHandler::PopAllExceptions());
	
if($statement->rowCount() == 0)
{
	$output = "<span  class='reportGenerator'>گزارش مورد نظر خالی می باشد.</span>";
}
else
{
	if($rptobj->refer_page != "")
	{
		/*for($i=0; $i<count($data); $i++)
		{
			for($k=0; $k<count($columns); $k++)
			{
				if(in_array($columns[$k]["used_type"], array("group", "order", "separation", "formula_column", "condition", "filter")))
					continue;

				if($columns[$k]["renderer"] != "")
				{
					$row_id = $columns[$k]["row_id"];
									
					$tmpVal = isset($data[$i][$row_id . "2"]) ? $data[$i][$row_id . "2"] : $data[$i][$row_id];
					if($tmpVal != "")
						eval("\$value = " . $columns[$k]["renderer"] . "('" . $tmpVal . "');");
					else
						$value = "";

					$data[$i][$row_id] = $value;
				}				
			}
		}*/
		if(isset($_REQUEST["excel"]))
		{
			header("Content-type: application/zip");
			header("Content-disposition: inline; filename=excel.xls");
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header("Pragma: public");
		}
		require_once "../../reports/" . $rptobj->refer_page;
		die();
	}
	
	//$data = $statement->fetchAll();
	
	if(isset($_REQUEST["excel"]))
	{
		require_once 'excel.php';
		header("Content-type: application/zip");
		header("Content-disposition: inline; filename=excel.xls");
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header("Pragma: public");
		
		require_once "php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php";
		require_once "php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php";

		$workbook = &new writeexcel_workbook("/tmp/hrmstemp.xls");
		$worksheet =& $workbook->addworksheet("Sheet1");
		$heading =& $workbook->addformat(array('align' => 'center', 'bold' => 1, 'bg_color' => 'black', 'color' => 'white'));

		
		//row number
		$worksheet->write(0, 0, $header[0], $heading);
		// group headers
		for($i=0; $i < count($separationRows); $i++)
			$worksheet->write(0, $i+1, $separationRows[$i]["col_name"], $heading);
		// column headers
		for($j=1; $j < count($header); $j++)
			$worksheet->write(0, $i+$j, $header[$j], $heading);

		// values
		//for($i=0; $i<count($data); $i++)
		$i = -1;
		while($row = $statement->fetch())
		{
			$i++;
			$worksheet->write($i+1, 0, ($i+1));
			
			for($j=0; $j<count($separationRows); $j++)
			{
				$value = $row[$separationRows[$j]["row_id"]];
				$worksheet->write($i+1, $j+1, $value);
			}

			$worksheet->write($i+1, $j, $value);

			$index = $j+1;
			for($k=0; $k<count($columns); $k++)
			{
				if(in_array($columns[$k]["used_type"], array("group", "order", "separation", "formula_column", "condition", "filter")))
					continue;

				$row_id = $columns[$k]["row_id"];
				$value = $row[$row_id];

				if(DateModules::IsDate($value))
					$value = DateModules::miladi_to_shamsi($value);
				if($columns[$k]["renderer"] != "")
				{
					$tmpVal = isset($row[$row_id . "2"]) ? $row[$row_id . "2"] : $row[$row_id];
					if($tmpVal != "")
						eval("\$value = " . $columns[$k]["renderer"] . "('" . $tmpVal . "');");
					else
						$value = "";
				}
				$worksheet->write($i+1, $index++, $value);
			}
			
		}
		$workbook->close();

		echo file_get_contents("/tmp/hrmstemp.xls");
		unlink("/tmp/hrmstemp.xls");
		die();
	}
	
	$output = "<table class='reportGenerator' cellspacing='0' cellpadding='0'>";	
	$headerStr = "<tr class='header' style='background-color:#" . $rowColors[count($separationRows)] . "'><td>";
	$headerStr .= implode("</td><td>", $header);
	$headerStr .= "</tr>";
	$index = 0;
		
	//-------------------------------------------------------
	//for($i=0; $i<count($data); $i++, $index++)
	
	$i = -1;
	while($row = $statement->fetch())
	{
		$i++;
		//------------ make group header --------------------
		if(count($separationRows) != 0)
		{
			$groupFlag = false;
			for($j=0; $j<count($separationRows); $j++)
			{
				if($currentSeparation[$j] != $row[$separationRows[$j]["row_id"]])
				{
					$value = $row[$separationRows[$j]["row_id"]];
		
					if($separationRows[$j]["renderer"] != "")
					{
						$tmpVal = isset($row[$separationRows[$j]["row_id"] . "2"]) ? $row[$separationRows[$j]["row_id"] . "2"] :
								$row[$separationRows[$j]["row_id"]];
						if($tmpVal != "")
							eval("\$value = " . $separationRows[$j]["renderer"] . "('" . $tmpVal . "');");
						else
							$value = "";
					}
					$output .= "<tr class='header'><td align='right' style='background-color:#" . $rowColors[$j] . "' colspan='" .
						$columnCount . "'>" . $separationRows[$j]["col_name"] .
						" : " . $value . "</td></tr>";
					$currentSeparation[$j] = $row[$separationRows[$j]["row_id"]];
					$groupFlag = true;
				}
			}
			if($groupFlag)
				$output .= $headerStr;
		}
		else if($i == 0)
			$output .= $headerStr;
		//---------------------------------------------------
		$output .= "<tr><td>" . ($i+1) . "</td>";
		for($j=0; $j<count($columns); $j++)
		{
			if(in_array($columns[$j]["used_type"], array("group", "order", "separation", "formula_column", "condition", "filter")))
				continue;

			$row_id = $columns[$j]["row_id"];
			$value = $row[$row_id];

			if(DateModules::IsDate($value))
				$value = DateModules::miladi_to_shamsi($value);
			if($columns[$j]["renderer"] != "")
			{
				$tmpVal = isset($row[$row_id . "2"]) ? $row[$row_id . "2"] : $row[$row_id];
				if($tmpVal != "")
					eval("\$value = " . $columns[$j]["renderer"] . "('" . $tmpVal . "');");
				else
					$value = "";
			}
			$output .= "<td>" . $value . "&nbsp;</td>";
		}

		$output .= "</tr>";

		if(!empty($_POST["pp"]) && $index == $_POST["pp"]-1)
		{
			$output .= "</table><div style='page-break-after: always;'>&nbsp;</div>";
			$output .= $headerStr;
			$index = -1;
		}
	}
	$output .= "</table>";

	/*$data = $statement->fetchAll();
	
	if(isset($_REQUEST["excel"]))
	{
		require_once 'excel.php';
		header("Content-type: application/zip");
		header("Content-disposition: inline; filename=excel.xls");
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header("Pragma: public");
		
		require_once "php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php";
		require_once "php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php";

		$workbook = &new writeexcel_workbook("/tmp/hrmstemp.xls");
		$worksheet =& $workbook->addworksheet("Sheet1");
		$heading =& $workbook->addformat(array('align' => 'center', 'bold' => 1, 'bg_color' => 'black', 'color' => 'white'));

		
		//row number
		$worksheet->write(0, 0, $header[0], $heading);
		// group headers
		for($i=0; $i < count($separationRows); $i++)
			$worksheet->write(0, $i+1, $separationRows[$i]["col_name"], $heading);
		// column headers
		for($j=1; $j < count($header); $j++)
			$worksheet->write(0, $i+$j, $header[$j], $heading);

		// values
		for($i=0; $i<count($data); $i++)
		{
			$worksheet->write($i+1, 0, ($i+1));
			
			for($j=0; $j<count($separationRows); $j++)
			{
				$value = $data[$i][$separationRows[$j]["row_id"]];
				$worksheet->write($i+1, $j+1, $value);
			}

			$worksheet->write($i+1, $j, $value);

			$index = $j+1;
			for($k=0; $k<count($columns); $k++)
			{
				if(in_array($columns[$k]["used_type"], array("group", "order", "separation", "formula_column", "condition", "filter")))
					continue;

				$row_id = $columns[$k]["row_id"];
				$value = $data[$i][$row_id];

				if(DateModules::IsDate($value))
					$value = DateModules::miladi_to_shamsi($value);
				if($columns[$k]["renderer"] != "")
				{
					$tmpVal = isset($data[$i][$row_id . "2"]) ? $data[$i][$row_id . "2"] : $data[$i][$row_id];
					if($tmpVal != "")
						eval("\$value = " . $columns[$k]["renderer"] . "('" . $tmpVal . "');");
					else
						$value = "";
				}
				$worksheet->write($i+1, $index++, $value);
			}
			
		}
		$workbook->close();

		echo file_get_contents("/tmp/hrmstemp.xls");
		unlink("/tmp/hrmstemp.xls");
		die();
	}
	
	$output = "<table class='reportGenerator' cellspacing='0' cellpadding='0'>";	
	$headerStr = "<tr class='header' style='background-color:#" . $rowColors[count($separationRows)] . "'><td>";
	$headerStr .= implode("</td><td>", $header);
	$headerStr .= "</tr>";
	$index = 0;
	$dataKeys = array_keys($data[0]);
	
	//-------------------------------------------------------
	for($i=0; $i<count($data); $i++, $index++)
	{
		//------------ make group header --------------------
		if(count($separationRows) != 0)
		{
			$groupFlag = false;
			for($j=0; $j<count($separationRows); $j++)
			{
				if($currentSeparation[$j] != $data[$i][$separationRows[$j]["row_id"]])
				{
					$value = $data[$i][$separationRows[$j]["row_id"]];
		
					if($separationRows[$j]["renderer"] != "")
					{
						$tmpVal = isset($data[$i][$separationRows[$j]["row_id"] . "2"]) ? $data[$i][$separationRows[$j]["row_id"] . "2"] :
								$data[$i][$separationRows[$j]["row_id"]];
						if($tmpVal != "")
							eval("\$value = " . $separationRows[$j]["renderer"] . "('" . $tmpVal . "');");
						else
							$value = "";
					}
					$output .= "<tr class='header'><td align='right' style='background-color:#" . $rowColors[$j] . "' colspan='" .
						$columnCount . "'>" . $separationRows[$j]["col_name"] .
						" : " . $value . "</td></tr>";
					$currentSeparation[$j] = $data[$i][$separationRows[$j]["row_id"]];
					$groupFlag = true;
				}
			}
			if($groupFlag)
				$output .= $headerStr;
		}
		else if($i == 0)
			$output .= $headerStr;
		//---------------------------------------------------
		$output .= "<tr><td>" . ($i+1) . "</td>";
		for($j=0; $j<count($columns); $j++)
		{
			if(in_array($columns[$j]["used_type"], array("group", "order", "separation", "formula_column", "condition", "filter")))
				continue;

			$row_id = $columns[$j]["row_id"];
			$value = $data[$i][$row_id];

			if(DateModules::IsDate($value))
				$value = DateModules::miladi_to_shamsi($value);
			if($columns[$j]["renderer"] != "")
			{
				$tmpVal = isset($data[$i][$row_id . "2"]) ? $data[$i][$row_id . "2"] : $data[$i][$row_id];
				if($tmpVal != "")
					eval("\$value = " . $columns[$j]["renderer"] . "('" . $tmpVal . "');");
				else
					$value = "";
			}
			$output .= "<td>" . $value . "&nbsp;</td>";
		}

		$output .= "</tr>";

		if(!empty($_POST["pp"]) && $index == $_POST["pp"]-1)
		{
			$output .= "</table><div style='page-break-after: always;'>&nbsp;</div>";
			$output .= $headerStr;
			$index = -1;
		}
	}
	$output .= "</table>";*/
}
?>

<html>
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>	
		<style>
		.reportGenerator {border-collapse: collapse;border: 1px solid black;font-family: tahoma;font-size: 8pt;
			text-align: center;width: 98%;padding: 2px;}
		.reportGenerator .header {color: white;font-weight: bold;}
		.reportGenerator td {border: 1px solid #555555;height: 20px;}
		@media print {
			.noprint{display:none;}
		}
		</style>
		<script>
			function showResult()
			{
				window.location = window.location.href + "&excel=true";
			}
		</script>
	</head>
	<title><?= isset($_GET["Q1"]) ? $_GET["Q1"] : ""?></title>
	<body dir=rtl>
		<center>
		<!--<input type="button" onclick="showResult();" class="big_button noprint" value="خروجی excel">-->
		<table width="98%" cellpadding="0" cellspacing="0">
			<tr>
				<td width="20%"><img src="/HumanResources/img/fum_symbol.jpg" ></td>
				<td align="center" style="font-family:'b titr'">
					<span style="font-family:tahoma; font-weight: bold;font-size: 9pt;">
					<?= isset($_GET["Q1"]) ? $_GET["Q1"] : ""?></span></td>
				<td width="20%" align="left" style="font-family:tahoma;font-size:8pt">تاریخ :  <?= DateModules::shNow();?></td>
			</tr>
		</table>
		<?= $output ?>
		</center>
	</body>
</html>