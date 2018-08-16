<?php
//---------------------------
// developer:	Sh.Jafarkhani
// Date:		97.05
//---------------------------
require_once '../header.inc.php';
require_once 'baseinfo.class.php';
require_once 'TreeModules.class.php';
require_once(inc_response);
require_once(inc_dataReader);

$task = isset($_REQUEST['task']) ? $_REQUEST['task'] : '';

switch ($task) {
	
	case "SelectProcesses":
	case 'DeleteProcess':
	case 'SaveProcess':
	case "GetProcessTree":
	
	case 'GetAllEvents':
	case 'DeleteEvent':
	case 'SaveEvent':
	case "GetEventsTree":

	case "selectEventRows":
	case "saveEventRow":
	case "DeleteEventRow":
		
		$task();
}

function SelectProcesses(){
	
	$where = "1=1";
	$param = array();
	
	if(isset($_REQUEST["ParentID"]))
	{
		$where .= " AND p.ParentID=:pid";
		$param[":pid"] = (int)$_REQUEST["ParentID"];
	}
	
	$dt = COM_processes::SelectProcesss($where, $param,isset($_REQUEST["hasChild"]));
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();		
}

function SaveProcess() {

	$processObj = new COM_processes();
	PdoDataAccess::FillObjectByArray($processObj, $_POST);
	$processObj->IsActive = 'YES';

	if($processObj->EventID == "")
		$processObj->EventID = PDONULL;
	
	if ($_POST["old_ProcessID"] == '')
		$res = $processObj->InsertProcess();
	else
		$res = $processObj->UpdateProcess((int)$_POST["old_ProcessID"]);

	Response::createObjectiveResponse($res, $processObj->ProcessID);
	die();
}

function DeleteProcess() {

	$res = COM_processes::DeleteProcess((int)$_POST['ProcessID']);
	echo Response::createResponse($res, "");
	die();
}

