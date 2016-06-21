<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------

require_once '../../header.inc.php';
require_once 'contract.class.php';
require_once '../templates/templates.class.php';
require_once getenv("DOCUMENT_ROOT") . '/office/workflow/wfm.class.php';

require_once inc_dataReader;
require_once inc_response;

$task = isset($_REQUEST ["task"]) ? $_REQUEST ["task"] : "";

if(!empty($task))
	$task();

function SelectContracts() {
	
	$where = "";
	$params = array();
	
	if(!empty($_REQUEST["ContractID"]))
	{
		$where .= " AND ContractID=:c";
		$params[":c"] = $_REQUEST["ContractID"];
	}
	
    $temp = CNT_contracts::Get(isset($_REQUEST["content"]), $where, $params, dataReader::makeOrder());
	$res = PdoDataAccess::fetchAll($temp, $_GET['start'], $_GET['limit']);
	
	for($i=0; $i < count($res);$i++)
	{
		$arr = WFM_FlowRows::GetFlowInfo(2, $res[$i]["ContractID"]);
		$res[$i]["IsStarted"] = $arr["IsStarted"] ? "YES" : "NO";
		$res[$i]["IsEnded"] = $arr["IsEnded"] ? "YES" : "NO";
		$res[$i]["StepDesc"] = $arr["StepDesc"];
	}
	
    echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
    die();
}

function SaveContract() {
   
	$pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
	
	$CntObj = new CNT_contracts();
	PdoDataAccess::FillObjectByArray($CntObj, $_POST);
	
	if ($_POST["ContractID"] == "")
	{
		$CntObj->RegPersonID = $_SESSION['USER']["PersonID"];
		$CntObj->RegDate = PDONOW;
		$result = $CntObj->Add($pdo);
	} 
	else
	{
		$result = $CntObj->Edit($pdo);
		/* removing values of contract items */
		CNT_ContractItems::RemoveAll($CntObj->ContractID, $pdo);
	}

	if(!$result)
	{
		$pdo->rollBack();
        print_r(ExceptionHandler::PopAllExceptions());
        //echo PdoDataAccess::GetLatestQueryString();
        echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
        die();
	}	
	
	/* Adding the values of Contract items */
	foreach ($_POST as $PostData => $val) 
	{
		if(empty($val))
			continue;
		
		if (!(substr($PostData, 0, 8) == "TplItem_"))
			continue;
		
		$items = explode('_', $PostData);
		$TemplateItemID = $items[1];
		
		$CntItemsObj = new CNT_ContractItems();
		$CntItemsObj->ContractID = $CntObj->ContractID;
		$CntItemsObj->TemplateItemID = $TemplateItemID;
		
		$TplItemObj = new CNT_TemplateItems($CntItemsObj->TemplateItemID);
		switch ($TplItemObj->ItemType) {
			case 'shdatefield':
				$CntItemsObj->ItemValue = DateModules::shamsi_to_miladi($val);
				break;
			default :
				$CntItemsObj->ItemValue = $val;
		}
		$result = $CntItemsObj->Add($pdo);
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
	echo Response::createObjectiveResponse(true, $CntObj->ContractID);
	die();
}

function GetContractItems() {
    $res = CNT_ContractItems::GetContractItems($_REQUEST['ContractID']);
    echo dataReader::getJsonData($res, count($res), $_GET["callback"]);
    die();
}

function DeleteContract(){
	
	$pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
	
	$obj = new CNT_contracts($_POST['ContractID']);
	$result = CNT_ContractItems::RemoveAll($obj->ContractID, $pdo);
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

function SelectContractTypes() {
   
	$dt = PdoDataAccess::runquery("select * from BaseInfo where TypeID=18");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

?>
