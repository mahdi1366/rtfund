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

if(isset($_REQUEST["task"]))
{
	switch ($_REQUEST["task"])
	{
		case "selectSystems":
			selectSystems();
			
		case "SaveSystem":
			SaveSystem();
			
		//------------------	
			
		case "GellMenus":
			GellMenus();
		
		case "SaveMenu":
			SaveMenu();

		case "DeleteMenu":
			DeleteMenu();

		//-----------------

		case "selectAccess":
			selectAccess();
			
		case "selectPersons":
			selectPersons();
			
		case "SavePersonAccess":
			SavePersonAccess();
			
		//------------------

		case "ResetPass":
			ResetPass();
	}
}

function selectSystems(){
	$temp = PdoDataAccess::runquery("select * from FRW_systems");
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SaveSystem(){
	
	$obj = new FRW_systems();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->SystemID > 0)
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

function SaveMenu(){
	if(isset($_POST["record"]))
	{
		$obj = new FRW_Menus();
		PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
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
	
	$temp = BSC_persons::SelectAll($where, $param);
	$no = count($temp);
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function ResetPass(){
	
	$result = BSC_persons::ResetPass($_POST["PersonID"]);
	echo Response::createObjectiveResponse($result, "");
	die();
}

?>
