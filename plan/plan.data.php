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
		
	case "selectSubGroups":
		selectSubGroups();
		
	case "SelectElements":
		SelectElements();
		
	case "SelectPlanItems":
		SelectPlanItems();
		
	case "SavePlanItems":
		SavePlanItems();
		
	case "DeletePlanItem":
		DeletePlanItem();
}

function selectSubGroups(){
	
	$ParentID = $_GET["ParentID"];
	
	$nodes = PdoDataAccess::runquery("
			select p4.ParentID, p4.GroupID id, p4.GroupDesc text , 'true' leaf , 'false' expanded, '' iconCls
			from PLN_groups  p4
				left join PLN_groups p3 on(p4.ParentID=p3.GroupID)  
				left join PLN_groups p2 on(p3.ParentID=p2.GroupID)
				left join PLN_groups p1 on(p2.ParentID=p1.GroupID)
				
			where (p4.ParentID=:p or p3.ParentID=:p or p2.ParentID=:p or p1.ParentID=:p)", array(":p" => $ParentID));
		
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
		}
		$lastIndex = count($parentNode["children"]);
		$parentNode["children"][$lastIndex] = $node;
	}

	echo json_encode($returnArr);
	die();
}

function SelectElements(){

	$GroupID = $_REQUEST["GroupID"];
	$dt = PdoDataAccess::runquery("select * from PLN_Elements where GroupID=? order by ParentID", array($GroupID));
	
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
	
	$st = stripslashes(stripslashes($_POST["record"]));
	$data = json_decode($st);
	
	$obj = new PLN_PlanItems();
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
