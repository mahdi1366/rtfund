<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//-------------------------
require_once('../header.inc.php');
require_once inc_dataReader;
require_once inc_response;
require_once 'meeting.class.php';
require_once '../office/letter/letter.class.php';
require_once '../framework/person/persons.class.php';

$task = $_REQUEST["task"];
if(!empty($task)) 
	$task();

function selectMeetingTypes(){
	
	$dt = PdoDataAccess::runquery("select * from BaseInfo where typeID=".TYPEID_MeetingType.
			" AND IsActive='YES' order by InfoID");
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
	
	if(!empty($_REQUEST["MeetingType"]))
	{
		$where .= " AND MeetingType=:mt";
		$param[":mt"] = $_REQUEST["MeetingType"]*1;
	}
	
	
	if (!empty($_REQUEST['fields']) && !empty($_REQUEST['query'])) {
        $field = $_REQUEST['fields'];
		$field = $field == "fullname" ? "concat_ws(' ',fname,lname,CompanyName)" : $field;
		$field = $field == "StatusDesc" ? "b1.InfoDesc" : $field;
		$field = $field == "MeetingTypeDesc" ? "b2.InfoDesc" : $field;
		
        $where .= ' and ' . $field . ' like :fld';
        $param[':fld'] = '%' . $_REQUEST['query'] . '%';
    }
		
	$where .= dataReader::makeOrder();
	$dt = MTG_meetings::Get($where, $param);
	//print_r(ExceptionHandler::PopAllExceptions());
	//echo PdoDataAccess::GetLatestQueryString();
	$count = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);	
	echo dataReader::getJsonData($dt, $count, $_GET["callback"]);
	die();
}

function SaveMeeting(){
	$obj = new MTG_meetings();
    pdoDataAccess::FillObjectByArray($obj, $_POST);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	if ($obj->MeetingID == '')
	{
		$res = $obj->Add($pdo);
		
		PdoDataAccess::runquery("insert into MTG_MeetingPersons(MeetingID,PersonID,AttendType) 
			select ". $obj->MeetingID.", PersonID,'MEMBER' from MTG_MeetingTypePersons where MeetingType=?",
			array($obj->MeetingType), $pdo);
	}
    else
        $res = $obj->Edit($pdo);
	
	if($res)
		$pdo->commit();
	else
		$pdo->rollBack();
	//sprint_r(ExceptionHandler::PopAllExceptions());
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
	
	if($_POST["StatusID"] == MTG_STATUSID_DONE)
	{
		$dt = MTG_MeetingPersons::Get(" AND MeetingID=? AND IsPresent='NOTSET'", 
				array((int)$_POST["MeetingID"]));
		if($dt->rowCount() > 0)
		{
			echo Response::createObjectiveResponse(false, "تا زمانیکه وضعیت حضور کلیه اعضای جلسه تعیین نشده قادر به تغییر وضعیت جلسه نمی باشید.");
			die();
		}
	}
	
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
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, "");
	die();
}

//-------------------------------------

function GetMeetingAgendas(){
	
	$dt = MTG_agendas::Get(" AND a.MeetingID=? " . dataReader::makeOrder(), array($_REQUEST["MeetingID"]));
	print_r(ExceptionHandler::PopAllExceptions());
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount());
	die();
}

function SaveAgenda(){
	
	$obj = new MTG_agendas();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->MeetingID*1 > 0)
	{
		$mobj = new MTG_meetings($obj->MeetingID);
		$obj->MeetingType = $mobj->MeetingType;
	}
	
	if($obj->AgendaID*1 > 0)
		$result = $obj->Edit();
	else
		$result = $obj->Add();

	print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, "");
	die();
}

function RemoveAgenda(){
	
	$obj = new MTG_agendas($_REQUEST["AgendaID"]);
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function GetRemainAgendas(){
	
	$Mobj = new MTG_meetings((int)$_REQUEST["MeetingID"]);
	
	$dt = PdoDataAccess::runquery_fetchMode("
		select a.* ,if(mp.PersonID=0,mp.fullname,concat_ws(' ',p2.fname,p2.lname,p2.CompanyName)) fullname
			from MTG_agendas a
			join MTG_meetings m using(MeetingID)
			left join MTG_agendas a2 on(a.AgendaID=a2.RefAgendaID)
			left join MTG_MeetingPersons mp on(a.PersonRowID=mp.RowID)
			left join BSC_persons p2 on(mp.PersonID=p2.PersonID)
		where a2.AgendaID is null AND a.IsDone='NO' 
			AND m.StatusID<>".MTG_STATUSID_RAW." AND a.MeetingID<>? AND a.MeetingType=?", 
			array($Mobj->MeetingID, $Mobj->MeetingType));
	print_r(ExceptionHandler::PopAllExceptions());
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount());
	die();
}

