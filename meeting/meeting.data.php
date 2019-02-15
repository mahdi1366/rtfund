<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//-------------------------
require_once('../header.inc.php');
require_once inc_dataReader;
require_once inc_response;
require_once 'meeting.class.php';

$task = $_REQUEST["task"];
if(!empty($task)) 
	$task();

function selectMeetingTypes(){
	
	$dt = PdoDataAccess::runquery("select * from BaseInfo where typeID=".TYPEID_MeetingType." AND IsActive='YES'");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SaveMeetingType(){
	
	$st = stripslashes(stripslashes($_POST["record"]));
	$data = json_decode($st);
	$data->TypeID = TYPEID_MeetingType;
	
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

function DeleteMeetingType(){
	
	PdoDataAccess::runquery("update BaseInfo set IsActive='NO' 
		where TypeID=? AND InfoID=?",array(TYPEID_MeetingType, $_REQUEST["InfoID"]));

	echo Response::createObjectiveResponse(ExceptionHandler::GetExceptionCount() == 0, "");
	die();
}

//-------------------------------------

function GetMeetingTypePersons(){
	
	$dt = MTG_MeetingTypePersons::Get(" AND MeetingType=?", array($_REQUEST["MeetingType"]));
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount());
	die();
}

function SaveMeetingTypePerson(){
	
	$obj = new MTG_MeetingTypePersons();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	$result = $obj->Add();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function RemoveMeetingTypePersons(){
	
	$obj = new MTG_MeetingTypePersons($_REQUEST["RowID"]);
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

//-------------------------------------

function SelectAllMeetings(){
	
	$param = array();
	$where = "";
	
	if(!empty($_REQUEST["MeetingID"]))
	{
		$where .= " AND MeetingID=:m";
		$param[":m"] = $_REQUEST["MeetingID"]*1;
	}
	
	if (isset($_REQUEST['fields']) && isset($_REQUEST['query'])) {
        $field = $_REQUEST['fields'];
		$field = $field == "fullname" ? "concat_ws(' ',fname,lname,CompanyName)" : $field;
		$field = $field == "StatusDesc" ? "b1.InfoDesc" : $field;
		$field = $field == "MeetingTypeDesc" ? "b2.InfoDesc" : $field;
		
        $where .= ' and ' . $field . ' like :fld';
        $param[':fld'] = '%' . $_REQUEST['query'] . '%';
    }
		
	$where .= dataReader::makeOrder();
	$dt = MTG_meetings::Get($where, $param);
	print_r(ExceptionHandler::PopAllExceptions());
	//echo PdoDataAccess::GetLatestQueryString();
	$count = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);	
	echo dataReader::getJsonData($dt, $count, $_GET["callback"]);
	die();
}

function SaveMeeting(){
	$obj = new MTG_meetings();
    pdoDataAccess::FillObjectByArray($obj, $_POST);
	
	if ($obj->MeetingID == '')
	{
		$res = $obj->Add();
		
		PdoDataAccess::runquery("insert into MTG_MeetingPersons(MeetingID,PersonID,AttendType) 
			select ". $obj->MeetingID.", PersonID,'MEMBER' from MTG_MeetingTypePersons where MeetingType=?",
			array($obj->MeetingType));
	}
    else
        $res = $obj->Edit();
	
	Response::createObjectiveResponse($res, $res ? $obj->MeetingID : "");
	die();
	
}

function DeleteMeeting(){
	
	$obj = new MTG_meetings($_REQUEST["MeetingID"]);
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function ChangeMeetingStatus(){
	
	$obj = new MTG_meetings((int)$_POST["MeetingID"]);
	$obj->StatusID = $_POST["StatusID"];
	$result = $obj->Edit();
	echo Response::createObjectiveResponse($result, "");
	die();
}
//-------------------------------------

function GetMeetingPersons(){
	
	$dt = MTG_MeetingPersons::Get(" AND MeetingID=? " . dataReader::makeOrder(), array($_REQUEST["MeetingID"]));
	print_r(ExceptionHandler::PopAllExceptions());
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount());
	die();
}

function SaveMeetingPerson(){
	
	$obj = new MTG_MeetingPersons();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	$result = $obj->Add();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function RemoveMeetingPersons(){
	
	$obj = new MTG_MeetingPersons($_REQUEST["RowID"]);
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function SetPresent(){
	
	$obj = new MTG_MeetingPersons($_REQUEST["RowID"]);
	$obj->IsPresent = $_POST["IsPresent"];
	$result = $obj->Edit();
	echo Response::createObjectiveResponse($result, "");
	die();
}

//-------------------------------------

function GetMeetingAgendas(){
	
	$dt = MTG_MeetingAgendas::Get(" AND ma.MeetingID=? " . dataReader::makeOrder(), array($_REQUEST["MeetingID"]));
	print_r(ExceptionHandler::PopAllExceptions());
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount());
	die();
}

function SaveMeetingAgenda(){
	
	$obj = new MTG_agendas();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->AgendaID*1 > 0)
		$result = $obj->Edit();
	else
	{
		$result = $obj->Add();
		if($result)
		{
			$st = stripslashes(stripslashes($_POST["record"]));
			$data = json_decode($st);

			$obj2 = new MTG_MeetingAgendas();
			$obj2->MeetingID = $data->MeetingID;
			$obj2->AgendaID = $obj->AgendaID;
			$obj2->Add();
		}
	}
	echo Response::createObjectiveResponse($result, "");
	die();
}

function RemoveAgenda(){
	
	$obj = new MTG_Agendas($_REQUEST["AgendaID"]);
	$result = $obj->Remove();
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

//-------------------------------------

function GetMeetingRecords(){
	
	$dt = MTG_MeetingRecords::Get(" AND MeetingID=? " . dataReader::makeOrder(), array($_REQUEST["MeetingID"]));
	print_r(ExceptionHandler::PopAllExceptions());
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount());
	die();
}

function SaveMeetingRecord(){
	
	$obj = new MTG_MeetingRecords();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	if($obj->RecordID*1 > 0)
		$result = $obj->Edit();
	else 
		$result = $obj->Add();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function RemoveMeetingRecords(){
	
	$obj = new MTG_MeetingRecords($_REQUEST["RecordID"]);
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}


?>
