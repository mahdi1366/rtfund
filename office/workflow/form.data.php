<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------

require_once '../header.inc.php';
require_once 'wfm.class.php';
require_once 'form.class.php';
require_once inc_dataReader;
require_once inc_response;

$task = !empty($_REQUEST["task"]) ? $_REQUEST["task"] : "";
switch($task)
{
	case "SelectForms":
	case "selectFormItems":
	case "GetEmptyFormID":
	case "SaveForm":
	case "saveFormItem":
	case "GetFormContent":
	case "GetFormTitle":
	case "deleteFormItem":
	case "deleteForm":
	case "PrepareContentToEdit":
	case "CopyForm":
	case "MoveItem":
	case "SelectValidForms":
	case "SelectMyRequests":
	case "SaveRequest":
	case "GetRequestItems":
	case "DeleteRequest":
	case "GetFormPersons":
	case "SaveFormPerson":
	case "RemoveFormPersons":
	case "SelectFormSteps":
	case "SelectAccessFromItems":
	case "ChangeFormAccess":
	case "ChangeTotalFormAccess":
		$task();
}

function SelectForms() {
    $where = " AND IsActive='YES'";
	
    $whereParams = array();
    if (!empty($_REQUEST['FormID'])) {
        $where = " AND FormID = :FormID";
        $whereParams[':FormID'] = $_REQUEST['FormID'];
    }
    $temp = WFM_forms::Get($where, $whereParams);
    $res = PdoDataAccess::fetchAll($temp, $_GET['start'], $_GET['limit']);
	
	if(!empty($_REQUEST['FormID']) && isset($_REQUEST["EditContent"]))
	{
		$obj = new WFM_forms($_REQUEST['FormID']);
		$content = $obj->FormContent;
		$res[0]["content"] = PrepareContentToEdit($content);
	}
	print_r(ExceptionHandler::PopAllExceptions());
    echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
    die();
}

function selectFormItems() {
	
	$where = "";
	$params = array();
	
	$params[":StepRowID"] = !isset($_REQUEST["StepRowID"]) ? "-1" : $_REQUEST["StepRowID"];
	
	if(!empty($_REQUEST["FormID"]))
	{
		$where .= " AND FormID in(0,:t)";
		$params[":t"] = $_REQUEST["FormID"];
	}
	
	if(!empty($_REQUEST["NotGlobal"]))
		$where .= " AND FormID >0";
	
    $temp = WFM_FormItems::Get($where . " order by if(FormID=0,1,0),ordering", $params);
	
	//echo PdoDataAccess::GetLatestQueryString();
	
	if(!empty($_REQUEST["limit"]))
		$res = PdoDataAccess::fetchAll ($temp, $_GET["start"], $_GET["limit"]);
	else
		$res = $temp->fetchAll();
	
    echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
    die();
}

function GetEmptyFormID() {
	
	$dt = PdoDataAccess::runquery("select FormID from WFM_forms where FormContent is null");
	if(count($dt) > 0)
		return $dt[0]["FormID"];
	
	$obj = new WFM_forms();
	$obj->Add();
	
	return $obj->FormID;
}