function AddRecordToAgenda(){
	
	$Robj = new MTG_MeetingRecords((int)$_POST["RecordID"]);
	$Mobj = new MTG_meetings($Robj->MeetingID);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$obj = new MTG_agendas();
	$obj->MeetingType = $Mobj->MeetingType;
	$obj->MeetingID = $_POST["MeetingID"];
	$obj->RecordID = $Robj->RecordID;
	$obj->PresentTime = $_POST["PresentTime"];
	$obj->title = "(مصوبه جلسه " . $Robj->MeetingID . " ) " . $Robj->subject;
	
	$dt = PdoDataAccess::runquery("select * from MTG_MeetingPersons where MeetingID=? AND PersonID=?",
			array($obj->MeetingID, $Robj->PersonID));
	if(count($dt) > 0)
		$obj->PersonRowID = $dt[0]["PersonRowID"];
	else
	{
		$pobj = new MTG_MeetingPersons();
		$pobj->MeetingID = $obj->MeetingID;
		$pobj->PersonID = $Robj->PersonID;
		$pobj->AttendType = 'GUEST';
		if(!$pobj->Add($pdo))
		{
			$pdo->rollBack();
			echo Response::createObjectiveResponse(false, "خطا در ایجاد شرکت کننده");
			die();
		}
		$obj->PersonRowID = $pobj->RowID;
	}
	
	if(!$obj->Add($pdo))
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "خطا در ایجاد دستور جلسه");
		die();
	}
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function AddRemainAgendaToAgenda(){
	
	$obj = new MTG_agendas((int)$_POST["AgendaID"]);
	$pobj = new MTG_MeetingPersons($obj->PersonRowID);
	
	$obj2 = new MTG_agendas();
	PdoDataAccess::FillObjectByObject($obj, $obj2);
	unset($obj2->AgendaID);
	$obj2->MeetingID = (int)$_POST["MeetingID"];
	$obj2->RefAgendaID = $obj->AgendaID;
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$dt = PdoDataAccess::runquery("select * from MTG_MeetingPersons where MeetingID=? AND fullname=? AND PersonID=?",
			array($obj2->MeetingID, $pobj->fullname, $pobj->PersonID));
	if(count($dt) > 0)
		$obj2->PersonRowID = $dt[0]["PersonRowID"];
	else
	{
		unset($pobj->RowID);
		unset($pobj->IsPresent);
		unset($pobj->AttendType);
		$pobj->MeetingID = $obj2->MeetingID;
		if(!$pobj->Add($pdo))
		{
			$pdo->rollBack();
			echo Response::createObjectiveResponse(false, "خطا در ایجاد شرکت کننده");
			die();
		}
		$obj2->PersonRowID = $pobj->RowID;
	}
	
	if(!$obj2->Add($pdo))
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "خطا در ایجاد دستور جلسه");
		die();
	}
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function GetNotDoneAgendas(){
	
	$dt = PdoDataAccess::runquery_fetchMode("
		select a.*,m.MeetingID,m.MeetingNo,
				case when a.PersonID>0 then concat_ws(' ',p.fname,p.lname,p.CompanyName) 
				else if(mp.PersonID=0,mp.fullname,concat_ws(' ',p2.fname,p2.lname,p2.CompanyName)) end fullname
			from MTG_agendas a
			left join BSC_persons p using(PersonID)	
			left join MTG_meetings m using(MeetingID)
			left join MTG_agendas a2 on(a.AgendaID=a2.RefAgendaID)
			left join MTG_MeetingPersons mp on(a.PersonRowID=mp.RowID)
			left join BSC_persons p2 on(mp.PersonID=p2.PersonID)
		where a2.AgendaID is null AND a.IsDone=?", array($_REQUEST["IsDone"]));
	print_r(ExceptionHandler::PopAllExceptions());
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount());
	die();
}

function DoneAgenda(){
	
	$obj = new MTG_agendas((int)$_POST["AgendaID"]);
	$obj->IsDone = "YES";
	$result = $obj->Edit();
	echo Response::createObjectiveResponse($result, "");
	die();
}
//-------------------------------------

function GetMeetingRecords(){
	
	$dt = MTG_MeetingRecords::Get(" AND MeetingID=? " . dataReader::makeOrder(), array($_REQUEST["MeetingID"]));
	//print_r(ExceptionHandler::PopAllExceptions());
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

function GetDueDateRecords(){
	
	$Mobj = new MTG_meetings((int)$_REQUEST["MeetingID"]);
	
	$dt = PdoDataAccess::runquery_fetchMode("
		select r.*,concat_ws(' ',fname,lname,CompanyName) fullname
			from MTG_MeetingRecords r
			join MTG_meetings m using(MeetingID)
			left join MTG_agendas a on(a.MeetingID=? AND a.RecordID=r.RecordID)
			left join BSC_persons p on(r.PersonID=p.PersonID)
		where a.AgendaID is null AND m.MeetingType=?
			AND FollowUpDate<>'0000-00-00' AND FollowUpDate <= ?",
			array($Mobj->MeetingID, $Mobj->MeetingType, $Mobj->MeetingDate));
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount());
	die();
}

