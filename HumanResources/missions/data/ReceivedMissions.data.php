<?php

/* -----------------------------
  //	Programmer	: Fatemipour
  //	Date		: 93.8
  ----------------------------- */
ini_set('display_errors', false);
require_once '../../header.inc.php';
require_once '../class/MissionRequest.class.php';
require_once inc_dataReader;
require_once inc_response;

$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");

switch ($task) {
    case "GetAllFinalized":
        GetAllFinalized();
        break;

    case "GetAllAccepted":
        GetAllAccepted();
        break;

    case "GetAllAdmitted":
        GetAllAdmitted();
        break;

    case "Accept":
        Accept();
        break;
case "GetReport":
        GetReport();
        break;

 case "ReturnRequest":
        ReturnRequest();
        break;
}

function GetAllFinalized() {
 
    $MissObj = new MissionRequest();
    $res = $MissObj->GetAllFinalized();
    $no = $res->RowCount();
    $res_limited = PdoDataAccess::fetchAll($res, $_GET["start"], $_GET["limit"]);
    echo dataReader::getJsonData($res_limited, $no, $_GET['callback']);
}

function Accept() {
  	
    $MissObj = new MissionRequest();
    $MissObj->RequestID = $_POST['MissRequestsID'];
  
    $res = $MissObj->Accept();
    if ($res == false)
        echo 'خطا در تایید پرداخت';
    else
        echo 'با موفقیت تایید شد';
}
function ReturnRequest() {
  	
    $MissObj = new MissionRequest();
    $MissObj->RequestID = $_POST['MissRequestsID'];
  
    $res = $MissObj->ReturnRequest();
    if ($res == false)
        echo 'خطا در برگشت درخواست';
    else
        echo 'با موفقیت برگشت داده شد';
}

function GetAllAdmitted() {


    $MissObj = new MissionRequest();
    $res = $MissObj->GetAllAdmitted();
    $no = $res->RowCount();
    $res_limited = PdoDataAccess::fetchAll($res, $_GET["start"], $_GET["limit"]);
    echo dataReader::getJsonData($res_limited, $no, $_GET['callback']);
}
function GetReport() {
    $MissObj = new MissionRequest();
    PdoDataAccess::FillObjectByArray($MissObj, $_POST);
    $res = $MissObj->GetReport();
    echo $res;
}
?>
