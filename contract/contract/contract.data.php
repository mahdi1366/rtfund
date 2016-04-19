<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once '../header.inc.php';
require_once 'contract.class.php';
require_once '../templates/templates.class.php';
require_once '../global/CNTconfig.class.php';

require_once inc_dataReader;
require_once inc_response;

$task = isset($_REQUEST ["task"]) ? $_REQUEST ["task"] : "";

if(!empty($task))
	$task();


function SelectMyContracts() {
	
    $temp = CNT_contracts::Get();
    $res = PdoDataAccess::fetchAll($temp, $_GET['start'], $_GET['limit']);
    echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
    die();
}

function SelectContracts() {
	
    $temp = CNT_contracts::Get();
    $res = PdoDataAccess::fetchAll($temp, $_GET['start'], $_GET['limit']);
    echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
    die();
}

function SaveContract() {
    $pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
	/* creating the contract */
	if ($_POST['CntID'] > 0) {
		$mode = 'edit';
		$CntID = $_POST['CntID'];
	} else {
		$mode = 'new';
		$CntID = '';
	}
	$CntObj = new CNT_contracts($CntID);
	$CntObj->TemplateID = $_POST['TemplateID'];
	$CntObj->description = $_POST['description'];
	$CntObj->StatusCode = CNTconfig::ContractStatus_Raw; // anyway it is being edited so it is raw
	if ($mode == 'new') {
		$CntObj->RegPersonID = $_SESSION['USER']["PersonID"];
		$CntObj->RegDate = PDONOW;
		$CntObj->Add($pdo);
	} else
		$CntObj->Edit($pdo);

	/* removing values of contract items */
	CNT_ContractItems::RemoveAll($CntObj->CntID, $pdo);

	/* Adding the values of Contract items */
	foreach ($_POST as $PostData => $val) {
		if (!(substr($PostData, 0, 8) == "TplItem_")) {
			continue;
		}
		$items = explode('_', $PostData);
		$TplItemID = $items[1];
		switch ($TplItemID) {
			/*case 1:
				// TODO : array in CNTConfig bashad
				$CntObj->SupplierID = $val;
				break;
			case 2:
				$CntObj->Supervisor = $val;
				break;
			case 3:
				$CntObj->StartDate = DateModules::shamsi_to_miladi($val);
				break;
			case 4:
				$CntObj->EndDate = DateModules::shamsi_to_miladi($val);
				break;*/
			case 5:
				$CntObj->price = $val;
				break;
			default:
				$CntItemsObj = new CNT_ContractItems();
				$CntItemsObj->CntID = $CntObj->CntID;
				$CntItemsObj->TplItemID = $TplItemID;
				$TplItemObj = new CNT_TemplateItems($CntItemsObj->TplItemID);
				switch ($TplItemObj->TplItemType) {
					case "numberfield":
					case 'textfield':
						$CntItemsObj->ItemValue = $val;
						break;
					case 'shdatefield':
						$CntItemsObj->ItemValue = DateModules::shamsi_to_miladi($val);
						break;
					default :
						echo Response::createObjectiveResponse(false, '');
						die();
				}
				$CntItemsObj->Add($pdo);
				break;
		}
	}
	if($CntObj->CntID != "")
		$res = $CntObj->Edit($pdo);
	else
		$res = $CntObj->Add($pdo);

	if(!$res)
	{
		$pdo->rollBack();
        print_r(ExceptionHandler::PopAllExceptions());
        //echo PdoDataAccess::GetLatestQueryString();
        echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
        die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, $CntObj->CntID);
	die();
}

function GetContractItems() {
    $res = CNT_ContractItems::GetContractItems($_REQUEST['CntID']);
    echo dataReader::getJsonData($res, count($res), $_GET["callback"]);
    die();
}

function SelectReceivedContracts() {
    $temp = CNT_contracts::Get(" AND c.StatusCode = " . CNTconfig::ContractStatus_Sent);
    print_r(ExceptionHandler::PopAllExceptions());
    $res = PdoDataAccess::fetchAll($temp, $_GET['start'], $_GET['limit']);
    echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
    die();
}

function Send() {
    $pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
    try {
        $obj = new CNT_contracts($_REQUEST['CntID']);
        $obj->StatusCode = CNTconfig::ContractStatus_Sent;
        $obj->Edit($pdo);
        //
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

function ConfirmRecContract() {
    $pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
    try {
        $obj = new CNT_contracts($_REQUEST['CntID']);
        $obj->StatusCode = CNTconfig::ContractStatus_Confirmed;
        $obj->Edit($pdo);
        //
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
?>

