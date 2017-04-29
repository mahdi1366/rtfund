<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//-------------------------

include_once('../header.inc.php');
include_once inc_dataReader;
include_once inc_response;
include_once 'elements.class.php';

$task = $_REQUEST["task"];
switch ($task) {
		
	default : 
		eval($task. "();");
}

function SelectScopes(){
	
	$dt = PdoDataAccess::runquery("select * from BaseInfo where TypeID=21 AND IsActive='YES'");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SaveGroup(){
	
	$obj = new PLN_groups();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	$obj->CustomerRelated = isset($_POST["CustomerRelated"]) ? "YES" : "NO";
	$obj->IsMandatory = isset($_POST["IsMandatory"]) ? "YES" : "NO";
		
	//------- check for having form/grid ------
	$dt = PLN_Elements::Get(" AND GroupID=?", array($obj->ParentID));
	if($dt->rowCount() > 0)
	{
		echo Response::createObjectiveResponse(false, "آیتم انتخابی شامل جدول اطلاعاتی می باشد");
		die();
	}
	//-----------------------------------------
	
	if($obj->GroupID*1 > 0)
		$reslt = $obj->Edit();
	else
		$reslt = $obj->Add();
	
	//print_r(ExceptionHandler::PopAllExceptions());	
	echo Response::createObjectiveResponse($reslt, "");
	die();
}

function DeleteGroup(){
	
	$obj = new PLN_groups($_POST["GroupID"]);
	$reslt = $obj->Remove();
	
	//print_r(ExceptionHandler::PopAllExceptions());	
	echo Response::createObjectiveResponse($reslt, "");
	die();
}

function selectGroupElements(){
	
	$dt = PLN_Elements::Get(" AND IsActive='YES' AND GroupID=? AND ParentID=?", 
		array($_REQUEST["GroupID"], $_REQUEST["ParentID"]));
	$dt = $dt->fetchAll();
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SaveElement(){
	
	$obj = new PLN_Elements();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	if(empty($obj->properties))
		$obj->properties = " ";
	
	if(empty($obj->EditorProperties))
		$obj->EditorProperties = " ";
	
	if($obj->ElementID*1 > 0)
		$result = $obj->Edit();
	else
		$result = $obj->Add();
	
	//print_r(ExceptionHandler::PopAllExceptions());	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteElement(){
	
	$obj = new PLN_Elements();
	$obj->ElementID = $_POST["ElementID"];
	$result = $obj->Remove();
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

//............................................

function selectGroups(){
	
	$filled = !isset($_REQUEST["filled"]) ? "" : $_REQUEST["filled"];
	$PlanID = $_REQUEST["PlanID"];
	
	$nodes = PdoDataAccess::runquery("select g.ParentID, g.GroupID id, g.GroupDesc text , 'true' leaf ,
		'javascript:void(0)' href, 'false' expanded, '' iconCls , 
		concat(if(count(pi.RowID)>0, 'filled ', ''), 
				case t.ActType when 'REJECT' then 'reject'
							   when 'CONFIRM' then 'confirm'
							   else '' end
		) cls,
		ifnull(ActDesc,'') qtip, g.ScopeID, g.CustomerRelated , g.IsMandatory
		
		FROM PLN_groups g
		left join PLN_Elements e on(e.ParentID=0 AND g.GroupID=e.GroupID)
		left join PLN_PlanItems pi on(pi.PlanID=:p AND e.ElementID=pi.ElementID)
		left join (
			select p.GroupID,ActType,ActDesc from PLN_PlanSurvey p,
				(select GroupID,max(RowID) RowID from PLN_PlanSurvey where PlanID=:p AND GroupID>0
				group by GroupID)t
			where PlanID=:p AND p.RowID =t.RowID AND p.GroupID=t.GroupID
			group by GroupID
		)t on(g.GroupID=t.GroupID)
		group by g.GroupID
		" . ($filled == "true" ? " having count(pi.RowID)>0 " : "") . "
	", array(":p" => $PlanID));
		
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
		where IsActive='YES' AND GroupID=? order by ElementID", array($GroupID));
	
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

?>