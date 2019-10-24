<?php
require_once '../header.inc.php';
require_once 'ManageGroup.class.php';
//require_once '../class/study_branch.class.php';
require_once(inc_response);
require_once inc_dataReader;
require_once inc_PDODataAccess;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task) {
	

	case "SelectGrop":
		  SelectGrop();

	case "SaveGrp":
          SaveGrp();

	case "removeGrp":
		  removeGrp();
          
    case "SelectMembers":
          SelectMembers();

    case "SaveMember":
          SaveMember();

    case "removeMember":
          removeMember();
        
    case "SelectMyMessage" :
          SelectMyMessage();
        
    case "SelectMessageGrp" :
          SelectMessageGrp();
        
    case "SaveMsg" :
          SaveMsg();
	      
}

function SelectGrop()
{
    
	//.................. secure section .....................
	
	if (!empty($_REQUEST["fields"]) && !InputValidation::validate($_REQUEST["fields"], InputValidation::Pattern_EnAlphaNum, false)) {
		echo dataReader::getJsonData(array(), 0);
		die();
	}
	if (!empty($_REQUEST["query"]) && !InputValidation::validate($_REQUEST["query"], InputValidation::Pattern_FaEnAlphaNum, false)) {
		echo dataReader::getJsonData(array(), 0);
		die();
	}
	//.......................................................

	$where = " (1=1) ";
	$whereParam = array();

	$field = isset ( $_GET ["fields"] ) ? $_GET ["fields"] : "";

	if (isset ( $_GET ["query"] ) && $_GET ["query"] != "") {
			switch ( $field) {
				case "GroupTitle" :
					$where .= " AND GroupTitle LIKE :qry " ;
					$whereParam[":qry"] = "%" . $_GET["query"] . "%";

				break;
				case "GID" :
					$where .= " AND GID = :qry " ;
					$whereParam[":qry"] = $_GET["query"] ;

				break;
				
			}
		}

	$where .=  dataReader::makeOrder(); 

	$temp = manage_msg_group::GetAll($where,$whereParam);
	$no = count($temp);
        //..........................secure section ........................
        $start = (int)$_GET["start"] ;
        $limit = (int)$_GET["limit"] ;
        if(!InputValidation::validate($_GET["callback"], InputValidation::Pattern_EnAlphaNum, false))
        {
            echo dataReader::getJsonData(array(), 0);
            die();
        }
        //................................................................
        $temp = array_slice($temp,$start,$limit);
	
	echo dataReader::getJsonData ($temp, $no, $_GET ["callback"] );
	die ();
}

function SaveGrp()
{
	$obj = new manage_msg_group();
    PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if(empty($obj->GID))
		$return = $obj->Add();
	else
		$return = $obj->Edit();
	if(!$return)
	{
		echo Response::createObjectiveResponse($return, ExceptionHandler::GetExceptionsToString());
		die();
	}
	echo Response::createObjectiveResponse(true, "");
	die();
		
}

function removeGrp()
{
    //.................. secure section .....................
    if (!InputValidation::validate($_POST["GID"], InputValidation::Pattern_Num, false)) 
    {
            echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
            die();
    }
    //.......................................................

	$return = manage_msg_group::Remove($_POST["GID"]);
	Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}

function SelectMembers()
{    
	//.................. secure section .....................
	
	if (!empty($_REQUEST["GID"]) && !InputValidation::validate($_REQUEST["GID"], InputValidation::Pattern_Num, false)) {
		echo dataReader::getJsonData(array(), 0);
		die();
	}
	
	//.......................................................

	$where = " GID =". $_GET['GID'];
	$where .=  dataReader::makeOrder();
    
    $temp = manage_msg_members::GetAll($where);
   
	$no = count($temp);
        //..........................secure section ........................
        $start = (int)$_GET["start"] ;
        $limit = (int)$_GET["limit"] ;
        if(!InputValidation::validate($_GET["callback"], InputValidation::Pattern_EnAlphaNum, false))
        {
            echo dataReader::getJsonData(array(), 0);
            die();
        }
        //................................................................
        $temp = array_slice($temp,$start,$limit);

	echo dataReader::getJsonData ($temp, $no, $_GET ["callback"] );
	die ();
}


function SaveMember()
{ 
	$obj = new manage_msg_members();   
   
    PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	if(empty($obj->MID))
		$return = $obj->Add();
	else
		$return = $obj->Edit();
	if(!$return)
	{
		echo Response::createObjectiveResponse($return, ExceptionHandler::GetExceptionsToString());
		die();
	}
	echo Response::createObjectiveResponse(true, "");
	die();

}



function removeMember()
{
   
    //.................. secure section .....................
    if (!InputValidation::validate($_POST["MID"], InputValidation::Pattern_Num, false)) 
    {
            echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
            die();
    }
    if (!InputValidation::validate($_POST["GID"], InputValidation::Pattern_Num, false)) 
    {
            echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
            die();
    }
    //.......................................................

    $return = manage_msg_members::Remove($_POST["GID"],$_POST["MID"]);
	Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}

function SelectMyMessage()
{    
    $where = "" ; 
    $whereParam = array(":PID" => $_SESSION["USER"]["PersonID"] );
    
	$temp = manage_msg_members::GetAllMyMessage($where,$whereParam);
 
	$no = count($temp);
        //..........................secure section ........................
        $start = (int)$_GET["start"] ;
        $limit = (int)$_GET["limit"] ;
        if(!InputValidation::validate($_GET["callback"], InputValidation::Pattern_EnAlphaNum, false))
        {
            echo dataReader::getJsonData(array(), 0);
            die();
        }
        //................................................................
        $temp = array_slice($temp,$start,$limit);
	
	echo dataReader::getJsonData ($temp, $no, $_GET ["callback"] );
	die ();
}

function SelectMessageGrp()
{    
    $where = "" ; 
    $whereParam = array(); array(":PID" => $_SESSION["USER"]["PersonID"] );
    
	$temp = manage_msg_members::GetAllGroupMessage($where,$whereParam);

	$no = count($temp);
        //..........................secure section ........................
        $start = (int)$_GET["start"] ;
        $limit = (int)$_GET["limit"] ;
        if(!InputValidation::validate($_GET["callback"], InputValidation::Pattern_EnAlphaNum, false))
        {
            echo dataReader::getJsonData(array(), 0);
            die();
        }
        //................................................................
        $temp = array_slice($temp,$start,$limit);
	
	echo dataReader::getJsonData ($temp, $no, $_GET ["callback"] );
	die ();
}

function SaveMsg()
{
	echo "*********"; die();
    /*$obj = new manage_msg_group();
    PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if(empty($obj->GID))
		$return = $obj->Add();
	else
		$return = $obj->Edit();
	if(!$return)
	{
		echo Response::createObjectiveResponse($return, ExceptionHandler::GetExceptionsToString());
		die();
	}
	echo Response::createObjectiveResponse(true, "");
	die();*/
		
}


	