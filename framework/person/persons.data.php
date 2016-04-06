<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.7
//-------------------------
include('../header.inc.php');
require_once 'persons.class.php';
require_once '../PasswordHash.php';

include_once inc_dataReader;
require_once inc_response;

if(isset($_REQUEST["task"]))
{
	switch ($_REQUEST["task"])
	{
		case "SavePerson":
			SavePerson();
			
		case "DeletePerson":
			DeletePerson();
			
		case "ResetPass":
			ResetPass();
			
		case "selectPersons":
			selectPersons();
			
		case "selectPendingPersons":
			selectPendingPersons();
			
		case "selectPosts":
			selectPosts();
			
		case "changePass":
			changePass();
			
		case "ConfirmPersons":
			ConfirmPersons();
			
		//--------------------------------
			
		case "selectCompanyTypes":
			selectCompanyTypes();
			
		//--------------------------------
			
		case "SelectSigners":
			SelectSigners();

		case "SaveSigner":
			SaveSigner();

		case "DeleteSigner":
			DeleteSigner();
			
		//--------------------------------
			
		case "SelectLicenses":
			SelectLicenses();

		case "SaveLicense":
			SaveLicense();

		case "DeleteLicense":
			DeleteLicense();
			
		case "ConfirmLicense":
			ConfirmLicense();
			
		//---------------------------------
			
		case "selectCities":
			selectCities();
	}
}

