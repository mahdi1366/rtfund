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
require_once '../../accounting/docs/import.data.php';

$task = isset($_REQUEST["task"]) ? $_REQUEST["task"] : "";
if(!empty($task))
{
	$task();
}

function SaveWarrentyRequest(){
	
	$obj = new WAR_requests();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	$obj->StatusID = WAR_STEPID_RAW;
	
	if(empty($obj->RequestID))
	{
		$obj->ReqDate = PDONOW;
		$obj->RequestID = WAR_requests::LastID();
		$result = $obj->Add();
		if($result)
			WAR_requests::ChangeStatus($obj->RequestID, $obj->StatusID, "", true);
		
		$obj->RefRequestID = $obj->RequestID;
		$result = $obj->Edit();
	}
	else
	{
		$result = $obj->Edit();
	}
	
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, $obj->RequestID);
	die();
}

function SelectAllWarrentyRequests(){
	
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
		$field = $field == "fullname" ? "concat_ws(' ',fname,lname,CompanyName)" : $field;
        $where .= ' and ' . $field . ' like :fld';
        $param[':fld'] = '%' . $_REQUEST['query'] . '%';
    }
	
	
	if(!empty($_REQUEST["IsEnded"]))
	{
		$where .= " AND StatusID = :e "; 
		$param[":e"] = WAR_STEPID_END;
	}
	
	$dt = WAR_requests::SelectAll($where, $param, dataReader::makeOrder());
	//print_r(ExceptionHandler::PopAllExceptions());
	//echo PdoDataAccess::GetLatestQueryString();
	$count = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);	
	
	echo dataReader::getJsonData($dt, $count, $_GET["callback"]);
	die();
}

function DeleteWarrentyRequest(){
	
	$obj = new WAR_requests($_POST["RequestID"]);
	
	if($obj->StatusID != WAR_STEPID_RAW)
	{
		echo Response::createObjectiveResponse(false, "فرم دارای گردش بوده و قابل حذف نمی باشد");
		die();
	}
	
	if(!$obj->Remove())
	{
		echo Response::createObjectiveResponse(false, "خطا در حذف ضمانت نامه");
		die();
	}

	echo Response::createObjectiveResponse(true, "");
	die();
}

function StartWarrentyFlow(){
	
	$RequestID = $_REQUEST["RequestID"];
	$result = WFM_FlowRows::StartFlow(FLOWID, $RequestID);
	echo Response::createObjectiveResponse($result, "");
	die();
}

function RegWarrentyDoc(){
	
	$ReqObj = new WAR_requests($_POST["RequestID"]);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$DocID = RegisterWarrantyDoc($ReqObj, $_POST["CostID"],
		$_POST["TafsiliID"],$_POST["TafsiliID2"],$_POST["Block_CostID"], null, $pdo);
	if(!$DocID)
	{
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function editWarrentyDoc(){

	$ReqObj = new WAR_requests($_POST["RequestID"]);
	
	$DocID = $ReqObj->GetAccDoc();
	if($DocID == 0)
	{
		echo Response::createObjectiveResponse(true, "");
		die();
	}
	
	//...............................................
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	ReturnWarrantyDoc($ReqObj, $pdo, true);
	
	$DocID = RegisterWarrantyDoc($ReqObj, $_POST["CostID"],
		$_POST["TafsiliID"],$_POST["TafsiliID2"], $_POST["Block_CostID"], $DocID, $pdo);
	if(!$DocID)
	{
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function ReturnWarrentyDoc(){
	
	$ReqObj = new WAR_requests($_POST["RequestID"]);
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	$result = ReturnWarrantyDoc($ReqObj, $pdo);
	if($result)
		$pdo->commit();
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function EndWarrentyDoc(){
	
	$ReqObj = new WAR_requests($_POST["RequestID"]);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$result = EndWarrantyDoc($ReqObj, $pdo);
	if(!$result)
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	
	$ReqObj->StatusID = WAR_STEPID_END;
	$result = $ReqObj->Edit($pdo);
	if(!$result)
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function ExtendWarrenty(){
	
	$baseObj = new WAR_requests($_POST["RequestID"]);
			
	$obj = new WAR_requests();
	PdoDataAccess::FillObjectByObject($baseObj, $obj);
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	unset($obj->RequestID);
	$obj->RefRequestID = $baseObj->RefRequestID;
	$obj->StartDate = $baseObj->EndDate;
	$obj->StatusID = WAR_STEPID_RAW;	
	$result = $obj->Add();
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, "");
	die();	
}
//------------------------------------------------

function GetRequestPeriods(){
	
	$RequestID = $_REQUEST["RequestID"];
	$temp = PdoDataAccess::runquery("select p.*,DocID,LocalNo  from WAR_periods p
		left join ACC_DocItems on(SourceType='" . DOCTYPE_WARRENTY . "' AND SourceID=RequestID)
		left join ACC_docs using(DocID)	
		where RequestID=?", array($RequestID));
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
	
	//print_r(ExceptionHandler::PopAllExceptions());
	if(!$result)
	{
		$pdo->rollback();
		echo Response::createObjectiveResponse(false, "خطا در ثبت ردیف ");
		die();
	}

	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function DeletePeriod(){
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$Obj = new WAR_periods($_POST["PeriodID"]);
		
	if(!$Obj->Remove())
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

//------------------------------------------------

function GetCosts(){
	
	$temp = WAR_costs::Get("AND RequestID=?", array($_REQUEST["RequestID"]));
	$res = $temp->fetchAll();
	echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
	die();
}

function SaveCosts(){
	
	$obj = new WAR_costs();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if(empty($obj->CostID))
		$result = $obj->Add();
	else
		$result = $obj->Edit();

	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();
}

function DeleteCosts(){
	
	$obj = new WAR_costs();
	$obj->CostID = $_POST["CostID"];
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();	
}

?>