<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 95.05
//-------------------------

require_once('../header.inc.php');
include_once inc_dataReader;
include_once inc_response;

require_once "config.inc.php";
include_once 'request.class.php';
require_once "../../office/workflow/wfm.class.php";
require_once '../../accounting/definitions.inc.php';
require_once '../../accounting/docs/import.data.php';

$task = isset($_REQUEST["task"]) ? $_REQUEST["task"] : "";
if(!empty($task))
{
	$task();
}

function SaveRequest(){
	
	$obj = new WAR_requests();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	//------------------------------------------------------
	if(isset($_SESSION["USER"]["portal"]))
	{
		$obj->PersonID = $_SESSION["USER"]["PersonID"];
		$obj->StatusID = 102;
	}
	else if(empty($obj->RequestID))
	{
		$obj->StatusID = 102;
	}
	
	//------------------------------------------------------
	if(empty($obj->RequestID))
	{
		$obj->ReqDate = PDONOW;
		$result = $obj->Add();
		if($result)
			ChangeStatus($obj->RequestID,$obj->StatusID, "", true);
	}
	else
	{
		$result = $obj->Edit();
		if($result)
			ChangeStatus($obj->RequestID,$obj->StatusID, "", true);
	}
	
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, $obj->RequestID);
	die();
}

function SelectMyRequests(){
	
	if($_SESSION["USER"]["IsCustomer"] == "YES")
		$where = "r.PersonID=" . $_SESSION["USER"]["PersonID"];
	$param = array();
	if (isset($_REQUEST['fields']) && isset($_REQUEST['query'])) {
        $field = $_REQUEST['fields'];
		$field = $field == "fullname" ? "concat_ws(' ',p1.fname,p1.lname,p1.CompanyName)" : $field;
        $where .= ' and ' . $field . ' like :fld';
        $param[':fld'] = '%' . $_REQUEST['query'] . '%';
    }
	
	$dt = WAR_requests::Get($where . dataReader::makeOrder(), $param);
	$count = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);
	//print_r(ExceptionHandler::PopAllExceptions());
	echo dataReader::getJsonData($dt, $count, $_GET["callback"]);
	die();
}

function SelectAllRequests(){
	
	$param = array();
	$where = "1=1 ";
	if(!empty($_REQUEST["RequestID"]))
	{
		$where .= " AND RequestID=:r";
		$param[":r"] = $_REQUEST["RequestID"];
	}
	$param = array();
	
	if (isset($_REQUEST['fields']) && isset($_REQUEST['query'])) {
        $field = $_REQUEST['fields'];
		$field = $field == "fullname" ? "concat_ws(' ',p1.fname,p1.lname,p1.CompanyName)" : $field;
        $where .= ' and ' . $field . ' like :fld';
        $param[':fld'] = '%' . $_REQUEST['query'] . '%';
    }
	
	
	if(!empty($_REQUEST["IsEnded"]))
	{
		$where .= " AND IsEnded = :e "; 
		$param[":e"] = $_REQUEST["IsEnded"];
	}
	
	$where .= dataReader::makeOrder();
	$dt = WAR_requests::SelectAll($where, $param);
	//print_r(ExceptionHandler::PopAllExceptions());
	//echo PdoDataAccess::GetLatestQueryString();
	$count = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);	
	echo dataReader::getJsonData($dt, $count, $_GET["callback"]);
	die();
}

function DeleteRequest(){
	
	$res = WAR_requests::Remove($_POST["RequestID"]);
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($res, !$res ? ExceptionHandler::GetExceptionsToString() : "");
	die();
}

function ChangeStatus($RequestID, $StatusID, $ActDesc = "", $LogOnly = false, $pdo = null){
	
	if(!$LogOnly)
	{
		$obj = new WAR_requests();
		$obj->RequestID = $RequestID;
		$obj->StatusID = $StatusID;
		if(!$obj->Edit($pdo))
			return false;
	}

	return WFM_FlowRows::AddOuterFlow(FLOWID, $RequestID, $StatusID, $ActDesc = "", $pdo);
}

function ChangeRequestStatus(){
	
	$result = ChangeStatus($_POST["RequestID"],$_POST["StatusID"],$_POST["StepComment"]);
	Response::createObjectiveResponse($result, "");
	die();
}

//------------------------------------------------

function GetRequestPeriods(){
	
	$RequestID = $_REQUEST["RequestID"];
	$temp = WAR_periods::Get(" AND RequestID=?", array($RequestID));
	//print_r(ExceptionHandler::PopAllExceptions());
	$temp = $temp->fetchAll();
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();	
}

function SavePeriod(){
	
	$obj = new WAR_periods();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	if(empty($obj->RowID))
		$result = $obj->Add($pdo);
	else
		$result = $obj->Edit($pdo);
	
	if(!$result)
	{
		$pdo->rollback();
		echo Response::createObjectiveResponse(false, "خطا در ثبت ردیف ");
		die();
	}
	if(!RegisterWarrantyDoc(null, $obj, $_POST["AccountTafsili"],  $pdo))
	{
		$pdo->rollback();
		echo Response::createObjectiveResponse(false, "خطا در صدور سند حسابداری");
		die();
	}
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function DeletePeriod(){
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$PayObj = new WAR_periods($_POST["RowID"]);
	if(!ReturnWarrantyDoc($PayObj, $pdo))
	{
		//print_r(ExceptionHandler::PopAllExceptions());
		//$pdo->rollBack();		
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	
	if(!WAR_periods::Remove($_POST["RowID"], $pdo))
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "خطا در حذف ردیف ");
		die();
	}
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function EditWarrantyDoc(){
	
	$obj = new WAR_periods($_POST["RowID"]);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$DocID = WAR_periods::GetAccDoc($obj->RowID);
	if($DocID == 0)
	{
		echo Response::createObjectiveResponse(false, "سند مربوطه یافت نشد");
		die();
	}
	$DocObj = new ACC_docs($DocID);
	if(!ReturnWarrantyDoc($obj, $pdo, true))
	{
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	if(!RegisterWarrentyDoc($DocObj, $obj, $_POST["AccountTafsili"],  $pdo))
	{
		$pdo->rollback();
		echo Response::createObjectiveResponse(false, "خطا در صدور سند حسابداری");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function GetWarrentyTypes(){
	
	$dt = PdoDataAccess::runquery("select * from BaseInfo where typeID=74");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

?>