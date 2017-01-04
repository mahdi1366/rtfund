<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	89.11
//-------------------------
include('../header.inc.php');
require_once 'framework.class.php';
require_once '../person/persons.class.php';
include_once inc_dataReader;
require_once inc_response;

if(!empty($_REQUEST["task"]))
	$_REQUEST["task"]();

function selectSystems(){
	$temp = PdoDataAccess::runquery("select * from FRW_systems");
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SaveSystem(){
	
	$obj = new FRW_systems();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	$obj2 = new FRW_systems($obj->SystemID);
	
	if($obj2->SystemID > 0)
		$result = $obj->EditSystem();
	else 
		$result = $obj->AddSystem();
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

//--------------------------------------------------

function GellMenus(){
	$temp = FRW_Menus::GetAllMenus($_GET["SystemID"]);
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function selectMenuGroups(){
	
	$dt = PdoDataAccess::runquery("
		select g.MenuID GroupID,g.MenuDesc
		from FRW_menus g
		where g.parentID=0 AND g.SystemID=?",array($_REQUEST["SystemID"]));
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SaveMenu(){
	if(isset($_POST["record"]))
	{
		$obj = new FRW_Menus();
		PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
		
		$st = stripslashes(stripslashes($_POST["record"]));
		$data = json_decode($st);
		
		$obj->ParentID = $data->GroupID;
		
		$res = $obj->EditMenu();
	}
	else
	{
		$obj = new FRW_Menus();
		PdoDataAccess::FillObjectByArray($obj, $_POST);
		if(isset($_POST["MenuID"]) && $_POST["MenuID"] > 0)
			$res = $obj->EditMenu();
		else
			$res = $obj->AddMenu();
	}
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($res, "");
	die();
}

function DeleteMenu(){
	$dt = PdoDataAccess::runquery("
		select g.MenuID, count(m.MenuID) cnt
		from FRW_menus g 
		left join FRW_menus m on(g.MenuID=m.ParentID) 
		where g.MenuID=? group by g.MenuID"
		,array($_POST["MenuID"]));
	if(count($dt) == 0 || $dt[0]["cnt"] > 0)
	{
		echo Response::createObjectiveResponse(false, "این منو دارای زیر منو بوده و قابل حذف نمی باشد");
		die();
	}
	$res = FRW_Menus::DeleteMenu($_POST["MenuID"]);
	echo Response::createObjectiveResponse($res, "");
	die();
}

//--------------------------------------------------

function selectAccess(){
	$temp = FRW_access::selectAccess($_REQUEST["SystemID"], $_REQUEST["PersonID"]);
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SavePersonAccess(){
	
	$keys = array_keys($_POST);

	$pdo = PdoDataAccess::getPdoObject();
	/*@var $pdo PDO*/
	$pdo->beginTransaction();
	PdoDataAccess::runquery("delete a from FRW_access a join FRW_menus using(MenuID) where SystemID=? AND PersonID=?",
		array($_POST["SystemID"],$_POST["PersonID"]));
	
	for($i=0; $i < count($keys); $i++)
	{
		if(strpos($keys[$i],"viewChk_") === false)
			continue;
		
		$obj = new FRW_access();
		$obj->PersonID = $_POST["PersonID"];
		
		$obj->MenuID = preg_split('/_/',$keys[$i]);
		$obj->MenuID = $obj->MenuID[1];
		
		$obj->ViewFlag = isset($_POST["viewChk_" . $obj->MenuID]) ? "YES" : "NO";
		$obj->AddFlag = isset($_POST["addChk_" . $obj->MenuID]) ? "YES" : "NO";
		$obj->EditFlag = isset($_POST["editChk_" . $obj->MenuID]) ? "YES" : "NO";
		$obj->RemoveFlag = isset($_POST["removeChk_" . $obj->MenuID]) ? "YES" : "NO";
		
		if(!$obj->AddAccess())
		{
			$pdo->rollBack();	
			//print_r(ExceptionHandler::PopAllExceptions());
			echo Response::createObjectiveResponse(false, "");
			die();
		}
		
	}
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

//--------------------------------------------------

function selectPersons(){
	
	$where = "IsStaff='YES'";
	$param = array();
	
	if(!empty($_REQUEST["query"]))
	{
		$where .= " AND concat(fname,' ',lname) like :p";
		$param[":p"] = "%" . $_REQUEST["query"] . "%";
	}
	
	$temp = BSC_persons::MinSelect($where, $param);
	$no = $temp->rowCount();
	//$temp = $temp->fetchAll();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function ResetPass(){
	
	$result = BSC_persons::ResetPass($_POST["PersonID"]);
	echo Response::createObjectiveResponse($result, "");
	die();
}

//--------------------------------------------------

function selectDataAudits(){

	$query = "select 
			SysName,
			concat_ws(' ',fname,lname,CompanyName) fullname , 
			MainObjectID , 
			SubObjectID, 
			ActionType , 
			ActionTime, 
			table_comment
			
		from DataAudit d
		join FRW_systems using(SystemID)
		join BSC_persons using(PersonID)
		join information_schema.TABLES on(Table_schema = 'krrtfir_rtfund' AND Table_name=d.TableName)
		
		where 1=1";
	$param = array();
	
	//------------------------------------------------------

	if(!empty($_POST["PersonID"]))
	{
		$query .= " AND d.PersonID=:p";
		$param[":p"] = $_POST["PersonID"];
	}
	if(!empty($_POST["SystemID"]))
	{
		$query .= " AND d.SystemID=:s";
		$param[":s"] = $_POST["SystemID"];
	}
	if(!empty($_POST["StartDate"]))
	{
		$query .= " AND d.ActionTime>:sd";
		$param[":sd"] = DateModules::shamsi_to_miladi($_POST["StartDate"],"-") . " 00:00:00";
	}
	if(!empty($_POST["EndDate"]))
	{
		$query .= " AND d.ActionTime<:ed";
		$param[":ed"] =DateModules::shamsi_to_miladi($_POST["EndDate"],"-") . " 23:59:59";
	}
	//------------------------------------------------------
	
	$temp = PdoDataAccess::runquery_fetchMode($query . dataReader::makeOrder(), $param);
	
	//print_r(ExceptionHandler::PopAllExceptions());
	//echo PdoDataAccess::GetLatestQueryString();
	
	$cnt = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_REQUEST["start"], $_REQUEST["limit"]);
	echo dataReader::getJsonData($temp, $cnt, $_GET["callback"]);
	die();
}

//--------------------------------------------------

function SelectCalenderEvents(){
	
	$where = " AND PersonID=" . $_SESSION["USER"]["PersonID"];
	$res = FRW_CalenderEvents::Get($where);	
	echo dataReader::getJsonData($res->fetchAll(), $res->rowCount(), $_GET["callback"]);
	die();
}

function saveCalenderEvent(){
	
	$obj = new FRW_CalenderEvents();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	if($obj->EventID != "")
		$result = $obj->Edit();
	else
	{
		$obj->PersonID = $_SESSION["USER"]["PersonID"];
		$result = $obj->Add();
	}
	//print_r(ExceptionHandler::PopAllExceptions());
	Response::createObjectiveResponse($result, "");
	die();
}

function removeCalenderEvent(){
	
	$obj = new FRW_CalenderEvents($_POST["EventID"]);
	$result = $obj->Remove();
	Response::createObjectiveResponse($result, "");
	die();
}
?>
