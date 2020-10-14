<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------

require_once '../header.inc.php';
require_once 'AgencyCnt.class.php';
require_once inc_dataReader;
require_once inc_response;



$task = isset($_REQUEST ["task"]) ? $_REQUEST ["task"] : "";

if(!empty($task)) 
	$task();

function selectAgencyTypes(){

    $where = "";
    $param = [];
    if(!empty($_REQUEST["InfoID"]))
    {
        $where .= " AND InfoID=:p";
        $param[":p"] = $_REQUEST["InfoID"];
    }
    $dt = PdoDataAccess::runquery("select * from BaseInfo 
		where typeID=8 AND IsActive='YES' AND param1=18" . $where . " order by InfoID",$param);
    /*var_dump($dt);*/
    echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
    die();
}

function selectReceiptTypes(){
    $where = "";
    $param = [];
    if(!empty($_REQUEST["InfoID"]))
    {
        $where .= " AND InfoID=:p";
        $param[":p"] = $_REQUEST["InfoID"];
    }
    $dt = PdoDataAccess::runquery("select * from BaseInfo 
		where typeID=8 AND IsActive='YES' AND param1=18" . $where . " order by InfoID",$param);
    /*var_dump($dt);*/
    echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
    die();
}

function SelectAgencyCnt() {
	
	$where = "";
	$params = array();
	/*var_dump($_REQUEST);*/
	if(!empty($_REQUEST["agencyCntID"]))
	{
		$where .= " AND agencyCntID=:c";
		$params[":c"] = $_REQUEST["agencyCntID"];
	}
	
	if(!empty($_REQUEST["AgencyID"]))
	{
		$where .= " AND b1.InfoID=:ct";
		$params[":ct"] = $_REQUEST["AgencyID"];
	}
	
    $temp = agencyContract::Get($where, $params, dataReader::makeOrder());
	/*var_dump($temp);*/
	$res = PdoDataAccess::fetchAll($temp, $_GET['start'], $_GET['limit']);
	
    echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
    die();
}

function DeleteAgencyCnt(){
    $pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();

    $obj = new agencyContract($_POST['agencyCntID']);
    /*var_dump($obj);*/
    $result = $obj->Remove();
    if(!$result)
    {
        $pdo->rollBack();
        //print_r(ExceptionHandler::PopAllExceptions());
        //echo PdoDataAccess::GetLatestQueryString();
        echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
        die();
    }
    $pdo->commit();
    echo Response::createObjectiveResponse(true, '');
    die();
}

function SaveAgencyCnt() {

    $pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();

    /*if($_POST['ContractType'] == 3){
        $_POST['content']='در قرارداد عاملیت، اطلاعات قرارداد به صورت پیوست قرار گرفته است.';
        $_POST['TemplateID']=1;
    }
    if($_POST['ContractType'] == 4){
        $_POST['content']='در قرارداد پیمانکاران، اطلاعات قرارداد به صورت پیوست قرار گرفته است.';
        $_POST['TemplateID']=2;
    }*/

    $CntObj = new agencyContract();
    /*var_dump($_POST);
    var_dump($CntObj);*/
    $fill = PdoDataAccess::FillObjectByArray($CntObj, $_POST);
    /*echo '<br>';
    echo '<br>';
    var_dump($fill);
    echo '<br>';
    echo '<br>';*/

    if ($_POST["agencyCntID"] == "")
    {
        /*$CntObj->RegPersonID = $_SESSION['USER']["PersonID"];
        $CntObj->RegDate = PDONOW;
        $CntObj->StatusID = CNT_STEPID_RAW;*/
        $result = $CntObj->Add($pdo);
    }
    else
    {
        $result = $CntObj->Edit($pdo);

        /* removing values of contract items */
        /*CNT_ContractItems::RemoveAll($CntObj->ContractID, $pdo);*/
    }

    if(!$result)
    {
        $pdo->rollBack();
        print_r(ExceptionHandler::PopAllExceptions());
        //echo PdoDataAccess::GetLatestQueryString();
        echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
        die();
    }

    $pdo->commit();
    echo Response::createObjectiveResponse(true, $CntObj->orgDocID);
    die();
}















function GetContractItems() {
    $res = CNT_ContractItems::GetContractItems($_REQUEST['ContractID']);
    echo dataReader::getJsonData($res, count($res), $_GET["callback"]);
    die();
}

function DeleteContract(){
	
	$pdo = PdoDataAccess::getPdoObject();
    $pdo->beginTransaction();
	
	$obj = new CNT_contracts($_POST['ContractID']);
	$result = CNT_ContractItems::RemoveAll($obj->ContractID, $pdo);
	$result = $obj->Remove();
	if(!$result)
	{
		$pdo->rollBack();
		//print_r(ExceptionHandler::PopAllExceptions());
		//echo PdoDataAccess::GetLatestQueryString();
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	$pdo->commit();
	echo Response::createObjectiveResponse(true, '');
	die();
	
}

function SelectContractTypes() {
   
	$dt = PdoDataAccess::runquery("select * from BaseInfo where TypeID=18 AND IsActive='YES' order by param1");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function StartFlow(){
	
	$obj = new CNT_contracts($_POST["ContractID"]);
	$obj->content = $obj->GetContractContext();
	$obj->StatusID = 0;
	if(!$obj->Edit())
	{
		echo Response::createObjectiveResponse(false, '');
		die();
	}
	
	$result = WFM_FlowRows::StartFlow(FLOWID_CONTRACT, $obj->ContractID);
	
	echo Response::createObjectiveResponse($result, '');
	die();
}
//------------------------------------------------

function GetSigns(){
	
	$temp = CNT_ContractSigns::Get(" AND ContractID=?", array($_REQUEST["ContractID"]));
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function SaveSign(){
	
	$obj = new CNT_ContractSigns();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if(empty($obj->SignerPost) && !empty($obj->PersonID))
	{
		$dt = PdoDataAccess::runquery("select PostName from BSC_persons p join BSC_posts using(PostID)
			where p.PersonID=" . $obj->PersonID);
		if(count($dt) > 0)
			$obj->SignerPost = $dt[0][0];
	}
	
	if(empty($obj->SignID))
		$result = $obj->Add();
	else
		$result = $obj->Edit();

	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();
}

function DeleteSign(){
	
	$obj = new CNT_ContractSigns();
	$obj->SignID = $_POST["SignID"];
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();	
}

?>

