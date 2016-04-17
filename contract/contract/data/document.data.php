<?php

//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.10
//-----------------------------

require_once '../../header.inc.php';
require_once '../class/documents.class.php';
require_once '../../global/CNTconfig.class.php';

$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");

switch ($task) {
    case "GetAll":
    case "AddDoc":
        $task();
}

function GetAll() {
    $where = "";
    $whereParams = array();
    $temp = CNT_documents::Get($where, $whereParams);
    $res = PdoDataAccess::fetchAll($temp, $_GET['start'], $_GET['limit']);
    echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
    die();
}

function AddDoc() {
    $pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
    try {
        $obj = new CNT_documents();
        PdoDataAccess::FillObjectByArray($obj, $_POST);
        // Add Record
        if ($obj->DocId == 0) {
            $obj->RegPersonId = $_SESSION['User']->PersonID;
            $obj->RegDate = PDONOW;
            $obj->Add($pdo);
        } else {
            $obj->Edit($pdo);
        }        
        $DocId = $obj->DocId;
        //---- Add File ---- 
        //   
        //------------------
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