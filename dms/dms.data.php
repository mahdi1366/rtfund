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

	case "GetAllLoans":
		GetAllLoans();

	case "SaveLoan":
		SaveLoan();

	case "DeleteLoan":
		DeleteLoan();
}

function SelectAll(){
	
	$where = "1=1";
	$param = array();
	
	if(!empty($_REQUEST["SourceType"]))
	{
		$where .= " AND SourceType=:st";
		$param[":st"] = $_REQUEST["SourceType"];
	}
	if(!empty($_REQUEST["SourceID"]))
	{
		$where .= " AND SourceID=:sid";
		$param[":sid"] = $_REQUEST["SourceID"];
	}
	
	$temp = DMS_documents::SelectAll($where, $param);
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SaveDocument() {

	$st = preg_split("/\./", $_FILES ['FileType']['name']);
    $extension = strtolower($st [count($st) - 1]);
    if (in_array($extension, array("jpg", "jpeg", "gif", "png", "pdf")) === false) 
	{
        Response::createObjectiveResponse(false, "فرمت فایل ارسالی نامعتبر است");
        die();
    }
	//..............................................
	
	$obj = new DMS_documents();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	if (empty($_POST["DocumentID"]))
		$result = $obj->AddDocument();
	else
		$result = $obj->EditDocument();
	
	if(!$result)
	{
		echo Response::createObjectiveResponse($result, "");
		die();
	}
	
    $obj->FileType = $extension;
    $fp = fopen(getenv("DOCUMENT_ROOT") . "/storage/documents/". $obj->DocumentID . "." . $extension, "w");
    fwrite($fp, substr(fread(fopen($_FILES['FileType']['tmp_name'], 'r'), $_FILES ['FileType']['size']),200) );
    fclose($fp);
	$obj->FileContent = substr(fread(fopen($_FILES['FileType']['tmp_name'], 'r'), $_FILES ['FileType']['size']), 0, 200);
	
	$db = PdoDataAccess::getPdoObject();
	$stmt = $db->prepare("insert into DMS_documents(DocumentID, FileType, FileContent) " .
		"VALUES (:did, :ft, EMPTY_BLOB()) RETURNING PIC_DATA INTO :data");
	$stmt->bindParam(":did", $obj->DocumentID);
	$stmt->bindParam(":ft", $obj->FileType);
	$stmt->bindParam(":data", $obj->FileContent, PDO::PARAM_LOB);
	$stmt->execute();
	
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteGroup(){
	
	$dt = PdoDataAccess::runquery("select * from LON_loans where GroupID=?",array($_POST["GroupID"]));
	if(count($dt)  > 0)
	{
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	PdoDataAccess::runquery("delete from BaseInfo where TypeID=1 AND InfoID=?",array($_POST["GroupID"]));
	echo Response::createObjectiveResponse(true, "");
	die();
}

function GetAllLoans() {
	$where = " GroupID=:g";
	$whereParam = array();
	$whereParam[":g"] = $_GET["GroupID"];
	
	$field = isset($_GET ["fields"]) ? $_GET ["fields"] : "";
	if (isset($_GET ["query"]) && $_GET ["query"] != "") {
		$where .= " AND " . $field . " LIKE :qry ";
		$whereParam[":qry"] = "%" . $_GET["query"] . "%";
	}

	$temp = LON_loans::SelectAll($where, $whereParam);
	$no = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);

	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function DeleteLoan() {
	
	$LoanID = $_POST["LoanID"];
	$result = LON_loans::DeleteLoan($LoanID);
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

?>
