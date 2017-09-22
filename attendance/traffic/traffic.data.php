<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 95.02
//-------------------------
include_once('../header.inc.php');
include_once inc_dataReader;
include_once inc_response;
include_once 'traffic.class.php';

$task = $_REQUEST["task"];
switch ($task) {

	default : 
		eval($task. "();");
}

function AddTraffic(){
	
	$obj = new ATN_traffic();
	$obj->TrafficDate = PDONOW;
	$obj->TrafficTime = DateModules::NowTime();
	$obj->IsSystemic = "YES";
	$obj->PersonID = $_SESSION["USER"]["PersonID"];
	$result = $obj->Add();
	
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();
}

function GetMyRequests(){
	
	$dt = ATN_requests::Get(" AND t.PersonID=?" . dataReader::makeOrder(), 
		array($_SESSION["USER"]["PersonID"]));
	$result = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData($result, $dt->rowCount(), $_GET["callback"]);
	die();
}

function GetAllRequests(){
	
	$where = "";
	$param = array();
	
	if(!empty($_REQUEST["fields"]) && !empty($_GET["query"]))
	{
		$field = $_REQUEST["fields"] == "fullname" ? "concat(p1.fname,' ',p1.lname)" : $_REQUEST["fields"];
		$where .= " AND	" . $field . " like :q";
		$param[":q"] = "%" . $_GET["query"] . "%";
	}
	
	if(isset($_REQUEST["AllReqs"]) && $_REQUEST["AllReqs"] == "false")
	{
		$where .= " AND IsArchive='NO'";
	}	
	
	$dt = ATN_requests::Get($where . dataReader::makeOrder(), $param);
	//print_r(ExceptionHandler::PopAllExceptions());	
	$result = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData($result, $dt->rowCount(), $_GET["callback"]);
	die();
}

function SaveRequest(){
	
	$obj = new ATN_requests();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	if($obj->ReqType != "CORRECT")
	{
		if(!empty($obj->ToDate))
		{
			$dt = PdoDataAccess::runquery("
				select * from ATN_requests 
				where PersonID=:p AND RequestID<>:r 
				AND ( (FromDate<=:f AND ToDate>=:f) OR (FromDate<=:t AND ToDate>=:t) )
			", array(
				":p" => $_SESSION["USER"]["PersonID"],
				":r" => $obj->RequestID,
				":f" => DateModules::shamsi_to_miladi($obj->FromDate, "-"),
				":t" => DateModules::shamsi_to_miladi($obj->ToDate, "-")
			));
			if(count($dt) > 0)
			{
				echo Response::createObjectiveResponse(false, "در بازه زمانی وارد شده قبلا درخواستی ثبت شده است");
				die();
			}
		}
		else
		{
			$dt = PdoDataAccess::runquery("
				select * from ATN_requests 
				where PersonID=:p AND RequestID<>:r 
				AND (
						(FromDate<=:f AND ToDate>=:f) OR 
						( if(ToDate is null,FromDate=:f,FromDate<=:f AND ToDate>=:f) AND 
							StartTime<=:st AND EndTime>=:st) OR 
						( if(ToDate is null,FromDate=:f,FromDate<=:f AND ToDate>=:f) AND 
							StartTime<=:et AND EndTime>= :et) )
			", array(
				":p" => $_SESSION["USER"]["PersonID"],
				":r" => $obj->RequestID,
				":f" => DateModules::shamsi_to_miladi($obj->FromDate, "-"),
				":st" => $obj->StartTime,
				":et" => $obj->EndTime
			));
			
			//echo PdoDataAccess::GetLatestQueryString();die();
			
			if(count($dt) > 0)
			{
				echo Response::createObjectiveResponse(false, "در بازه زمانی وارد شده قبلا درخواستی ثبت شده است");
				die();
			}
		}
	}
	
	if(empty($obj->RequestID))
	{
		$obj->PersonID = $_SESSION["USER"]["PersonID"];
		$obj->ReqDate = PDONOW;
		$result = $obj->Add();
	}
	else
		$result = $obj->Edit();
	
	//print_r(ExceptionHandler::PopAllExceptions());
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteRequest(){
	
	$obj = new ATN_requests($_POST["RequestID"]);

	if($obj->ReqStatus != "1")
	{
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	$result = $obj->Remove();
	//print_r(ExceptionHandler::PopAllExceptions());
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function selectOffTypes(){
	
	$dt = PdoDataAccess::runquery("select * from BaseInfo where TypeID=20 AND IsActive='YES'");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function selectMeans(){
	
	$dt = PdoDataAccess::runquery("select * from BaseInfo where TypeID=23  AND IsActive='YES'");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function ChangeStatus(){
	
	$obj = new ATN_requests($_POST["RequestID"]);
	$obj->ReqStatus = $_POST["mode"];
	$obj->SurveyPersonID = $_SESSION["USER"]["PersonID"];
	$obj->SurveyDate = PDONOW;
	$obj->SurveyDesc = $_POST["SurveyDesc"];
	$obj->ConfirmExtra = $_POST["ConfirmExtra"];
	
	if($obj->ReqType == "EXTRA")
	{
		$dt = ATN_requests::Get(" AND RequestID=?", array($obj->RequestID));
		$dt = $dt->fetch();
		$SUM = ATN_traffic::Compute($dt["FromDate"], $dt["FromDate"], $dt["PersonID"]);
		$obj->RealExtra = $SUM["extra"];
		$obj->LegalExtra = $SUM["LegalExtra"];
	}
	$result = $obj->Edit();
	
	/*if($obj->ReqType == "CORRECT")
	{
		$obj2 = new ATN_traffic();
		$obj2->IsSystemic = "NO";
		$obj2->RequestID = $obj->RequestID;
		$obj2->PersonID = $obj->PersonID;
		$obj2->TrafficDate = $obj->FromDate;
		$obj2->TrafficTime = $obj->StartTime;
		$result = $obj2->Add();
	}*/
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function ArchiveRequest(){
	
	$obj = new ATN_requests($_POST["RequestID"]);
	$obj->IsArchive = "YES";
	$result = $obj->Edit();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function SelectDayTraffics(){
	
	$dt = PdoDataAccess::runquery("
		select * from (

			select 'user' ReqType, TrafficID,TrafficDate,TrafficTime,'' EndTime,IsActive 
			from ATN_traffic where PersonID=:p AND TrafficDate=:d

			union all

			select t.ReqType,null,t.FromDate,StartTime,EndTime,'YES'
			from ATN_requests t
			where t.PersonID=:p AND t.ReqType in('CORRECT','OFF','MISSION') 
				AND t.ToDate is null AND ReqStatus=2 AND t.FromDate=:d

		)t 
		order by TrafficTime,ReqType",
		array(":p" => $_GET["PersonID"], ":d" => $_GET["TrafficDate"]));
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function DeleteTraffic(){
	
	$TrafficID = $_POST["TrafficID"];
	PdoDataAccess::runquery("update ATN_traffic set IsActive='NO' where TrafficID=?",
		array($TrafficID));
	echo Response::createObjectiveResponse(true, "");
	die();
}

function GetExtraInfo(){
	
	$RequestID = $_REQUEST["RequestID"];
	$dt = ATN_requests::Get(" AND RequestID=?", array($RequestID));
	$dt = $dt->fetch();
	$SUM = ATN_traffic::Compute($dt["FromDate"], $dt["FromDate"], $dt["PersonID"]);
	echo dataReader::getJsonData($SUM, 1, $_GET["callback"]);
	die();
}
?>