function SendRecordLetter(){
	
	$RecordID = $_POST["RecordID"];
	$recObj = new MTG_MeetingRecords($RecordID);
	$metObj = new MTG_meetings($recObj->MeetingID);
	$personsArr = json_decode($_POST["persons"]);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$LetterObj = new OFC_letters();
	$LetterObj->LetterType = "INNER";
	$LetterObj->LetterTitle = 
		$_POST["subject"] != "" ? $_POST["subject"] :
			"مصوبه جلسه " . $metObj->MeetingNo . " " . $metObj->_MeetingTypeDesc . " : " . $recObj->subject;
	$LetterObj->LetterDate = PDONOW;
	$LetterObj->RegDate = PDONOW;
	$LetterObj->PersonID = $_SESSION["USER"]["PersonID"];
	$LetterObj->context = $recObj->details;
	if(!$LetterObj->AddLetter($pdo))
	{
		echo Response::createObjectiveResponse(false, "خطا در ثبت  نامه");
		die();
	}
	//---------------------------------------	
	foreach($personsArr as $PersonID)
	{
		$personObj = new BSC_persons($PersonID);
		if($personObj->IsStaff == "YES")
		{
			$SendObj = new OFC_send();
			$SendObj->LetterID = $LetterObj->LetterID;
			$SendObj->FromPersonID = $LetterObj->PersonID;
			$SendObj->ToPersonID = $PersonID;
			$SendObj->SendDate = PDONOW;
			$SendObj->SendType = 1;
			if(!$SendObj->AddSend($pdo))
			{
				$pdo->rollBack();
				echo Response::createObjectiveResponse(false, "خطا در ارسال  نامه");
				die();
			}
		}
		else
		{
			$Cobj = new OFC_LetterCustomers();
			$Cobj->LetterID = $LetterObj->LetterID;
			$Cobj->PersonID = $PersonID;
			$Cobj->IsHide = "NO";
			$Cobj->LetterTitle = $LetterObj->LetterTitle;
			if(!$Cobj->Add($pdo))
			{
				$pdo->rollBack();
				echo Response::createObjectiveResponse(false, "خطا در ارسال  نامه");
				die();
			}
		}
	}	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function SendAgendaLetter(){
	
	$MeetingID = $_POST["MeetingID"];
	$metObj = new MTG_meetings($MeetingID);
	$personsArr = json_decode($_POST["persons"]);
	
	ob_start();
	require_once './PrintAgendas.php';
	$content = ob_get_contents();
	ob_clean();
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$LetterObj = new OFC_letters();
	$LetterObj->LetterType = "INNER";
	$LetterObj->LetterTitle = 
		$_POST["subject"] != "" ? $_POST["subject"] :
			"دعتنامه جلسه " . $metObj->MeetingNo . " " . $metObj->_MeetingTypeDesc;
	$LetterObj->LetterDate = PDONOW;
	$LetterObj->RegDate = PDONOW;
	$LetterObj->PersonID = $_SESSION["USER"]["PersonID"];
	$LetterObj->context = $content;
	if(!$LetterObj->AddLetter($pdo))
	{
		echo Response::createObjectiveResponse(false, "خطا در ثبت  نامه");
		die();
	}
	//---------------------------------------	
	foreach($personsArr as $PersonID)
	{
		$personObj = new BSC_persons($PersonID);
		if($personObj->IsStaff == "YES")
		{
			$SendObj = new OFC_send();
			$SendObj->LetterID = $LetterObj->LetterID;
			$SendObj->FromPersonID = $LetterObj->PersonID;
			$SendObj->ToPersonID = $PersonID;
			$SendObj->SendDate = PDONOW;
			$SendObj->SendType = 1;
			if(!$SendObj->AddSend($pdo))
			{
				$pdo->rollBack();
				echo Response::createObjectiveResponse(false, "خطا در ارسال  نامه");
				die();
			}
		}
		else
		{
			$Cobj = new OFC_LetterCustomers();
			$Cobj->LetterID = $LetterObj->LetterID;
			$Cobj->PersonID = $PersonID;
			$Cobj->IsHide = "NO";
			$Cobj->LetterTitle = $LetterObj->LetterTitle;
			if(!$Cobj->Add($pdo))
			{
				$pdo->rollBack();
				echo Response::createObjectiveResponse(false, "خطا در ارسال  نامه");
				die();
			}
		}
	}	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}


?>
