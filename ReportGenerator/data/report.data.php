<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.04
//---------------------------
require_once '../../header.inc.php';
require_once '../class/report.class.php';

require_once inc_dataReader;
require_once inc_response;

$task = isset($_POST["task"]) ? $_POST["task"] : (isset($_GET["task"]) ? $_GET["task"] : "");

switch ($task)
{
	case "save":
		saveReport();
	
	case "select":
		selectReports();
	
	case "delete":
		deleteReport();
		
	case "formSave":
		formSave();

	case "getColumns":
		getColumns();
	//....................................

	case "selectReportColumns":
		selectReportColumns();

	case "addReportColumn":
		addReportColumn();

	case "ChangeColumnOrder":
		ChangeColumnOrder();

	case "deleteColumn":
		deleteColumn();
}

function saveReport()
{
	$summaryNames = array("sum" => "مجموع",
						"count" => "تعداد",
						"avg" => "میانگین",
						"max" => "ماکزیمم",
						"min" => "مینیمم");
	
	//-------------------- Add or Edit report header ---------------------------

	$rptobj = new rp_reports();
	$rptobj->report_title = $_POST["report_title"];
	$rptobj->conditions = stripslashes($_POST["conditions"]);
	$rptobj->refer_page = $_POST["refer_page"];

	if(empty($_POST["report_id"]))
	{
		$result = $rptobj->Add();
		$rptobj->report_id = rp_reports::LastID();
	}
	else
	{
		$rptobj->report_id = $_POST["report_id"];
		$result = $rptobj->Edit();
	}
	if(!$result)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		die();
	}
	//------------------------ insert all used columns -------------------------
	PdoDataAccess::runquery("delete from rp_report_columns where report_id=" . $rptobj->report_id .
		" AND used_type in('group','separation','filter','order')");
	$variables = array("groupColumns","separationColumns","filterColumns","orderColumns");
	$query = "insert into rp_report_columns(report_id,parent_path,column_id,used_type) values";
	
	foreach ($variables as $var)
	{
		$st = preg_split('/,/', $_POST[$var]);
		for($i=0; $i < count($st)-1; $i++)
		{
			$id = $st[$i];
			$parent_path = $id;
			$tmp = preg_split('/_/', $id);
			$column_id = $tmp[count($tmp)-1];

			$query .= "(" . $rptobj->report_id . ",'" . $parent_path . "'," . $column_id . ",'" . str_replace("Columns", "", $var) . "'),";
		}
	}

	$st = array();
	preg_match_all("|\[[^\]]+(.*)+\]|U", $_POST["conditions"], $st, PREG_PATTERN_ORDER);
	if($st && $st[0])

		for($i=0; $i < count($st[0]); $i++)
		{
			$tmp = preg_replace("/\[|\]/", "", $st[0][$i]);

			$parent_path = $tmp;
			$tmp = preg_split('/_/', $parent_path);
			$column_id = $tmp[count($tmp) - 1];
			$query .= "(" . $rptobj->report_id . ",'" . $parent_path . "'," . $column_id . ",'condition'),";
		}

	$query = substr($query, 0, strlen($query)-1);
	$query .= " ON DUPLICATE KEY UPDATE row_id=row_id";
	PdoDataAccess::runquery($query);

	//-------------- make All tables name/value Collection ---------------------
	$temp = PdoDataAccess::runquery("select * from rp_tables order by table_id");
	$allMasters = "";
	for($i=0; $i<count($temp); $i++)
		$allMasters[$temp[$i]["table_id"]] = $temp[$i];

	//----------------------  extract all used relations -----------------------
	$columns = PdoDataAccess::runquery("
		select rc.*,c.*,rt.join_text from rp_report_columns rc
			join rp_columns c using(column_id)
			left join rp_tables as rt on(rt.table_id=c.basic_info_table)
		where report_id=" . $rptobj->report_id . " and used_type<>'formula'");

	global $ALIAS_counter;
	$ALIAS_counter = 0;
	$tables = array();
	$allRelations = array();
	$EXTRA_QUERY_FROM = "";
	
	for($i=0; $i < count($columns); $i++)
	{
		$tmp = preg_split('/_/', $columns[$i]["parent_path"]);
		
		$parent = "";
		$cur_column_alias = "";
		for($j=0; $j < count($tmp)-1; $j++)
		{
			if(array_search($tmp[$j], $allRelations) === false)
				array_push($allRelations, $tmp[$j]);
				
			if(isset($tables[$tmp[$j]]))
			{
				for($k=0; $k < count($tables[$tmp[$j]]["parents"]); $k++)
				{
					if($tables[$tmp[$j]]["parents"][$k][0] == $parent)
					{
						$cur_column_alias = $tables[$tmp[$j]]["parents"][$k][1];
						break;
					}
				}
				if($k < count($tables[$tmp[$j]]["parents"]))
				{
					$parent .= $parent == "" ? $tmp[$j] : "_" . $tmp[$j];
					continue;
				}
				$tables[$tmp[$j]]["parents"][] = array($parent, $ALIAS_counter++);
				$cur_column_alias = $ALIAS_counter-1;
			}
			else
			{
				$tables[$tmp[$j]] = array("parents" => array(array($parent, $ALIAS_counter++)));
				$cur_column_alias = $ALIAS_counter-1;
			}
			$parent .= $parent == "" ? $tmp[$j] : "_" . $tmp[$j];
		}

		//.............................
		$parent_path = $columns[$i]["parent_path"];
		$tmp = preg_split('/_/', $parent_path);
		$column_id = $columns[$i]["column_id"];
		$alias = "tbl" . $cur_column_alias;

		$field = $alias . "." . $columns[$i]["field_name"];
		$base_field = $alias . "." . $columns[$i]["field_name"];

		if($columns[$i]["basic_type_id"] != "" && $columns[$i]["basic_type_id"] != "0")
		{
			$EXTRA_QUERY_FROM .= "\n left join Basic_Info as tbl" . $ALIAS_counter .
					" on(tbl$ALIAS_counter.TypeID=" . $columns[$i]["basic_type_id"] .
						" AND tbl$ALIAS_counter.InfoID=" . $alias . "." . $columns[$i]["field_name"] . ")";

			$field = "tbl" . $ALIAS_counter . ".Title";
			$ALIAS_counter++;
		}
		if($columns[$i]["basic_info_table"] != "" && $columns[$i]["basic_info_table"] != "0")
		{
			$result = AddInfoTable($allMasters, $allMasters[$columns[$i]["basic_info_table"]], $alias,
				$columns[$i]["field_name"]);

			$EXTRA_QUERY_FROM .= "\n" . $result["from"];
			$field = addslashes($result["field"]);
		}

		PdoDataAccess::runquery("update rp_report_columns
			set field = '" . $field . "', base_field = '" . $base_field . "'
			where row_id=" . $columns[$i]["row_id"]);

	}

	//--------------------------- make from clause -----------------------------
	$relations = PdoDataAccess::runquery("
		select relation_id,t1.table_name as parent_table_name,t2.table_name as table_name, join_text
		from rp_relations r
			left join rp_base_tables t1 on(r.parent_table_id=t1.table_id)
			left join rp_base_tables t2 on(r.table_id=t2.table_id)
		where relation_id in(" . implode(",", $allRelations) . ") order by relation_id");

	$QUERY_FROM = "\n from " . $relations[0]["table_name"] . " as tbl" . $tables[$relations[0]["relation_id"]]["parents"][0][1];
	for($i=1; $i < count($relations); $i++)
	{
		$relation_id = $relations[$i]["relation_id"];
		for($j=0; $j < count($tables[$relation_id]["parents"]); $j++)
		{
			$on = $relations[$i]["join_text"];
			$on = str_replace("ALIAS2", "tbl" . $tables[$relation_id]["parents"][$j][1], $on);

			$parent = preg_split('/_/', $tables[$relation_id]["parents"][$j][0]);
			$last_parent = $parent[count($parent)-1];
			unset($parent[count($parent)-1]);
			$path_parent = implode("_", $parent);

			for($k=0; $k < count($tables[$last_parent]["parents"]); $k++)
				if($path_parent == $tables[$last_parent]["parents"][$k][0])
				{
					$on = str_replace("ALIAS1", "tbl" . $tables[$last_parent]["parents"][$k][1], $on);
					break;
				}

			$QUERY_FROM .= "\n left join " . $relations[$i]["table_name"] . " as tbl" . $tables[$relation_id]["parents"][$j][1] .
				" on(" . $on . ")";
		}
	}

	//--------------------------------------------------------------------------
	$rptobj->query = $QUERY_FROM . $EXTRA_QUERY_FROM;
	
	$result = $rptobj->Edit();
	if($result)
		echo Response::createObjectiveResponse(true, $rptobj->report_id);
	else
		print_r(ExceptionHandler::PopAllExceptions());
	die();














	//--------------------- extract all used relations -------------------------
	$columns = PdoDataAccess::runquery("
		select rc.*,c.*,rt.join_text from rp_report_columns rc
			join rp_columns c using(column_id)
			left join rp_tables as rt on(rt.table_id=c.basic_info_table)
		where report_id=" . $rptobj->report_id . " and used_type<>'formula'");
	
	$allRelations = array();
	$EXTRA_QUERY_FROM = "";
	global $ALIAS_counter;
	$ALIAS_counter = 0;
	$aliases = array();
	
	for($i=0; $i < count($columns); $i++)
	{
		$tmp = preg_split('/_/', $columns[$i]["parent_path"]);
		for($j=0; $j < count($tmp)-1; $j++)
			if(array_search($tmp[$j], $allRelations) === false)
				array_push($allRelations, $tmp[$j]);

		//.............................
		$parent_path = $columns[$i]["parent_path"];
		$tmp = preg_split('/_/', $parent_path);
		$column_id = $columns[$i]["column_id"];
		$relation_id = $tmp[count($tmp)-2];
		$aliases[$relation_id] = "tbl" . $ALIAS_counter++;
		$alias = $aliases[$relation_id];
		
		$field = $alias . "." . $columns[$i]["field_name"];
		$base_field = $alias . "." . $columns[$i]["field_name"];

		if($columns[$i]["basic_type_id"] != "" && $columns[$i]["basic_type_id"] != "0")
		{
			$EXTRA_QUERY_FROM .= "\n left join Basic_Info as tbl" . $ALIAS_counter .
					" on(tbl$ALIAS_counter.TypeID=" . $columns[$i]["basic_type_id"] .
						" AND tbl$ALIAS_counter.InfoID=" . $alias . "." . $columns[$i]["field_name"] . ")";

			$field = "tbl" . $ALIAS_counter . ".Title";
			$ALIAS_counter++;
		}
		if($columns[$i]["basic_info_table"] != "" && $columns[$i]["basic_info_table"] != "0")
		{
			$result = AddInfoTable($allMasters, $allMasters[$columns[$i]["basic_info_table"]], $alias,
				$columns[$i]["field_name"]);

			$EXTRA_QUERY_FROM .= "\n" . $result["from"];
			$field = addslashes($result["field"]);
		}

		PdoDataAccess::runquery("update rp_report_columns 
			set field = '" . $field . "', base_field = '" . $base_field . "'
			where row_id=" . $columns[$i]["row_id"]);
	}
	
	array_multisort($allRelations);
	//--------------------------- make from clause -----------------------------
	$relations = PdoDataAccess::runquery("
		select relation_id,t1.table_name as parent_table_name,t2.table_name as table_name, join_text
		from rp_relations r
			left join rp_base_tables t1 on(r.parent_table_id=t1.table_id)
			left join rp_base_tables t2 on(r.table_id=t2.table_id)
		where relation_id in(" . implode(",", $allRelations) . ") order by relation_id");

	//$QUERY_FROM = "\n from " . $relations[0]["table_name"] . " as tbl" . $relations[0]["relation_id"];
	$QUERY_FROM = "\n from " . $relations[0]["table_name"] . " as " . $aliases[$relations[0]["relation_id"]];
	//$aliases = array();
	//$aliases[$relations[0]["table_name"]] = "tbl" . $relations[0]["relation_id"];
	$aliases[$relations[0]["table_name"]] = $aliases[$relations[0]["relation_id"]];

	for($i=1; $i < count($relations); $i++)
	{
		$on = $relations[$i]["join_text"];
		$on = str_replace("ALIAS1", $aliases[$relations[$i]["parent_table_name"]], $on);
		//$on = str_replace("ALIAS2", "tbl" . $relations[$i]["relation_id"], $on);
		$on = str_replace("ALIAS2", $aliases[$relations[0]["relation_id"]], $on);
		//$QUERY_FROM .= "\n left join " . $relations[$i]["table_name"] . " as tbl" . $relations[$i]["relation_id"] . " on(" . $on . ")";
		$QUERY_FROM .= "\n left join " . $relations[$i]["table_name"] . " as " . $aliases[$relations[0]["relation_id"]] . " on(" . $on . ")";
		//$aliases[$relations[$i]["table_name"]] = "tbl" . $relations[$i]["relation_id"];
		
	}
	//--------------------------------------------------------------------------
	$rptobj->query = $QUERY_FROM . $EXTRA_QUERY_FROM;
	$result = $rptobj->Edit();
	if($result)
		echo Response::createObjectiveResponse(true, $rptobj->report_id);
	else
		print_r(ExceptionHandler::PopAllExceptions());
	die();

}

function AddDetailTable(&$allMasters, &$AddedMasters, $row)
{
	global $ALIAS_counter;
	
	if(strpos($AddedMasters, "[" . $row["table_id"] . "]") !== false)
		return;

	if($row["master_id"] == 0)
		return false;
		
	$returnvalue = "";
	if(strpos($AddedMasters, "[" . $row["master_id"] . "]") === false)
	{
		$returnvalue = AddDetailTable($allMasters, $AddedMasters, $allMasters["tbl" . $row["master_id"]]);
		if($returnvalue === false)
			return false;
	}
	$join = $row["join_text"];
	$join = str_replace("ALIAS1", $allMasters["tbl" . $row["master_id"]]["alias"], $join);
	$join = str_replace("ALIAS2", "tbl" . $ALIAS_counter, $join);


	$returnvalue .= " join " . $row["table_name"] . " as tbl" . $ALIAS_counter . " on(" . $join . ")";
	$AddedMasters .= "[" . $row["table_id"] . "]";
	$allMasters["tbl" . $row["table_id"]]["alias"] = "tbl" . $ALIAS_counter;
	$ALIAS_counter++;
	
	return $returnvalue;
}

function AddInfoTable($allMasters, $row, $alias ,$field)
{
	global $ALIAS_counter;

	$result["field"] = $row["display_field"] != "" ? $row["display_field"] : "tbl" . $ALIAS_counter . "." . $row["value_field"];
	$result["from"] = "";
	if($row["join_text"] != "")
	{
		$on = $row["join_text"];
		$on = str_replace("ALIAS1", $alias, $on);
		$on = str_replace("ALIAS2", "tbl" . $ALIAS_counter, $on);
	}
	else
		$on = "tbl" . $ALIAS_counter . "." . $row["key_field"] . "=" . $alias . "." . $field;

	while(true)
	{
		$on = str_replace("ALIAS1", "tbl" . $ALIAS_counter, $on);
		$result["from"] .= " left join " . $row["table_name"] . " as tbl" . $ALIAS_counter . " on(" . $on . ")";
		$result["field"] = str_replace('ALIAS' . $row["table_id"], "tbl" . $ALIAS_counter, $result["field"]);
		
		$on = $row["master_join_text"];
		$on = str_replace("ALIAS2", "tbl" . $ALIAS_counter, $on);
		$alias = "tbl" . $ALIAS_counter;
		global $ALIAS_counter;
		$ALIAS_counter++;
		
		if($row["master_id"] == 0)
			return $result;
		$row = $allMasters[$row["master_id"]];

	}

return;



	if($row["master_id"] == 0)
	{
		$result["field"] = "tbl" . $ALIAS_counter . "." . $row["value_field"];
		if($row["join_text"] != "")
		{
			$on = $row["join_text"];
			$on = str_replace("ALIAS1", $alias, $on);
			$on = str_replace("ALIAS2", "tbl" . $ALIAS_counter, $on);
		}
		else
			$on = "tbl" . $ALIAS_counter . "." . $row["key_field"] . "=" . $alias . "." . $field;
			
		$result["from"] = " left join " . $row["table_name"] . " as tbl" . $ALIAS_counter . " on(" . $on . ")";
					
		
		$ALIAS_counter++;
		return $result;
	}

	$joinText = " left join " . $row["table_name"] . " as tbl" . $ALIAS_counter . " on(" . $row["join_text"] . ")";
	$joinText = str_replace("ALIAS2", "tbl" . $ALIAS_counter, $joinText);
	$ALIAS_counter++;
	$joinText = str_replace("ALIAS1", "tbl" . $ALIAS_counter, $joinText);
	
	$result = AddInfoTable($allMasters, $allMasters[$row["master_id"]], $alias ,$field);
	$result["from"] .= $joinText;
	$result["field"] = ($row["display_field"] != "" ? $row["display_field"] : "tbl" . $ALIAS_counter . "." . $row["value_field"]);
	return $result;
}

function selectReports()
{
	$temp = rp_reports::select();
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function deleteReport()
{
	$return = rp_reports::remove($_POST["Q0"]);
	echo ($return) ? "true" : "false";
	die();
}

function formSave()
{
	//-------------------- file upload ------------------------------
	if (isset ( $_FILES ['attach'] ) && trim ( $_FILES ['attach'] ['tmp_name'] ) != '') 
	{
		$st = split ( '\.', $_FILES ['attach'] ['name'] );
		$extension = $st [count ( $st ) - 1];
		
		$fp = fopen("../../" . ReportImagePath . "Report" . $_POST["Q0"] . "." . $extension, "w");
		fwrite ($fp, fread ( fopen ( $_FILES ['attach'] ['tmp_name'], 'r' ), $_FILES ['attach']['size']));
		fclose ($fp);
		
		PdoDataAccess::runquery("update rpt_reports set FileType='$extension' where report_id=" . $_POST["Q0"]);
		//dataAccess::AUDIT("تعیین فرم گزارش کد[" . $_POST["Q0"] . "]");
		
		echo "true";
		die();
	}
	echo "false";
	die();
}

function getColumns()
{
	$ref = array();
	$returnArray = array();
	
	$nodes = PdoDataAccess::runquery("
		select relation_id,if(parent_tree_id<>0, parent_tree_id,parent_table_id) as parent_table_id,
				if(tree_id<>0, tree_id, table_id) as table_id,description
		from rp_relations
		order by relation_id");

	$returnArray[] = array("id" => $nodes[0]["relation_id"], "text" => $nodes[0]["description"], "leaf" => "false");
	$ref[$nodes[0]["table_id"]][] =  & $returnArray[count($returnArray) - 1];
	
	for($i=1; $i<count($nodes); $i++)
	{
		if($nodes[$i]["parent_table_id"] == "")
		{
			$returnArray[] = array("id" => $nodes[$i]["relation_id"], "text" => $nodes[$i]["description"], "leaf" => "false");
			$ref[$nodes[$i]["table_id"]][] =  & $returnArray[count($returnArray) - 1];
			continue;
		}
		$parent = & $ref[$nodes[$i]["parent_table_id"]];
		for($j=0; $j<count($parent); $j++)
		{
			if(!isset($parent[$j]["children"]))
				$parent[$j]["children"] = array();

			$parent[$j]["children"][] = array("id" => $parent[$j]["id"] . "_" . $nodes[$i]["relation_id"], "text" => $nodes[$i]["description"], "leaf" => "false");
			$ref[$nodes[$i]["table_id"]][] = & $parent[$j]["children"][count($parent[$j]["children"]) - 1];
		}
	}

	$nodes = PdoDataAccess::runquery("
		select column_id as id,
			col_name as text,
			field_name as qtip,
			'true' as leaf,
			field_name,
			table_name,
			if(basic_info_table is null AND basic_type_id is null,0,1) as relatedField
		from rp_columns");

	for($i=0; $i < count($nodes); $i++)
	{
		$parent_node = & $ref[$nodes[$i]["table_name"]];
		for($j=0; $j<count($parent_node); $j++)
		{
			if(!isset($parent_node[$j]["children"]))
			{
				$parent_node[$j]["children"] = array();
				$parent_node[$j]["leaf"] = "false";
			}
			$cur_node = $nodes[$i];
			$cur_node["id"] = $parent_node[$j]["id"] . "_" . $cur_node["id"];
			$parent_node[$j]["children"][] = $cur_node;
		}
	}
	$str = json_encode($returnArray);

	$str = str_replace('"children"', 'children', $str);
	$str = str_replace('"leaf"', 'leaf', $str);
	$str = str_replace('"text"', 'text', $str);
	$str = str_replace('"qtip"', 'qtip', $str);
	$str = str_replace('"id"', 'id', $str);
	$str = str_replace('"true"', 'true', $str);
	$str = str_replace('"false"', 'false', $str);

	echo $str;
	die();
}

//------------------------------------------------------------------------------

function selectReportColumns()
{
	$report_id = $_REQUEST["Q0"];
	
	$query = "select rc.*,if(used_type='basic',c.col_name,rc.field_title) as title
		from rp_report_columns rc left join rp_columns c using(column_id)
		where rc.report_id=? AND used_type in('basic','formula','summary')
		order by row_id";
	$temp = PdoDataAccess::runquery($query, array($report_id));

	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function addReportColumn()
{
	$obj = new rp_report_columns();
	PdoDataAccess::FillObjectByArray($obj, $_POST);

	$obj->Add();

	if($obj->used_type == "formula")
	{
		$st = array();
		preg_match_all("|\[[^\]]+(.*)+\]|U", $_POST["parent_path"], $st, PREG_PATTERN_ORDER);
		if($st && $st[0])
		{
			for($i=0; $i < count($st[0]); $i++)
			{
				$id = preg_replace("/\[|\]/", "", $st[0][$i]);

				$tmp = preg_split('/_/', $id);
				$column_id = $tmp[count($tmp) - 1];

				$obj = new rp_report_columns();
				$obj->report_id = $_POST["report_id"];
				$obj->parent_path = $id;
				$obj->used_type = "formula_column";
				$obj->column_id = $column_id;
				$obj->base_evaluate = $_POST["base_evaluate"];
				
				$obj->Add();
			}
		}
	}
	
	echo "true";
	die();
}

function ChangeColumnOrder()
{
	$cur = $_POST["cur_row_id"];
	$sec = $_POST["sec_row_id"];

	$db = PdoDataAccess::getPdoObject();
	/*@var $db PDO*/

	$db->beginTransaction();
	$ret = PdoDataAccess::runquery("update rp_report_columns set row_id=-100 where row_id=" . $sec);
	if($ret === false)
	{
		$db->rollBack();
		echo "false";
		die();
	}
	$ret = PdoDataAccess::runquery("update rp_report_columns set row_id=$sec where row_id=" . $cur);
	if($ret === false)
	{
		$db->rollBack();
		echo "false";
		die();
	}
	$ret = PdoDataAccess::runquery("update rp_report_columns set row_id=$cur where row_id=-100");
	if($ret === false)
	{
		$db->rollBack();
		echo "false";
		die();
	}
	$db->commit();
	echo "true";
	die();
}

function deleteColumn()
{
	rp_report_columns::remove($_POST["row_id"]);

	echo "true";
	die();
}

















?>