function selectPersons(){
	
	$where = "p.IsActive in ('YES','PENDING')";
	$param = array();
	
	if(!empty($_REQUEST["UserType"]))
	{
		switch($_REQUEST["UserType"])
		{
			case "IsAgent":		$where .= " AND IsAgent='YES'";break;
			case "IsCustomer":	$where .= " AND IsCustomer='YES'";break;
			case "IsStaff":		$where .= " AND IsStaff='YES'";break;
			case "IsSupporter":	$where .= " AND IsSupporter='YES'";break;
		}
	}
	
	if(!empty($_REQUEST["UserTypes"]))
	{
		$arr = preg_split("/,/", $_REQUEST["UserTypes"]);
		$where .= " AND ( 1=0 ";
		foreach($arr as $r)
			$where .= " OR $r='YES'";
		$where .= ")";
	}
	
	if (isset($_REQUEST['fields']) && isset($_REQUEST['query'])) 
	{
        $field = $_REQUEST['fields'];
		$field = $_REQUEST['fields'] == "fullname" ? "concat_ws(' ',fname,lname,CompanyName)" : $field;
        $where .= ' and ' . $field . ' like :fld';
		$_REQUEST['query'] = $_REQUEST['query'] == "*" ? "YES" : $_REQUEST['query'];
        $param[':fld'] = '%' . $_REQUEST['query'] . '%';
    }
		
	if(!empty($_REQUEST["PersonID"]))
	{
		$where .= " AND PersonID=:p";
		$param[":p"] = $_REQUEST["PersonID"];
	}
	
	if(!empty($_REQUEST["query"]) && !isset($_REQUEST['fields']))
	{
		$where .= " AND ( concat(fname,' ',lname) like :p or CompanyName like :p)";
		$param[":p"] = "%" . $_REQUEST["query"] . "%";
	}
	
	$temp = BSC_persons::SelectAll($where . dataReader::makeOrder(), $param);
	$no = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function selectPendingPersons(){
	
	$temp = BSC_persons::SelectAll("p.IsActive='PENDING'");
	$no = $temp->rowCount();
	echo dataReader::getJsonData($temp->fetchAll(), $no, $_GET["callback"]);
	die();
}

function SavePerson(){
	
	$obj = new BSC_persons();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	if(isset($_SESSION["USER"]["portal"]))
		$obj->PersonID = $_SESSION["USER"]["PersonID"];
	
	if(empty($obj->PersonID))
	{
		$hash_cost_log2 = 8;	
		$hasher = new PasswordHash($hash_cost_log2, true);
		$obj->UserPass = $hasher->HashPassword(md5("123456"));
	}
	
	if(isset($_SESSION["USER"]["framework"]))
	{
		$obj->IsAgent = !isset($_POST["IsAgent"]) ? "NO" : "YES";
		$obj->IsCustomer = !isset($_POST["IsCustomer"]) ? "NO" : "YES";
		$obj->IsStaff = !isset($_POST["IsStaff"]) ? "NO" : "YES";
		$obj->IsShareholder = !isset($_POST["IsShareholder"]) ? "NO" : "YES";
		$obj->IsSupporter = !isset($_POST["IsSupporter"]) ? "NO" : "YES";
	}
	if($obj->PersonID > 0)
		$result = $obj->EditPerson();
	else 
		$result = $obj->AddPerson();
	 
	echo Response::createObjectiveResponse($result, !$result ? ExceptionHandler::GetExceptionsToString() : "");
	die();
}

function DeletePerson(){
	
	$result = BSC_persons::DeletePerson($_POST["PersonID"]);
	echo Response::createObjectiveResponse($result, "");
	die();
}

function ResetPass(){
	
	$result = BSC_persons::ResetPass($_POST["PersonID"]);
	echo Response::createObjectiveResponse($result, "");
	die();
}

function selectPosts(){
	
	$temp = PdoDataAccess::runquery("select * from BSC_posts order by PostName");
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function changePass(){
	
	$dt = PdoDataAccess::runquery("select * from BSC_persons where PersonID=:p",
			array(":p" => $_SESSION['USER']["PersonID"]));
	if(count($dt) == 0)
	{ 		
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	$hash_cost_log2 = 8;	
	$hasher = new PasswordHash($hash_cost_log2, true);
	if (!$hasher->CheckPassword($_POST["cur_pass"], $dt[0]["UserPass"])) {

		echo Response::createObjectiveResponse(false, "CurPassError");
		die();
	}

	PdoDataAccess::RUNQUERY("update BSC_persons set UserPass=? where PersonID=?",
		array($hasher->HashPassword($_POST["new_pass"]), $_SESSION["USER"]["PersonID"]));
								
	if( ExceptionHandler::GetExceptionCount() != 0 )
	{				
		echo Response::createObjectiveResponse(false, "CurPassError");
		die();
	}	
	echo Response::createObjectiveResponse(true, "");
	die();
}

function ConfirmPersons(){
	
	$PersonID = $_POST["PersonID"];
	$mode = $_POST["mode"];
	
	$obj = new BSC_persons($PersonID);
	$obj->IsActive = $mode == "1" ? "YES" : "NO";
	$obj->EditPerson();
	
	echo Response::createObjectiveResponse(true, "");
	die();
}

function selectCompanyTypes(){
	
	$temp = PdoDataAccess::runquery("select * from BaseInfo where typeID=14 order by InfoDesc");
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

//............................................................

function SelectSigners(){
	
	$where = "PersonID=?";
	if(isset($_SESSION["USER"]["portal"]))
		$param = array($_SESSION["USER"]["PersonID"]);
	else
		$param = array($_REQUEST["PersonID"]);
	
	$temp = BSC_OrgSigners::GetAll($where, $param);
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SaveSigner(){
	
	$obj = new BSC_OrgSigners();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if(isset($_SESSION["USER"]["portal"]))
		$obj->PersonID = $_SESSION["USER"]["PersonID"];
	
	if($obj->RowID > 0)
		$result = $obj->EditSigner();
	else 
		$result = $obj->AddSigner();
	
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteSigner(){
	
	$result = BSC_OrgSigners::DeleteSigner($_POST["RowID"]);
	echo Response::createObjectiveResponse($result, "");
	die();
}
//............................................................

function SelectLicenses(){
	$where = "PersonID=?";
	if(isset($_SESSION["USER"]["portal"]))
		$param = array($_SESSION["USER"]["PersonID"]);
	else
		$param = array($_REQUEST["PersonID"]);
	
	$temp = BSC_licenses::GetAll($where, $param);
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SaveLicense(){
	
	$obj = new BSC_licenses();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if(isset($_SESSION["USER"]["portal"]))
		$obj->PersonID = $_SESSION["USER"]["PersonID"];
	
	$obj->IsConfirm = "NOTSET";
		
	if($obj->LicenseID > 0)
		$result = $obj->EditLicense();
	else 
		$result = $obj->AddLicense();
	
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteLicense(){
	
	$result = BSC_licenses::DeleteLicense($_POST["LicenseID"]);
	echo Response::createObjectiveResponse($result, "");
	die();
}

function ConfirmLicense(){
	
	$obj = new BSC_licenses();
	
	$obj->LicenseID = $_POST["LicenseID"];
	$obj->IsConfirm = $_POST["mode"];
	$obj->ConfirmPersonID = $_SESSION["USER"]["PersonID"];
	$obj->RejectDesc = $_POST["RejectDesc"];
	
	$result = $obj->EditLicense();
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, "");
	die();
}

//............................................................

function selectCities(){
	
	$temp = PdoDataAccess::runquery("select * from BaseInfo where typeID=15 order by InfoDesc");
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}


?>
