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
		
	case "selectDocTypeGroups":
		selectDocTypeGroups();
		
	case "selectDocTypes":
		selectDocTypes();
		
	case "selectAllParams":
		selectAllParams();
		
	case "selectParamValues":
		selectParamValues();
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
	if(!empty($_REQUEST["ObjectID2"]))
	{
		$where .= " AND ObjectID2=:oid2";
		$param[":oid2"] = $_REQUEST["ObjectID2"];
	}
	if(isset($_REQUEST["checkRegPerson"]) && $_REQUEST["checkRegPerson"] == "true")
	{
		$where .= " AND RegPersonID=" . $_SESSION["USER"]["PersonID"];
	}
	
	
	$temp = DMS_documents::SelectAll($where, $param);
	for($i=0; $i<count($temp); $i++)
	{
		$temp[$i]["paramValues"] = "";
		
		$dt = PdoDataAccess::runquery("select * from DMS_DocParamValues join DMS_DocParams using(ParamID)
			where DocumentID=?", array($temp[$i]["DocumentID"]));
		foreach($dt as $row)
		{
			$value = $row["ParamValue"];
			if($row["ParamType"] == "currencyfield")
				$value = number_format((int)$value);
			$temp[$i]["paramValues"] .= $row["ParamDesc"] . " : " . $value . "<br>";
		}
		if($temp[$i]["paramValues"] != "")
			$temp[$i]["paramValues"] = substr($temp[$i]["paramValues"], 0 , strlen($temp[$i]["paramValues"])-4);
	}
	//print_r(ExceptionHandler::PopAllExceptions());
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
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	$obj->ObjectID = $_POST["ObjectID"];
	$obj->ObjectID2 = $_POST["ObjectID2"];
	$obj->ObjectType = $_POST["ObjectType"];

	if (empty($obj->DocumentID))
		$result = $obj->AddDocument();
	else
	{
		$oldObj = new DMS_documents($obj->DocumentID);
		if($oldObj->IsConfirm == "YES")
		{
			echo Response::createObjectiveResponse(false, "");
			die();
		}
		$obj->IsConfirm = "NOTSET";
		
		$result = $obj->EditDocument();
	}
	if(!$result)
	{
		echo Response::createObjectiveResponse($result, "");
		die();
	}
	
	//-------------- params ------------------
	PdoDataAccess::runquery("delete from DMS_DocParamValues where DocumentID=?", array($obj->DocumentID));
	$arr = array_keys($_POST);
	foreach($arr as $key)
	{
		if(strpos($key, "Param") !== false)
		{
			PdoDataAccess::runquery("insert into DMS_DocParamValues values(?,?,?)",
				array($obj->DocumentID, substr($key,5), $_POST[$key] ));
		}
	}
	//----------------------------------------
	
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
	if($obj->IsConfirm == "YES")
	{
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	PdoDataAccess::runquery("delete from DMS_DocParamValues where DocumentID=?", array($DocumentID));	
	$result = DMS_documents::DeleteDocument($DocumentID);
	unlink(getenv("DOCUMENT_ROOT") . "/storage/documents/". $obj->DocumentID . "." . $obj->FileType);
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function ConfirmDocument(){
	
	$obj = new DMS_documents();
	
	$obj->DocumentID = $_REQUEST["DocumentID"];
	$obj->IsConfirm = $_POST["mode"];
	$obj->ConfirmPersonID = $_SESSION["USER"]["PersonID"];
	$obj->RejectDesc = $_POST["RejectDesc"];
	
	$result = $obj->EditDocument();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function selectDocTypeGroups(){
	
	$dt = PdoDataAccess::runquery("select * from BaseInfo where typeID=7");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function selectDocTypes(){
	
	$groupID = $_REQUEST["GroupID"];
	$dt = PdoDataAccess::runquery("select * from BaseInfo where typeID=8 AND param1=?", array($groupID));
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function selectAllParams(){
	
	$dt = PdoDataAccess::runquery("select * from DMS_DocParams");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function selectParamValues(){
	
	$dt = PdoDataAccess::runquery("select * from DMS_DocParamValues where DocumentID=?", 
			array($_GET["DocumentID"]));
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}
?>
