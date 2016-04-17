<?php

//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------

require_once '../header.inc.php';
require_once 'templates.class.php';
require_once 'TemplateItems.class.php';
require_once '../global/CNTconfig.class.php';

$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");

switch ($task) {
	
	default : 
      $task();
}

function SelectTemplates() {
    $where = '';
    $whereParams = array();
    if (!empty($_REQUEST['TplId'])) {
        $where = " AND TplId = :TplId";
        $whereParams[':TplId'] = $_REQUEST['TplId'];
    }
    $temp = CNT_templates::Get($where, $whereParams);
    $res = PdoDataAccess::fetchAll($temp, $_GET['start'], $_GET['limit']);
    echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
    die();
}

function selectTemplateItems() {
    $temp = CNT_TemplateItems::Get();
    if (isset($_REQUEST['All']) && $_REQUEST['All'] == 'true') {
        $res = PdoDataAccess::fetchAll($temp, 0, $temp->rowCount());
    } else
        $res = PdoDataAccess::fetchAll($temp, $_GET['start'], $_GET['limit']);
    echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
    die();
}

function SaveTpl() {
    $pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
    try {
        $CorrectContent = CNT_templates::CorrectTplContentItems($_POST['TplContent']);
        $obj = new CNT_templates();
        $obj->TplContent = $CorrectContent;
        $obj->TplTitle = $_POST['TplTitle'];
        if ($_POST['TplId'] > 0) {
            $obj->TplId = $_POST['TplId'];
            $obj->Edit($pdo);
        } else {
            $obj->StatusCode = CNTconfig::TemplateStatus_Raw;
            $obj->Add($pdo);
        }
        $pdo->commit();
        echo Response::createObjectiveResponse(true, $obj->TplId);
        die();
    } catch (Exception $e) {
        $pdo->rollBack();
        //print_r(ExceptionHandler::PopAllExceptions());
        //echo PdoDataAccess::GetLatestQueryString();
        echo Response::createObjectiveResponse(false, $e->getMessage());
        die();
    }
}

function saveTplItem() {
    $pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
    try {
        $obj = new CNT_TemplateItems();
        PdoDataAccess::FillObjectByJsonData($obj, $_POST['record']);
        if ($obj->TplItemId > 0) {
            $obj->Edit();
        } else {
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

function GetTplContent() {
    $obj = new CNT_templates($_POST['TplId']);
    echo Response::createObjectiveResponse(true, $obj->TplContent);
    die();
}

function GetTplTitle() {
    $obj = new CNT_templates($_POST['TplId']);
    echo Response::createObjectiveResponse(true, $obj->TplTitle);
    die();
}

function GetTplContentToEdit() {
    $obj = new CNT_templates($_POST['TplId']);
    $content = $obj->TplContent;
    $RevContent = '';
    $arr = explode(CNTconfig::TplItemSeperator, $content);
    for ($i = 0; $i < count($arr); $i++) {
        $val = $arr[$i];
        if (is_numeric($val)) {
            $obj = new CNT_TemplateItems($val);
            $RevContent .= CNTconfig::TplItemSeperator . $val . '--' . $obj->TplItemName . CNTconfig::TplItemSeperator;

            unset($obj);
        } else {
            $RevContent .= $val;
        }
    }
    echo Response::createObjectiveResponse(true, $RevContent);
    die();
}

function deleteTplItem() {
    $pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
    try {
        $obj = new CNT_TemplateItems($_POST['TplItemId']);
        $obj->Remove($pdo);
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

function deleteTpl() {
    $pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
    try {
        $obj = new CNT_templates($_POST['TplId']);
        $obj->Remove();
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

//------------------------------------------------------------------------------
?>
