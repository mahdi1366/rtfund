<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.07
//---------------------------
require_once '../header.inc.php';
require_once 'vote.class.php';
require_once inc_dataReader;
require_once inc_response;

$task = isset($_POST["task"]) ? $_POST["task"] : (isset($_GET["task"]) ? $_GET["task"] : "");
if(!empty($task))
	$task();

function SelectAllForms(){
	
	$dt = VOT_forms::Get();
	$no = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData($dt, $no, $_GET["callback"]);
	die();
}

function SaveForm(){
	
	$obj = new VOT_forms();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	$obj->IsStaff = $obj->IsStaff ? "YES" : "NO";
	$obj->IsCustomer = $obj->IsCustomer ? "YES" : "NO";
	$obj->IsShareholder = $obj->IsShareholder ? "YES" : "NO";
	$obj->IsSupporter = $obj->IsSupporter ? "YES" : "NO";
	$obj->IsExpert = $obj->IsExpert ? "YES" : "NO";
	$obj->IsAgent = $obj->IsAgent ? "YES" : "NO";
	
	if($obj->FormID > 0)
		$result = $obj->Edit();
	else
		$result = $obj->Add();
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteForm(){
	
	$obj = new VOT_forms($_POST["FormID"]);
	$result =  $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function selectFormItems(){
	
	$dt = PdoDataAccess::runquery("select * from VOT_FormItems 
		where FormID=?", array($_GET["FormID"]));
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

//............................

function SelectItems(){
	
	$dt = VOT_FormItems::Get(" AND f.FormID=? order by g.ordering,f.ordering", array($_GET["FormID"]));
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount(), $_GET["callback"]);
	die();
}

function SaveItem(){
	
	$obj = new VOT_FormItems();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->ItemID > 0)
		$result = $obj->Edit();
	else
	{
		$dt = PdoDataAccess::runquery("select ifnull(max(ordering),0) 
			from VOT_FormItems where FormID=?",	array($obj->FormID));
		$obj->ordering = $dt[0][0]*1 + 1;
		
		$result = $obj->Add();
	}
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteItem(){
	
	$obj = new VOT_FormItems($_POST["ItemID"]);
	$result =  $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function MoveItem(){
	
	$FormID = $_POST["FormID"];
	$ItemID = $_POST["ItemID"];
	$ordering = $_POST["ordering"];
	$direction = $_POST["direction"];
	
	$direction = $direction == "-1" ? "-1" : "+1";
	
	PdoDataAccess::runquery("update VOT_FormItems 
		set ordering=ordering $direction
		where FormID=? AND ItemID=?",
			array($FormID, $ItemID));
		
	PdoDataAccess::runquery("update VOT_FormItems 
			set ordering=? 
			where FormID=? AND ItemID<>? AND ordering=? ",
			array($ordering, $FormID, $ItemID, $ordering*1 + $direction*1));
	
	echo Response::createObjectiveResponse(true, "");
	die();
}

//----------------------------------

function SelectGroups(){
	
	$dt = VOT_FormGroups::Get(" AND FormID=? order by ordering", array($_GET["FormID"]));
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount(), $_GET["callback"]);
	die();
}

function SaveGroup(){
	
	$obj = new VOT_FormGroups();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->GroupID > 0)
		$result = $obj->Edit();
	else
	{
		$dt = PdoDataAccess::runquery("select ifnull(max(ordering),0) 
			from VOT_FormGroups where FormID=?", array($obj->FormID));
		$obj->ordering = $dt[0][0]*1 + 1;
		
		$result = $obj->Add();
	}
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteGroup(){
	
	$obj = new VOT_FormGroups($_POST["GroupID"]);
	$result =  $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function MoveGroup(){
	
	$FormID = $_POST["FormID"];
	$GroupID = $_POST["GroupID"];
	$ordering = $_POST["ordering"];
	$direction = $_POST["direction"];
	
	$direction = $direction == "-1" ? "-1" : "+1";
	
	PdoDataAccess::runquery("update VOT_FormGroups 
		set ordering=ordering $direction
		where FormID=? AND GroupID=?",
			array($FormID, $GroupID));
		
	PdoDataAccess::runquery("update VOT_FormGroups 
			set ordering=? 
			where FormID=? AND GroupID<>? AND ordering=? ",
			array($ordering, $FormID, $GroupID, $ordering*1 + $direction*1));
	
	echo Response::createObjectiveResponse(true, "");
	die();
}

//----------------------------------

function SelectMyFilledForms(){
	
	$dt = PdoDataAccess::runquery("select * from VOT_FilledForms join VOT_forms using(FormID) 
		where PersonID=" . $_SESSION["USER"]["PersonID"]);
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SelectNewVoteForms(){
	
	$dt = PdoDataAccess::runquery("select f.* from VOT_forms f
		left join VOT_FilledForms ff on(ff.FormID=f.FormID AND ff.PersonID=:pid)
		join BSC_persons p on(p.PersonID=:pid AND (f.IsStaff=p.IsStaff OR f.IsCustomer=p.IsCustomer OR
			f.IsShareholder=p.IsShareholder OR f.IsAgent=p.IsAgent OR f.IsSupporter=p.IsSupporter OR 
			f.IsExpert=p.IsExpert))
		where ff.FormID is null", array(":pid" => $_SESSION["USER"]["PersonID"]));
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SaveFilledForm(){
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	PdoDataAccess::runquery("insert into VOT_FilledForms values(?,?," . PDONOW . ")",array(
		$_POST["FormID"],
		$_SESSION["USER"]["PersonID"]
	), $pdo);
	
	$arr = array_keys($_POST);
	for($i=0; $i < count($arr); $i++)
	{
		if(strpos($arr[$i], "elem_") === false)
			continue;
		
		$ItemID = str_replace("elem_", "", $arr[$i]);
		$value = $_POST[ $arr[$i] ];
		
		PdoDataAccess::runquery("insert into VOT_FilledItems values(?,?,?,?)",
			array(
				$_POST["FormID"],
				$_SESSION["USER"]["PersonID"],
				$ItemID,
				$value
			), $pdo);
	}
	
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function FilledItemsValues(){
	
	if(isset($_SESSION["USER"]["framework"]) && !empty($_REQUEST["PersonID"]))
		$PersonID = $_REQUEST["PersonID"];
	else
		$PersonID = $_SESSION["USER"]["PersonID"];
		
	$dt = PdoDataAccess::runquery("select * 
		from VOT_FormItems f join VOT_FormGroups g using(GroupID)
		left join VOT_FilledItems i on(f.ItemID=i.ItemID AND i.PersonID=?)
		where f.FormID=? order by g.ordering,f.ordering", array($PersonID,$_GET["FormID"]));
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SelectFilledForms(){
	
	$dt = PdoDataAccess::runquery("select f.*, concat_ws(' ',fname,lname,CompanyName) fullname 
		from VOT_FilledForms f join VOT_forms using(FormID) 
			join BSC_persons using(PersonID)
		where FormID=?", array($_GET["FormID"]));
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

//-------------------------------------

function SelectChart1Data(){
	
	$FormID = $_GET["FormID"];
	$GroupID = $_GET["GroupID"];
	
	$dt = PdoDataAccess::runquery("select ItemID,fi.ordering,ItemTitle,ItemValues,ItemValue,count(*) cnt
		from VOT_FilledItems f join VOT_FormItems fi using(ItemID)
			join BSC_persons using(PersonID)
		
		where f.FormID=? AND fi.GroupID=? AND ItemValues<>'' AND locate('#',ItemValues) >0
		
		group by ItemID,ItemValue", array($FormID, $GroupID));
	
	$ReturnDate = array();
	$currentItemID = 0;
	$valuesArr = array();
	$factor = 0;
	$total = 0;
	$totalCount = 0;
	for($i=0; $i< count($dt); $i++)
	{
		$row = $dt[$i];
		
		if($currentItemID != $row["ItemID"])
		{
			$currentItemID = $row["ItemID"];
			$valuesArr = preg_split('/#/', $row["ItemValues"]);
			$factor = 100/(count($valuesArr)-1);
			$total = 0;
			$totalCount = 0;
		}
		
		$total += 100 - array_search($row["ItemValue"], $valuesArr)*$factor;
		$totalCount++;
		
		if($i+1 == count($dt) || $dt[$i+1]["ItemID"] != $currentItemID)
		{
			$ReturnDate[] = array(
				"ItemTitle" => $row["ItemTitle"], 
				"ordering" =>  $row["ordering"], 
				"mid" => round($total/$totalCount));
		}		
	}
	
	echo dataReader::getJsonData($ReturnDate, count($ReturnDate), $_GET["callback"]);
	die();
	
}

?>