function GetProcessTree() {

	$nodes = PdoDataAccess::runquery("
			select ProcessID id, concat('[',ProcessID,'] ',ProcessTitle) text, ParentID, EventID,'' EventTitle,
				ProcessID,ProcessTitle,description
			from COM_processes where IsActive='YES'
				union all 
			select concat(ProcessID,'-',EventID) id , concat('[',EventID,'] ',EventTitle) text, ProcessID ParentID, EventID,EventTitle,
				'','',''
			from COM_processes p
			join COM_events using(EventID)
			where p.IsActive='YES'");
	$returnArr = TreeModulesclass::MakeHierarchyArray($nodes);
	echo json_encode($returnArr);
	die();
}

//------------------------------------------------------------------------------

function GetAllEvents() {
	
	$where = '1=1';
	$param = array();
             if (!InputValidation::validate($_POST['query'], InputValidation::Pattern_FaEnAlphaNum, false)) {
                echo dataReader::getJsonData(array(), 0, $_GET["callback"]);
                die();
           }
        
	if(!empty($_REQUEST["query"]))
	{
		$where .= " AND ( EventTitle like :q or EventID like :q)";
		$param[":q"] = "%" . $_REQUEST["query"] . "%";
	}
	
	if(!empty($_REQUEST["EventID"]))
	{
		$where .= " AND EventID =:eid";
		$param[":eid"] =(int) $_REQUEST["EventID"];
	}
	
	if(!isset($_REQUEST["all"]))
		$where .= " AND ParentID>0";

	$list = COM_events::SelectEvents($where, $param);
	echo dataReader::getJsonData($list, count($list), $_GET['callback']);
	die();
}

function SaveEvent() {

	$eventObj = new COM_events();
	PdoDataAccess::FillObjectByArray($eventObj, $_POST);
	$eventObj->IsActive = 'YES';

	if ($_POST["old_EventID"] == '')
		$res = $eventObj->InsertEvent();
	else
		$res = $eventObj->UpdateEvent((int)$_POST["old_EventID"]);

	Response::createObjectiveResponse($res, $eventObj->EventID);
	die();
}

function DeleteEvent() {

	$res = COM_events::DeleteEvent((int)$_POST['EventID']);
	echo Response::createResponse($res, "");
	die();
}

function GetEventsTree() {

	$nodes = COM_events::SelectEvents("IsActive='YES'");

	$ref_cur_level_nodes = array();
	$returnArray = array();
	
	for ($i = 0; $i < count($nodes); $i++) {
		
		$nodes[$i]["leaf"] = "true";
		$nodes[$i]["title"] = "[" . $nodes[$i]["EventID"] . "] " . $nodes[$i]["EventTitle"];
		
		
		if($nodes[$i]["ParentID"] == "0")
		{
			$returnArray[] = $nodes[$i];			
			$ref_cur_level_nodes[$nodes[$i]['EventID']] = & $returnArray[ count($returnArray)-1 ];
		}
		else
		{
			$parent = & $ref_cur_level_nodes[ $nodes[$i]["ParentID"] ];
			if (!isset($parent["children"])) {
				$parent["children"] = array();
				$parent["leaf"] = "false";
			}
			
			$parent["children"][] = $nodes[$i];
			$ref_cur_level_nodes[$nodes[$i]["EventID"]] = & $parent["children"][ count($parent["children"])-1 ];
		}
	}

	$str = json_encode($returnArray);

	$str = str_replace('"children"', 'children', $str);
	$str = str_replace('"leaf"', 'leaf', $str);
	$str = str_replace('"title"', 'text', $str);
	$str = str_replace('"EventID"', 'id', $str);
	$str = str_replace('"EventTitle"', 'EventTitle', $str);
	$str = str_replace('"true"', 'true', $str);
	$str = str_replace('"false"', 'false', $str);
	
	echo $str;
	die();
}

//------------------------------------------------------------------------------

function selectEventRows(){

	$where = "EventID=? ";
	if(!isset($_REQUEST["AllHistory"]) || $_REQUEST["AllHistory"] == "false")
		$where .= " AND er.IsActive='YES'";
		
	$where .= " order by CostType,CostCode,IsActive desc,ChangeDate";
	
	$list = COM_EventRows::SelectAll($where, array($_REQUEST["EventID"]));
	//print_r(ExceptionHandler::PopAllExceptions());
	//echo PdoDataAccess::GetLatestQueryString();
	echo dataReader::getJsonData($list, count($list), $_GET['callback']);
	die();
}

function saveEventRow(){
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$obj = new COM_EventRows();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	//------ add new Row -------------
	if(empty($obj->RowID))
	{
		if(!$obj->InsertEventRow($pdo))
		{
			$pdo->rollBack();
			echo Response::createObjectiveResponse(false, "");
			die();
		}
		$pdo->commit();
		echo Response::createObjectiveResponse(true, "");
		die();	
	}
	//--------- edit row ------------
	unset($obj->RowID);
	//unset($obj->ChangeDesc);
	if(!$obj->InsertEventRow($pdo))
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	$obj2 = new COM_EventRows();
	$obj2->NewRowID = $obj->RowID;
	$obj2->RowID = $_POST["RowID"];
	$obj2->IsActive = 'NO';
	$obj2->ChangeDesc = $_POST["ChangeDesc"];
	if(!$obj2->UpdateEventRow($pdo))
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();	
}

function DeleteEventRow(){
	
	$obj = new COM_EventRows();
	$obj->RowID = $_POST["RowID"];
	$obj->ChangeDesc = $_POST["ChangeDesc"];
	$obj->ChangeDate = PDONOW;
	$obj->ChangePersonID = $_SESSION["USER"]["PersonID"];
	$obj->IsActive = 'NO';
	$res = $obj->UpdateEventRow();
	
	echo Response::createObjectiveResponse($res, "");
	die();
}


?>