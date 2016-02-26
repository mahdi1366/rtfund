<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.06
//---------------------------
require_once '../header.inc.php';
require_once 'baseInfo.class.php';
require_once inc_response;
require_once inc_dataReader;

$task = isset($_POST["task"]) ? $_POST["task"] : (isset($_GET["task"]) ? $_GET["task"] : "");

switch ($task)
{
	case "SaveUnit":
		SaveUnit();
		
	case "DeleteUnit":
		DeleteUnit();
		
	case "MoveUnit":
		MoveUnit();
		
	case "GetTreeNodes":
		GetTreeNodes();
		
	//--------------------------- 
		
	case "SavePost":
		SavePost();
		
	case "DeletePost":
		DeletePost();
		
	//---------------------------
		
	case "SelectBranches":
		SelectBranches();
		
	case "SaveBranch":
		SaveBranch();
		
	case "DeleteBranch":
		DeleteBranch();
		
	//----------------------------
		
	case "SelectUserBranches":
		SelectUserBranches();
		
	case "SaveBranchAccess":
		SaveBranchAccess();
		
	case "DeleteBranchAccess":
		DeleteBranchAccess();
		
	//------------------------------
		
	case "SelectBaseTypes":
		SelectBaseTypes();
		
	case "SelectBaseInfo":
		SelectBaseInfo();
		
	case "SaveBaseInfo":
		SaveBaseInfo();
		
	case "DeleteBaseInfo":
		DeleteBaseInfo();
}

function SaveUnit()
{
	$obj = new BSC_units();
	PdoDataAccess::FillObjectByArray($obj, $_POST);

	if($_POST["ParentID"] == "source")
		unset($obj->ParentID);
	else
		$obj->ParentID = $_POST["ParentID"];
	
	if($obj->UnitID > 0)
		$res = $obj->EditUnit();
	else
		$res = $obj->AddUnit();

	echo Response::createObjectiveResponse($res, $obj->UnitID);
	die();
}

function DeleteUnit()
{
	$res = BSC_units::RemoveUnit($_POST["UnitID"]);
	echo Response::createObjectiveResponse($res,"");
	die();
}

