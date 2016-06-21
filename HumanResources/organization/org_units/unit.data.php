<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.12
//---------------------------
require_once '../header.inc.php';
require_once 'unit.class.php';
require_once inc_response;
require_once inc_component;

$task = isset($_POST["task"]) ? $_POST["task"] : (isset($_GET["task"]) ? $_GET["task"] : "");

switch ($task)
{
	case "SaveUnit":
		SaveUnit();
		
	case "DeleteUnit":
		DeleteUnit();
		
	case "MoveUnit":
		MoveUnit();

	case "GetTreeNodes":
		GetTreeNodes();
}

function SaveUnit()
{
	$obj = new manage_units();
	PdoDataAccess::FillObjectByArray($obj, $_POST);

	if(empty($_POST["ouid"]))
	{
		$obj->RegDate = date('Y-m-d');
		$obj->parent_path = ($_POST["parent_path"] == "") ? $obj->parent_path : ( $_POST["parent_path"] . "," . $obj->parent_ouid ) ;
		$obj->AddUnit();
	}
	else
	{
		$obj->ouid = $_POST["ouid"];
		$obj->EditUnit();
	}	
	echo Response::createObjectiveResponse("true", $obj->ouid);
	die();
}

function DeleteUnit()
{
	manage_units::RemoveUnit($_POST["ouid"]);
	echo "true";
	die();
}

function MoveUnit()
{
	$obj = new manage_units();
	$obj->parent_ouid = $_POST["desc_ouid"];
	$obj->parent_path = (!isset($_POST["parent_path"]) || $_POST["parent_path"] == "") ?
			$obj->parent_ouid : $_POST["parent_path"] . "," . $obj->parent_ouid;
	$obj->ouid = $_POST["source_ouid"];
	
	$obj->EditUnit();
	
	echo "true";
	die();
}
//-------------------------------------------------------------------
function GetTreeNodes()
{
	$nodes = PdoDataAccess::runquery("select ouid as id,ptitle as text,'true' as leaf,parent_path
		from org_new_units where parent_ouid=0 or parent_ouid=0 is null order by ptitle");
		
	$cur_level_uids = "";
	$returnArray = $nodes;

	$ref_cur_level_nodes = array(); 
	for($i=0; $i<count($nodes); $i++)
	{
		$ref_cur_level_nodes[] = & $returnArray[$i];
		$cur_level_uids .= $nodes[$i]["id"] . ",";
	}
	$cur_level_uids = substr($cur_level_uids, 0, strlen($cur_level_uids) - 1);
	
	while (true)
	{
		$nodes = PdoDataAccess::runquery("select ouid as id,ptitle as text,'true' as leaf,parent_ouid as parentId,parent_path
			from org_new_units where parent_ouid in (" . $cur_level_uids . ") order by ptitle");
		
		if(count($nodes) == 0)
			break;
		//............ add current level nodes to returnArray ................
		$temp_ref = array();
		$cur_level_uids = "";
		
		for($i=0; $i<count($nodes); $i++)
		{
			//............ extract current level uids ..................
			$cur_level_uids .= $nodes[$i]["id"] . ",";
			
			for($j=0; $j < count($ref_cur_level_nodes); $j++)
			{
				if($nodes[$i]["parentId"] == $ref_cur_level_nodes[$j]["id"])
				{
					if(!isset($ref_cur_level_nodes[$j]["children"]))
					{
						$ref_cur_level_nodes[$j]["children"] = array();
						$ref_cur_level_nodes[$j]["leaf"] = "false";
					}
					$ref_cur_level_nodes[$j]["children"][] = $nodes[$i];
					$temp_ref[] = & $ref_cur_level_nodes[$j]["children"][count($ref_cur_level_nodes[$j]["children"])-1];
					break;
				}
			}
		}
		
		$ref_cur_level_nodes = $temp_ref;
		$cur_level_uids = substr($cur_level_uids, 0, strlen($cur_level_uids) - 1);	
	}

	$str = json_encode($returnArray);

	$str = str_replace('"children"', 'children', $str);
	$str = str_replace('"leaf"', 'leaf', $str);
	$str = str_replace('"text"', 'text', $str);
	$str = str_replace('"id"', 'id', $str);
	$str = str_replace('"true"', 'true', $str);
	$str = str_replace('"false"', 'false', $str);

	echo $str;
	die();

	//print_r($returnArray);
	$return_str = '{"text":"واحد های سازمانی","id":"source","parent_path":"",';
	$return_str .= (count($returnArray) == 0) ? '"leaf":true' : '"children":';
	if(count($returnArray) != 0)
		$return_str .= json_encode($returnArray);
	$return_str .= '}';
	return $return_str;
}

function DRP_State_City(&$stateoutput, &$cityOutput, $stateFieldName, $cityFieldName
		, $stateSelectedID = "", $citySelectedID = ""
		, $stateExtraRow = "", $cityExraRow = "", $stateWidth = "")
{
	$obj = new MaserDetail_DROPDOWN();

	$obj->Master_datasource = PdoDataAccess::runquery("select state_id,ptitle from states");
	if(!empty($stateExtraRow))
		$obj->Master_datasource = array_merge(array(array("state_id" => "-1", "ptitle" => $stateExtraRow)),$obj->Master_datasource);
	$obj->Master_id = $stateFieldName;
	$obj->Master_valuefield = "%state_id%";
	$obj->Master_textfield = "%ptitle%";

	$obj->Detail_datasource = PdoDataAccess::runquery("select * from cities");
	if(!empty($cityExraRow))
		$obj->Detail_datasource = array_merge(array(array("state_id" => "-1", "ptitle" => $cityExraRow)),$obj->Detail_datasource);
	$obj->Detail_id = $cityFieldName;
	$obj->Detail_valuefield = "%city_id%";
	$obj->Detail_textfield = "%ptitle%";
	$obj->Detail_masterfield = "state_id";

	$obj->bind_dropdown($stateoutput, $cityOutput, $stateSelectedID, $citySelectedID);
}









?>