<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------

require_once '../../../header.inc.php';
require_once '../class/dependent.class.php';
require_once '../class/dependent_support.class.php';
require_once(inc_response);
require_once inc_dataReader;
require_once inc_PDODataAccess;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task) {
	
	case "saveDependent";
	      saveDepData();
	
	case "selectDep":
		  selectDep();
		  
	case "DelDependent":
		  DelDep();

	case "selectDepSupport":
		  selectDepSupp();

	case "saveDepSupport":
		saveDepSupport();

	case "removeDepSupport":
		removeDepSupport();

    case "SelectPersonDependents" :
		  SelectPersonDependents();

	case "transferAction" :
		 transferAction();
}

function transferAction()
{
	$selected_writs = array();
	$keys = array_keys($_POST);
	for($i=0; $i < count($_POST); $i++)
	{
		if(strpos($keys[$i],"chk_") !== false)
		{
			$arr = preg_split('/_/', $keys[$i]);
			$selected_writs[] = array("PersonID" => $arr[1] ,
									  "staff_id" => $arr[2]);
		}
	}

	$return = true;

	$Pids = ''; 
	for($i=0; $i < count($selected_writs); $i++)
	{		
		$Pids .= $selected_writs[$i]["PersonID"]."," ;
		
	}

	$Pids = substr($Pids, 0, -1);
	
	if($Pids != '') {
		if( manage_person_dependency::Change_State_Dep($Pids) == false)
			$return = false;

		if(	manage_person_dependency::Delete_Dependent_Supports($Pids) == false)
			$return = false;

			}

	if($return)
		echo "true";
	else
		print_r(ExceptionHandler::PopAllExceptions());
	die();
	
}

function SelectPersonDependents()
{
	$where = "1=1";
	$whereParam = array();
	//.................................
	if(!empty($_REQUEST["from_StaffID"]))
	{
		$where .= " AND s.staff_id>=:fpid";
		$whereParam[":fpid"] = $_REQUEST["from_StaffID"];
	}

	if(!empty($_REQUEST["to_StaffID"]))
	{
		$where .= " AND s.staff_id<=:tpid";
		$whereParam[":tpid"] = $_REQUEST["to_StaffID"];
	}
	if(!empty($_REQUEST["pfname"]))
	{
		$where .= " AND p.pfname like :pfname";
		$whereParam[":pfname"] = "%" . $_REQUEST["pfname"] . "%";
	}
	if(!empty($_REQUEST["plname"]))
	{
		$where .= " AND p.plname like :plname";
		$whereParam[":plname"] = "%" . $_REQUEST["plname"] . "%";
	}

	$no = count(manage_person_dependency::GetAllDepSupports($where, $whereParam));
	
	$temp = manage_person_dependency::GetAllDepSupports($where, $whereParam);

	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die ();
}

function selectDep()
{ 
			
	$where = " pd.PersonID = :PID ";
	$whereParam[":PID"] = $_GET["Q0"];

	$no = manage_person_dependency::CountDependency($where, $whereParam);
	
	$where .=  dataReader::makeOrder(); 
	$where .= isset($_GET ["start"]) ? " limit " . $_GET ["start"] . "," . $_GET ["limit"] : "";
	
	$temp = manage_person_dependency::GetAllDependency($where,$whereParam);
	
	echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
	die();
}

function saveDepData()
{  
	
    $obj = new manage_person_dependency();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	$obj->PersonID = $_POST['PersonID'];
	
	if(empty($_POST["row_no"]))
		$return = $obj->AddDependency();
	else 
		$return = $obj->EditDependency();
	
	echo $return ? Response::createObjectiveResponse(true,$obj->row_no) :
				   Response::createObjectiveResponse(false,ExceptionHandler::GetExceptionsToString("\n"));
	die();
 }

function DelDep()
{
	$return = manage_person_dependency::RemoveDependency($_POST['PersonID'], $_POST['row_no']);
	Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
		
}

function selectDepSupp()
{ 
	
	$where = " dsh.PersonID = :PID AND dsh.master_row_no = :RNO ";
	$info ="";
	if(isset($_GET["info"])){

		if($_GET["info"]==1){
			
			$whereParam[":RNO"] = $_GET["row_no"];
			$whereParam[":PID"] = $_GET["PID"];
			$info = "true";
		}
		
	}
	else {
		
		$whereParam[":RNO"] = $_GET["master_row_no"];
		$whereParam[":PID"] = $_GET["personid"];		
	}

	$temp = manage_dependent_support::GetAllDependencySupport($where, $whereParam,$info);

	echo dataReader::getJsonData($temp, count($temp), $_GET ["callback"]);
	die();
}

function saveDepSupport()
{
	$obj = new manage_dependent_support();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->row_no == "")
		$return = $obj->Add();
	else
		$return = $obj->Edit();
	
	echo $return ? "true" : "false";
	print_r(ExceptionHandler::PopAllExceptions());
	die();
}

function removeDepSupport()
{
	$obj = new manage_dependent_support();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	echo $obj->Remove() ? "true" : "false";
	die();
}
?>