function GetTreeNodes()
{
	$nodes = PdoDataAccess::runquery("
		select UnitID as id,UnitName as text, 
		case when ParentID is null then 0 else ParentID end ParentID, 'true' as leaf
		from BSC_units order by ParentID,UnitName");
	
	$returnArray = array();
    $refArray = array();

    foreach ($nodes as $row) {
        if ($row["ParentID"] == 0) {
            $returnArray[] = $row;
            $refArray[$row["id"]] = &$returnArray[count($returnArray) - 1];
            continue;
        }

        $parentNode = &$refArray[$row["ParentID"]];

        if (!isset($parentNode["children"])) {
            $parentNode["children"] = array();
            $parentNode["leaf"] = "false";
        }
        $lastIndex = count($parentNode["children"]);
        $parentNode["children"][$lastIndex] = $row;
        $refArray[$row["id"]] = &$parentNode["children"][$lastIndex];
    }
	
	$posts = PdoDataAccess::runquery("select concat('p_',PostID) id,PostName text,PostID, 'true' leaf,'user' iconCls, UnitID 
		from BSC_posts order by PostName");
	foreach($posts as $post)
	{
		$parentNode = &$refArray[ $post["UnitID"] ];

        if (!isset($parentNode["children"])) {
            $parentNode["children"] = array();
            $parentNode["leaf"] = "false";
        }
		$lastIndex = count($parentNode["children"]);
        $parentNode["children"][$lastIndex] = $post;
	}

	$str = json_encode($returnArray);

    $str = str_replace('"children"', 'children', $str);
    $str = str_replace('"leaf"', 'leaf', $str);
	$str = str_replace('"iconCls"', 'iconCls', $str);
    $str = str_replace('"text"', 'text', $str);
    $str = str_replace('"id"', 'id', $str);
    $str = str_replace('"true"', 'true', $str);
    $str = str_replace('"false"', 'false', $str);

    echo $str;
    die();
}

//---------------------------------

function SavePost()
{
	
	$obj = new BSC_posts();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	$obj->PostID = $obj->PostID != "" ? substr($obj->PostID, 2) : 0; 
	
	if($obj->PostID > 0)
		$res = $obj->EditPost();
	else
		$res = $obj->AddPost();

	//print_r(ExceptionHandler::PopAllExceptions()); 
	echo Response::createObjectiveResponse($res, $obj->PostID);
	die();
}

function DeletePost()
{
	$PostID = substr($_POST["PostID"], 2);
	$res = BSC_posts::RemovePost($PostID);
	echo Response::createObjectiveResponse($res,"");
	die();
}

//---------------------------------

function SelectBranches(){
	$temp = PdoDataAccess::runquery("select * from BSC_branches where IsActive='YES'");
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SaveBranch(){
	
	$obj = new BSC_branches();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->BranchID > 0)
		$result = $obj->EditBranch();
	else 
		$result = $obj->AddBranch();
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteBranch(){
	
	$result = BSC_branches::RemoveBranch($_POST["BranchID"]);
	echo Response::createObjectiveResponse($result, "");
	die();
}

//---------------------------------

function SelectUserBranches(){
	
	$temp = PdoDataAccess::runquery("
		select b.*,concat(fname, ' ', lname) fullname, BranchName 
		from BSC_BranchAccess b 
		join BSC_persons using(PersonID)
		join BSC_branches using(BranchID)");
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SaveBranchAccess(){
	
	PdoDataAccess::runquery("insert into BSC_BranchAccess values(?,?,'NO')", array($_POST["PersonID"],$_POST["BranchID"]));
	echo Response::createObjectiveResponse(true, "");
	die();
}

function DeleteBranchAccess(){
	
	PdoDataAccess::runquery("delete from BSC_BranchAccess where PersonID=? AND BranchID=?", array($_POST["PersonID"],$_POST["BranchID"]));
	echo Response::createObjectiveResponse(true, "");
	die();
}

//---------------------------------
function SelectBaseTypes(){
	
	$temp = PdoDataAccess::runquery("select * from BaseTypes where editable='YES'");
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SelectBaseInfo(){
	
	$temp = PdoDataAccess::runquery("select * from BaseInfo where typeID=? AND IsActive='YES'",
		array($_REQUEST["TypeID"]));

	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SaveBaseInfo(){
	
	$st = stripslashes(stripslashes($_POST["record"]));
	$data = json_decode($st);

	if($data->InfoID*1 == 0)
	{
		$pdo = PdoDataAccess::getPdoObject();
		$pdo->beginTransaction();
	
		$data->InfoID = PdoDataAccess::GetLastID("BaseInfo", "InfoID", "TypeID=?", array($data->TypeID), $pdo);
		$data->InfoID = $data->InfoID*1 + 1;
		
		PdoDataAccess::runquery("insert into BaseInfo(TypeID,InfoID,InfoDesc) values(?,?,?)",
			array($data->TypeID, $data->InfoID, $data->InfoDesc), $pdo);
		
		$pdo->commit();
	}
	else
		PdoDataAccess::runquery("update BaseInfo set InfoDesc=? where typeID=? AND InfoID=?",
			array($data->InfoDesc, $data->TypeID, $data->InfoID));	

	echo Response::createObjectiveResponse(ExceptionHandler::GetExceptionCount() == 0, "");
	die();
}

function DeleteBaseInfo(){
	
	PdoDataAccess::runquery("update BaseInfo set IsActive='NO' 
		where TypeID=? AND InfoID=?",array($_REQUEST["TypeID"], $_REQUEST["InfoID"]));

	echo Response::createObjectiveResponse(ExceptionHandler::GetExceptionCount() == 0, "");
	die();
}

?>
