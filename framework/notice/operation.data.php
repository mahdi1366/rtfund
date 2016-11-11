<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 95.05
//-------------------------

require_once('../header.inc.php');
include_once inc_dataReader;
include_once inc_response;

require_once "config.inc.php";
include_once 'operation.class.php';
require_once 'email.php';

$task = isset($_REQUEST["task"]) ? $_REQUEST["task"] : "";
if(!empty($task))
{
	$task();
}

function SaveOperation(){
	
	$obj = new NTC_operations();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	
	if(empty($obj->OperationID))
	{
		$obj->OperationDate = PDONOW;
		$result = $obj->Add($pdo);
	}
	else
	{
		$result = $obj->Edit($pdo);
	}
	
	require_once("phpExcelReader.php");
	
	$data = new Spreadsheet_Excel_Reader();
    $data->setOutputEncoding('utf-8');
    $data->setRowColOffset(0);
	$data->read($_FILES["PersonFile"]["tmp_name"]);		
	
	for ($i = 0; $i < $data->sheets[0]['numRows']; $i++) 
	{
		if(!empty($data->sheets[0]['cells'][$i][0]))
		{
			$PersonID = $data->sheets[0]['cells'][$i][0];
			$dt = PdoDataAccess::runquery("select PersonID from BSC_persons where PersonID=?", array($PersonID));
			if(count($dt) > 0)
			{
				$PersonObj = new NTC_persons();
				$PersonObj->OperationID = $obj->OperationID;
				$PersonObj->PersonID = $PersonID;
				
				for($j=1; $j<count($data->sheets[0]['cells'][$i]); $j++)
					eval("\$PersonObj->col$j = '" . $data->sheets[0]['cells'][$i][$j] . "';");
				
				$PersonObj->Add($pdo);
			}
		}
	}
	
	$dt = NTC_persons::Get(" AND OperationID=?", array($obj->OperationID), $pdo);
	if($dt->rowCount() == 0)
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "در فایل ارسالی هیچ فرد معتبری یافت نشد");
		die();
	}
	$dt = $dt->fetchAll();
	foreach($dt as $row)
	{
		$context = $obj->context;
		for($i=1; $i<=10; $i++)
			$context = preg_replace ("/\[col".$i."\]/", $row["col" . $i], $context);
		
		switch($obj->SendType){
			case "SMS" :
				break;
			//------------------------------------------------------------------
			case "EMAIL" : 
				$email = $row["email"];
				if($email == "")
					continue;
				SendEmail($email, $obj->title, $context) ? "true" : "false";
				break;
			//------------------------------------------------------------------
			case "LETTER" : 
				break;
			//------------------------------------------------------------------
		}
	}
	
	$pdo->commit();
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, "");
	die();
}

function SelectOperations(){
	
	$param = array();
	$where = "";
	
	if (isset($_REQUEST['fields']) && isset($_REQUEST['query'])) {
        $field = $_REQUEST['fields'];
        $where .= ' and ' . $field . ' like :fld';
        $param[':fld'] = '%' . $_REQUEST['query'] . '%';
    }
		
	$dt = NTC_operations::Get($where . dataReader::makeOrder(), $param);
	print_r(ExceptionHandler::PopAllExceptions());
	//echo PdoDataAccess::GetLatestQueryString();
	$count = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);	
	
	echo dataReader::getJsonData($dt, $count, $_GET["callback"]);
	die();
}

function DeleteOperation(){
	
	$obj = new NTC_operations($_POST["OperationID"]);
	
	if($obj->SendType != NTC_SENDTYPE_LETTER)
	{
		echo Response::createObjectiveResponse(false, "قادر به حذف نمی باشید");
		die();
	}
	
	if(!$obj->Remove())
	{
		echo Response::createObjectiveResponse(false, "خطا در حذف");
		die();
	}

	echo Response::createObjectiveResponse(true, "");
	die();
}

function SelectPersons(){
	
	$temp = NTC_persons::Get(" AND OperationID=?", array($_REQUEST["OperationID"]));
	echo dataReader::getJsonData($temp->fetchAll(), $temp->rowCount(), $_GET["callback"]);
	die();
}
//.............................................

function SelectTemplates(){

	$where  = "";
	$param = array();
	
	if(!empty($_REQUEST["SendType"]))
	{
		$where .= " AND SendType=?";
		$param[] = $_REQUEST["SendType"];
	}
	
	$temp = NTC_templates::Get($where, $param);
	//print_r(ExceptionHandler::PopAllExceptions());
	echo dataReader::getJsonData($temp->fetchAll(), $temp->rowCount(), $_GET["callback"]);
	die();
}

function AddToTemplates(){
	
	$obj = new NTC_templates();
	$obj->FillObjectByArray($obj, $_POST);
	
	$result = $obj->Add();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function SaveTemplates(){
	
	$obj = new NTC_templates();
	$obj->FillObjectByArray($obj, $_POST);
	
	$result = $obj->Edit();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteTemplate(){
	
	$obj = new NTC_templates($_POST["TemplateID"]);
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

?>