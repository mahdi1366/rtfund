<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.07
//---------------------------
require_once '../header.inc.php';
require_once 'vote.class.php';
require_once inc_dataReader;
require_once inc_response;

ini_set("display_errors", "On");

$task = isset($_REQUEST['task']) ? $_REQUEST['task'] : '';
switch($task)
{
	case "SelectAllForms":
	case "SaveForm":
	case "DeleteForm":
	case "selectFormItems":
	case "CopyForm":
	case "SelectItems":
	case "SaveItem":
	case "DeleteItem":
	case "MoveItem":
	case "SelectGroups":
	case "SaveGroup":
	case "DeleteGroup":
	case "MoveGroup":
	case "SelectMyFilledForms":
	case "SelectNewVoteForms":
	case "SaveFilledForm":
	case "FilledItemsValues":
	case "SelectFilledForms":
	case "SelectChart1Data":
	case "GetFormPersons":
	case "SaveFormPerson":
	case "RemoveFormPersons":
		$task();   
}
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
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();
}

function selectFormItems(){
	
	$dt = PdoDataAccess::runquery("select * from VOT_FormItems 
		where FormID=?", array($_GET["FormID"]));
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function CopyForm(){
	
	$FormID = $_POST["FormID"];
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$obj = new VOT_forms($FormID);
	$obj->FormTitle .= " (کپی)";
	unset($obj->FormID);
	$obj->Add($pdo);
	
	PdoDataAccess::runquery("insert into VOT_FormGroups
		(FormID,GroupDesc,GroupWeight,CopyGroupID,ordering)
		select :copy,GroupDesc,GroupWeight,GroupID,ordering 
		from VOT_FormGroups where FormID=:src",
			array(":src" => $FormID, ":copy" => $obj->FormID), $pdo);
	
	PdoDataAccess::runquery("insert into VOT_FormItems
		(FormID,GroupID,ItemType,ItemTitle,ItemValues,ordering,
				weight,ValueWeights)
		select :copy,g.GroupID,ItemType,ItemTitle,ItemValues,i.ordering,
				weight,ValueWeights
		from VOT_FormItems i join VOT_FormGroups g on(i.GroupID=g.CopyGroupID) 
		where i.FormID=:src",
			array(":src" => $FormID, ":copy" => $obj->FormID), $pdo);
	
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
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
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
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
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
	
	$dt = PdoDataAccess::runquery("
		select f.* from VOT_forms f
		left join VOT_FilledForms ff on(ff.FormID=f.FormID AND ff.PersonID=:pid)
		left join VOT_FormPersons fp on(fp.FormID=f.FormID)
		join BSC_persons p on(
			case when fp.FormID is not null then fp.PersonID=:pid AND p.PersonID=fp.PersonID
			else
				p.PersonID=:pid AND ( 
					if(f.IsStaff='YES',f.IsStaff=p.IsStaff,1=0) OR
					if(f.IsCustomer='YES',f.IsCustomer=p.IsCustomer,1=0) OR
					if(f.IsShareholder='YES',f.IsShareholder=p.IsShareholder,1=0) OR
					if(f.IsAgent='YES',f.IsAgent=p.IsAgent,1=0) OR
					if(f.IsSupporter='YES',f.IsSupporter=p.IsSupporter,1=0) OR
					if(f.IsExpert='YES',f.IsExpert=p.IsExpert,1=0) ) 
			end)
		where ff.FormID is null
		group by f.FormID", array(":pid" => $_SESSION["USER"]["PersonID"]));
	
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
	
	$dt = PdoDataAccess::runquery("
		select  fi.*,f.FilledValue,GroupWeight
		from VOT_FilledItems f join VOT_FormItems fi using(ItemID)
			join VOT_FormGroups using(GroupID)
			join BSC_persons using(PersonID)
		
		where f.FormID=? AND fi.GroupID=? AND ItemValues<>'' AND locate('#',ItemValues) >0
		order by ordering", array($FormID, $GroupID));
	
	$ReturnDate = array();
	$currentItemID = 0;
	$valuesArr = array();
	$factor = 0;
	$total = 0;
	$totalCount = 0;
	$ItemWeight = 1;
	$GroupTotal = 0;
	$GroupTotalWeight = 0;
	for($i=0; $i< count($dt); $i++)
	{
		$row = $dt[$i];
		
		if($currentItemID != $row["ItemID"])
		{
			$currentItemID = $row["ItemID"];
			$valuesArr = preg_split('/#/', $row["ItemValues"]);
			$weightsArr = preg_split('/#/', $row["ValueWeights"]);
			$factor = 100/(count($valuesArr)-1);
			$total = 0;
			$totalCount = 0;
			$ItemWeight = $row["weight"]*1;
		}
		
		$index = array_search($row["FilledValue"], $valuesArr);
		$weight = isset($weightsArr[$index]) ? $weightsArr[$index] : 1;
		$total += $weight*(100 - $index*$factor);
		$totalCount += $weight;
		
		if($i+1 == count($dt) || $dt[$i+1]["ItemID"] != $currentItemID)
		{
			$ReturnDate[] = array(
				"GroupID" => $row["GroupID"], 
				"GroupWeight" => $row["GroupWeight"], 
				"ItemTitle" => $row["ItemTitle"], 
				"ordering" =>  $row["ordering"], 
				"mid" => round($total/$totalCount));
			
			$GroupTotal += $ItemWeight*round($total/$totalCount);
			$GroupTotalWeight += $ItemWeight;
		}		
	}
	
	$GroupTotalWeight = $GroupTotalWeight == 0 ? 1 : $GroupTotalWeight;
	
	echo dataReader::getJsonData($ReturnDate, count($ReturnDate), $_GET["callback"], 
			round($GroupTotal/$GroupTotalWeight));
	die();
	
}

//-------------------------------------

function GetFormPersons(){
	
	$dt = VOT_FormPersons::Get(" AND FormID=?", array($_REQUEST["FormID"]));
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount());
	die();
}

function SaveFormPerson(){
	
	$obj = new VOT_FormPersons();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	$result = $obj->Add();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function RemoveFormPersons(){
	
	$obj = new VOT_FormPersons($_REQUEST["RowID"]);
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

?>