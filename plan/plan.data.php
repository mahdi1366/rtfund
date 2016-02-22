<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//-------------------------

include_once('header.inc.php');
include_once inc_dataReader;
include_once inc_response;
include_once 'plan.class.php';

$task = $_REQUEST["task"];
switch ($task) {
		
	case "selectGroups":
		selectGroups();
		
	case "SelectElements":
		SelectElements();
		
	case "SelectPlanItems":
		SelectPlanItems();
		
	case "SavePlanItems":
		SavePlanItems();
		
	case "DeletePlanItem":
		DeletePlanItem();
}

function selectGroups(){
	
	//$ParentID = $_GET["ParentID"];
	
	$nodes = PdoDataAccess::runquery("select g.ParentID, g.GroupID id, g.GroupDesc text , 'true' leaf ,
		'javascript:void(0)' href, 'false' expanded, '' iconCls , if(count(pi.RowID)>0, 'filled', '') cls
		
		FROM PLN_groups g
		left join PLN_Elements e on(e.ParentID=0 AND g.GroupID=e.GroupID)
		left join PLN_PlanItems pi on(pi.PlanID=1 AND e.ElementID=pi.ElementID)

		group by g.GroupID
		
	");
		
	$returnArr = array(); 
	$refArr = array();
	
	foreach($nodes as $node)
	{
		$parentNode = &$refArr[$node["ParentID"]];
		if(!isset($parentNode))
		{
			$node["text"] = "[ " . (count($returnArr)+1) . " ] " . $node["text"];
			$returnArr[] = $node;
			$refArr[ $node["id"] ] = &$returnArr[ count($returnArr)-1 ];
			continue;
		}

		if (!isset($parentNode["children"])) {
			$parentNode["children"] = array();
			$parentNode["leaf"] = "false";
			unset($parentNode["href"]);
		}
		$lastIndex = count($parentNode["children"]);
		$parentNode["children"][$lastIndex] = $node;
		
		$refArr[ $node["id"] ] = &$parentNode["children"][$lastIndex];
	}

	echo json_encode($returnArr);
	die();
}

function SelectElements(){

	$PlanID = $_REQUEST["PlanID"];
	$GroupID = $_REQUEST["GroupID"];
	$dt = PdoDataAccess::runquery("select e.* from PLN_Elements e
		where GroupID=? order by ElementID", array($GroupID));
	
	$planValues = array();
	for($i=0; $i < count($dt); $i++)
	{
		if($dt[$i]["ElementType"] == "grid")
			continue;
		if($dt[$i]["ElementType"] == "panel")
		{
			$temp = PLN_PlanItems::SelectAll("PlanID=? AND ElementID=?", array($PlanID, $dt[$i]["ElementID"]));
			if(count($temp) == 0)
				continue;
			$p = xml_parser_create();
			xml_parse_into_struct($p, $temp[0]["ElementValue"], $vals);
			xml_parser_free($p);
			$planValues[ $dt[$i]["ElementID"] ] = $vals;
		}
		else
		{
			if(!isset($planValues[ $dt[$i]["ParentID"] ]))
			{
				$dt[$i]["ElementValue"] = "";
				break;
			}
			
			$vals = $planValues[ $dt[$i]["ParentID"] ];
			foreach($vals as $element)
			{
				if($element["tag"] == "ELEMENT_" . $dt[$i]["ElementID"])
				{
					$dt[$i]["ElementValue"] = empty($element["value"]) ? "" : $element["value"];
					break;
				}
			}
		}
	}
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SelectPlanItems(){
	
	$PlanID = $_REQUEST["PlanID"];
	$ElementID = $_REQUEST["ElementID"];
	
	$dt = PLN_PlanItems::SelectAll("PlanID=? AND ElementID=?", array($PlanID, $ElementID));
	for($i=0; $i < count($dt); $i++)
	{
		$p = xml_parser_create();
		xml_parse_into_struct($p, $dt[$i]["ElementValue"], $vals);
		xml_parser_free($p);
		
		foreach($vals as $element)
		{
			if(strpos($element["tag"],"ELEMENT_") !== false)
				$dt[$i][strtolower($element["tag"]) ] = empty($element["value"]) ? "" : $element["value"];
		}		
		unset($dt[$i]["ElementValue"]);
	}	
		
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SavePlanItems(){
	
	$obj = new PLN_PlanItems();
	
	if(isset($_POST["record"]))
	{
		$st = stripslashes(stripslashes($_POST["record"]));
		$data = json_decode($st);
	
		$obj->RowID = $data->RowID;
		$obj->PlanID = $data->PlanID;
		$obj->ElementID = $data->ElementID;

		$xml = new SimpleXMLElement('<root/>');
		$elems = array_keys(get_object_vars($data));
		foreach($elems as $el)
		{
			if(strpos($el, "element_") === false)
				continue;
			$xml->addChild($el, $data->$el);
		}
		$obj->ElementValue = $xml->asXML();
	}
	else
	{
		$obj->PlanID = $_POST["PlanID"];
		$obj->ElementID = $_POST["ElementID"];
		
		$dt = PdoDataAccess::runquery("select RowID from PLN_PlanItems where PlanID=? AND ElementID=?",
			array($obj->PlanID, $obj->ElementID));
		if(count($dt)>0)
			$obj->RowID = $dt[0]["RowID"];		
		
		$xml = new SimpleXMLElement('<root/>');
		foreach($_POST as $key => $value)
		{
			if(strpos($key, "element_") === false)
				continue;
			$xml->addChild($key, $value);
		}
		$obj->ElementValue = $xml->asXML();
	}
	
	if($obj->RowID > 0)
		$result = $obj->EditItem();
	else
		$result = $obj->AddItem();
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeletePlanItem(){
	
	$RowID = $_POST["RowID"];
	
	$result = PLN_PlanItems::DeleteItem($RowID);
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

?>
