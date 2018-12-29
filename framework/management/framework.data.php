<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	89.11
//-------------------------
require_once('../header.inc.php');
require_once 'framework.class.php';
require_once '../person/persons.class.php';
require_once inc_dataReader;
require_once inc_response;

if(!empty($_REQUEST["task"]))
	$_REQUEST["task"]();

function selectSystems(){
	
	$where = "";
	if(isset($_REQUEST["ExcludePortal"]))
	{
		$where = " AND SystemID<>1000";
	}
	
	$temp = PdoDataAccess::runquery("select * from FRW_systems where 1=1 ".$where." order by ordering");
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SaveSystem(){
	
	$obj = new FRW_systems();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	$obj2 = new FRW_systems($obj->SystemID);
	
	if($obj2->SystemID > 0)
		$result = $obj->EditSystem();
	else 
		$result = $obj->AddSystem();
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, "");
	die();
}

//--------------------------------------------------

function GellMenus(){
	$temp = FRW_Menus::GetAllMenus($_GET["SystemID"]);
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function selectMenuGroups(){
	
	$dt = PdoDataAccess::runquery("
		select g.MenuID GroupID,g.MenuDesc
		from FRW_menus g
		where g.parentID=0 AND g.SystemID=?",array($_REQUEST["SystemID"]));
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SaveMenu(){
	if(isset($_POST["record"]))
	{
		$obj = new FRW_Menus();
		PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
		
		$st = stripslashes(stripslashes($_POST["record"]));
		$data = json_decode($st);
		
		$obj->ParentID = $data->GroupID;
		
		$res = $obj->EditMenu();
	}
	else
	{
		$obj = new FRW_Menus();
		PdoDataAccess::FillObjectByArray($obj, $_POST);
		if(isset($_POST["MenuID"]) && $_POST["MenuID"] > 0)
			$res = $obj->EditMenu();
		else
			$res = $obj->AddMenu();
	}
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($res, "");
	die();
}

function DeleteMenu(){
	$dt = PdoDataAccess::runquery("
		select g.MenuID, count(m.MenuID) cnt
		from FRW_menus g 
		left join FRW_menus m on(g.MenuID=m.ParentID) 
		where g.MenuID=? group by g.MenuID"
		,array($_POST["MenuID"]));
	if(count($dt) == 0 || $dt[0]["cnt"] > 0)
	{
		echo Response::createObjectiveResponse(false, "این منو دارای زیر منو بوده و قابل حذف نمی باشد");
		die();
	}
	$res = FRW_Menus::DeleteMenu($_POST["MenuID"]);
	echo Response::createObjectiveResponse($res, "");
	die();
}

//--------------------------------------------------

function selectAccess(){
	
	$GroupID = empty($_REQUEST["GroupID"]) ? 0 : $_REQUEST["GroupID"];
	$PersonID = empty($_REQUEST["PersonID"]) ? 0 : $_REQUEST["PersonID"];
	
	$temp = FRW_access::selectAccess($_REQUEST["SystemID"], $GroupID, $PersonID);
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SavePersonAccess(){
	
	$keys = array_keys($_POST);
	$GroupID = empty($_REQUEST["GroupID"]) ? 0 : $_REQUEST["GroupID"];
	$PersonID = empty($_REQUEST["PersonID"]) ? 0 : $_REQUEST["PersonID"];

	$pdo = PdoDataAccess::getPdoObject();
	/*@var $pdo PDO*/
	$pdo->beginTransaction();
	PdoDataAccess::runquery("delete a from FRW_access a join FRW_menus using(MenuID) "
			. " where SystemID=? AND PersonID=? AND GroupID=?",
		array($_POST["SystemID"],$PersonID,$GroupID));
	
	for($i=0; $i < count($keys); $i++)
	{
		if(strpos($keys[$i],"viewChk_") === false)
			continue;
		
		$obj = new FRW_access();
		$obj->PersonID = $PersonID;
		$obj->GroupID = $GroupID;
		
		$obj->MenuID = preg_split('/_/',$keys[$i]);
		$obj->MenuID = $obj->MenuID[1];
		
		$obj->ViewFlag = isset($_POST["viewChk_" . $obj->MenuID]) ? "YES" : "NO";
		$obj->AddFlag = isset($_POST["addChk_" . $obj->MenuID]) ? "YES" : "NO";
		$obj->EditFlag = isset($_POST["editChk_" . $obj->MenuID]) ? "YES" : "NO";
		$obj->RemoveFlag = isset($_POST["removeChk_" . $obj->MenuID]) ? "YES" : "NO";
		
		if(!$obj->AddAccess())
		{
			$pdo->rollBack();	
			print_r(ExceptionHandler::PopAllExceptions());
			echo Response::createObjectiveResponse(false, "");
			die();
		}
		
	}
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

//--------------------------------------------------

function selectPersons(){
	
	$where = "IsStaff='YES'";
	$param = array();
	
	if(!empty($_REQUEST["query"]))
	{
		$where .= " AND concat(fname,' ',lname) like :p";
		$param[":p"] = "%" . $_REQUEST["query"] . "%";
	}
	
	$temp = BSC_persons::MinSelect($where, $param);
	$no = $temp->rowCount();
	//$temp = $temp->fetchAll();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function ResetPass(){
	
	$result = BSC_persons::ResetPass($_POST["PersonID"]);
	echo Response::createObjectiveResponse($result, "");
	die();
}

//--------------------------------------------------

function selectDataAudits(){

	$query = "select 
			SysName,
			IPAddress,
			concat_ws(' ',fname,lname,CompanyName) fullname , 
			MainObjectID , 
			SubObjectID, 
			ActionType , 
			ActionTime, 
			table_comment
			
		from DataAudit d
		join FRW_systems using(SystemID)
		join BSC_persons using(PersonID)
		join information_schema.TABLES on(Table_schema = 'krrtfir_rtfund' AND Table_name=d.TableName)
		
		where 1=1";
	$param = array();
	
	//------------------------------------------------------

	if(!empty($_POST["PersonID"]))
	{
		$query .= " AND d.PersonID=:p";
		$param[":p"] = $_POST["PersonID"];
	}
	if(!empty($_POST["SystemID"]))
	{
		$query .= " AND d.SystemID=:s";
		$param[":s"] = $_POST["SystemID"];
	}
	if(!empty($_POST["StartDate"]))
	{
		$query .= " AND d.ActionTime>:sd";
		$param[":sd"] = DateModules::shamsi_to_miladi($_POST["StartDate"],"-") . " 00:00:00";
	}
	if(!empty($_POST["EndDate"]))
	{
		$query .= " AND d.ActionTime<:ed";
		$param[":ed"] =DateModules::shamsi_to_miladi($_POST["EndDate"],"-") . " 23:59:59";
	}
	//------------------------------------------------------
	
	$temp = PdoDataAccess::runquery_fetchMode($query . dataReader::makeOrder(), $param);
	
	//print_r(ExceptionHandler::PopAllExceptions());
	//echo PdoDataAccess::GetLatestQueryString();
	
	$cnt = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_REQUEST["start"], $_REQUEST["limit"]);
	echo dataReader::getJsonData($temp, $cnt, $_GET["callback"]);
	die();
}

//--------------------------------------------------

function SelectCalendarEvents(){
	
	$params = array();
	$where = " AND PersonID=" . $_SESSION["USER"]["PersonID"];
	
	if(!empty($_GET["fields"]) && !empty($_GET["query"]))
	{
		$where .= " AND " . $_GET["fields"] . " like ?";
		$params [] = "%" . $_GET["query"] . "%";
	}
	
	$where .= dataReader::makeOrder();
	
	$res = FRW_CalendarEvents::Get($where,$params);	
	echo dataReader::getJsonData($res->fetchAll(), $res->rowCount(), $_GET["callback"]);
	die();
}

function saveCalendarEvent(){
	
	$obj = new FRW_CalendarEvents();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	$obj->IsAllDay = isset($obj->IsAllDay) ? $obj->IsAllDay : "NO";
	$obj->reminder = isset($obj->reminder) ? $obj->reminder : "NO";
	
	if($obj->EventID != "")
		$result = $obj->Edit();
	else
	{
		$obj->PersonID = $_SESSION["USER"]["PersonID"];
		$result = $obj->Add();
	}
	//print_r(ExceptionHandler::PopAllExceptions());
	Response::createObjectiveResponse($result, "");
	die();
}

function removeCalendarEvent(){
	
	$obj = new FRW_CalendarEvents($_POST["EventID"]);
	$result = $obj->Remove();
	Response::createObjectiveResponse($result, "");
	die();
}

function SelectTodayReminders(){
	
	$res = FRW_CalendarEvents::SelectTodayReminders();	
	echo dataReader::getJsonData($res->fetchAll(), $res->rowCount(), $_GET["callback"]);
	die();
}

function SeenReminder(){
	
	$obj = new FRW_CalendarEvents($_POST["EventID"]);
	if($obj->PersonID != $_SESSION["USER"]["PersonID"])
	{
		Response::createObjectiveResponse(false, "");
		die();
	}
	
	$obj->IsSeen = "YES";
	$result = $obj->Edit();
	Response::createObjectiveResponse($result, "");
	die();
}

//---------------------------------------------------

function SaveAccessGroup(){
	
	$obj = new FRW_AccessGroups();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	if(empty($obj->GroupID))
		$result = $obj->Add ();
	else
		$result = $obj->Edit ();
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function SelectAccessGroups(){
	
	$temp = FRW_AccessGroups::Get();
	echo dataReader::getJsonData($temp->fetchAll(), $temp->rowCount(), $_GET["callback"]);
	die();
}

function DeleteAccessGroup(){
	
	$obj = new FRW_AccessGroups($_POST["GroupID"]);
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function SelectGroupList() {
	
	$temp = FRW_AccessGroupList::SelectAll(" and GroupID=" . $_REQUEST["GroupID"]);
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SaveGroupList() {

	$obj = new FRW_AccessGroupList();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	$result = $obj->Add();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteGroupList() {
	
	$obj = new FRW_AccessGroupList();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	$result = $obj->Remove();	
	echo Response::createObjectiveResponse($result, "");
	die();
}

//---------------------------------------------------

function SelectFollowUps(){

	$dt = PdoDataAccess::runquery(" 
		select 'letter' type, 'یادداشت نامه' title, LetterID ObjectID, 
		concat_ws(' ','[',NoteTitle,']',NoteDesc) description
		from OFC_LetterNotes where PersonID=:p AND ReminderDate=substr(" . PDONOW . ",1,10) 
		
		union all
		
		select 'letter' type, 'پاسخ به نامه' title, LetterID ObjectID, 
		concat_ws(' ',LetterTitle,'[',SendComment,']')
		from OFC_send join OFC_letters using(LetterID)
		where ToPersonID=:p AND ResponseTimeout=substr(" . PDONOW . ",1,10)
		
		union all
		
		select 'letter' type, 'پیگیری نامه' title, LetterID ObjectID, 
		concat_ws(' ',LetterTitle,'[',SendComment,']')
		from OFC_send join OFC_letters using(LetterID)
		where FromPersonID=:p AND FollowUpDate=substr(" . PDONOW . ",1,10)
	
		union all
		
		select 'loan' type, 'رویداد وام' title, RequestID, EventTitle
		from LON_events 
		where FollowUpDate= substr(" . PDONOW . ",1,10) AND FollowUpPersonID=:p" 
		
	, array(":p" => $_SESSION["USER"]["PersonID"]));
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();			
}

//---------------------------------------------------

function GetPics(){
	
    $dt = FRW_pics::Get();
    echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount(), $_GET["callback"]);
    die;
}

function SavePics(){
	
    $obj = new FRW_pics();
    PdoDataAccess::FillObjectByArray($obj, $_POST);
	
    // first get the file extension
    if (isset($_FILES) && $_FILES["attachfile"] && $_FILES["attachfile"]['name'] && $_FILES["attachfile"]['name'] != "") {
        $st = preg_split("/\./", $_FILES["attachfile"]['name']);
        $extension = $st[count($st) - 1];
        $extension = strtolower($extension);
        
        if(!in_array($extension, explode(",", "png,jpg,jpeg,gif"))){
            echo Response::createObjectiveResponse(false, "نوع فایل نا معتبر است.");
            die;
        }
        $obj->FileType = $extension;
    }
	
    if(empty($obj->PicID))
		$result = $obj->Add();
    else
		$result = $obj->Edit();
	
    
    if (isset($_FILES) && $_FILES["attachfile"] && $_FILES["attachfile"]['name'] && $_FILES["attachfile"]['name'] != "") {
       
        $fileFullName = FILE_FRAMEWORK_PICS . "pic#" . $obj->PicID . "." . $obj->FileType;
        if (file_exists($fileFullName))
            unlink($fileFullName);

        move_uploaded_file($_FILES["attachfile"]["tmp_name"], $fileFullName);
    }
	
	//print_r(ExceptionHandler::PopAllExceptions());
    echo Response::createObjectiveResponse($result, "");
    die();
}

function removePics(){
    
    $obj = new FRW_pics($_POST['PicID']);
    $result = $obj->Remove();
	$fileFullName = FILE_FRAMEWORK_PICS . "pic#" . $obj->PicID . "." . $obj->FileType;
	unlink($fileFullName);
    echo Response::createObjectiveResponse($result, "");
    die();
}

//.............................................

function SelectNews(){

	$temp = FRW_news::Get();
	echo dataReader::getJsonData($temp->fetchAll(), $temp->rowCount(), $_GET["callback"]);
	die();
}

function SaveNews(){
	
	$obj = new FRW_news();
	$obj->FillObjectByArray($obj, $_POST);
	
	if(empty($obj->NewsID))
		$result = $obj->Add();
	else
		$result = $obj->Edit();
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteNew(){
	
	$obj = new FRW_news($_POST["NewsID"]);
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

?>
