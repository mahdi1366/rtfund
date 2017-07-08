<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//-------------------------

include_once('../header.inc.php');
include_once inc_dataReader;
include_once inc_response;
include_once 'form.class.php';

if(!empty($_REQUEST["task"])) {
	
	$task = $_REQUEST["task"];
	$task();
}

function SelectForms() {
    $where = " AND IsActive='YES' ";
    $whereParams = array();
	
    if (!empty($_REQUEST['FormID'])) {
        $where = " AND FormID = :FormID";
        $whereParams[':FormID'] = $_REQUEST['FormID'];
    }
	if (isset($_REQUEST['ParentID'])) {
        $where = " AND ParentID = :ParentID";
        $whereParams[':ParentID'] = $_REQUEST['ParentID'];
    }
	
    $temp = FRG_forms::Get($where, $whereParams);
    $res = PdoDataAccess::fetchAll($temp, $_GET['start'], $_GET['limit']);
	
	if(!empty($_REQUEST['FormID']) && isset($_REQUEST["EditContent"]))
	{
		$obj = new FRG_forms($_REQUEST['FormID']);
		$content = $obj->FormContent;
		$res[0]["content"] = PrepareContentToEdit($content);
	}
	
    echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
    die();
}

function SaveForm() {
	
    $pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
	
	$obj = new FRG_forms();
	
	if(!empty($_POST["record"]))
		PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	else
		PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	if(!empty($_POST['FormContent']))
	{
		$CorrectContent = FRG_forms::CorrectFormContentItems($_POST['FormContent']);
		$obj->FormContent = $CorrectContent;
	}
	if ($obj->FormID > 0) {
		$result = $obj->Edit($pdo);
	} else {
		$result = $obj->Add($pdo);
	}
	
	if(!$result)
	{
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		//echo PdoDataAccess::GetLatestQueryString();
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function GetFormContent() {
	
    $obj = new FRG_forms($_POST['FormID']);
    //echo Response::createObjectiveResponse(true, $obj->FormContent);
	echo $obj->FormContent;
    die();
}

function GetFormTitle() {
    $obj = new FRG_forms($_POST['FormID']);
    echo Response::createObjectiveResponse(true, $obj->FormTitle);
    die();
}

function deleteForm() {
    $pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
	
	$obj = new FRG_forms($_POST['FormID']);
	$result = $obj->Remove();
	
	if(!$result)
	{
		$pdo->rollBack();
		//print_r(ExceptionHandler::PopAllExceptions());
		//echo PdoDataAccess::GetLatestQueryString();
		echo Response::createObjectiveResponse(false, $e->getMessage());
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, '');
	die();
}

function PrepareContentToEdit($content){
	
	$dt = FRG_FormElems::Get();
	$ItemsArr = array();
	foreach($dt as $item)
		$ItemsArr[ $item["ElementID"] ] = $item["ElementTitle"];
		
	$RevContent = '';
    $arr = explode(FRG_forms::TplItemSeperator, $content);
    for ($i = 0; $i < count($arr); $i++) {
        $ElementID = $arr[$i];
        if (is_numeric($ElementID)) {
            $RevContent .= FRG_forms::TplItemSeperator . 
				$ElementID . '--' . $ItemsArr[$ElementID] . FRG_forms::TplItemSeperator;
        } else {
            $RevContent .= $ElementID;
        }
    }
	return $RevContent;
}

function CopyForm(){
	
	$FormID = $_POST["FormID"];
	
	$obj = new FRG_forms($FormID);
	$obj->FormTitle .= " (کپی)";
	unset($obj->FormID);
	$obj->Add();
	
	PdoDataAccess::runquery("insert into FRG_FormElems(FormID,ParentID,ElementTitle,ElementType,alias,
		properties,EditorProperties,ElementValues)
		select :copy,ParentID,ElementTitle,ElementType,alias,
		properties,EditorProperties,ElementValues from FRG_FormElems where FormID=:src",
			array(":src" => $FormID, ":copy" => $obj->FormID));
	
	echo Response::createObjectiveResponse(true, "");
	die();
}

//............................................

function selectFromElements(){
	
	$where = " AND IsActive='YES'";
	$where .= isset($_REQUEST["all"]) ? " AND FormID in(?,0)" : " AND FormID=?"; 
	$params = array($_REQUEST["FormID"]);
	
	if(isset($_REQUEST["ParentID"]))
	{
		$where .= " AND ParentID=?";
		$params[] = $_REQUEST["ParentID"];
	}		
		
	$dt = FRG_FormElems::Get($where, $params);
	$dt = $dt->fetchAll();
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SaveElement(){
	
	$obj = new FRG_FormElems();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	if(empty($obj->properties))
		$obj->properties = " ";
	
	if(empty($obj->ParentID))
		$obj->ParentID = 0;
	
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
	
	$obj = new FRG_GroupElems();
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
		
		FROM FRG_groups g
		left join FRG_GroupElems e on(e.ParentID=0 AND g.GroupID=e.GroupID)
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
	$dt = PdoDataAccess::runquery("select e.* from FRG_GroupElems e
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