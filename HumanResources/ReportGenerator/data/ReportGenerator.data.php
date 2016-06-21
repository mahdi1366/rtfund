<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.06
//---------------------------
require_once '../../header.inc.php';
require_once '../class/columns.class.php';
require_once inc_dataReader;
require_once inc_response;

$task = isset($_POST["task"]) ? $_POST["task"] : (isset($_GET["task"]) ? $_GET["task"] : "");

switch ($task)
{
	case "GetTreeNodes":
		GetTreeNodes();
		
	case "newTable":
		newTable();
}

function GetTreeNodes()
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
		select c.column_id as id,
			c.col_name as text,
			'col' as uiProvider,
			'true' as leaf,

			c.field_name,
			c.basic_type_id,
			c.search_mode,
			c.basic_info_table,
			c.check_value,
			c.check_text,
			c.renderer,
			c.table_name as parent_id,
			t.table_name,
			if(basic_info_table is null AND basic_type_id is null,0,1) as relatedField
		from rp_columns c join rp_tables t on(c.table_name=t.table_id)");

	for($i=0; $i < count($nodes); $i++)
	{
		$parent_node = & $ref[$nodes[$i]["parent_id"]];
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

function newTable()
{
	$query = "insert into rp_base_tables(table_name, description) values(?,?)";
	PdoDataAccess::runquery($query, array($_POST["table_name"], $_POST["description"]));

	echo "true";
	die();
}

?>