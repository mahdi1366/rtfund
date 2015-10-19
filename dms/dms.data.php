<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//-------------------------
include_once('header.inc.php');
include_once inc_dataReader;
include_once inc_response;
require_once 'dms.class.php';

$task = $_REQUEST["task"];
switch ($task) {

	case "SelectAll":
		SelectAll();

	case "SaveDocument":
		SaveDocument();

	case "DeleteDocument":
		DeleteDocument();	

	case "ConfirmDocument":
		ConfirmDocument();
}

function SelectAll(){
	
	$where = "1=1";
	$param = array();
	
	if(!empty($_REQUEST["ObjectType"]))
	{
		$where .= " AND ObjectType=:st";
		$param[":st"] = $_REQUEST["ObjectType"];
	}
	if(!empty($_REQUEST["ObjectID"]))
	{
		$where .= " AND ObjectID=:sid";
		$param[":sid"] = $_REQUEST["ObjectID"];
	}
	
	$temp = DMS_documents::SelectAll($where, $param);
	print_r(ExceptionHandler::PopAllExceptions());
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SaveDocument() {

	if(!empty($_FILES["FileType"]["tmp_name"]))
	{
		$st = preg_split("/\./", $_FILES ['FileType']['name']);
		$extension = strtolower($st [count($st) - 1]);
		if (in_array($extension, array("jpg", "jpeg", "gif", "png", "pdf")) === false) 
		{
			Response::createObjectiveResponse(false, "فرمت فایل ارسالی نامعتبر است");
			die();
		}
	}	
	//..............................................
	
	$obj = new DMS_documents();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	$obj->ObjectID = $_POST["ObjectID"];
	$obj->ObjectType = $_POST["ObjectType"];

	if (empty($obj->DocumentID))
		$result = $obj->AddDocument();
	else
		$result = $obj->EditDocument();
	if(!$result)
	{
		echo Response::createObjectiveResponse($result, "");
		die();
	}
	
	if(!empty($_FILES["FileType"]["tmp_name"]))
	{
	
		$fp = fopen(getenv("DOCUMENT_ROOT") . "/storage/documents/". $obj->DocumentID . "." . $extension, "w");
		fwrite($fp, substr(fread(fopen($_FILES['FileType']['tmp_name'], 'r'), $_FILES ['FileType']['size']),200) );
		fclose($fp);
		$obj->FileContent = substr(fread(fopen($_FILES['FileType']['tmp_name'], 'r'), $_FILES ['FileType']['size']), 0, 200);

		$db = PdoDataAccess::getPdoObject();
		$stmt = $db->prepare("update DMS_documents set FileType = :ft, FileContent = :data where DocumentID=:did");
		$stmt->bindParam(":did", $obj->DocumentID);
		$stmt->bindParam(":ft", $extension);
		$stmt->bindParam(":data", $obj->FileContent, PDO::PARAM_LOB);
		$stmt->execute();
	}
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteDocument() {
	
	$DocumentID = $_POST["DocumentID"];
	
	$obj = new DMS_documents($DocumentID);
	$result = DMS_documents::DeleteDocument($DocumentID);
	unlink(getenv("DOCUMENT_ROOT") . "/storage/documents/". $obj->DocumentID . "." . $obj->FileType);
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function ConfirmDocument(){
	
	$obj = new DMS_documents();
	
	$obj->DocumentID = $_REQUEST["DocumentID"];
	$obj->IsConfirm = "YES";
	$obj->ConfirmPersonID = $_SESSION["USER"]["PersonID"];
	
	$result = $obj->EditDocument();
	echo Response::createObjectiveResponse($result, "");
	die();
}
?>
