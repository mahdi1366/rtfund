<?php

//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
ini_set('display_errors', 'On');
error_reporting(E_ERROR || E_NOTICE || E_WARNING);
require_once '../../header.inc.php';
require_once '../class/contract.class.php';
require_once '../class/ContractItems.class.php';
require_once '../class/TemplateItems.class.php';
require_once '../../global/CNTconfig.class.php';

$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");

switch ($task) {

    case "SelectMyContracts":
    case "SelectContracts":
    case "SaveContract":
    case "GetContractItems":
    case "SelectReceivedContracts":
    case "Send":
    case "ConfirmRecContract":

        $task();
}

function SelectMyContracts() {
    $temp = CNT_contracts::Get(" AND RegPersonId = " . $_SESSION["User"]->PersonID);
    $res = PdoDataAccess::fetchAll($temp, $_GET['start'], $_GET['limit']);
    echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
    die();
}

function SelectContracts() {
    $temp = CNT_contracts::Get(" AND c.StatusCode > " . CNTconfig::ContractStatus_Raw);
    $res = PdoDataAccess::fetchAll($temp, $_GET['start'], $_GET['limit']);
    echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
    die();
}

function SaveContract() {
    $pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
    try {
        /* creating the contract */
        if ($_POST['CntId'] > 0) {
            $mode = 'edit';
            $CntId = $_POST['CntId'];
        } else {
            $mode = 'new';
            $CntId = '';
        }
        $CntObj = new CNT_contracts($CntId);
        $CntObj->TplId = $_POST['TplId'];
        $CntObj->description = $_POST['description'];
        $CntObj->StatusCode = CNTconfig::ContractStatus_Raw; // anyway it is being edited so it is raw
        if ($mode == 'new') {
            $CntObj->RegPersonID = $_SESSION['User']->PersonID;
            $CntObj->RegDate = PDONOW;
            $CntObj->Add($pdo);
        } else
            $CntObj->Edit($pdo);

        /* removing values of contract items */
        CNT_ContractItems::RemoveAll($CntObj->CntId, $pdo);

        /* Adding the values of Contract items */
        $continue = 0;
        foreach ($_POST as $PostData => $val) {
            if (!(substr($PostData, 0, 8) == "TplItem_")) {
                continue;
            }
            $items = explode('_', $PostData);
            $TplItemId = $items[1];
            switch ($TplItemId) {
                case 1:
                    // TODO : array in CNTConfig bashad
                    $CntObj->SupplierId = $val;
                    break;
                case 2:
                    $CntObj->Supervisor = $val;
                    break;
                case 3:
                    $CntObj->StartDate = DateModules::shamsi_to_miladi($val);
                    break;
                case 4:
                    $CntObj->EndDate = DateModules::shamsi_to_miladi($val);
                    break;
                case 5:
                    $CntObj->price = $val;
                    break;
                default:
                    $CntItemsObj = new CNT_ContractItems();
                    $CntItemsObj->CntId = $CntObj->CntId;
                    $CntItemsObj->TplItemId = $TplItemId;
                    $TplItemObj = new CNT_TemplateItems($CntItemsObj->TplItemId);
                    switch ($TplItemObj->TplItemType) {
                        case "numberfield":
                        case 'textfield':
                            $CntItemsObj->ItemValue = $val;
                            break;
                        case 'shdatefield':
                            $CntItemsObj->ItemValue = DateModules::shamsi_to_miladi($val);
                            /*
                              if ($items[2] != 'DAY') {
                              $continue = 1;
                              break;
                              }
                              $day = $val;
                              $month = $_POST['TplItem_' . $items[1] . '_MONTH'];
                              $year = $_POST['TplItem_' . $items[1] . '_YEAR'];
                              if ($year < 100)
                              $year += 1300;
                              $CntItemsObj->ItemValue = DateModules::shamsi_to_miladi($year . '-' . $month . '-' . $day, '-'); */
                            break;
                        default :
                            echo Response::createObjectiveResponse(false, '');
                            die();
                    }
                    /* if ($continue == 1) {
                      $continue = 0;
                      continue;
                      } */
                    $CntItemsObj->Add($pdo);
                    break;
            }
        }
        $res = $CntObj->Edit($pdo);

        $pdo->commit();
        echo Response::createObjectiveResponse(true, $CntObj->CntId);
        die();
    } catch (Exception $e) {
        $pdo->rollBack();
        //print_r(ExceptionHandler::PopAllExceptions());
        //echo PdoDataAccess::GetLatestQueryString();
        echo Response::createObjectiveResponse(false, $e->getMessage());
        die();
    }
}

function GetContractItems() {
    $res = CNT_ContractItems::GetContractItems($_REQUEST['CntId']);
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
        $obj = new CNT_contracts($_REQUEST['CntId']);
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
        $obj = new CNT_contracts($_REQUEST['CntId']);
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

