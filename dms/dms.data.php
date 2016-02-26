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
		
	//.......................................
	
	case "SaveDocType":
		SaveDocType();
		
	case "DeleteDocType":
		DeleteDocType();
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

	foreach($_FILES as $file)
	{
		if(!empty($file["tmp_name"]))
		{
			$st = preg_split("/\./", $file["name"]);
			$extension = strtolower($st [count($st) - 1]);
			if (in_array($extension, array("jpg", "jpeg", "gif", "png", "pdf")) === false) 
			{
				Response::createObjectiveResponse(false, "فرمت فایل ارسالی نامعتبر است");
				die();
			}
		}
	}
	//..............................................
	
	$obj = new DMS_documents();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	$obj->ObjectID = $_POST["ObjectID"];
	$obj->ObjectID2 = isset($_POST["ObjectID2"]) ? $_POST["ObjectID2"] : "0";
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
	foreach($_FILES as $name => $file)
	{
		if(empty($file["tmp_name"]))
			continue;
		
		$obj2 = new DMS_DocFiles();
		$obj2->DocumentID = $obj->DocumentID;
		$obj2->PageNo = str_replace("FileType_", "", $name);
		$obj2->PageNo = $obj2->PageNo*1 == 0 ? 1 : $obj2->PageNo;
		$obj2->FileType = $extension;
		$obj2->FileContent = substr(fread(fopen($file['tmp_name'], 'r'), $file['size']), 0, 200);
		$obj2->AddPage();
		
		$fp = fopen(getenv("DOCUMENT_ROOT") . "/storage/documents/". $obj2->RowID . "." . $extension, "w");
		fwrite($fp, substr(fread(fopen($file['tmp_name'], 'r'), $file['size']),200) );
		fclose($fp);
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
	
	$dt = PdoDataAccess::runquery("select * from BaseInfo where typeID=7 AND IsActive='YES'");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function selectDocTypes(){
	
	$groupID = $_REQUEST["GroupID"];
	$dt = PdoDataAccess::runquery("select * from BaseInfo 
		where typeID=8 AND IsActive='YES' AND param1=?", array($groupID));
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

//.................................................

function SaveDocType(){
	
	$st = stripslashes(stripslashes($_POST["record"]));
	$data = json_decode($st);

	if($data->InfoID*1 == 0)
	{
		$pdo = PdoDataAccess::getPdoObject();
		$pdo->beginTransaction();
	
		$data->InfoID = PdoDataAccess::GetLastID("BaseInfo", "InfoID", "TypeID=?", array($data->TypeID), $pdo);
		$data->InfoID = $data->InfoID*1 + 1;
		
		PdoDataAccess::runquery("insert into BaseInfo(TypeID,InfoID,InfoDesc,param1) values(?,?,?,?)",
			array($data->TypeID, $data->InfoID, $data->InfoDesc, $data->param1), $pdo);
		
		$pdo->commit();
	}
	else
		PdoDataAccess::runquery("update BaseInfo set InfoDesc=? where TypeID=? AND InfoID=?",
			array($data->InfoDesc, $data->TypeID, $data->InfoID));	

	echo Response::createObjectiveResponse(ExceptionHandler::GetExceptionCount() == 0, "");
	die();
}

function DeleteDocType(){
	
	PdoDataAccess::runquery("update BaseInfo set IsActive='NO' 
		where TypeID=? AND InfoID=?",array($_REQUEST["TypeID"], $_REQUEST["InfoID"]));

	echo Response::createObjectiveResponse(ExceptionHandler::GetExceptionCount() == 0, "");
	die();
}

?>
