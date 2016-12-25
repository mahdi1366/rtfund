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
if(!empty($task))
	$task();

function SaveUnit(){
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

function DeleteUnit(){
	$res = BSC_units::RemoveUnit($_POST["UnitID"]);
	echo Response::createObjectiveResponse($res,"");
	die();
}

function GetTreeNodes(){
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
	
        if($_REQUEST['AddMode'] == "false" ){
	$posts = PdoDataAccess::runquery("select concat('p_',PersonID) id,concat_ws(' ',fname,lname,CompanyName,'[',PostName,']') text,
		'true' leaf,'user' iconCls, p.UnitID
		from BSC_persons p join BSC_posts using(PostID) order by PostName");
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

function SelectPosts(){
	
	$temp = BSC_posts::Get(" AND IsActive='YES'" . dataReader::makeOrder());
	echo dataReader::getJsonData($temp->fetchAll(), $temp->rowCount(), $_GET["callback"]);
	die();
}

function SavePost(){
	
	$obj = new BSC_posts();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->PostID > 0)
		$res = $obj->Edit();
	else
		$res = $obj->Add();

	//print_r(ExceptionHandler::PopAllExceptions()); 
	echo Response::createObjectiveResponse($res, "");
	die();
}

function DeletePost(){
	
	$obj = new BSC_posts($_POST["PostID"]);
	$res = $obj->Remove();
	echo Response::createObjectiveResponse($res,  ExceptionHandler::GetExceptionsToString());
	die();
}

//---------------------------------

function SelectBranches(){
	$temp = PdoDataAccess::runquery("select * from BSC_branches");
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

function GetAccessBranches(){
	
	$branches = FRW_access::GetAccessBranches();
	$dt = PdoDataAccess::runquery("select * from BSC_branches where BranchID in(" .
		implode(",", $branches) . ")");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
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

//.............................................

function SelectDomainNodes(){

	$dt = PdoDataAccess::runquery("
		SELECT 
			ParentID,DomainID id,DomainDesc as text,'true' as leaf, 'javascript:void(0)' href,d.*
		FROM BSC_ActDomain d order by ParentID,DomainDesc");

    $returnArray = array();
    $refArray = array();

    foreach ($dt as $row) {
        if ($row["ParentID"] == 0) {
            $returnArray[] = $row;
            $refArray[$row["id"]] = &$returnArray[count($returnArray) - 1];
            continue;
        }

        $parentNode = &$refArray[$row["ParentID"]];

        if (!isset($parentNode["children"])) {
            $parentNode["children"] = array();
            $parentNode["leaf"] = "false";
			$parentNode["href"] = "";
        }
        $lastIndex = count($parentNode["children"]);
        $parentNode["children"][$lastIndex] = $row;
        $refArray[$row["id"]] = &$parentNode["children"][$lastIndex];
    }

	echo json_encode($returnArray);
	die();
}

function SaveDomain(){
	
	$obj = new BSC_ActDomain();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	$obj->ParentID = $obj->ParentID == "src" ? "0" : $obj->ParentID;		
	
	if(empty($obj->DomainID))
		$result = $obj->AddDomain();
	else
		$result = $obj->EditDomain();

	echo Response::createObjectiveResponse($result, $result ? $obj->DomainID : "");
	die();
}

function DeleteDomain(){
	
	$DomainID = $_POST["DomainID"];
	$result = BSC_ActDomain::DeleteDomain($DomainID);
	echo Response::createObjectiveResponse($result, "");
	die();
}

//.............................................

function SelectExpertDomainNodes(){

	$dt = PdoDataAccess::runquery("
		SELECT 
			ParentID,DomainID id,DomainDesc as text,'true' as leaf, 'javascript:void(0)' href,d.*
		FROM BSC_ExpertDomain d order by ParentID,DomainDesc");

    $returnArray = array();
    $refArray = array();

    foreach ($dt as $row) {
        if ($row["ParentID"] == 0) {
            $returnArray[] = $row;
            $refArray[$row["id"]] = &$returnArray[count($returnArray) - 1];
            continue;
        }

        $parentNode = &$refArray[$row["ParentID"]];

        if (!isset($parentNode["children"])) {
            $parentNode["children"] = array();
            $parentNode["leaf"] = "false";
			$parentNode["href"] = "";
        }
        $lastIndex = count($parentNode["children"]);
        $parentNode["children"][$lastIndex] = $row;
        $refArray[$row["id"]] = &$parentNode["children"][$lastIndex];
    }

	echo json_encode($returnArray);
	die();
}

function SaveExpertDomain(){
	
	$obj = new BSC_ExpertDomain();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	$obj->ParentID = $obj->ParentID == "src" ? "0" : $obj->ParentID;		
	
	if(empty($obj->DomainID))
		$result = $obj->Add();
	else
		$result = $obj->Edit();

	echo Response::createObjectiveResponse($result, $result ? $obj->DomainID : "");
	die();
}

function DeleteExpertDomain(){
	
	$obj = new BSC_ExpertDomain();
	$obj->DomainID = $_POST["DomainID"];
	
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function SelectPersonExpertDomains(){
	
	$query = "select RowID,PersonID,d.DomainID,
		concat_ws(' ',fname,lname,CompanyName) fullname,DomainDesc
		
		from BSC_PersonExpertDomain d
		join BSC_ExpertDomain using(DomainID)
		join BSC_persons using(PersonID)
		
		where 1=1";
	$param = array();
	
	if(!empty($_REQUEST["fields"]) && !empty($_REQUEST["query"]))
	{
		if($_REQUEST["fields"] == "PersonID")
		{
			$query .= " AND concat_ws(' ',fname,lname,CompanyName) like :fl";
			$param[":fl"] = "%" . $_REQUEST["query"] . "%";
		}
		else
		{
			$query .= " AND DomainDesc like :d";
			$param[":d"] = "%" . $_REQUEST["query"] . "%";
		}
	}	
	
	$temp = PdoDataAccess::runquery_fetchMode($query, $param);
	$result = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	
	echo dataReader::getJsonData($result, $temp->rowCount(), $_GET["callback"]);
	die();
}

function SavePersonExpertDomain(){
	
	$obj = new BSC_PersonExpertDomain();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	$result = $obj->Add();
	//print_r(ExceptionHandler::PopAllExceptions());	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeletePersonExpertDomain(){
	
	$obj = new BSC_PersonExpertDomain();
	$obj->RowID = $_POST["RowID"];
	
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, "");
	die();
}

//.............................................


?>