function SaveForm() {
	
    $pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
	
	$obj = new WFM_forms();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	$CorrectContent = WFM_forms::CorrectFormContentItems($_POST['FormContent']);
	$obj->FormContent = $CorrectContent;
	
	$obj->IsStaff = $obj->IsStaff ? "YES" : "NO";
	$obj->IsCustomer = $obj->IsCustomer ? "YES" : "NO";
	$obj->IsShareholder = $obj->IsShareholder ? "YES" : "NO";
	$obj->IsSupporter = $obj->IsSupporter ? "YES" : "NO";
	$obj->IsExpert = $obj->IsExpert ? "YES" : "NO";
	$obj->IsAgent = $obj->IsAgent ? "YES" : "NO";
	
	if ($_POST['FormID'] > 0) {
		$obj->FormID = $_POST['FormID'];
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
	echo Response::createObjectiveResponse(true, $obj->FormID);
	die();
}

function saveFormItem() {
	
    $pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
    try {
        $obj = new WFM_FormItems();
        PdoDataAccess::FillObjectByJsonData($obj, $_POST['record']);
        if ($obj->FormItemID > 0) {
            $obj->Edit();
        } else {
			$dt = PdoDataAccess::runquery("select ifnull(max(ordering),0) from WFM_FormItems where FormID=? AND IsActive='YES'", 
				array($obj->FormID));
			$obj->ordering = $dt[0][0]*1 + 1;
            $obj->Add($pdo);
        }
        $pdo->commit();
        echo Response::createObjectiveResponse(true, '');
        die();
    } catch (Exception $e) {
        $pdo->rollBack();
        //print_r(ExceptionHandler::PopAllExceptions());
        //echo PdoDataAccess::GetLatestQueryString();
        echo Response::createObjectiveResponse(false, $e->getMessage());
        die();
    }
}

function GetFormContent() {
	
    $obj = new WFM_forms($_POST['FormID']);
    //echo Response::createObjectiveResponse(true, $obj->FormContent);
	echo $obj->FormContent;
    die();
}

function GetFormTitle() {
    $obj = new WFM_forms($_POST['FormID']);
    echo Response::createObjectiveResponse(true, $obj->FormTitle);
    die();
}

function deleteFormItem() {

	$obj = new WFM_FormItems($_POST['FormItemID']);
	$result = $obj->Remove($pdo);
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function deleteForm() {
    $pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
	
	$obj = new WFM_forms($_POST['FormID']);
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
	
	$dt = WFM_FormItems::Get("", array(":StepRowID" => -1));
	$ItemsArr = array();
	foreach($dt as $item)
		$ItemsArr[ $item["FormItemID"] ] = $item["ItemName"];
		
	$RevContent = '';
    $arr = explode(WFM_forms::TplItemSeperator, $content);
    for ($i = 0; $i < count($arr); $i++) {
        $FormItemID = $arr[$i];
        if (is_numeric($FormItemID)) {
            $RevContent .= WFM_forms::TplItemSeperator . 
				$FormItemID . '--' . $ItemsArr[$FormItemID] . WFM_forms::TplItemSeperator;
        } else {
            $RevContent .= $FormItemID;
        }
    }
	return $RevContent;
}

function CopyForm(){
	
	$FormID = $_POST["FormID"];
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$obj = new WFM_forms($FormID);
	$obj->FormTitle .= " (کپی)";
	unset($obj->FormID);
	$obj->Add($pdo);
	
	PdoDataAccess::runquery("insert into WFM_FormItems(FormID,ItemName,ItemType)
		select :copy,ItemName,ItemType from WFM_FormItems where FormID=:src",
			array(":src" => $FormID, ":copy" => $obj->FormID), $pdo);
	
	PdoDataAccess::runquery("insert into WFM_FormAccess(FormItemID,StepRowID)
		select m2.FormItemID,a.StepRowID
		from WFM_FormItems m1
		join WFM_FormAccess a using(FormItemID)
		join WFM_FormItems m2 using(ItemType,ItemName,ordering)
		where m1.FormID=:src AND m2.FormID=:copy",
			array(":src" => $FormID, ":copy" => $obj->FormID), $pdo);
	
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function MoveItem(){
	
	$FormID = $_POST["FormID"];
	$FormItemID = $_POST["FormItemID"];
	$direction = $_POST["direction"] == "-1" ? -1 : 1;
	$revdirection = $direction == "-1" ? "+1" : "-1";
	
	$obj = new WFM_FormItems($FormItemID);
	
	PdoDataAccess::runquery("update WFM_FormItems 
			set ordering=ordering $revdirection 
			where FormID=? AND ordering=? AND IsActive='YES'",
			array($FormID, $obj->ordering*1 + $direction));
	
	PdoDataAccess::runquery("update WFM_FormItems 
		set ordering=? 
		where FormID=? AND FormItemID=? AND IsActive='YES'",
			array($obj->ordering*1 + $direction, $FormID, $FormItemID));
	echo Response::createObjectiveResponse(ExceptionHandler::GetExceptionCount() == 0, "");
	die();
}
//------------------------------------------------------------------------------

function SelectValidForms(){
	
	$dt = PdoDataAccess::runquery("
		select f.* from WFM_forms f
		left join WFM_FormPersons fp on(fp.FormID=f.FormID)
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
		where f.IsActive='YES'
		group by f.FormID
		", array(":pid" => $_SESSION["USER"]["PersonID"]));
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SelectMyRequests() {
	
	$where = " AND r.PersonID=:p";
	$params = array(":p" => $_SESSION["USER"]["PersonID"]);
	
	if(!empty($_REQUEST["RequestID"]))
	{
		$where .= " AND RequestID=:req";
		$params[":req"] = $_REQUEST["RequestID"];
	}
	
    $temp = WFM_requests::Get($where, $params, dataReader::makeOrder());
	$res = PdoDataAccess::fetchAll($temp, $_GET['start'], $_GET['limit']);
	
	for($i=0; $i < count($res);$i++)
	{
		$arr = WFM_FlowRows::GetFlowInfo($res[$i]["FlowID"], $res[$i]["RequestID"]);
		$res[$i]["IsStarted"] = $arr["IsStarted"] ? "YES" : "NO";
		$res[$i]["IsEnded"] = $arr["IsEnded"] ? "YES" : "NO";
		$res[$i]["JustStarted"] = $arr["JustStarted"] ? "YES" : "NO";
		$res[$i]["StepDesc"] = $arr["StepDesc"];
	}
	
    echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
    die();
}

function SaveRequest() {
   
	$pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
	
	$ReqObj = new WFM_requests();
	PdoDataAccess::FillObjectByArray($ReqObj, $_POST);
	$formObj = new WFM_forms($ReqObj->FormID);
	
	$ReqObj->ReqContent = $formObj->FormContent;
	
	if ($_POST["RequestID"] == "")
	{
		$ReqObj->PersonID = $_SESSION['USER']["PersonID"];
		$ReqObj->RegDate = PDONOW;
		$result = $ReqObj->Add($pdo);
	} 
	else
	{
		$result = $ReqObj->Edit($pdo);
		/* removing values of contract items */
		WFM_RequestItems::RemoveAll($ReqObj->RequestID, $pdo);
	}

	if(!$result)
	{
		$pdo->rollBack();
        print_r(ExceptionHandler::PopAllExceptions());
        //echo PdoDataAccess::GetLatestQueryString();
        echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
        die();
	}	
	
	/* Adding the values of Request items */
	foreach ($_POST as $PostData => $val) 
	{
		if(empty($val))
			continue;
		
		if (!(substr($PostData, 0, 8) == "ReqItem_"))
			continue;
		
		$items = explode('_', $PostData);
		$FormItemID = $items[1];
		
		$ReqItemsObj = new WFM_RequestItems();
		$ReqItemsObj->RequestID = $ReqObj->RequestID;
		$ReqItemsObj->FormItemID = $FormItemID;
		
		$FormItemObj = new WFM_FormItems($ReqItemsObj->FormItemID);
		switch ($FormItemObj->ItemType) {
			case 'shdatefield':
				$ReqItemsObj->ItemValue = DateModules::shamsi_to_miladi($val);
				break;
			case "checkbox":
				if(count($items) > 2 && $items[2] == "checkbox")
				{
					$val = $items[3];
				}
			default :
				$ReqItemsObj->ItemValue = $val;
		}
		$result = $ReqItemsObj->Add($pdo);
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
	echo Response::createObjectiveResponse(true, $ReqObj->RequestID);
	die();
}

function GetRequestItems() {
	
    $res = WFM_RequestItems::Get(" AND RequestID=?", array($_REQUEST['RequestID']));
    echo dataReader::getJsonData($res->fetchAll(),$res->rowCount(), $_GET["callback"]);
    die();
}

function DeleteRequest(){
	
	$pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
	
	$obj = new WFM_requests($_POST['RequestID']);
	
	$result = WFM_RequestItems::RemoveAll($obj->RequestID, $pdo);
	$result = $obj->Remove();
	if(!$result)
	{
		$pdo->rollBack();
		//print_r(ExceptionHandler::PopAllExceptions());
		//echo PdoDataAccess::GetLatestQueryString();
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	$pdo->commit();
	echo Response::createObjectiveResponse(true, '');
	die();
	
}

//-------------------------------------

function GetFormPersons(){
	
	$dt = WFM_FormPersons::Get(" AND FormID=?", array($_REQUEST["FormID"]));
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount());
	die();
}

function SaveFormPerson(){
	
	$obj = new WFM_FormPersons();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	$result = $obj->Add();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function RemoveFormPersons(){
	
	$obj = new WFM_FormPersons($_REQUEST["RowID"]);
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

//---------------------------------------

function SelectFormSteps(){
	
	$FormID = $_REQUEST["FormID"];
	
	$dt = PdoDataAccess::runquery("SELECT s.* FROM WFM_FlowSteps s join WFM_forms using(FlowID)
		where FormID=?", array($FormID));
	
	$dt = array_merge(array(array(
		"StepRowID" => 0,
		"StepDesc" => "درخواست دهنده"
	)), $dt);
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SelectAccessFromItems(){
	
	$FormID = $_REQUEST["FormID"];
	$StepRowID = $_REQUEST["StepRowID"];
	
	$dt = PdoDataAccess::runquery("
		SELECT fi.FormItemID,ItemName, if(StepRowID is null,'NO','YES') access,AccessID
		FROM WFM_FormItems fi left join WFM_FormAccess fa on(fi.FormItemID=fa.FormItemID AND fa.StepRowiD=?)
		where FormID=? order by ordering", array($StepRowID, $FormID));
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function ChangeFormAccess(){
	
	$access = $_POST["access"];
	if($access == "true")
	{
		$obj = new WFM_FormAccess();
		$obj->FormItemID = $_POST["FormItemID"];
		$obj->StepRowID = $_POST["StepRowID"];
		$result = $obj->Add();
	}
	else
	{
		$obj = new WFM_FormAccess($_POST["AccessID"]);
		$result = $obj->Remove();
	}
	echo Response::createObjectiveResponse($result, "");
	die();
}

function ChangeTotalFormAccess(){
	
	$access = $_POST["access"];
	$StepRowID = $_POST["StepRowID"];
	$FormID = $_POST["FormID"];
	
	$dt = PdoDataAccess::runquery("delete a from WFM_FormAccess a join WFM_FormItems using(FormItemID) "
				. " where FormID=? AND StepRowID=?" , array($FormID, $StepRowID));
	
	if($access == "true")
		$dt = PdoDataAccess::runquery("insert into WFM_FormAccess(FormItemID,StepRowID) 
				SELECT FormItemID,? FROM WFM_FormItems s join WFM_forms using(FormID)
				where FormID=?" , array($StepRowID, $FormID));

	echo Response::createObjectiveResponse(ExceptionHandler::GetExceptionCount() == 0, "");
	die();
}
?>
